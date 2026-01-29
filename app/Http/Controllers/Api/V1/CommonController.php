<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Common\AreaRequest;
use App\Http\Requests\Api\V1\Common\DictRequest;
use App\Http\Resources\Api\V1\DictResource;
use Illuminate\Http\JsonResponse;

/**
 * 公共接口
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class CommonController extends Controller
{
    /**
     * 重载 Fpm
     */
    public function fpm()
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response()->json(['message' => __('system.successful_operation')]);
    }

    /**
     * 系统配置
     *
     * @return JsonResponse
     */
    public function settings()
    {
        $settings = [
            // 系统基本配置
            'system' => [
                'title' => settings('system.title'),
                'keywords' => settings('system.keywords'),
                'description' => settings('system.description'),
                'icp_beian' => settings('system.icp_beian'),
                'police_beian' => settings('system.police_beian'),
                'support_email' => settings('system.support_email'),
                'lawyer_email' => settings('system.lawyer_email'),
                'url' => settings('system.url'),
                'm_url' => settings('system.m_url'),
            ],
            // 用户配置
            'user' => [
                'enable_register' => settings('user.enable_register', true),
                'enable_phone_register' => settings('user.enable_phone_register', true),
                'enable_email_register' => settings('user.enable_email_register', true),
                'enable_wechat_login' => settings('user.enable_wechat_login', true),
                'enable_phone_login' => settings('user.enable_phone_login', true),
                'enable_password_login' => settings('user.enable_password_login', true),
                'enable_change_username' => settings('user.username_change', 0) > 0,
                'username_change' => settings('user.username_change', 0),
            ],
        ];

        return response()->json($settings);
    }


    /**
     * 字典接口
     */
    public function dict(DictRequest $request)
    {
        $items = \App\Models\System\Dict::getDicts($request->type);

        return DictResource::collection($items);
    }

    /**
     * 地区接口
     */
    public function area(AreaRequest $request): JsonResponse
    {
        $results = \App\Models\System\Area::getAreas($request->id, ['id', 'name']);

        return response()->json($results);
    }

    /**
     * 获取 Source Types
     */
    public function sourceTypes(): \Illuminate\Http\JsonResponse
    {
        $maps = \Illuminate\Database\Eloquent\Relations\Relation::morphMap();

        return response()->json(array_keys($maps));
    }
}
