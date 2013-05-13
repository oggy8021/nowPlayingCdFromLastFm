<?php

/*
	Plugin Name: nowPlayingCdFromLastfm
	Plugin URI: http://oggy.no-ip.info/blog/
	Description: Last.fm -> user.getRecentTracks
	Version: 1.7
	Author: oggy
	Author URI: http://oggy.no-ip.info/blog/
 */

require_once('Services/Amazon.php');
#require_once( WP_PLUGIN_DIR . '/' . 'nowPlayingCd/lastfmapi/lastfmapi.php');
require_once('lastfmapi/lastfmapi.php');

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
		$imagesize = apply_filters('widget_title', $instance['imagesize']);
		$imagesize = esc_attr( $instance['imagesize'] );
		$userid = get_option('widget_nowplayingcdfromlastfm_userid');
		$apikey = get_option('widget_nowplayingcdfromlastfm_apikey');

		$sizelist = array('large', 'medium', 'small');
		$imagesize = $sizelist["$imagesize"];

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
				<label for="<?php echo $this->get_field_id('imagesize'); ?>">
					<?php _e('Image Size'); ?>
				</label><BR />
				<select id="<?php echo $this->get_field_id('imagesize'); ?>" name="<?php echo $this->get_field_name('imagesize'); ?>">
					<?php 
						foreach ($sizelist as $size) {
							$selected = ($size == $imagesize) ? 'selected="selected"' : '';
							echo '<option ' . $selected .' value="' . $size . '">' . $size . '</option>';
						}
					?>
				</select>
			</p>
		<?php
	} //formge

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['imagesize'] = (int) $new_instance['imagesize'];
		return $instance;
	} //update

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		$imagesize = apply_filters('widget_title', $instance['imagesize']);
		$userid = get_option('widget_nowplayingcdfromlastfm_userid');
		$apikey = get_option('widget_nowplayingcdfromlastfm_apikey');

		$sizelist = array('large', 'medium', 'small');
		$imagesize = $sizelist["$imagesize"];

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$this->tracks = user_getRecentTracks($userid, $apikey);

		$album = $this->tracks[0]['album']['name'];
		if ( '' != $this->tracks[0]['images']["$imagesize"] )
		{
			$image = $this->tracks[0]['images']["$imagesize"];
		} else {
			$image = WP_PLUGIN_URL . '/nowPlayingCd/noimg.png';
		}
		$artist = $this->tracks[0]['artist']['name'];

		$this->save_settings( saveRecentArtist($this->get_settings(), $title, $artist) );

		echo '<div id="nowPlayingCdFromLastfm">';
		echo '<img src="' . $image . '" border="0" alt="' . $album . '" title="' . $album . '" />';
		echo '<p>' . $artist . '</p>';
		echo '<p>' . $album . '</p>';
		echo '</div>';
		echo $after_widget;

	} //widget

} //WP_Widget_playingCd


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


function saveRecentArtist( $settings, $title, $artist )
{
	foreach ($settings as $number => $instance) {
		if ( isset( $instance['title'] ) ) {
			if ( $instance['title'] == $title ) {
				break;
			}
		}
	}
	if ( ! isset( $instance['artist']) ) {
		$settings["$number"] += array("artist" => $artist);
	} else {
		$settings["$number"]['artist'] = $artist;
	}

	return $settings;

} // saveRecentArtist


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
		$playingcd = apply_filters('widget_title', $instance['playingcd']);

		$widgetList = wp_get_sidebars_widgets();
		array_shift($widgetList);

		$playingcds = array();
		foreach($widgetList as $activeWidgets) {
			foreach( $activeWidgets as $widgetId) {
				$names = preg_split("/-/", $widgetId);
				if ('playingcd' == $names[0]) {
					$settings = get_option('widget_' . $names[0]);
					$widgetTitle = $settings["$names[1]"]['title'];
					$playingcds += array("$widgetTitle" => "$names[1]");
				}
			}
		}

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
			<p>
				<label for="<?php echo $this->get_field_id('playingcd'); ?>">
					<?php _e('対応するウィジェット'); ?>
				</label><BR />
				<select id="<?php echo $this->get_field_id('playingcd'); ?>" name="<?php echo $this->get_field_name('playingcd'); ?>">
					<?php 
						foreach ($playingcds as $widgetTitle => $widgetId) {
							$selected = ($widgetId == $playingcd) ? 'selected="selected"' : '';
							echo '<option ' . $selected .' value="' . $widgetId . '">' . $widgetTitle . '</option>';
						}
					?>
				</select>
			</p>
		<?php
	} //form

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['diskCount'] = strip_tags($new_instance['diskCount']);
		$instance['playingcd'] = strip_tags($new_instance['playingcd']);
		return $instance;
	} //update

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		$diskCount = apply_filters('widget_title', $instance['diskCount']);
		$playingcd = apply_filters('widget_title', $instance['playingcd']);
		// 2012.11.20
		$accesskey = get_option('widget_nowplayingcdfromlastfm_accesskey');
		$secretkey = get_option('widget_nowplayingcdfromlastfm_secretkey');
		$assoctag = get_option('widget_nowplayingcdfromlastfm_assoctag');

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		if (! $settings = get_option('widget_playingcd') ) {
			$artist = 'test artist';
		} else {
			$artist = $settings["$playingcd"]['artist'];
		}

		echo '<div id="recentRelease">';
		echo MusicItemSearch( $artist, $diskCount, $accesskey, $secretkey, $assoctag );
		echo '</div>';

		echo $after_widget;
	} //widget

} //WP_Widget_recentRelease


