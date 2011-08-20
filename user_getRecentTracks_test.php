<?php

require_once 'user_getRecentTracks.php';

$lastfmobj = new user_getRecentTracks();

print_r ( $lastfmobj->tracks );

$lastfmobj->setUserRecentTracks('oggy-k', '');

print_r ( $lastfmobj->getUserRecentTracks() );

?>
