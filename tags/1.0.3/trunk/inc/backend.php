<?php

/**
 * @package		WP-Summy
 * @author		Christodoulos Tsoulloftas
 * @copyright   Copyright 2013, http://www.komposta.net
 */
defined('ABSPATH') || die('Nothing Here...');

if('POST' != $_SERVER['REQUEST_METHOD'])
{
	header('Allow: POST');
	header('HTTP/1.1 405 Method Not Allowed');
	header('Content-Type: text/plain');
	exit;
}

check_ajax_referer('summy-summy-summarize', '_summynonce');
spl_autoload_register(function($class) {
	static $map = null;
	if($map === null)
	{
		$map = require_once dirname(dirname(__FILE__)) . '/lib/Summy/autoload_classmap.php';
	}
	if(isset($map[$class]))
	{
		require_once $map[$class];
		return $class;
	}

	return false;
}, true, true);


load_plugin_textdomain('summy', false, 'summy/lang');
$_POST = stripslashes_deep($_POST);
$error = null;
$title = $_POST['title'];
$content = $_POST['content'];
$data = array(
	'language' => in_array($_POST['language'], array('en', 'gr')) ? $_POST['language'] : null,
	'rate' => floatval($_POST['rate']),
	'minWordsLimit' => max(0, intval($_POST['minWordsLimit'])),
	'maxWordsLimit' => max(1, intval($_POST['maxWordsLimit'])),
	'termScore' => 'tfisf',
	'positionScore' => in_array($_POST['positionScore'], array('baxendale', 'article')) ? $_POST['positionScore'] : null,
	'TW' => max(0.0, floatval($_POST['TW'])),
	'PW' => max(0.0, floatval($_POST['PW'])),
	'KW' => max(0.0, floatval($_POST['KW']))
);

if($content == '')
{
	$error = __('Text is empty.', 'summy');
}
else if(!$data['language'])
{
	$error = __('Select a valid language.', 'summy');
}
else if(!$data['positionScore'])
{
	$error = __('Select a valid position score method.', 'summy');
}
else if($data['rate'] >= 100 OR $data['rate'] <= 0)
{
	$error = __('Enter a valid output rate between 1-99.', 'summy');
}
else
{
	$summy = new \Summy\Core($content, $title, $data);
	if($summy->summarize())
	{
		update_option('summy', $data);
		//arsort($summy->stoppedWords);
		//arsort($summy->scoredWords);
		//ksort($summy->stemmedWords);
		$data = array(
			//'wordCount' => $summy->sentenceWordCount,
			//'scores' => $summy->sentenceScores,
			//'detailedScores' => $summy->detailedSentenceScores,
			//'sentences' => $summy->sentences,
			'summary' => $summy->getSummaryText(),
			//'title' => $summy->title,
			//'textdiff' => $summy->getDiffText(),
			//'totalParagraphs' => $summy->totalParagraphs,
			//'totalSentences' => $summy->totalSentences,
			//'totalWords' => $summy->totalWords,
			//'stoppedWords' => $summy->stoppedWords,
			//'stemmedWords' => $summy->stemmedWords,
			//'scoredWords' => $summy->scoredWords,
			//'termFrequency' => $summy->termFrequency,
			//'sentencesWords' => $summy->sentenceWordsIndex,
			'config' => $summy->config
		);
	}
	else
	{
		$error = __('Excerpt generation failed for some reason.', 'summy');
	}
}

header("Content-Type: application/json");
echo json_encode(compact('data', 'error'));
exit;

?>