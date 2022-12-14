<?php

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Create;
use Symfony\Component\DomCrawler\Crawler;


class NewsController extends BaseController
{
    private ?Client $client = null;

    private string $domain = APTECH_URI;

    function __construct()
    {
        parent::__construct();
        if ($this->client == null)
            $this->client = new Client([
                // Base URI is used with relative requests
                //'base_uri' => ARENA_URI,
                'base_uri' => $this->domain,
                // You can set any number of default request options.
                'timeout'  => 5000.0,
            ]);
    }
    /**
     * "/news/list" Endpoint - Get list of news
     */
    public function listAction($domain = '')
    {
        switch ($domain) {
            case 'aptech':
                $this->domain = APTECH_URI;
                break;
            case 'arena':
                $this->domain = ARENA_URI;
                break;
            default:
                $this->domain = APTECH_URI;
        }

        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();

        if (strtoupper($requestMethod) == 'GET') {
            try {

                $bodyCrawler = new Crawler($this->client->request('GET', $this->domain)->getBody()->getContents());

                $result = $bodyCrawler->filter('.other-news-item')->each(function (Crawler $node, $i) {
                    return array(
                        'title' => $node->filter('.title')->text(),
                        'newsList' => array(
                            'first' => $node->filter('.news-content .css_tintuc_tbox')->each(function (Crawler $news) {
                                return array(
                                    'title' => $news->filter('div .css_tintuc_link_btitle')->text(),
                                    'description' => $news->filter('div p')->text(),
                                    'link' => $this->domain . $news->filter('div .css_tintuc_link_btitle')->extract(['href'])[0],
                                    'image' => $this->domain . $news->filter('div .cms_img_tintuc')->extract(['src'])[0],
                                );
                            }),
                            'rest' => $node->filter('.news-content .css_tintuc_tbox')->siblings()->each(function (Crawler $news) {
                                return array(
                                    'title' => $news->filter('.css_tintuc_link_title')->text(),
                                    'description' => $news->filter('div p')->text(),
                                    'link' => $this->domain . $news->filter('.css_tintuc_link_title')->extract(['href'])[0],
                                    'image' => $this->domain . $news->filter('.css_tintuc_link_title div img')->extract(['src'])[0],
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
    public function detailAction($domain = '')
    {
        //Detect domain
        $contentHtmlClass = '';
        switch ($domain) {
            case 'aptech':
                $this->domain = APTECH_URI;
                $contentHtmlClass = '.cusc_contentpane';
                break;
            case 'arena':
                $this->domain = ARENA_URI;
                $contentHtmlClass = '.cssContContent';
                break;
            default:
                $contentHtmlClass = '.cusc_contentpane';
                $this->domain = APTECH_URI;
        }
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if (strtoupper($requestMethod) == 'GET' && isset($_GET['newsLink'])) {
            try {
                $link = $_GET['newsLink'];
                // $link = 'https://arenacantho.cusc.vn/?tabid=359&NDID=12571&key=%E2%80%9CHOC_ARENA_MOI_DUNG_LA_MULTIMEDIA%E2%80%9D_Dang_ky_de_nhan_khuyen_hoc_20_hoc_phi_den_30_06_2022';
                //htmlspecial   https://arenacantho.cusc.vn/?tabid=359&amp;NDID=12571&amp;key=%E2%80%9CHOC_ARENA_MOI_DUNG_LA_MULTIMEDIA%E2%80%9D_Dang_ky_de_nhan_khuyen_hoc_20_hoc_phi_den_30_06_2022
                $responseData = json_encode('');
                $response = $this->client->request('GET', $link);

                if ($response->getStatusCode() == 200) {

                    $bodyCrawler = new Crawler($response->getBody()->getContents());

                    $title = $bodyCrawler->filter('li.TieuDe')->text();
                    switch ($domain) {
                        case 'aptech':
                            $content = $bodyCrawler->filter($contentHtmlClass)->outerHtml();
                            // Change relative path to absolute path
                            $content = str_replace('src="/', 'src="' . $this->domain . '/', $content);
                            break;
                        case 'arena':
                            $content = $bodyCrawler->filter($contentHtmlClass)->filter('td')->outerHtml();
                            // Change relative path to absolute path
                            $content = str_replace('src="/', 'src="' . $this->domain . '/', $content);
                            // Modify inline css
                            $content = str_replace('<ul style="padding-left:9px;">', '<ul style="list-style: none; padding-left:9px;">', $content);
                            $content = str_replace('style="font-family:comic sans ms,cursive;"', '', $content);
                            $content = str_replace('font-family: Tahoma, Arial, Helvetica; color: rgb(0, 0, 0);', '', $content);
                            $content = str_replace('font-size:12px;', 'font-size:1.1rem;', $content);
                            $content = str_replace('line-height: 16px;', 'line-height: 1.2rem;', $content);
                          
                            break;
                    } 
                    $responseData = json_encode(array(
                        'title' => $title,
                        'content' => $content
                    ));
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
