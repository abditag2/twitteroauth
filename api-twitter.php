<?php
/**
 * Created by PhpStorm.
 * User: tanish
 * Date: 10/20/15
 * Time: 12:05 AM
 */
session_start();

require __DIR__."/vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;



define('CONSUMER_KEY', 'uVJwx59DhsSdfgROnF9Q6sItp');
define('CONSUMER_SECRET', 'u3oDyVcobCc2NQqnCR4shP4lMjYgnbRARQJbUysQc2HFnus22F');


function getLast20Tweets($token){

}



function authorizationTwitterUser(){

    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
    if (!isset($_SESSION['oauth_token']) || empty($_SESSION['oauth_token'])){

        echo "no token".PHP_EOL;
        $content = $connection->get("account/verify_credentials");
        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => 'https://localhost:63342/test/api-twitter.php'));

        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
        $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

        Echo "<a href=$url>login</a>";

    }

    else if (isset($_REQUEST['oauth_token']) && !empty($_REQUEST['oauth_token']) && isset($_REQUEST['oauth_verifier']) && !empty($_REQUEST['oauth_verifier']))
    {
        echo "we have a token".PHP_EOL;

        if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
            echo "oauth token is not the same as request token.";
            session_destroy();
        }

        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

        $access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));
        $_SESSION['access_token'] = $access_token;

        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
        $user = $connection->get("account/verify_credentials");

        $timeLine = $connection->get("statuses/user_timeline");

        echo $timeLine[0]->{'user'}->{'profile_image_url'};

    }
    else{
        session_destroy();
    }


}

