<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\HttpServer\Router\Router;
use App\Middleware\PassportCustomAuth;

Router::addGroup('', function () {
    Router::addGroup('/advertise', function () { //广告
        Router::post('/list', 'App\Controller\WebSite\AdvertiseController@getList');
        Router::post('/detail', 'App\Controller\WebSite\AdvertiseController@detail');
    });
    Router::addGroup('/notice', function () { //公告
        Router::post('/list', 'App\Controller\WebSite\NoticeController@getList');
        Router::post('/detail', 'App\Controller\WebSite\NoticeController@detail');
    });
    Router::addGroup('/information', function () { //资讯
        Router::post('/list', 'App\Controller\WebSite\InformationController@getList');
        Router::post('/detail', 'App\Controller\WebSite\InformationController@detail');
    });
    Router::addGroup('/article', function () { //文章
        Router::post('/list', 'App\Controller\WebSite\ArticleController@getList');
        Router::post('/detail', 'App\Controller\WebSite\ArticleController@detail');
    });
    Router::addGroup('/spots', function () { //景点
        Router::post('/list', 'App\Controller\WebSite\SpotsController@getList');
        Router::post('/detail', 'App\Controller\WebSite\SpotsController@detail');
    });
    Router::addGroup('/set', function () { //设置
        Router::post('/detail', 'App\Controller\WebSite\SetController@detail');
    });

    Router::addGroup('/scenic', function () { //景点
        Router::post('/todayVerifyNum', 'App\Controller\WebSite\ScenicController@todayVerifyNum');
    });

    Router::addGroup('/city', function () { //景点
        Router::post('/weather', 'App\Controller\WebSite\CityController@getWeather');
    });
    /**
     *  服务点
     */
    Router::addGroup('/venue', function () {
        Router::post('/detail', 'App\Controller\WebSite\VenueController@detail');
        Router::post('/list', 'App\Controller\WebSite\VenueController@getList');
        Router::post('/category', 'App\Controller\WebSite\VenueController@categoryList');
    });
    Router::post('/search', 'App\Controller\WebSite\SearchController@getList');
}, ['middleware' => [PassportCustomAuth::class]]);