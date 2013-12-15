<?php

/**
 * @package		Summy
 * @author		Christodoulos Tsoulloftas
 * @copyright	Copyright 2011-2013, http://www.komposta.net
 */
namespace Summy;

use Summy\Score\Position;
use Summy\Score\Term;

class Core
{

	/**
	 * List of skipped words with their frequency
	 * @var array
	 */
	public $stoppedWords = array();

	/**
	 * List of the original words and their stems
	 * @var array
	 */
	public $stemmedWords = array();

	/**
	 * Word counter for each sentence
	 * @var array
	 */
	public $sentenceWordCount = array();

	/**
	 * Total number of words including the stopped/skipped terms
	 * @var integer
	 */
	public $totalWords = 0;

	/**
	 * Total number of sentences
	 * @var integer
	 */
	public $totalSentences = 0;

	/**
	 * Total number of paragraphs
	 * @var integer
	 */
	public $totalParagraphs = 0;

	/**
	 * List of valid stems with their scores
	 * @var array
	 */
	public $scoredWords = array();

	/**
	 * Original document title or keywords
	 * @var string
	 */
	public $title = null;

	/**
	 * Title tokenized to valid stems
	 * @var array
	 */
	public $keywords = array();

	/**
	 * Original text with proper utf8 encoding
	 * @var string
	 */
	public $text = null;

	/**
	 * List of the sentence indexes that have been choosen as the summary
	 * @var array
	 */
	public $summarySentences = array();

	/**
	 * Original Sentences list
	 * @var array
	 */
	public $sentences = array();

	/**
	 * List of the sentences per paragraph
	 * @var array
	 */
	public $paragraphSentencesIndex = array();

	/**
	 * List of words per sentence
	 * @var array
	 */
	public $sentenceWordsIndex = array();

	/**
	 * Unsorted list of sentences scores
	 * @var array
	 */
	public $sentenceScores = array();

	/**
	 * Complete list of all score types for each sentence
	 * eg 0 => array('TS' => 2.1554, 'PS' => 1, 'KW' => 0.5)
	 * @var array
	 */
	public $detailedSentenceScores = array();

	/**
	 * Term frequency
	 * @var array
	 */
	public $termFrequency = array();

	/**
	 * Sentence frequency
	 * @var array
	 */
	public $sentenceFrequency = array();

	/**
	 * Database Object
	 * @var object
	 */
	public $dbAdapter = null;

	/**
	 * Default configuration
	 * @var array
	 */
	public $config = array();

	/**
	 * Summarizer constructor pass values, overwrite default config
	 *
	 * @param string $text
	 * @param string $title
	 * @param array $config
	 */
	public function __construct($text, $title = '', $config = array())
	{
		if(array_key_exists('adapter', $config))
		{
			$this->dbAdapter = $config['adapter'];
			unset($config['adapter']);
		}

		$default = array(
			'rate' => 20,
			'language' => 'gr',
			'termScore' => 'tfisf',
			'positionScore' => 'article',
			'minWordsLimit' => 6,
			'maxWordsLimit' => 20,
			'TW' => 1,
			'PW' => 1,
			'KW' => 1
		);

		$this->config = $config + $default;
		$textFilter = self::instance('filter', $this->config['language'], 'text');
		$this->text = $textFilter->clear($text);
		$this->title = $textFilter->clear($title);
	}

	/**
	 * Breaks down text into stems and their frequency.
	 * It is used during the internal build of terms library
	 * @param string $text
	 * @param string $language
	 * @return array
	 */
	public static function processDocument($text, $language)
	{
		$result = array();
		$textFilter = self::instance('filter', $language, 'text');
		$wordLength = self::instance('filter', 'wordLength');
		$stopWords = self::instance('filter', $language, 'stopWords');
		$stemmer = self::instance('filter', $language, 'stemmer');
		$wordFilters = compact('stopWords', 'stemmer', 'wordLength');

		$text = $textFilter->clear($text);
		$text = $textFilter->process($text);
		$terms = array_filter(explode(" ", $text));
		foreach($terms AS $term)
		{
			foreach($wordFilters AS $filter)
			{
				$term = $filter->filter($term);
				if($term === false)
				{
					break;
				}
			}

			if($term !== false)
			{
				$result[$term] = isset($result[$term]) ? $result[$term] + 1 : 1;
			}
		}
		return $result;
	}

	/**
	 * Run the summarizer logic and return true/false on success/failure
	 * @return bool
	 */
	public function summarize()
	{
		$i = $j = 0;
		$text = str_replace(array("\r", "\n\n"), "\n", $this->text);
		$paragraphs = array_filter(explode("\n", $text));

		foreach($paragraphs AS $paragraph)
		{
			preg_match_all('/(.*?[.;!])(\s+|$)/', $paragraph, $matches);
			if(count($matches[1]) == 0)
			{
				continue;
			}
			foreach($matches[1] AS $sentence)
			{
				$this->sentences[$j] = $sentence;
				$this->paragraphSentencesIndex[$i][] = $j;
				$j++;
			}
			$i++;
		}

		$this->totalParagraphs = count($this->paragraphSentencesIndex);
		$this->totalSentences = count($this->sentences);
		if($this->totalSentences > 0)
		{
			$this->_calculateWordScores();
			$this->_calculateSentenceScores();
			arsort($this->sentenceScores);

			$i = 0;
			$total = ceil(($this->config['rate'] / 100) * $this->totalSentences);
			//Grab the top x sentences
			foreach($this->sentenceScores AS $index => $score)
			{
				if($score == 0 || $total == $i)
				{
					break;
				}
				$i++;
				$this->summarySentences[] = $index;
			}

			//Return false if no senteces made it to the summary
			if(empty($this->summarySentences))
			{
				return false;
			}

			//Re-sort them by original order
			sort($this->summarySentences);
			return true;
		}
		return false;
	}

