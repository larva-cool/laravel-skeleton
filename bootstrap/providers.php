<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\DynamicConfigServiceProvider::class,
    App\Providers\FileServiceProvider::class,
    App\Providers\SmsServiceProvider::class,
    //取消自动注册TelescopeServiceProvider，因为在本地环境下，需要手动注册
    //App\Providers\TelescopeServiceProvider::class,
    App\Providers\OpenAIProvider::class,
];
