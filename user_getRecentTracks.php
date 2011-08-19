<?php

// Include the API
require '../lastfmapi/lastfmapi.php';

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
	echo '<b>Data Returned</b>';
	echo '<pre>';
	print_r($tracks);
	echo '</pre>';
}
else {
	die('<b>Error '.$userClass->error['code'].' - </b><i>'.$userClass->error['desc'].'</i>');
}

?>
