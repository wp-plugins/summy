<?php

/**
 * @package		Summy
 * @version		$Id: Text.php 128 2013-03-17 23:44:07Z Tefra $
 * @author		Christodoulos Tsoulloftas
 * @copyright	Copyright 2011-2013, http://www.komposta.net
 */
namespace Summy\Filter\En;

class Text
{

	/**
	 * Normalize english text:
	 * - Make paragraphs from html
	 * - Strip html tags
	 * - Convert/clean UTF-8 encoding
	 * - Decode html entities
	 * - Remove wikipedia type references eg [4]
	 * - Attempt to kill most common abbreviations like on leter abbr or letter by letter abbrs
	 * @param string $string
	 * @return string
	 */
	public function clear($string)
	{
		if($string == '')
		{
			return $string;
		}

		// Make Paragraphs from html
		$string = str_replace('</p>', "\n", $string);
		// Remove html tags
		$string = strip_tags($string);
		// Convert-Clean text to utf-8 encoding
		$string = mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string, 'UTF-8, ISO-8859-1', true));
		// Decode html entities
		$string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
		// Kill the wikipedia numerical references e.g. [4]
		$string = preg_replace('/\[\d+\]/', '', $string);
		// Kill one letter abbreviations e.g. Chris T. => Chris T
		$string = preg_replace('/(\s)(\p{L})\.(\s)/u', '$1$2$3', $string);
		// Kill the most common abbreviations periods with more than 1 period
		$tokens = explode(" ", $string);
		$string = '';
		foreach($tokens AS $token)
		{
			$dotCount = substr_count($token, '.');
			if($dotCount > 1)
			{
				$token = str_replace('.', '', $token);
			}
			else if($dotCount == 1)
			{
				$token = str_replace('.', '. ', $token);
			}
			$string .= $token . ' ';
		}
		return $string;
	}

	/**
	 * Sentence filtering
	 * - Upper case letters
	 * - Remove words-breaking over new lines
	 * - Trim empty space
	 * @param string $value
	 * @return string
	 */
	public function process($value)
	{
		// Uppercase
		$value = mb_strtoupper($value, 'UTF-8');
		// Remove dots from abbreviations
		$value = str_replace('.', '', $value);
		// Remove words-breaking over new lines
		$value = str_replace(array("-\n", "\r"), '', $value);
		// Remove any non alphabetic characters
		$value = preg_replace('/[^A-Z]/u', ' ', $value);
		// Remove whitespace, spaces new lines etc...
		return trim($value);
	}
}

?>