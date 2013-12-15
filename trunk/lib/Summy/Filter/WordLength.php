<?php

/**
 * @package		Summy
 * @author		Christodoulos Tsoulloftas
 * @copyright	Copyright 2011-2013, http://www.komposta.net
 */
namespace Summy\Filter;

class WordLength
{

	/**
	 * Minimum allowed length for valid terms/stems
	 * @var integer
	 */
	protected $_limit = null;

	/**
	 * Public constructor, set minimum limit
	 * @param int $limit
	 */
	public function __construct($limit = 3)
	{
		$this->_limit = $limit;
	}

	/**
	 * Filter string based on its length
	 * @param string $value
	 * @return string|false
	 */
	public function filter($value)
	{
		return ($this->_limit && mb_strlen($value, 'UTF-8') <= $this->_limit) ? false : $value;
	}
}