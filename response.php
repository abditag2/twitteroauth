<?php
///**
// * Created by PhpStorm.
// * User: tanish
// * Date: 10/20/15
// * Time: 11:35 AM
// */
//
//
//echo "this the response page!";
////require __DIR__."/vendor/autoload.php";
//
//
////use Abraham\TwitterOAuth\TwitterOAuth;

echo "this is response";
$consumerKey = 'uVJwx59DhsSdfgROnF9Q6sItp';
$consumerSecret = 'u3oDyVcobCc2NQqnCR4shP4lMjYgnbRARQJbUysQc2HFnus22F';


$request_token = [];
$request_token['oauth_token'] = $_SESSION['oauth_token'];
$request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

if (isset($_REQUEST['oauth_token']) && $request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
    // Abort! Something is wrong.
}