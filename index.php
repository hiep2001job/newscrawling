<?php
require './vendor/autoload.php';
require __DIR__ . "/inc/bootstrap.php";


//Enable CORS

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST');

header("Access-Control-Allow-Headers: X-Requested-With");

 
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

 
if ((isset($uri[2]) && $uri[2] != 'news') || !isset($uri[3])) {
    header("HTTP/1.1 404 Not Found");
    exit();
}
 
require PROJECT_ROOT_PATH . "/Controllers/Api/NewsController.php";
 
$objFeedController = new NewsController();
$strMethodName = $uri[3] . 'Action';
$objFeedController->{$strMethodName}();

// $controller='';
// $method='';
// $params='';

// if(file_exists("../app/controllers/" . strtolower($url[2]) . ".php"))
// 		{

// 			$controller = strtolower($url[2]);
// 			unset($url[2]);
// 		}

// 		require "../app/controllers/" . $this->controller . ".php";
// 		$this->controller = new $this->controller;

// 		if(isset($url[1]))
// 		{
// 			$url[1] = strtolower($url[1]);
// 			if(method_exists($this->controller, $url[1]))
// 			{
// 				$this->method = $url[1];
// 				unset($url[1]);
// 			}
// 		}

// 		$this->params = (count($url) > 0) ? $url : ["home"];
		
// 		call_user_func_array([$this->controller,$this->method], $this->params);