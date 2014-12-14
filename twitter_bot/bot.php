<?php

//12時と18時に起動

require_once('twitteroauth/twitteroauth.php');
require_once('config.php');

$conn = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN,ACCESS_TOKEN_SECRET);

$msg = (date("H")==12) ? "こんにちは！":"今日もおつれさまです！";


$params = array(
	'status' => $msg.'http://yukihirai0505.com #yukihirai'
);

$result = $conn->post('statuses/update',$params);

var_dump($result);
