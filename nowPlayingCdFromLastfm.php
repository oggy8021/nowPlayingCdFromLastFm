<?php

/*
	Plugin Name: nowPlayingCdFromLastfm
	Plugin URI: http://oggy.no-ip.info/blog/
	Description: Last.fm -> user.getRecentTracks
	Version: 1.0
	Author: oggy
	Author URI: http://oggy.no-ip.info/blog/
 */

class WP_Widget_plaingCd extends WP_Widget
{
	function WP_Widget_plaingCd() {
//		$widget_ops = array(
//			'classname' => 'WP_Widget_plaingCd',
//			'description' => 'nowPlaying CD from Last.fm'
//		);
//		parent::WP_Widget(false, $name='nowPlayingCdFromLastfm' ,$widget_ops);
		parent::WP_Widget(false, $name='nowPlayingCdFromLastfm');
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'nowPlayingCdFromLastfm', 'userid' => '', 'apikey' => '') );
		$userid = esc_attr( $instance['userid'] );
		$apikey = esc_attr( $instance['apikey'] );
		?>
			<p>
				<label for="<?php echo $this->get_field_id('userid'); ?>">
					<?php _e('Userid'); ?>
				</label>
				<input
					 type="text"
					 name="<?php echo $this->get_field_name('userid'); ?>"
					 value="<?php echo $userid; ?>"
					 id="<?php echo $this->get_field_id('userid'); ?>"
					 class="widefat" />
				<br />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('apikey'); ?>">
					<?php _e('Apikey'); ?>
				</label>
				<input
					 type="text"
					 name="<?php echo $this->get_field_name('apikey'); ?>"
					 value="<?php echo $apikey; ?>"
					 id="<?php echo $this->get_field_id('apikey'); ?>"
					 class="widefat" />
				<br />
			</p>
		<?php
	} //form

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['userid'] = strip_tags($new_instance['userid']);
		$instance['apikey'] = strip_tags($new_instance['apikey']);
		return $instance;
	} //update

	function widget( $args, $instance ) {
		// titleに関する処理はお作法的か？
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		$userid = apply_filters('widget_title', $instance['userid']);
		$apikey = apply_filters('widget_title', $instance['apikey']);

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		echo '<div id="testing nowPlayingCd">';
		echo $instance['title'];
		echo $instance['userid'];
		echo $instance['apikey'];
		echo '</div>';

		echo $after_widget;
	} //widget

} //WP_Widget_plaingCd


function nowPlayingCd_register_widgets() {
	register_widget("WP_Widget_plaingCd");
} //nowPlayingCd_register_widgets


//Main
require_once( WP_PLUGIN_DIR . '/' . 'lastfmapi/lastfmapi.php');
add_action('widgets_init', 'nowPlayingCd_register_widgets');

?>
