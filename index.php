<?php
require './vendor/autoload.php';
require __DIR__ . "/inc/bootstrap.php";


//Enable CORS

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST');

header("Access-Control-Allow-Headers: X-Requested-With");

//For dev localhost
// $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// $uri = explode( '/', $uri );

 
// if ((isset($uri[2]) && $uri[2] != 'news') || !isset($uri[3])) {
//     header("HTTP/1.1 404 Not Found");
//     exit();
// }
 
// require PROJECT_ROOT_PATH . "/Controllers/Api/NewsController.php";
 
// $objFeedController = new NewsController();
// $strMethodName = $uri[3] . 'Action';
// $objFeedController->{$strMethodName}();


//For production
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

 
if ((isset($uri[1]) && $uri[1] != 'news') || !isset($uri[2])) {
    header("HTTP/1.1 404 Not Found");
    exit();
}
 
require PROJECT_ROOT_PATH . "/Controllers/Api/NewsController.php";
 
$objFeedController = new NewsController();
$strMethodName = $uri[2] . 'Action';
$objFeedController->{$strMethodName}();