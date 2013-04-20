<?php
/**
 * @package		WP-Summy
 * @author		Christodoulos Tsoulloftas
 * @copyright   Copyright 2013, http://www.komposta.net
 */
defined('ABSPATH') || die('Nothing Here...');

global $post_type;
if(post_type_supports($post_type, 'excerpt'))
{
	load_plugin_textdomain('summy', false, 'summy/lang');
	$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
	wp_enqueue_script('summy', plugins_url("/js/summy{$min}.js", dirname(__FILE__)), array(), true, true);
	add_meta_box('summyexcerpt', __('Summy: Excerpt Extraction', 'summy'), 'summy_add_meta_box', null, 'normal', 'high');
}

function summy_add_meta_box()
{
	$def = get_option('summy');
	$languages = array('en' => __('English', 'summy'), 'gr' => __('Greek', 'summy'));
	$positions = array('article' => __('Article', 'summy'), 'baxendale' => __('Baxendale', 'summy'));
	wp_nonce_field('summy-summy-summarize', '_summynonce');

	?>

	<input type="hidden" name="summynonce" value=""
		   <ul>
		<li>
			<label for="summyLanguage"><?php _e('Laguage', 'summy') ?>:</label>
			<select name="summy[language]" id="summyLanguage">
				<?php foreach($languages AS $key => $text): ?>
					<option value="<?php echo $key; ?>"<?php echo $key == $def['language'] ? ' selected="selected"' : ''; ?>><?php echo $text; ?></option>
				<?php endforeach; ?>
			</select>
		</li>
		<li>
			<label for="summyRate"><?php _e('Output Rate', 'summy') ?>:</label>
			<input type="number" name="summy[rate]" id="summyRate" min="1" max="99" value="<?php echo esc_attr($def['rate']); ?>" />%
		</li>
		<li>
			<label><?php _e('Words Limits', 'summy') ?>:</label>
			<input type="number" name="summy[minWordsLimit]" id="summyMinWordsLimit" min="1" max="99" value="<?php echo esc_attr($def['minWordsLimit']); ?>" /> <?php _e('Min', 'summy') ?>
			<input type="number" name="summy[maxWordsLimit]" id="summyMaxWordsLimit" min="1" max="99" value="<?php echo esc_attr($def['maxWordsLimit']); ?>" /> <?php _e('Max', 'summy') ?>
			<p>
			<ul class="description ul-square">
				<li><?php _e('The sentences that exceed the word limits will be ingored. 0 disables the limit.', 'summy'); ?></li>
			</ul>
			</p>
		</li>
		<li>
			<label for="summyTermScore"><?php _e('Terms Score', 'summy') ?>:</label>
			<select name="summy[termScore]" id="summyTermScore">
				<option value="tfisf"><?php _e('TF-ISF', 'summy') ?></option>
			</select>
			<p>
			<ul class="description ul-square">
				<li><?php _e('Term Frequency Inverse Sentence Frequency, rewards the less used words in sentences.', 'summy'); ?></li>
			</ul>
			</p>
		</li>
		<li>
			<label for="summyPositionScore"><?php _e('Position Score', 'summy') ?>:</label>
			<select name="summy[positionScore]" id="summyPositionScore">
				<?php foreach($positions AS $key => $text): ?>
					<option value="<?php echo $key; ?>"<?php echo $key == $def['positionScore'] ? ' selected="selected"' : ''; ?>><?php echo $text; ?></option>
				<?php endforeach; ?>
			</select>
			<p>
			<ul class="description ul-square">
				<li><?php _e('The Article method ranks higher top paragraphs/sentence.', 'summy'); ?></li>
				<li><?php _e('The Baxendale\'s method ranks higher the first and last sentences in a paragraph.', 'summy'); ?></li>
			</ul>
			</p>
		</li>
		<li>
			<label><?php _e('Scores Weights', 'summy') ?>:</label>
			<input type="number" name="summy[TW]" id="summyTW" min="0.0" max="5.0" value="<?php echo esc_attr($def['TW']); ?>" step="0.1" /> <?php _e('TW', 'summy') ?>
			<input type="number" name="summy[PW]" id="summyPW" min="0.0" max="5.0" value="<?php echo esc_attr($def['PW']); ?>" step="0.1" /> <?php _e('PW', 'summy') ?>
			<input type="number" name="summy[KW]" id="summyKW" min="0.0" max="5.0" value="<?php echo esc_attr($def['KW']); ?>" step="0.1" /> <?php _e('KW', 'summy') ?>
			<p>
			<ul class="description ul-square">
				<li><?php _e('Sentence(i) Score =  TW * T(i) + PW * P(i) + KW * K(i).', 'summy'); ?></li>
				<li><?php _e('The T, P, K are each sentence\'s terms score, position score and keywords score from title.', 'summy'); ?></li>
				<li><?php _e('The TW, PW, KW are the scores weights.', 'summy'); ?></li>
				<li><?php _e('The weights are unsigned float values, set a weight to 0 to disable it.', 'summy'); ?></li>
			</ul>
			</p>
		</li>
	</ul>
	<input type="button" class="button" id="summyWork" value="<?php _e('Summarize', 'summy') ?>"> <span class="spinner" id="summySpinner" style="display: none;"></span>
	<?php
}

?>