<?php
// http://localhost/ftp/dev.emotivellc.com/chang/social_feed/prototype1.php?fb=82076817158&fkr=45436510@N03&tt=18915709&yt=IowaGOP&rt=iowagop&w=500&h=500&count=10&s=includes/tabs-min1.css

write_js($_GET);

function write_js($get) {
	
	header('Content-type: text/javascript');
	
//	$facebook = $get['fb'];
//	$flickr = $get['fkr'];
//	$twitter = $get['tt'];
//	$youtube = $get['yt'];

	$feeds = _build_feeds($get);
	$_feeds = implode('&', $feeds);
	
	$rt = $get['rt'];
	$width = $get['w'];
	$_height = $get['h'];
	$height = $_height+40;
	$count = $get['count'];
	
	$style = $get['s'];
	if(!isset($style) || $style == '') {
		$style = '';	
	}
	else {
		$style = '&s=' . $style;	
	}
	
	
	echo 'document.write(\'<iframe src=\"http://labs.emotivellc.com/socialfeeds/socialfeeds.php?' . $_feeds . '&rt=' . $rt . '&h=' . $_height . '&w=' . $width . '&count=' . $count . $style . '" width="' . $width . '" height="' . $height . '" marginheight="0" frameborder="0" allowtransparency="true" marginwidth="0" scrolling="no" style="margin:0;border:0"></iframe>\');';
	
}

function _build_feeds($get = array()) {

	$data = array();
	
	foreach($get as $feed => $id) {	
		if(isset($id)) {
			switch($feed) {
				case 'fb':
					$data['facebook'] = 'fb=' . $get[$feed];
				break;
				
				case 'fkr':
					$data['flickr'] = 'fkr=' . $get[$feed];
				break;
				
				case 'tt':
					$data['twitter'] = 'tt=' . $get[$feed];
				break;
				
				case 'yt':
					$data['youtube'] = 'yt=' . $get[$feed];
				break;
			}
		}
		
	}
	
	return $data;
}

?>