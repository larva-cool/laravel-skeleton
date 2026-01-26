<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Support;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminMenu;
use App\Models\Admin\AdminRole;

/**
 * 权限助手
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class PermissionHelper
{
    /**
     * 获取管理员账号权限
     *
     * @return array|string[]
     */
    public static function getPermissions(Admin $user): array
    {
        // 系统内置超管权限为 *
        if ($user->is_super) {
            return ['*'];
        }
        $rules = PermissionHelper::getRules($user->getRoleIds());
        // 超级管理员
        if (in_array('*', $rules)) {
            return ['*'];
        }
        $keys = AdminMenu::query()->whereIn('id', $rules)->pluck('key');
        $permissions = [];
        foreach ($keys as $key) {
            $code = str_replace('/', '.', trim($key, '/'));
            $permissions[] = $code;
        }

        return $permissions;
    }

    /**
     * 获取权限规则
     */
    public static function getRules($roleIds): array
    {
        $rulesStrings = $roleIds ? AdminRole::query()->whereIn('id', $roleIds)->pluck('rules') : [];
        $rules = [];
        foreach ($rulesStrings as $ruleString) {
            if (! $ruleString) {
                continue;
            }
            $rules = array_merge($rules, explode(',', $ruleString));
        }

        return $rules;
    }

    /**
     * 移除空的菜单
     */
    public static function emptyFilter($menus): array
    {
        return array_map(
            function ($menu) {
                if (isset($menu['children'])) {
                    $menu['children'] = self::emptyFilter($menu['children']);
                }

                return $menu;
            },
            array_values(array_filter(
                $menus,
                function ($menu) {
                    return $menu['type'] != 0 || isset($menu['children']) && count(self::emptyFilter($menu['children'])) > 0;
                }
            ))
        );
    }

    /**
     * 移除不包含某些数据的数组
     */
    public static function removeNotContain(&$array, $key, $values): void
    {
        foreach ($array as $k => &$item) {
            if (! is_array($item)) {
                continue;
            }
            if (! self::arrayContain($item, $key, $values)) {
                unset($array[$k]);
            } else {
                if (! isset($item['children'])) {
                    continue;
                }
                self::removeNotContain($item['children'], $key, $values);
            }
        }
    }

    /**
     * 判断数组是否包含某些数据
     */
    public static function arrayContain(&$array, $key, $values): bool
    {
        if (! is_array($array)) {
            return false;
        }
        if (isset($array[$key]) && in_array($array[$key], $values)) {
            return true;
        }
        if (! isset($array['children'])) {
            return false;
        }
        foreach ($array['children'] as $item) {
            if (self::arrayContain($item, $key, $values)) {
                return true;
            }
        }

        return false;
    }
}
