<?php

namespace Gegewv\Weather;

use GuzzleHttp\Client;
use Gegewv\Weather\Exceptions\HttpException;
use Gegewv\Weather\Exceptions\InvalidArgumentException;

class Weather
{
    // 高德开放平台创建的应用 API Key
    protected $key;
    protected $guzzleOptions = [];

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    /**
     * $city - 城市名 / 高德地址位置 adcode，比如：“深圳” 或者（adcode：440300）
     * $type - 返回内容类型(live:返回实况天气 / forecast:返回预报天气)
     * $format - 输出的数据格式， 默认是 json 格式, 当设置为 xml 时, 输出的为 xml 格式的数据
     */
    public function getWeather($city, $type = 'live', $format = 'json')
    {
        $url = 'https://restapi.amap.com/v3/weather/weatherInfo';

        $types = [
            'live' => 'base',
            'forecast' => 'all',
        ];

        // 1. 对 `$format` 与 `$type` 参数进行检查，不在范围内的抛出异常。

        if (!\in_array(\strtolower($format), ['xml', 'json'])) {
            throw new InvalidArgumentException('Invalid response format: ' . $format);
        }

        if (!\array_key_exists(\strtolower($type), $types)) {
            throw new InvalidArgumentException('Invalid type value(live/forecast): ' . $type);
        }
        

        // 2. 封装 query 参数，并对空值进行过滤。
        // array_filter()函数是用来用回调函数过滤数组中的元素，就是将数组中的每个键值传递给回调函数，回调函数返回true，则把数组中的当前键值返回给函数的返回数组，数组键名保持不变。
        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'output' => $format,
            'extensions' => $types[$type],
        ]);

        try {
            // 3. 调用 getHttpClient 获取实例，并调用该实例的 `get` 方法，
            // 传递参数为两个：$url、['query' => $query]，
            $response = $this->getHttpClient()->get($url, [
                'query' => $query,
            ])->getBody()->getContents();
    
            // 4. 返回值根据 $format 返回不同的格式，
            // 当 $format 为 json 时，返回数组格式，否则为 xml。
            return 'json' === $format ? \json_decode($response, true) : $response;
        } catch (\Exception $e) {
            // 5. 当调用出现异常时捕获并抛出，消息为捕获到的异常消息，
            // 并将调用异常作为 $previousException 传入。
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
        
    }

    public function getLiveWeather($city, $format = 'json')
    {
        // 获取实时天气
        return $this->getWeather($city, 'live', $format);
    }

    public function getForecastsWeather($city, $format = 'json')
    {
        // 获取天气预报
        return $this->getWeather($city, 'forecast', $format);
    }
}