<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

/**
 * Get setting value or object.
 *
 * @param  mixed|null  $default
 * @return \App\Services\SettingManagerService|mixed
 */
if (! function_exists('settings')) {
    function settings(string $key = '', $default = null)
    {
        if (empty($key)) {
            return app(\App\Services\SettingManagerService::class);
        }

        return app(\App\Services\SettingManagerService::class)->get($key, $default);
    }
}

/**
 * Get file service.
 *
 * @return \App\Services\FileService
 */
if (! function_exists('file_service')) {
    function file_service(): \App\Services\FileService
    {
        return app(\App\Services\FileService::class);
    }
}
