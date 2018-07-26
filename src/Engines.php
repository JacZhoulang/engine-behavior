<?php
/**
 * Created by PhpStorm.
 * User: querying
 * Date: 18-6-28
 * Time: 下午7:26
 * Describe: 文件描述
 */

namespace Querying\Engine;


class Engines
{
    public static $instance;

    public static function getInstance($engine){

        if(!isset(self::$instance[$engine])){
            self::$instance[$engine] = new $engine();
        }

        return self::$instance[$engine];
    }




}