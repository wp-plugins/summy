<?php

/*
  Plugin Name: Summy: Except Extraction
  Plugin URI: http://www.komposta.net/article/wp-summy
  Description: Summy can generate excerpts for your posts by applying various algorithms for automatic summarization extraction.
  Version: 1.0
  Author: Christodoulos Tsoulloftas
  Author URI: http://www.komposta.net
  ---------------------------------------------------------------------------------
  Copyright 2013  Christodoulos Tsoulloftas  (http://www.komposta.net/core/contact)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined('ABSPATH') || die('Nothing Here...');

add_action('add_meta_boxes', function() {
	require_once dirname(__FILE__) . '/inc/ui.php';
});

add_action('wp_ajax_summy', function() {
	require_once dirname(__FILE__) . '/inc/backend.php';
});

register_deactivation_hook(__FILE__, function() {
	require_once dirname(__FILE__) . '/inc/setup.php';
	summy_deactivate();
});

register_activation_hook(__FILE__, function() {
	require_once dirname(__FILE__) . '/inc/setup.php';
	summy_activate();
});

?>