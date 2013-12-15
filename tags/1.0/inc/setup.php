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
	/** 
	 * @todo this won't work you idiot...
	 */
	if(version_compare(PHP_VERSION, '5.3', '<'))
	{
		load_plugin_textdomain('summy', false, 'summy/lang');
		$message = __('WP-Summy requires PHP version 5.3 or greater.', 'summy');
		deactivate_plugins(dirname(__FILE__));
		wp_die($message, '', array('response' => 200, 'back_link' => true));
	}

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