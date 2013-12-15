<?php

/**
 * @package		Summy
 * @author		Christodoulos Tsoulloftas
 * @copyright	Copyright 2011-2013, http://www.komposta.net
 */
namespace Summy\Filter;

abstract class StopWords
{
	/**
	 * An array list of stop words
	 * @var array
	 */
	protected $_stopWords = array();

	/**
	 * Public constructor
	 * Flip stop words to be able to check with isset instead of in_array
	 */
	public function __construct()
	{
		$this->_stopWords = array_flip($this->_stopWords);
	}

	/**
	 * Filter words based on the stop words list
	 * @param string $value
	 * @return string|false
	 */
	public function filter($value)
	{
		return isset($this->_stopWords[$value]) ? false : $value;
	}
}