<?php
/**
 * Created by PhpStorm.
 * User: querying
 * Date: 18-6-28
 * Time: 下午7:10
 * Describe: 文件描述
 */

namespace Querying\Engine\Engines;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;
use QL\QueryList;
use QL\Ext\PhantomJs;
use QL\Services\HttpService;
use Querying\Engine\EngineRules\BaiduPcRules;

class Baidu
{
    public static $allCookie;

    public static $queryParams = [
        'word' => '',
    ];

    public static function getWebSearchInitUrl($keyword)
    {
        return 'https://www.baidu.com/s?pn=0&word='.urlencode($keyword);
    }

    /**
     * 从匹配结果中找出 - 某个域名的搜索结果
     *
     * @param $searchResult
     * @param $domain
     *
     * @return array
     */
    public static function searchDomainSearchResult($searchResult, $domain)
    {
        $tmpSearchResult = [];

        foreach ($searchResult as $value) {
            $pattern = preg_quote($domain);
            if (preg_match("/{$pattern}/", $value['sogou_domain'], $match)) {
                $tmpSearchResult[] = $value;
            }
        }

        return $tmpSearchResult;
    }

    /**
     * 根据规则进行html页面匹配数据
     *
     * @param $html
     * @param $rules
     *
     * @return mixed
     */
    public static function filterDataByRule($html, $ruleName)
    {
        $rules = BaiduPcRules::$ruleName();

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
    
    public static function getPageNum($pn){
        // 0 10 20 30 40
        // 1 2  3  4  5
        return ($pn / 10) + 1;
    }

}