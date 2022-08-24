<?php
require './vendor/autoload.php';
require __DIR__ . "/inc/bootstrap.php";

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Create;
use Symfony\Component\DomCrawler\Crawler;


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


$base_url = 'https://arenacantho.cusc.vn';

$client = new Client([
    // Base URI is used with relative requests
    'base_uri' => $base_url,
    // You can set any number of default request options.
    'timeout'  => 2.0,
]);

$bodyCrawler = new Crawler($client->request('GET')->getBody()->getContents());

$result = $bodyCrawler->filter('.other-news-item')->each(function (Crawler $node, $i) {
    return array(
        'title' => $node->filter('.title')->text(),
        'newsList' => array(
            'first' => $node->filter('.news-content .css_tintuc_tbox')->each(function (Crawler $news) {
                return array(
                    'title' => $news->filter('div .css_tintuc_link_btitle')->text(),
                    'description' => $news->filter('div p')->text(),
                    'link' => ARENA_URI . $news->filter('div .css_tintuc_link_btitle')->extract(['href'])[0],
                    'image' => ARENA_URI . $news->filter('div .cms_img_tintuc')->extract(['src'])[0],
                );
            }),
            'rest' => $node->filter('.news-content .css_tintuc_tbox')->siblings()->each(function (Crawler $news) {
                return array(
                    'title' => $news->filter('.css_tintuc_link_title')->text(),
                    'description' => $news->filter('div p')->text(),
                    'link' => ARENA_URI . $news->filter('.css_tintuc_link_title')->extract(['href'])[0],
                    'image' => ARENA_URI . $news->filter('.css_tintuc_link_title div img')->extract(['src'])[0],
                );
            })
        )

    );
});
echo json_encode($result);