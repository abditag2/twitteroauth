<?php

session_start();
require __DIR__ . "/vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

define('CONSUMER_KEY', 'uVJwx59DhsSdfgROnF9Q6sItp');
define('CONSUMER_SECRET', 'u3oDyVcobCc2NQqnCR4shP4lMjYgnbRARQJbUysQc2HFnus22F');
$access_token = authorizationTwitterUser();


?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Bootstrap Case</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    </head>
    <body>

    <div class="container">
        <!--    <div class="jumbotron">-->
        <!--        <h1>The best twitter summary webpage!</h1>-->
        <!--        <p>We cna write anything here</p>-->
        <!--    </div>-->

        <div class="row">
            <div class="col-md-4">
                <p><?php
                    $access_token = authorizationTwitterUser();
                    if ($access_token) {
                        try{
                            getLast20Mention($access_token);
                        }
                        catch(Exception $e){
                            echo "error";
                        }

                    }
                    ?></p>
            </div>
            <div class="col-md-4">
                <p><?php
                    $access_token = authorizationTwitterUser();
                    if ($access_token) {
                        getLast20Tweets($access_token);
                    }
                    ?></p>
            </div>
            <div class="col-md-4">
                <p><?php
                    $access_token = authorizationTwitterUser();
                    if ($access_token) {
                        getUserInfo($access_token);
                    }
                    ?></p>
            </div>
        </div>
    </div>

    </body>
    </html>


<?php
/**
 * Created by PhpStorm.
 * User: tanish
 * Date: 10/20/15
 * Time: 12:05 AM
 */
//
//session_start();
//require __DIR__."/vendor/autoload.php";
//use Abraham\TwitterOAuth\TwitterOAuth;
//$access_token = authorizationTwitterUser();
//define('CONSUMER_KEY', 'uVJwx59DhsSdfgROnF9Q6sItp');
//define('CONSUMER_SECRET', 'u3oDyVcobCc2NQqnCR4shP4lMjYgnbRARQJbUysQc2HFnus22F');

function getUserInfo($access_token)
{

    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
    $user = $connection->get("account/verify_credentials");

    echo '<ul>';
    echo "<li>followers count: " . $user->followers_count . "</li>";
    echo "<li>friends count: " . $user->friends_count . "</li>";
    echo "<li>listed count: " . $user->listed_count . "</li>";
    echo "<li>favourites count: " . $user->favourites_count . "</li>";
    echo '</ul>';

}

function getLast20Mention($access_token)
{
    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
    $mentions = $connection->get("statuses/mentions_timeline");

    $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
    $reg_exHash = "/#([a-z_0-9]+)/i";
    $reg_exUser = "/@([a-z_0-9]+)/i";

    echo '<ul>';
    foreach ($mentions as $tweet) {
        $tweet_text = $tweet->text; //get the tweet

        $tweet_text = formatTweet($tweet_text);
        // display each tweet in a list item
        echo "<li>" . $tweet_text . "</li>";
    }
    echo '</ul>';

}


function getLast20Tweets($access_token)
{

    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
    $timeLine = $connection->get("statuses/user_timeline");

    echo '<ul>';
    foreach ($timeLine as $tweet) {
        $tweet_text = $tweet->text; //get the tweet
        $tweet_location = $tweet->user->location;
        $tweet_id = $tweet->id;

        $tweet_text = formatTweet($tweet_text);
        echo "<li>" . $tweet_text . "<br> Location: " . $tweet_location . " | <a href=https://twitter.com/twitter/status/$tweet_id>Go to tweet</a></li>";

    }
    echo '</ul>';
}


function formatTweet($tweet_text)
{
    $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
    $reg_exHash = "/#([a-z_0-9]+)/i";
    $reg_exUser = "/@([a-z_0-9]+)/i";


    // make links link to URL
    // http://css-tricks.com/snippets/php/find-urls-in-text-make-links/
    if (preg_match($reg_exUrl, $tweet_text, $url)) {

        // make the urls hyper links
        $tweet_text = preg_replace($reg_exUrl, "<a href='{$url[0]}'>{$url[0]}</a> ", $tweet_text);

    }

    if (preg_match($reg_exHash, $tweet_text, $hash)) {

        // make the hash tags hyper links    https://twitter.com/search?q=%23truth
        $tweet_text = preg_replace($reg_exHash, "<a href='https://twitter.com/search?q={$hash[0]}'>{$hash[0]}</a> ", $tweet_text);

        // swap out the # in the URL to make %23
        $tweet_text = str_replace("/search?q=#", "/search?q=%23", $tweet_text);

    }

    if (preg_match($reg_exUser, $tweet_text, $user)) {

        $tweet_text = preg_replace("/@([a-z_0-9]+)/i", "<a href='http://twitter.com/$1'>$0</a>", $tweet_text);

    }

    return $tweet_text;
}

function authorizationTwitterUser()
{

    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);


    if (isset($_REQUEST['oauth_token']) && !empty($_REQUEST['oauth_token']) && isset($_REQUEST['oauth_verifier']) && !empty($_REQUEST['oauth_verifier'])) {

        if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
            echo "oauth token is not the same as request token.";
            session_destroy();
        }

        if (!isset($_SESSION['access_token']) || empty($_SESSION['access_token'])) {
            $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
            try {
                $access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));
                $_SESSION['access_token'] = $access_token;
            } catch (Abraham\TwitterOAuth\TwitterOAuthException $e) {
                echo("The returned oauth token is not valid.");
            }
        }

        return $_SESSION['access_token'];
    } else if (isset($_SESSION['access_token']) && !empty($_SESSION['access_token'])) {
        return $_SESSION['access_token'];
    } else {

        $content = $connection->get("account/verify_credentials");
        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => 'https://localhost:63342/test/api-twitter.php'));

        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
        $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

        Echo "<a href=$url>login</a>";
        return null;
    }

}

?>