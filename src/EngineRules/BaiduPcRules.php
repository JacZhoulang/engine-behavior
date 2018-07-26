<?php
/**
 * Created by PhpStorm.
 * User: querying
 * Date: 18-6-28
 * Time: 下午9:04
 * Describe: 文件描述
 */

namespace Querying\Engine\EngineRules;

class BaiduPcRules
{

    public static $searchPcUrl = 'https://www.baidu.com';

    /**
     * 获取下一页的链接
     *
     * @param string $html
     *
     * @return string
     */
    public static function getNextPageLink()
    {
        $rules = [
            'page_link' => [
                '#page .n', 'href', '', function ($content) {
                    //组装真实url
                    return self::$searchPcUrl.$content;
                },
            ],
        ];

        return $rules;
    }


    /**
     * 获取页面的所有a标签链接
     *
     * @param $html
     *
     * @return mixed
     */
    public static function getAllLinks()
    {
        $rules = [
            'page_link' => [
                'a', 'href', '', function ($content) {
                    if (stripos($content, '?') === 0) {
                        //第一个是?
                        return self::$searchPcUrl.$content;
                    } else {
                        return $content;
                    }
                },
            ],
        ];
        return $rules;
    }

    /**
     * 获取页面里的搜索结果(剔除百度自身的内容和广告,result_op 和 i_NmPA)
     *
     * @param $html
     *
     * @return mixed
     */
    public static function getSearchResultList()
    {
        $rules = [
            //搜索引擎转链
            'page_link' => [
                '#content_left>.result>.t>a', 'href',
            ],
            'title'     => [
                '#content_left>.result>.t>a', 'text',
            ],
            //域名下的链接
            'domain'    => [
                '#content_left .result .f13 a.c-showurl', 'text', '', function ($content) {
                    //取出域名
                    if (preg_match('/(([a-zA-Z0-9]*\.)+[a-zA-Z0-9]+)(\/[a-zA-Z0-9]*)*/', $content, $match)) {
                        return $match[1];
                    } else {
                        return $content;
                    }
                },
            ],
        ];

        return $rules;
    }


    /**
     * 获取页面的热搜排行
     *
     * @param $html
     *
     * @return mixed
     */
    public function getPageHotSearch()
    {
        $rules = [
            'order_id'  => [
                '.c-table tbody tr c-index', 'text',
            ],
            'link'      => [
                '.c-table tbody tr a', 'href',
            ],
            'hot_title' => [
                '.c-table tbody tr a', 'text',
            ],
        ];
        //获取热搜榜
        return $rules;
    }


}