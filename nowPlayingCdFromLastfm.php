<?php

/*
	Plugin Name: nowPlayingCdFromLastfm
	Plugin URI: http://oggy.no-ip.info/blog/
	Description: Last.fm -> user.getRecentTracks
	Version: 1.3
	Author: oggy
	Author URI: http://oggy.no-ip.info/blog/
 */

//require_once 'AWSSDKforPHP/sdk.class.php';	// AWSSDKforPHP
require_once('/usr/lib/php/modules/cloudfusion/cloudfusion.class.php');	// cloudfusion
require_once( WP_PLUGIN_DIR . '/' . 'lastfmapi/lastfmapi.php');
require_once 'debuggy.php';

class WP_Widget_playingCd extends WP_Widget
{
	function __construct() {
		$widget_ops = array(
			'classname' => 'WP_Widget_playingCd',
			'description' => 'nowPlaying CD from Last.fm'
		);
		parent::__construct('playingcd', $name='nowPlayingCdFromLastfm' ,$widget_ops);
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'nowPlayingCdFromLastfm', 'userid' => '', 'apikey' => '') );
		$title = esc_attr( $instance['title'] );
		$userid = get_option('widget_nowplayingcdfromlastfm_userid');
		$apikey = get_option('widget_nowplayingcdfromlastfm_apikey');
		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php _e('Title'); ?>
				</label>
				<input
					 type="text"
					 name="<?php echo $this->get_field_name('title'); ?>"
					 value="<?php echo $title; ?>"
					 id="<?php echo $this->get_field_id('title'); ?>"
					 class="widefat" />
				<br />
			</p>
		<?php
	} //form

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	} //update

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		$userid = get_option('widget_nowplayingcdfromlastfm_userid');
		$apikey = get_option('widget_nowplayingcdfromlastfm_apikey');

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

//		$this->tracks = user_getRecentTracks($userid, $apikey);
		$this->tracks = driver_getRecentTracks($userid, $apikey);

		$album = $this->tracks[0]['album']['name'];
		if ( '' != $this->tracks[0]['images']['large'] )
		{
			$image = $this->tracks[0]['images']['large'];
		} else {
			$image = WP_PLUGIN_URL . '/nowPlayingCd/noimg.png';
		}
		$artist = $this->tracks[0]['artist']['name'];

		if ( $getArtist = get_option('widget_' . $this->id_base .'RecentTrackArtist') ) {
			update_option('widget_' . $this->id_base .'RecentTrackArtist', $artist);
		} else {
			add_option('widget_' . $this->id_base .'RecentTrackArtist', $artist);
		}

		$sets = $this->get_settings();

		echo '<div id="nowPlayingCdFromLastfm">';
		echo '<img src="' . $image . '" border="0" alt="' . $album . '" title="' . $album . '" />';
		echo '<p>' . $artist . '</p>';
		echo '<p>' . $album . '</p>';
		echo '<p>[' . $getArtist . ']</p>';
		echo '</div>';
		echo $after_widget;

	} //widget

} //WP_Widget_playingCd


function driver_getRecentTracks( $userid, $apikey ) {
	$tracks = array(
		0 => array(
			"name" => "アイロニー", 
			"artist" => array(
				"name" => "Jill-Decoy Association"
				), 
			"album" => array(
				"name" => "ジルデコ"
				),
			"images" => array(
				"large" => ""
				)
		)
	);
	return $tracks;

} // driver_getRecentTracks


function driver_MusicItemSearch($artist, $listed)
{
	return "driver Code";

} // driver_MusicItemSearch


function user_getRecentTracks( $userid, $apikey ) {
	$authVars['apiKey'] = $apikey;

	$config = array(
		'enabled' => true,
		'path' => WP_PLUGIN_DIR . 'lastfmapi/',
		'cache_length' => 1800
	);

	// Pass the array to the auth class to eturn a valid auth
	$auth = new lastfmApiAuth('setsession', $authVars);

	$apiClass = new lastfmApi();
	$userClass = $apiClass->getPackage($auth, 'user', $config);

	// Setup the variables
	$methodVars = array(
		'user' => $userid,
		'limit' => 1
	);

	if ( $tracks = $userClass->getRecentTracks($methodVars) ) {
		return $tracks;
	}
	else {
		die('<b>Error '.$userClass->error['code'].' - </b><i>'.$userClass->error['desc'].'</i>');
	}
} // user_getRecentTracks