	/**
	 * Process sentences, words and produce the core statistics
	 * - Loop through sentences
	 * - Extra text filtering
	 * - Build the stopped words, stemmed words, TF, SF, sentence word index, keywords and word scores lists,
	 */
	public function _calculateWordScores()
	{
		$textFilter = self::instance('filter', $this->config['language'], 'text');
		$wordLength = self::instance('filter', 'wordLength');
		$stopWords = self::instance('filter', $this->config['language'], 'stopWords');
		$stemmer = self::instance('filter', $this->config['language'], 'stemmer');
		$wordFilters = compact('stopWords', 'stemmer', 'wordLength');

		foreach($this->sentences AS $index => $sentence)
		{
			$sentence = $textFilter->process($sentence);
			$words = array_filter(explode(' ', $sentence));
			$this->totalWords += count($words);
			$this->sentenceWordsIndex[$index] = array();

			foreach($words AS $word)
			{
				$term = $word;
				foreach($wordFilters AS $filter)
				{
					$term = $filter->filter($term);
				}

				if($term === false)
				{
					$this->stoppedWords[$word] = isset($this->stoppedWords[$word]) ? $this->stoppedWords[$word] + 1 : 1;
				}
				else
				{
					$this->stemmedWords[$word] = $term;
					$this->termFrequency[$term] = isset($this->termFrequency[$term]) ? $this->termFrequency[$term] + 1 : 1;
					$this->sentenceFrequency[$term][$index] = true;
					$this->sentenceWordsIndex[$index][] = $term;
				}
			}
		}

		$title = $textFilter->process($this->title);
		$titleWords = explode(' ', $title);
		foreach($titleWords AS $word)
		{
			foreach($wordFilters AS $filter)
			{
				$word = $filter->filter($word);
			}

			if($word)
			{
				$this->keywords[$word] = true;
			}
		}

		$term = new Term($this->config['language'], $this->dbAdapter);
		$this->scoredWords = $term->score($this->config['termScore'], $this->totalSentences, $this->termFrequency, $this->sentenceFrequency);
	}

	/**
	 * Do the trivial stuff of processing the statistic data
	 * - Sum sentences words
	 * - Build the sentences word counts, sentences and detailed sentences scores
	 */
	protected function _calculateSentenceScores()
	{
		$position = new Position();
		foreach($this->paragraphSentencesIndex AS $pNo => $sentences)
		{
			$totalSentencesInParagraph = count($sentences);
			foreach($sentences AS $sNo => $sentence)
			{
				$TS = $PS = $KS = 0;
				$totalTerms = count($this->sentenceWordsIndex[$sentence]);
				if((!$this->config['minWordsLimit'] || $totalTerms >= $this->config['minWordsLimit']) && (!$this->config['maxWordsLimit'] || $totalTerms <= $this->config['maxWordsLimit']))
				{
					foreach($this->sentenceWordsIndex[$sentence] AS $word)
					{
						$TS += $this->scoredWords[$word];
						if(array_key_exists($word, $this->keywords))
						{
							$KS += 0.5;
						}
					}
					$PS += $position->score($this->config['positionScore'], $this->totalParagraphs, $totalSentencesInParagraph, $pNo + 1, $sNo + 1);
				}

				$this->sentenceWordCount[$sentence] = $totalTerms;
				$this->sentenceScores[$sentence] = $this->config['TW'] * $TS + $this->config['PW'] * $PS + $this->config['KW'] * $KS;
				$this->detailedSentenceScores[$sentence] = compact('TS', 'PS', 'KS');
			}
		}
	}

	/**
	 * Returns the summary body
	 * @return string
	 */
	public function getSummaryText()
	{
		$sentences = array_intersect_key($this->sentences, array_flip($this->summarySentences));
		return implode(' ', $sentences);
	}

	/**
	 * Returns the original text with the summary sentences highlighted
	 * @return string
	 */
	public function getDiffText()
	{
		$res = '';
		foreach($this->paragraphSentencesIndex AS $p => $sentences)
		{
			$res .= '<p>';
			foreach($sentences AS $index)
			{
				$class = in_array($index, $this->summarySentences) ? 'highlight' : 'noclass';
				$res .= '<span class="' . $class . '">' . $this->sentences[$index] . ' </span>';
			}
			$res .= '</p>';
		}
		return $res;
	}

	/**
	 *
	 * @var type
	 */
	static $instances = array();

	/**
	 *
	 * @return type
	 */
	public static function instance()
	{
		$class = 'Summy\\' . implode('\\', array_map('ucfirst', func_get_args()));
		if(!isset(self::$instances[$class]))
		{
			self::$instances[$class] = new $class();
		}
		return self::$instances[$class];
	}
}