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

/**
 * 生成验证码
 */
if (! function_exists('generate_verify_code')) {
    function generate_verify_code(int $length = 6): string
    {
        $letters = '678906789067890678906';
        $vowels = '12345';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            if ($i % 2 && mt_rand(0, 10) > 2 || ! ($i % 2) && mt_rand(0, 10) > 9) {
                $code .= $vowels[mt_rand(0, 4)];
            } else {
                $code .= $letters[mt_rand(0, 20)];
            }
        }

        return $code;
    }
}
