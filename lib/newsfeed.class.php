<?php

require_once('simplepie.inc');
require_once('LIB_http.php');
require_once('LIB_parse.php');

class newsfeed {
	
	// key = rss, value = feed url
	private $feeds = array('flickr' => '', 'facebook' => '', 'twitter' => '', 'youtube' => '');
	private $feed_data;
	private $number_of_items;
	private $output;
	private $account_meta;
	
	public function __construct($arg_feeds, $arg_num_items = 10, $arg_output = 'php', $arg_account_meta) {
		$this->feeds = $arg_feeds;
		$this->number_of_items = $arg_num_items;
		$this->output = $arg_output;
		$this->account_meta = $arg_account_meta;
	}
	
	// get and set methods 	
	public function __set($varible, $value) {
		$this->$varible = $value;
	}
	
	public function __get($varible) {
		return $this->$varible;
	}
	
	/*
	 * Final output to be called
	 */
	public function output() {
		switch($this->output) {
			case 'php':
			default:
				$this->_php_gen_feed();
			break;
			
			case 'js':
			
			break;
		}
	}
	
	private function _php_gen_feed() {
		foreach($this->feeds as $name => $url) {
			$this->_php_gen_feed_data($name);
		}
	}
	
	private function _php_gen_feed_data($feed_name) {
		$feed = new SimplePie();
	 
		// Set which feed to process.
		// This is not reliable
		// $feed->set_feed_url($this->feeds[$feed_name]);
		 
		$result = http_get($this->feeds[$feed_name], '');
		$result = $result['FILE']; 
		$feed->set_raw_data($result);
		 
		// $feed->force_feed(true);
		$feed->enable_cache(false);
		$feed->init();
		$feed->handle_content_type();
		
		$this->feed_data[$feed_name] .= '<h2><a href="' . $feed->get_permalink() . '">' . $feed->get_title() . '</a></h2>';
		$flag = 0;
		foreach ($feed->get_items() as $item) {
				
			if($flag < $this->number_of_items) {
				
				$description = $item->get_description();
				
				switch($feed_name) {
					case 'twitter':
						$description = $this->replace_urls_callback($description, "linkify");
					break;
					
					case 'facebook':
						$description = str_replace('<a ', '<a target="_blank" ', $description);
						$description = urldecode($description);
						$description = remove($description, 'http://external.ak.fbcdn.net/safe_image.php', 'url=');
					break;
					
					case 'flickr':
						$description = str_replace('<a ', '<a target="_blank" ', $description);
						$description = urldecode($description);
					break;
					
					case 'youtube':
						$description = strip_tags($description, '<img><p><a><span><div>');
						$description = str_replace('<a ', '<a target="_blank" ', $description);
						$description = urldecode($description);
					break;
				}
	 
				$this->feed_data[$feed_name] .= '<div class="tab_rss_item">'
					. $description . 
					'<small>Posted on ' . $item->get_date('j F Y | g:i a') . '</small>
				<span class="tabs_share">' . $this->_share($item->get_title(), $item->get_permalink()) . '</span>
				</div>';
			
			}
		
		$flag++;
				
		}
				
	} // end of the function
	
	// linkify the text
	public function replace_urls_callback($text, $callback) {
		// Start off with a regex
		preg_match_all('#(?:(?:https?|ftps?)://[^.\s]+\.[^\s]+|(?:[^.\s/]+\.)+(?:museum|travel|[a-z]{2,4})(?:[:/][^\s]*)?)#i', $text, $matches);
		
		// Then clean up what the regex left behind
		$offset = 0;
		foreach($matches[0] as $url) {
			$url = htmlspecialchars_decode($url);
			
			// Remove trailing punctuation
			$url = rtrim($url, '.?!,;:\'"`');
	
			// Remove surrounding parens and the like
			preg_match('/[)\]>]+$/', $url, $trailing);
			if (isset($trailing[0])) {
				preg_match_all('/[(\[<]/', $url, $opened);
				preg_match_all('/[)\]>]/', $url, $closed);
				$unopened = count($closed[0]) - count($opened[0]);
	
				// Make sure not to take off more closing parens than there are at the end
				$unopened = ($unopened > strlen($trailing[0])) ? strlen($trailing[0]):$unopened;
	
				$url = ($unopened > 0) ? substr($url, 0, $unopened * -1):$url;
			}
	
			// Remove trailing punctuation again (in case there were some inside parens)
			$url = rtrim($url, '.?!,;:\'"`');
			
			// Make sure we didn't capture part of the next sentence
			preg_match('#((?:[^.\s/]+\.)+)(museum|travel|[a-z]{2,4})#i', $url, $url_parts);
			
			// Were the parts capitalized any?
			$last_part = (strtolower($url_parts[2]) !== $url_parts[2]) ? true:false;
			$prev_part = (strtolower($url_parts[1]) !== $url_parts[1]) ? true:false;
			
			// If the first part wasn't cap'd but the last part was, we captured too much
			if ((!$prev_part && $last_part)) {
				$url = substr_replace($url, '', strpos($url, '.'.$url_parts[2], 0));
			}
			
			// Capture the new TLD
			preg_match('#((?:[^.\s/]+\.)+)(museum|travel|[a-z]{2,4})#i', $url, $url_parts);
			
			$tlds = array('ac', 'ad', 'ae', 'aero', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'aq', 'ar', 'arpa', 'as', 'asia', 'at', 'au', 'aw', 'ax', 'az', 'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'biz', 'bj', 'bm', 'bn', 'bo', 'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz', 'ca', 'cat', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'com', 'coop', 'cr', 'cu', 'cv', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'edu', 'ee', 'eg', 'er', 'es', 'et', 'eu', 'fi', 'fj', 'fk', 'fm', 'fo', 'fr', 'ga', 'gb', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gov', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw', 'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'info', 'int', 'io', 'iq', 'ir', 'is', 'it', 'je', 'jm', 'jo', 'jobs', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'me', 'mg', 'mh', 'mil', 'mk', 'ml', 'mm', 'mn', 'mo', 'mobi', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'museum', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'name', 'nc', 'ne', 'net', 'nf', 'ng', 'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'om', 'org', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pm', 'pn', 'pr', 'pro', 'ps', 'pt', 'pw', 'py', 'qa', 're', 'ro', 'rs', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sj', 'sk', 'sl', 'sm', 'sn', 'so', 'sr', 'st', 'su', 'sv', 'sy', 'sz', 'tc', 'td', 'tel', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tp', 'tr', 'travel', 'tt', 'tv', 'tw', 'tz', 'ua', 'ug', 'uk', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'ye', 'yt', 'yu', 'za', 'zm', 'zw');
	
			if (!in_array($url_parts[2], $tlds)) continue;
			
			// Call user specified func
			$modified_url = $callback($url);
			
			// Replace it!
			$start = strpos($text, $url, $offset);
			$text = substr_replace($text, $modified_url, $start, strlen($url));
			$offset = $start + strlen($modified_url);
		}
		
		return $text;
	}
	
