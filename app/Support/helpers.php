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

if (! function_exists('sms')) {

    /**
     * 发送短信
     *
     * @param  \Overtrue\EasySms\PhoneNumber|string  $mobile
     * @param  string|\Overtrue\EasySms\Message  $message
     * @return array|\Overtrue\EasySms\EasySms
     *
     * @throws NoGatewayAvailableException|InvalidArgumentException
     */
    function sms()
    {
        $arguments = func_get_args();
        /** @var \Overtrue\EasySms\EasySms $sms */
        $sms = app(\Overtrue\EasySms\EasySms::class);
        if (empty($arguments)) {
            return $sms;
        }

        return $sms->send($arguments[0], $arguments[1]);
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

/**
 * 手机号替换
 */
if (! function_exists('mobile_replace')) {
    function mobile_replace(?string $value): string
    {
        if (! $value) {
            return '';
        }

        return substr_replace($value, '****', 3, 4);
    }
}

/**
 * 解析UA
 */
if (! function_exists('parse_user_agent')) {
    function parse_user_agent($userAgent): array
    {
        $userAgent = trim($userAgent);
        $agent = new \Jenssegers\Agent\Agent;
        $agent->setUserAgent($userAgent);

        return [
            'platform' => $agent->platform(),
            'device' => $agent->device(),
            'browser' => $agent->browser(),
            'isMobile' => $agent->isMobile(),
            'isTablet' => $agent->isTablet(),
            'isDesktop' => $agent->isDesktop(),
            'isPhone' => $agent->isPhone(),
        ];
    }
}

if (! function_exists('ip_address')) {
    function ip_address(string $ip)
    {
        $location = \Zhuzhichao\IpLocationZh\Ip::find($ip);

        return is_array($location) ? implode(' ', $location) : $location;
    }
}
