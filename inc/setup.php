<?php

/**
 * @package		WP-Summy
 * @author		Christodoulos Tsoulloftas
 * @copyright   Copyright 2013, http://www.komposta.net
 */
defined('ABSPATH') || die('Nothing Here...');

/**
 * Delete options
 */
function summy_deactivate()
{
	delete_option('summy');
}

/**
 * Add options
 */
function summy_activate()
{
	update_option('summy', array(
		'rate' => 20,
		'language' => 'en',
		'termScore' => 'tfisf',
		'positionScore' => 'news',
		'minWordsLimit' => 6,
		'maxWordsLimit' => 20,
		'TW' => 1,
		'PW' => 1,
		'KW' => 1
	));
}

?>