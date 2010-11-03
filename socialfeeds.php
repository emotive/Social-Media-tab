<?php require_once('lib/newsfeed.class.php'); 

	$tabs_height = $_GET['h'];
	$tabs_width = $_GET['w'];
	$num_items = $_GET['count'];
	$stylesheet = (isset($_GET['s'])) ? urldecode($_GET['s']) : 'includes/tabs-min.css';
	
	
	$feeds_uids = newsfeed_gen::_build_feeds($_GET);
	
	$feeds = newsfeed_gen::_return_feeds($feeds_uids);
	
	$account_meta = array(
		'twitter' => $_GET['rt'],
	);
	

$newsfeeds = new newsfeed($feeds, $num_items, 'php', $account_meta);
$newsfeeds->output();
$data = $newsfeeds->__get('feed_data');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<title>Newsfeeds</title>
<script src="includes/jquery.tools.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $stylesheet; ?>" media="screen" />
<style type="text/css">
	.height-control {
		height: <?php echo $tabs_height; ?>px;
	}
	.width-control {
		width: <?php echo $tabs_width; ?>px;	
	}
</style>
</head>
<body>
<div class="tabs_wrapper width-control">
  <ul class="css-tabs">
    <?php if(isset($feeds_uids['facebook'])) { ?><li><a href="#"><img src="images/facebook.png" width="16" height="16" alt="facebook" />Facebook</a></li><?php } ?>
    <?php if(isset($feeds_uids['flickr'])) { ?><li><a href="#"><img src="images/flickr.png" width="16" height="16" alt="flickr" />Flickr</a></li><?php } ?>
    <?php if(isset($feeds_uids['twitter'])) { ?><li><a href="#"><img src="images/twitter.png" width="16" height="16" alt="twitter" />Twitter</a></li><?php } ?>
    <?php if(isset($feeds_uids['youtube'])) { ?><li><a href="#"><img src="images/youtube.png" width="16" height="16" alt="youtube" />Youtube</a></li><?php } ?>
  </ul>
  <!-- panes -->
  <div class="css-panes">
    <?php foreach ($data as $feed): ?>
    <div class="tabs-display height-control" style="height:<?php echo $tabs_height; ?>"><?php echo $feed; ?></div>
    <?php endforeach; ?>
  </div>
</div>
<script>
$(function() {
	// :first selector is optional if you have only one tabs on the page
	$("ul.css-tabs:first").tabs("div.css-panes:first > div");

});
</script>
</body>
