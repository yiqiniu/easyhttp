<?php

namespace Gouguoyin\EasyHttp;

class Facade
{
    protected $facade;
    /**
     * \GuzzleHttp\Client单例
     * @var array
     */
    private static $instances = [];


    public function __construct()
    {
        $this->facade = new $this->facade;
    }

    public function __call($name, $params) {
        return call_user_func_array([$this->facade, $name], $params);
    }

    public static function __callStatic($name, $params) {
        $key = md5(static::class);
        if(!isset(self::$instances[$key])){
            self::$instances[$key] = new static();
        }
        return call_user_func_array([self::$instances[$key], $name], $params);
    }
}
