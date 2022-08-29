<?php

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Create;
use Symfony\Component\DomCrawler\Crawler;

use function GuzzleHttp\Promise\each;

class NewsController extends BaseController
{
    private ?Client $client = null;

    function __construct()
    {
        parent::__construct();
        if ($this->client == null)
            $this->client = new Client([
                // Base URI is used with relative requests
                'base_uri' => ARENA_URI,
                // You can set any number of default request options.
                'timeout'  => 10.0,
            ]);
    }
    /**
     * "/news/list" Endpoint - Get list of news
     */
    public function listAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();

        if (strtoupper($requestMethod) == 'GET') {
            try {

                $bodyCrawler = new Crawler($this->client->request('GET')->getBody()->getContents());

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

    /**
     * "/news/detail" Endpoint - Get detail of news
     */
    public function detailAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];


        if (strtoupper($requestMethod) == 'GET' && isset($_GET['newsLink'])) {
            try {
                // $link = $_GET['newsLink'];
                $link = 'https://arenacantho.cusc.vn/?tabid=359&NDID=12571&key=%E2%80%9CHOC_ARENA_MOI_DUNG_LA_MULTIMEDIA%E2%80%9D_Dang_ky_de_nhan_khuyen_hoc_20_hoc_phi_den_30_06_2022';
                $responseData = json_encode('');
                $response = $this->client->request('GET', $link);

                if ($response->getStatusCode() == 200) {

                    $bodyCrawler = new Crawler($response->getBody()->getContents());

                    $title=$bodyCrawler->filter('li.TieuDe')
                    
                    ->text();

                    $content = $bodyCrawler->filter('#dnn_ctr1287_ModuleContent > div:nth-child(11)')
                        ->filter('td')
                        ->outerHtml();
                    $content=str_replace('src="/','src="'.ARENA_URI.'/',$content);
                    $content=str_replace('<ul style="padding-left:9px;">','<ul style="list-style: none; padding-left:9px;">',$content);

                    
                    $responseData = json_encode(array(
                        'title'=>$title,
                        'content' => $content));
                }
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            } catch (GuzzleHttp\Exception\ClientException $ge) {
                $strErrorDesc = 'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            } catch (GuzzleHttp\Exception\ConnectException $ce) {
                $strErrorDesc = 'Connection error';
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
