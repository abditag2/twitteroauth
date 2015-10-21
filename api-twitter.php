<?php
/**
 * Author: Fardin Abdi
 * Twitter Oauth1.1 interface
 */
session_start();
require __DIR__ . "/vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

define('CONSUMER_KEY', 'uVJwx59DhsSdfgROnF9Q6sItp');
define('CONSUMER_SECRET', 'u3oDyVcobCc2NQqnCR4shP4lMjYgnbRARQJbUysQc2HFnus22F');
define('REDIRECT_URL', 'http://localhost:63342/test/api-twitter.php');


/**
 * Example on how to use.
 * TODO: remove this later
 */
$access_token = authorizationTwitterUser();
if ($access_token) {
    getLast20TweetsAsAHTMLList($access_token);
    getLast20MentionAsAHTMLList($access_token);
    getUserInfoAsAHTMLList($access_token);
}



/**
 * This function returns user info
 *
 * for an example look at function getUserInfoAsAHTMLList
 *
 * @param $access_token
 * @return array of arrays with four elements the following
 * $rv['followers_count'] =
 * $rv['friends_count'] =
 * $rv['listed_count'] =
 * $rv['favourites_count'] =
 */
function getUserInfoInAnArray($access_token)
{
    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
    $user = $connection->get("account/verify_credentials");

    $rv = array();
    $rv['followers_count'] = $user->followers_count;
    $rv['friends_count'] = $user->friends_count;
    $rv['listed_count'] = $user->listed_count;
    $rv['favourites_count'] = $user->favourites_count;

    return $rv;
}


/**
 * this function returns the last tweets, their location, and their link.
 * it also formats the text of the tweet to make links to the mentioned people and the hashtags
 *
 * For an example look at function getLast20MentionAsAHTMLList
 *
 * @param $access_token
 * @return array[][] an array of arrays with three parameters in each element
 * $oneTweet['text'] = $oneTweet['location']
 * $oneTweet['link']
 */
function getLast20TweetsAsAnArray($access_token)
{

    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
    $timeLine = $connection->get("statuses/user_timeline");

    $rv = array();

    foreach ($timeLine as $tweet) {
        $oneTweet = array();

        $oneTweet['text'] = formatTweet($tweet->text);
        $oneTweet['location'] = $tweet->user->location;
        $oneTweet['link'] = "https://twitter.com/twitter/status/".$tweet->id;

        $rv[] = $oneTweet;
    }

    return $rv;
}


/**
 * returns the last 20 tweets that user was mentioned in. each mention is formatted such that there are links to the users and hashtags
 * for an example look at function getLast20MentionAsAnArray
 *
 * @param $access_token
 * @return array[][] an array of arrays each with one element
 * $oneTweet['text']
 */

function getLast20MentionAsAnArray($access_token)
{
    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
    $mentions = $connection->get("statuses/mentions_timeline");

    $rv = array();

    foreach ($mentions as $tweet) {

        $oneTweet = array();
        $tweet_text = $tweet->text; //get the tweet
        $tweet_text = formatTweet($tweet_text);
        $oneTweet['text'] = $tweet_text;
        $rv[] = $oneTweet;
    }

    return $rv;
}


function getUserInfoAsAHTMLList($access_token)
{

    $user_info = getUserInfoInAnArray($access_token);

    echo '<ul>';
    echo "<li>followers count: " . $user_info['followers_count'] . "</li>";
    echo "<li>friends count: " . $user_info['friends_count'] . "</li>";
    echo "<li>listed count: " . $user_info['listed_count'] . "</li>";
    echo "<li>favourites count: " . $user_info['favourites_count'] . "</li>";
    echo '</ul>';

}


function getLast20MentionAsAHTMLList($access_token)
{
    $mentions = getLast20MentionAsAnArray($access_token);


    echo '<ul>';
    foreach ($mentions as $tweet) {
        echo "<li>" . $tweet['text'] . "</li>";
    }
    echo '</ul>';

}


/**
 * @param $access_token
 */
function getLast20TweetsAsAHTMLList($access_token)
{


    $timeLine = getLast20TweetsAsAnArray($access_token);

    echo '<ul>';
    foreach ($timeLine as $tweet) {
        $tweet_text = $tweet['text'];
        $tweet_location = $tweet['location'];
        $tweet_link = $tweet['link'];

        echo "<li>" . $tweet_text . "<br> Location: " . $tweet_location . " | <a href=$tweet_link>Go to tweet</a></li>";

    }
    echo '</ul>';
}


/**
 * This function formats twitter text and adds all the links to accounts, hashtags and mentions.
 * @param $tweet_text
 * @return mixed
 */
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


/**
 *
 * This function is for authentication of the user to twitter server.
 * You should call this function at the beginning of the page before anything else.
 * If the user is not logged in, it will display a login link that by clicking on it
 * user will be redirected to twitter login page. Once user came back, this function will put the access the token in the session.
 *
 * If the access token is in the session, it will not make a call to twitter api again!
 *
 * if not logged in
 * @return null + echos a login link
 * if logged in
 * @return access_token
 * @throws \Abraham\TwitterOAuth\TwitterOAuthException
 */
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

        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => REDIRECT_URI));

        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
        $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

        Echo "<a href=$url>login</a>";
        return null;
    }

}



