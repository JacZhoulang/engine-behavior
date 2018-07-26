<?php
/**
 * Created by PhpStorm.
 * User: querying
 * Date: 18-6-4
 * Time: 上午9:23
 * Describe: 文件描述
 */

namespace Querying\Engine\Engines;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;
use QL\QueryList;
use QL\Ext\PhantomJs;
use QL\Services\HttpService;

class Sogou
{
    public static $allCookie = [];

    public static $usePhantomJs = 1;

    public static $queryParams = [
        //关键词
        'query'   => '',
        //cookie中的SUV
        'sugsuv'  => '',
        //随机四位数
        'sut'     => '',
        //搜索的微妙时间
        'sugtime' => '',
        //搜索框
        's_form'  => 'result_up',

        'ie'   => 'utf-8',
        //页数
        'page' => 1,
        //时间戳
        '_ast' => '',

        '_asf' => '',

        'w' => '01029901',

        'p' => '40040100',

        'dp'   => '1',
        'cid'  => '',
        'sst0' => '1527686708213',
        'lkt'  => '0,0,0',
    ];

    public static $html;

    public static $query;

    public static $proxyIp;

    public static $urlList = [
        'web_search' => [
            'url' => 'https://www.sogou.com/web',
        ],
    ];

    public static function addCookie()
    {
        self::$allCookie = array_merge(self::$allCookie, HttpService::getCookieJar()->toArray());
    }

    public static function getCookie($cookieName, $domain = null)
    {
        self::$allCookie = HttpService::getCookieJar()->toArray();
        $cookieValue     = '';

        foreach (self::$allCookie as $cookie) {
            if ($domain === null) {
                if ($cookie['Name'] === $cookieName) {
                    $cookieValue = $cookie['Value'];
                }
            } else {
                if ($cookie['Name'] === $cookieName && $cookie['Domain'] == $domain) {
                    $cookieValue = $cookie['Value'];
                }
            }
        }

        return $cookieValue;
    }


    public static function getDomainCookieStr($domain = null)
    {
        self::$allCookie = HttpService::getCookieJar()->toArray();
        $cookieArr       = [];

        foreach (self::$allCookie as $cookie) {
            if ($domain == null) {
                $cookieArr[] = "{$cookie['Name']}={$cookie['Value']}";
            } else {
                if ($cookie['Domain'] === $domain) {
                    $cookieArr[] = "{$cookie['Name']}={$cookie['Value']}";
                }
            }
        }

        return join('; ', $cookieArr);
    }


    /**
     * curl采集html
     *
     * @param string        $getUrl
     * @param array         $headers
     * @return mixed
     */
    public static function proxyGetHtml($getUrl, $headers = [])
    {
        self::$queryParams = array_merge(self::$queryParams, self::getUrlParams($getUrl));

        return QueryList::get($getUrl, self::$queryParams, $headers)->getHtml();
    }

    /**
     * 用phantomjs采集html
     *
     * @param string        $getUrl
     * @param array         $headers
     * @return mixed
     */
    public static function proxyGetHtmlUsePhantomJs($getUrl, $headers = [])
    {
        $QlObj = QueryList::getInstance();

        self::$queryParams = array_merge(self::$queryParams, self::getUrlParams($getUrl));

        $QlObj->use(PhantomJs::class, '/usr/bin/phantomjs');

        return QueryList::browser(function (\JonnyW\PhantomJs\Http\RequestInterface $r) use ($getUrl, $headers) {
            $r->setMethod('GET');
            $r->setUrl($getUrl);
            $r->setTimeout(20000); // 10 seconds
            $r->setDelay(3); // 3 seconds
            $r->setRequestData(self::$queryParams);
            $r->setHeaders($headers);
            return $r;
        })->getHtml();
    }



    /**
     * 随机生成参数
     */
    public static function randParams()
    {
        self::$queryParams['query']   = self::$query;
        self::$queryParams['page']    = 1;
        self::$queryParams['sut']     = rand(1000, 9999);
        self::$queryParams['sst0']    = time() . '000';
        self::$queryParams['sugtime'] = time() . '000';
        self::$queryParams['sugsuv']  = self::getCookie('SUV');
        self::$queryParams['_ast']    = time();
    }

    /**
     * 根据关键词创建一个url
     *
     * @param $keyword
     * @return string
     */
    public static function createSearchKeywordUrl($searchIndexUrl,$keyword)
    {
        self::$query = $keyword;

        self::randParams();

        $paramsStr = http_build_query(self::$queryParams);

        $getUrl = $searchIndexUrl . '?' . $paramsStr;
        dump('返回的url:'.$getUrl);
        return $getUrl;
    }

    public static function request($getUrl, $headers)
    {
        self::$html = self::proxyGetHtmlUsePhantomJs($getUrl, $headers);
    }

    /**
     * 根据规则进行html页面匹配数据
     * @param $html
     * @param $rules
     * @return mixed
     */
    public static function filterDataByRule($html,$rules){
        return QueryList::html($html)->rules($rules)->query()->getData()->all();
    }

    /**
     * 解析URL的参数
     *
     * @param $url
     * @return array
     */
    public static function getUrlParams($url)
    {
        //分析url的数据
        $urlInfo = parse_url($url);
        //如果没有参数
        if (!isset($urlInfo['query'])) {
            return [];
        }
        //参数数组
        parse_str($urlInfo['query'], $params);

        return $params;
    }

    public static function clearSessionCookies(){
        HttpService::getCookieJar()->clearSessionCookies();
    }

}