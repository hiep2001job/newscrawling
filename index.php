<?php
require './vendor/autoload.php';
require __DIR__ . "/inc/bootstrap.php";

//  Real
// $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// $uri = explode( '/', $uri );
 
// if ((isset($uri[2]) && $uri[2] != 'news') || !isset($uri[3])) {
//     header("HTTP/1.1 404 Not Found");
//     exit();
// }
 
// require PROJECT_ROOT_PATH . "/Controller/Api/NewsController.php";
 
// $objFeedController = new NewsController();
// $strMethodName = $uri[3] . 'Action';
// $objFeedController->{$strMethodName}();

// Fake
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
 
if ((isset($uri[3]) && $uri[3] != 'news') || !isset($uri[4])) {
    header("HTTP/1.1 404 Not Found");
    exit();
}
 
require PROJECT_ROOT_PATH . "/Controller/Api/NewsController.php";
 
$objFeedController = new NewsController();
$strMethodName = $uri[4] . 'Action';
$objFeedController->{$strMethodName}();
?>