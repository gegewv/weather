<?php

/*
 * This file is part of the gegewv/weather.
 *
 * (c) gegewv
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Gegewv\Weather;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    // 其中我们设置了 $defer 属性为 true，并且添加了方法 provides，这是 Laravel 扩展包的延迟注册方式，它不会在框架启动就注册，而是当你调用到它的时候才会注册。
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(Weather::class, function () {
            return new Weather(config('services.weather.key'));
        });

        $this->app->alias(Weather::class, 'weather');
    }

    public function provides()
    {
        return [Weather::class, 'weather'];
    }
}