class WP_Widget_recentReleaseCd extends WP_Widget
{
	function __construct() {
		$widget_ops = array(
			'classname' => 'WP_Widget_recentReleaseCd',
			'description' => 'This Artist Recent Release CD'
		);
		parent::__construct('recentreleasecd', $name='recentReleaseCd' ,$widget_ops);
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'recentReleaseCd', 'diskCount' => 1 ) );
		$title = esc_attr( $instance['title'] );
		$diskCount = esc_attr( $instance['diskCount'] );
		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php _e('Title'); ?>
				</label>
				<input
					 type="text"
					 name="<?php echo $this->get_field_name('title'); ?>"
					 value="<?php echo $title; ?>"
					 id="<?php echo $this->get_field_id('title'); ?>"
					 class="widefat" />
				<br />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('diskCount'); ?>">
					<?php _e('DiskCount'); ?>
				</label>
				<input
					 type="text"
					 name="<?php echo $this->get_field_name('diskCount'); ?>"
					 value="<?php echo $diskCount; ?>"
					 id="<?php echo $this->get_field_id('diskCount'); ?>"
					 class="widefat" />
				<br />
			</p>
		<?php
	} //form

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['diskCount'] = strip_tags($new_instance['diskCount']);
		return $instance;
	} //update

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		$diskCount = apply_filters('widget_title', $instance['diskCount']);

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

//		if (! $artist = get_option('widget_' . $this->id_base .'RecentTrackArtist') ) {
		if (! $artist = get_option('widget_playingcdRecentTrackArtist') ) {
			$artist = 'test artist';
		}

		echo '<div id="recentRelease">';
		echo '<p>' . $artist . '</p>';
//		echo MusicItemSearch( $artist, $diskCount );
		echo driver_MusicItemSearch( $artist, $diskCount );
		echo '</div>';

		echo $after_widget;
	} //widget

} //WP_Widget_recentRelease


function MusicItemSearch($artist, $listed)
{
	$noimgUrl = WP_PLUGIN_URL . '/nowPlayingCd/noimg.png';

	$pas = new AmazonPAS();
//	$pas->set_locale(AmazonPAS::LOCALE_JAPAN);	// AWSSDKforPHP
	$pas->set_locale(PAS_LOCALE_JAPAN);	// cloudfusion
	$opt['ResponseGroup'] = 'Images,ItemAttributes';
	$opt['Sort'] = '-releasedate';
	$opt['SearchIndex'] = 'Music';
	$opt['Artist'] = (String)$artist;
//	$res = $pas->item_search((String)$artist, $opt, AmazonPAS::LOCALE_JAPAN);	// AWSSDKforPHP
	$res = $pas->item_search((String)$artist, $opt, PAS_LOCALE_JAPAN);	// cloudfusion

	$getItems =& $res->body->Items->Item;
	$getItemCnt = count($getItems);

	if (0 === $getItemCnt) {
		return '';

	} elseif ($listed >= $getItemCnt) {
		$lmax = $getItemCnt;

	} else {
		$lmax = $listed;

	}

	$MISresult = "";
	for ($cnt = 0; $cnt <= ($lmax - 1); $cnt++)
	{
		$MISresult .= '<p><a href="' . $getItems[$cnt]->DetailPageURL . '" target="_blank">';

		if ("" !=  $getItems[$cnt]->SmallImage->URL)
		{
			$MISresult .= '<img src="' . $getItems[$cnt]->SmallImage->URL . '" class="aligncenter" alt="' . $getItems[$cnt]->ItemAttributes->Title . '" title="' . $getItems[$cnt]->ItemAttributes->Title . '" /><br />';

		} else {
			$MISresult .= '<img src="' . $noimgUrl . '" class="aligncenter" width="75" height="75" /><br />';

		}

		$MISresult .= $getItems[$cnt]->ItemAttributes->Title . '</a></p>' . "\n";

	}
	return $MISresult;

}//MusicItemSearch


function nowPlayingFromLastfm_menu() {
	add_options_page('nowPlayingFromLastfm', 'nowPlaying CD From Last.fm settings', 8, __FILE__, 'nowPlayingFromLastfm_options');
} //nowPlayingFromLastfm_menu


function nowPlayingFromLastfm_options() {

?>
<div class="wrap">
	<h2>nowPlaying CD From Last.fm</h2>

	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

		<p>userid:
			<input
				 type="text"
				 name="widget_nowplayingcdfromlastfm_userid"
				 value="<?php echo get_option('widget_nowplayingcdfromlastfm_userid'); ?>" />
			<br />
		</p>
		<p>apikey:
			<input
				 type="text"
				 name="widget_nowplayingcdfromlastfm_apikey"
				 value="<?php echo get_option('widget_nowplayingcdfromlastfm_apikey') ?>" />
			<br />
		</p>

		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="widget_nowplayingcdfromlastfm_userid,widget_nowplayingcdfromlastfm_apikey" />

	    <p class="submit">
	    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	    </p>
	 
	</form>
</div>
<?php
} //nowPlayingFromLastfm_options


function nowPlayingCd_register_widgets() {
	register_widget("WP_Widget_playingCd");
} //nowPlayingCd_register_widgets


function recentReleaseCd_register_widgets() {
	register_widget("WP_Widget_recentReleaseCd");
} //recentReleaseCd_register_widgets


//Main
add_action('admin_menu', 'nowPlayingFromLastfm_menu');
add_action('widgets_init', 'nowPlayingCd_register_widgets');
add_action('widgets_init', 'recentReleaseCd_register_widgets');

?>
