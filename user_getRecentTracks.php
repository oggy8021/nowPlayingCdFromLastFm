<?php

// Include the API
require '../../lastfmapi/lastfmapi.php';

$authVars['apiKey'] = '';

$config = array(
	'enabled' => true,
	'path' => '../lastfmapi/',
	'cache_length' => 1800
);

// Pass the array to the auth class to eturn a valid auth
$auth = new lastfmApiAuth('setsession', $authVars);

$apiClass = new lastfmApi();
$userClass = $apiClass->getPackage($auth, 'user', $config);

// Setup the variables
$methodVars = array(
	'user' => 'oggy-k',
	'limit' => 1
);

if ( $tracks = $userClass->getRecentTracks($methodVars) ) {
	$album = $tracks[0]['album']['name'];
	echo '<img src="' . $tracks[0]['images']['large'] . '" border="0" alt="' . $album . '" title="' . $album . '" />';
	echo '<p>' . $tracks[0]['artist']['name'] . '</p>';
	echo '<p>' . $album . '</p>';
}
else {
	die('<b>Error '.$userClass->error['code'].' - </b><i>'.$userClass->error['desc'].'</i>');
}

?>
