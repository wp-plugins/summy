=== Summy: Excerpt Extraction ===
Contributors: chr15
Donate link: https://flattr.com/donation/give/to/chr15
Tags: auto, excerpt, generation, extraction, summary
Requires at least: 3.5.0
Tested up to: 3.5.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Summy generates excerpts for your posts by applying various algorithms for automatic summarization extraction.

== Description ==

Summy generates excerpts for your posts by applying various algorithms for automatic summarization extraction.
It scores your text's sentences, based on extended configuration options, and returns the highest ranked.
WP-Summy is based on the [Sum+my](http://summy.komposta.net "Summarization Methodology Yardstick") and was
created in an attempt to further develop the Core Library through your [feedback](http://www.komposta.net/article/wp-summy).

= Important Notes =

* Currently only **English** & **Greek** languages are supported.
* PHP 5.3 is **required** in order to use this plugin.
* Please report if it's compatible with older versions of wordpress.
* Check [Sum+my](http://summy.komposta.net) to learn how the core works.

= How To Use =

* Write your blog post as you normally do
* Make sure excerpt and summy blocks are on screen
* Experiment with all the options and hit Summarize

= Why use Summy =

Wordpress excerpts can be displayed in places where quick summaries are preferable to full content like:

* RSS feeds
* Search Results
* Archives Pages
* Meta Description

When a post has no manual excerpt, WordPress generates ONE automatically by selecting the first 55 words of the post.
Automatically generated excerpts don't summarize the post properly leaving guests guessing what the post is about.
Summy will analyze your post's sentences and use the highest ranked that best describe your post, which could lead
in better search engine results and more targetted audience for your blog. Summy's summaries are not perfect and you 
might also need to do some editing before publishing your content but they are a lot better than random 55 words...

== Installation ==

1. Upload the entire folder `summy` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Why is only Greek and English languages supported? =

To improve system accuracy various NLP algorithms are used. The most
hard to find or build are the stemming algorithms because they are build
with specific language rules. If you find or can provide a stemming algorithm 
for another language please contact me.

= In summy.komposta.net there are more options than this plugin =

The term score algorithms TF-IDF and TF-RIDF require an internal database
with linguistics statistics. At least for now it's not included.

== Screenshots ==

1. Settings Panel

== Changelog ==

= 1.0.1 =
* Fixed Typo in plugin name (omg)
* Removed the PHP Version Check, it wasn't really working
* Updated translations
* Updated readme.txt

= 1.0 =
* Initial Release