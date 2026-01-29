<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * RESTFul API version 1.
 *
 * Define the version of the interface that conforms to most of the
 * REST ful specification.
 */

Route::group(['prefix' => 'v1', 'as' => 'api.v1.'], function () {
    /**
     * 公共接口
     */
    Route::group(['prefix' => 'common', 'as' => 'common.'], function (Illuminate\Contracts\Routing\Registrar $registrar) {
        $registrar->any('fpm', [\App\Http\Controllers\Api\V1\CommonController::class, 'fpm'])->name('fpm'); // reload fpm
        // 增加缓存Header
        $registrar->group(['middleware' => 'cache.headers:public;max_age=2628000;etag'], function (Illuminate\Contracts\Routing\Registrar $registrar) {
            $registrar->get('dict', [\App\Http\Controllers\Api\V1\CommonController::class, 'dict'])->name('dict'); // 字典列表
            $registrar->get('area', [\App\Http\Controllers\Api\V1\CommonController::class, 'area'])->name('area'); // 地区列表
            $registrar->get('source-types', [\App\Http\Controllers\Api\V1\CommonController::class, 'sourceTypes'])->name('source_types'); // 获取 Source Types
            $registrar->get('settings', [\App\Http\Controllers\Api\V1\CommonController::class, 'settings'])->name('settings'); // 系统配置
        });
    });



});