	private function _share($share_title, $share_url) {
	
		$share = array(
			'fb_share' => 'http://www.facebook.com/sharer.php?u=' . rawurlencode($share_url) . '&t=' . urlencode($share_title),
			're_tweet' => 'http://twitter.com/home?status=RT@' . $this->account_meta['twitter'] . '+' . $this->_format_twitter_share($share_title),
		);
		
		
		return '<a href="' . $share['fb_share'] . '" target="_blank"><img src="images/fb_share.png" alt="share on facebook" title="share on facebook"></a>
				<a href="' . $share['re_tweet'] . '" target="_blank"><img src="images/retweet.png" alt="re-tweet" title="re-tweet"></a>';
		
	}
	
	private function _format_twitter_share($share_title) {
		
		$title = substr($share_title, 0, strpos($share_title, 'http'));
		$url = substr($share_title, strpos($share_title, 'http'));
		
		$title = substr(urlencode($title), 0 ,150);
		
		return $title . $url;
		
	}

} // end of class

function linkify($url) {
	if (!preg_match('#^[a-z]+://#i', $url)) {
		return "<a href=\"http://$url\" class=\"extlink\">$url</a>";
	}
	return "<a href=\"$url\" target=\"_blank\" class=\"extlink\">$url</a>";
}

/*
 * Utility class
 */
class newsfeed_gen {

	// return the ids to be used in the query variables
	public static function _return_feed_uids($feeds = array()) {
		
		$data = array(
			'facebook' => substr($feeds['facebook'], strpos($feeds['facebook'], 'id=')+3), 
			'flickr' => return_between($feeds['flickr'], 'id=', '&lang', EXCL),
			'twitter' => return_between($feeds['twitter'], 'user_timeline/', '.rss', EXCL),
			'youtube' => return_between($feeds['youtube'], 'users/', '/uploads?', EXCL),
		);
		
		return $data;
	}
	
	// return the long url needed to construct the newsfeed class from the ids
	public static function _return_feeds($feed_uids = array()) {
	
	$data = array();
	
	foreach($feed_uids as $service => $id) {
		switch($service) {
			case 'facebook':
				$data['facebook'] = 'http://www.facebook.com/feeds/page.php?format=atom10&id=' . $feed_uids['facebook'];
			break;
			
			case 'flickr':
				$data['flickr'] = 'http://api.flickr.com/services/feeds/photos_public.gne?id=' . $feed_uids['flickr'] . '&lang=en-us&format=rss_200';
			break;
			
			case 'twitter':
				$data['twitter'] = 'http://twitter.com/statuses/user_timeline/' . $feed_uids['twitter'] . '.rss';
			break;
			
			case 'youtube':
				$data['youtube'] = 'http://gdata.youtube.com/feeds/base/users/' . $feed_uids['youtube'] . '/uploads?alt=rss&v=2&orderby=published&client=ytapi-youtube-profile';
			break;
		}
	}
	
		return $data;
	}
	
	public static function _build_feeds($get = array()) {
		
		$data = array();
		
		foreach($get as $feed => $id) {	
			if(isset($id)) {
				switch($feed) {
					case 'fb':
						$data['facebook'] = $get[$feed];
					break;
					
					case 'fkr':
						$data['flickr'] = $get[$feed];
					break;
					
					case 'tt':
						$data['twitter'] = $get[$feed];
					break;
					
					case 'yt':
						$data['youtube'] = $get[$feed];
					break;
				}
			}
			
		}
		
		return $data;
	}

} // end of class


?>