<?php

//require_once( WP_PLUGIN_DIR . '/' . 'lastfmapi/lastfmapi.php');
require_once( '../..' . '/lastfmapi/lastfmapi.php');

class user_getRecentTracks
{
	public $tracks = '';

	function setUserRecentTracks( $userid, $apikey )
	{
		$authVars['apiKey'] = $apikey;

		$config = array(
			'enabled' => true,
//			'path' => WP_PLUGIN_DIR . '/lastfmapi/',
			'path' => '../lastfmapi/',
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

		if ( $this->tracks = $userClass->getRecentTracks($methodVars) )
		{
			return ;
		}
		else {
			die('<b>Error '.$userClass->error['code'].' - </b><i>'.$userClass->error['desc'].'</i>');
		}
	} // setUserRecentTracks

	function getUserRecentTracks()
	{
		return $this->tracks;
	} //getUserRecentTracks

} //user_getRecentTracks

?>
