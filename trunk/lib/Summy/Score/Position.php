<?php

/**
 * @package		Summy
 * @author		Christodoulos Tsoulloftas
 * @copyright	Copyright 2011-2013, http://www.komposta.net
 */
namespace Summy\Score;

class Position
{

	/**
	 * Shortcut method kinda...
	 * @param string $method
	 * @param integer $totalParagraphs
	 * @param integer $sentencesInParagraph
	 * @param integer $paragraph
	 * @param integer $sentence
	 * @return float
	 */
	public function score($method, $totalParagraphs, $sentencesInParagraph, $paragraph, $sentence)
	{
		if(method_exists($this, $method))
		{
			return $this->{$method}($totalParagraphs, $sentencesInParagraph, $paragraph, $sentence);
		}
	}

	/**
	 * Returns the Position Weight for a sentence based on the Baxendale investigation
	 *
	 * Baxendale (1958) investigated a sample of 200 paragraphs to determine
	 * where the important words are most likely to be found. He concluded that
	 * in 85% of the paragraphs,  the first sentence was a topic sentence and in
	 * 7% of the paragraphs, the final one.
	 *
	 * @param integer $totalParagraphs
	 * @param integer $sentencesInParagraph
	 * @param integer $paragraph
	 * @param integer $sentence
	 * @return float
	 */
	public function baxendale($totalParagraphs, $sentencesInParagraph, $paragraph, $sentence)
	{
		switch(true)
		{
			case ($sentence == 1):
			case ($sentence == $sentencesInParagraph):
				return 0.5;
				break;
			default:
				return 0;
				break;
		}
	}

	/**
	 * Returns the Position Weight for a sentence based on the hypothesis that
	 * first paragraphs/sentences are the most meaningful to a document, which
	 * applies to small articles, like newspaper news.
	 *
	 * @param integer $totalParagraphs
	 * @param integer $sentencesInParagraph
	 * @param integer $paragraph
	 * @param integer $sentence
	 * @return float
	 */
	public function article($totalParagraphs, $sentencesInParagraph, $paragraph, $sentence)
	{
		return (($totalParagraphs - $paragraph + 1) / $totalParagraphs) * (($sentencesInParagraph - $sentence + 1) / $sentencesInParagraph);
	}
}