function MusicItemSearch($artist, $listed, $access_key, $secret_key, $assoctag)
{
//	$access_key = '';
//	$secret_key = '';
	$noimgUrl = WP_PLUGIN_URL . '/nowPlayingCd/noimg.png';

	$sa = new Services_Amazon($access_key, $secret_key);
	$sa->setVersion('2011-08-11');
	$sa->setLocale('JP');

	$opt = array();
	$opt['ResponseGroup'] = 'Images,ItemAttributes';
	$opt['Sort'] = '-releasedate';
	$opt['Artist'] = (String)$artist;
//	$opt['AssociateTag'] = 'oggyblog-20';
	$opt['AssociateTag'] = $assoctag;
	$res = $sa->ItemSearch('Music', $opt);

	if (is_array(gettype($res['Item'])) != FALSE and is_string(gettype($res['Item'])) != FALSE)
	{
		error_log(gettype($res['Item']) . "\n", 3, '/tmp/nowplaying289.log');
	}
	$getItemCnt = count($res['Item']);

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
		$MISresult .= '<p><a href="' . $res['Item'][$cnt]['DetailPageURL'] . '" target="_blank">';
		if (array_key_exists('SmallImage', $res['Item'][$cnt]) and ("" !=  $res['Item'][$cnt]['SmallImage']['URL']))
		{
			$MISresult .= '<img src="' . $res['Item'][$cnt]['SmallImage']['URL'] . '" class="aligncenter" alt="' . $res['Item'][$cnt]['ItemAttributes']['Title'] . '" title="' . $res['Item'][$cnt]['ItemAttributes']['Title'] . '" /><br/>';
		} else {
			$MISresult .= '<img src="' . $noimgUrl . '" class="aligncenter" width="75" height="75" /><br/>';
		}
		$MISresult .= $res['Item'][$cnt]['ItemAttributes']['Title'] . '</a></p>' . "\n";
	}
	return $MISresult;

}//MusicItemSearch


function nowPlayingFromLastfm_menu() {
	add_options_page('nowPlayingFromLastfm', 'nowPlaying CD From Last.fm settings', 8, __FILE__, 'nowPlayingFromLastfm_options');
} //nowPlayingFromLastfm_menu


function nowPlayingFromLastfm_options() {

?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>nowPlaying CD From Last.fm</h2>

	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>
		<h3>Last.fm</h3>
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

		<h3>Amazon</h3>
		<p>access_key:
			<input
				 type="text"
				 name="widget_nowplayingcdfromlastfm_accesskey"
				 value="<?php echo get_option('widget_nowplayingcdfromlastfm_accesskey'); ?>" />
			<br />
		</p>
		<p>secret_key:
			<input
				 type="text"
				 name="widget_nowplayingcdfromlastfm_secretkey"
				 value="<?php echo get_option('widget_nowplayingcdfromlastfm_secretkey') ?>" />
			<br />
		</p>
		<p>AssociateTag:
			<input
				 type="text"
				 name="widget_nowplayingcdfromlastfm_assoctag"
				 value="<?php echo get_option('widget_nowplayingcdfromlastfm_assoctag') ?>" />
			<br />
		</p>

		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="widget_nowplayingcdfromlastfm_userid,widget_nowplayingcdfromlastfm_apikey,widget_nowplayingcdfromlastfm_accesskey,widget_nowplayingcdfromlastfm_secretkey,widget_nowplayingcdfromlastfm_assoctag" />

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
