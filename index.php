<?php
require './vendor/autoload.php';
require __DIR__ . "/inc/bootstrap.php";


//Enable CORS

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST');

header("Access-Control-Allow-Headers: X-Requested-With");

//Domain list
$domains = array('aptech', 'arena');

//Domain parsing
//Domain detail : $host/aptech/news/list
//  uri[1] (aptech) : domain name -> parameter in action
//  uri[2] (news) : controller name
//  uri[3] (list) : action name
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);


//For dev localhost
// if ((isset($uri[2]) && in_array($uri[2], $domains) && $uri[3] != 'news') || !isset($uri[3])) {
//     header("HTTP/1.1 404 Not Found");
//     exit();
// }

// require PROJECT_ROOT_PATH . "/Controllers/Api/NewsController.php";

// $objFeedController = new NewsController();
// $strMethodName = $uri[4] . 'Action';
// $objFeedController->{$strMethodName}($uri[2]);


//For production
if ((isset($uri[2]) && in_array($uri[1], $domains) && $uri[2] != 'news') || !isset($uri[2])) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

require PROJECT_ROOT_PATH . "/Controllers/Api/NewsController.php";

$objFeedController = new NewsController();
$strMethodName = $uri[3] . 'Action';
$objFeedController->{$strMethodName}($uri[1]);