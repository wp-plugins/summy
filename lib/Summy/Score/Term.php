<?php

/**
 * @package		Summy
 * @author		Christodoulos Tsoulloftas
 * @copyright	Copyright 2011-2013, http://www.komposta.net
 */
namespace Summy\Score;

use Exception;

class Term
{

	/**
	 * Language identifier (gr,en)
	 * @var string
	 */
	private $_language = null;

	/**
	 * Database adapter
	 * @var object
	 */
	private $_dbAdapter = null;

	/**
	 * Public constructor: initialize language and db adapter
	 * @param string $language
	 * @param object $dbAdapter
	 */
	public function __construct($language, $dbAdapter = null)
	{
		$this->setLanguage($language);
		$this->setAdapter($dbAdapter);
	}

	/**
	 * Return the db adapter or throw an exception if it's not set
	 * @return object
	 * @throws Exception
	 */
	public function getAdapter()
	{
		if($this->_dbAdapter === null)
		{
			throw new Exception('DB Adapter hasn\'t been set');
		}
		return $this->_dbAdapter;
	}

	/**
	 * Set the db adapter
	 * @param type $dbAdapter
	 * @return \Summy\Score\Term
	 */
	public function setAdapter($dbAdapter)
	{
		$this->_dbAdapter = $dbAdapter;
		return $this;
	}

	/**
	 * Return the language identifier
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->_language;
	}

	/**
	 * Set the language identifier
	 * @param string $language
	 * @return \Summy\Score\Term
	 */
	public function setLanguage($language)
	{
		$this->_language = $language;
		return $this;
	}

	/**
	 * Shortcut method kinda...
	 * @param string $method
	 * @param integer $totalSentences
	 * @param array $tf
	 * @param array $sf
	 * @return array
	 */
	public function score($method, $totalSentences, $tf = array(), $sf = array())
	{
		return method_exists($this, $method) ? $this->{$method}($totalSentences, $tf, $sf) : array();
	}

	/**
	 * Rank sentences by TF-ISF http://www.waset.org/journals/waset/v53/v53-47.pdf
	 * ISF = log(S/SF)
	 * S =  number of sentences in a news article
	 * SF = number of sentences the word can occurs
	 *
	 * @param integer $totalSentences
	 * @param array $tf
	 * @param array $sf
	 * @return array
	 */
	public function tfisf($totalSentences, $tf = array(), $sf = array())
	{
		$wordScore = array();
		$totalWords = array_sum($tf);
		foreach($tf AS $word => $frequency)
		{
			// Normalized frequency
			$_tf = $frequency / $totalWords;
			$_isf = log($totalSentences / count($sf[$word]));
			// TF * ISF
			$wordScore[$word] = $_tf * $_isf;
		}
		return $wordScore;
	}

	/**
	 * Rank sentences by TF-IDF http://en.wikipedia.org/wiki/Tf-idf
	 *
	 * @param integer $totalSentences
	 * @param array $tf
	 * @param array $sf
	 * @return array
	 */
	public function tfidf($totalSentences, $tf = array(), $sf = array())
	{
		$docFreq = array();
		$wordScore = array();
		$totalWords = array_sum($tf);
		$db = $this->getAdapter();
		$language = $this->getLanguage();
		$sql = $db->query("SELECT COUNT(*) as total FROM document WHERE language = ?", array($language))->current();
		$totalDocuments = $sql['total'] + 1;
		$terms = implode(',', array_map(array($db->driver->getConnection()->getResource(), 'quote'), array_keys($tf)));
		$sql = $db->query("SELECT term, documents FROM term WHERE term IN ({$terms}) AND language = ?", array($language));
		foreach($sql AS $row)
		{
			$docFreq[$row['term']] = $row['documents'];
		}

		foreach($tf AS $word => $frequency)
		{
			$docFreq[$word] = isset($docFreq[$word]) ? $docFreq[$word] + 1 : 1;
			// Normalized frequency
			$_tf = $frequency / $totalWords;
			$_idf = log($totalDocuments / $docFreq[$word]);

			// TF * IDF
			$wordScore[$word] = $_tf * $_idf;
		}
		return $wordScore;
	}

	/**
	 * Rank sentences by TF-RIDF http://citeseer.ist.psu.edu/viewdoc/download?doi=10.1.1.112.3361&rep=rep1&type=pdf
	 * RIDF = IDF − expIDF
	 * expIDF = −log(1 − e^(−fw/D))
	 *
	 * @param integer $totalSentences
	 * @param array $tf
	 * @param array $sf
	 * @return array
	 */
	public function tfridf($totalSentences, $tf = array(), $sf = array())
	{
		$wordScore = $docFreq = $totFreq = array();
		$totalWords = array_sum($tf);
		$db = $this->getAdapter();
		$language = $this->getLanguage();
		$sql = $db->query("SELECT COUNT(*) as total FROM document WHERE language = ?", array($language))->current();
		$totalDocuments = $sql['total'] + 1;
		$terms = implode(',', array_map(array($db->driver->getConnection()->getResource(), 'quote'), array_keys($tf)));
		$sql = $db->query("SELECT term, frequency, documents FROM term WHERE term IN ({$terms}) AND language = ?", array($language));

		foreach($sql AS $row)
		{
			$docFreq[$row['term']] = $row['documents'];
			$totFreq[$row['term']] = $row['frequency'];
		}

		foreach($tf AS $word => $frequency)
		{
			$docFreq[$word] = isset($docFreq[$word]) ? $docFreq[$word] + 1 : 1;
			$totFreq[$word] = isset($totFreq[$word]) ? $totFreq[$word] + $frequency : $frequency;
			// Normalized frequency
			$_tf = $frequency / $totalWords;
			$_idf = log($totalDocuments / $docFreq[$word]);
			$_expIdf = - log(1 - exp(- $totFreq[$word] / $totalDocuments));
			// TF * RIDF
			$wordScore[$word] = $_tf * ($_idf - $_expIdf);
		}
		return $wordScore;
	}
}