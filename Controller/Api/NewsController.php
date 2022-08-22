<?php

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Create;
use Symfony\Component\DomCrawler\Crawler;

class NewsController extends BaseController
{
    /**
     * "/news/list" Endpoint - Get list of users
     */
    public function listAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();

        if (strtoupper($requestMethod) == 'GET') {
            try {

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
                $responseData = json_encode($result);
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }

        // send output
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(
                json_encode(array('error' => $strErrorDesc)),
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }
}
