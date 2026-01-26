<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Services\FileService;
use App\Services\SettingManagerService;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * Helper 函数测试
 */
#[TestDox('Helper 函数测试')]
class HelperTest extends TestCase
{
    /**
     * 测试 setUp
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 模拟 SettingManagerService
        $settingManagerMock = $this->createMock(SettingManagerService::class);
        $settingManagerMock->method('get')
            ->withAnyParameters()
            ->willReturnCallback(function ($key, $default) {
                if ($key === 'test.key') {
                    return 'test.value';
                }

                return $default;
            });

        // 绑定到服务容器
        App::instance(SettingManagerService::class, $settingManagerMock);
    }

    /**
     * 测试 settings 函数
     */
    #[Test]
    #[TestDox('测试 settings 函数')]
    public function test_settings_function()
    {
        // 测试不带参数调用
        $result = settings();
        $this->assertInstanceOf(SettingManagerService::class, $result);

        // 测试带参数调用
        $result = settings('test.key', 'default');
        $this->assertEquals('test.value', $result);

        // 测试带不存在的键调用
        $result = settings('non.existent.key', 'default');
        $this->assertEquals('default', $result);
    }

    /**
     * 测试 file_service 函数
     */
    #[Test]
    #[TestDox('测试 file_service 函数')]
    public function test_file_service_function()
    {
        // 模拟 FileService
        $fileServiceMock = $this->createMock(FileService::class);

        // 绑定到服务容器
        App::instance(FileService::class, $fileServiceMock);

        // 测试 file_service 函数
        $result = file_service();
        $this->assertInstanceOf(FileService::class, $result);

        // 测试多次调用是否返回相同实例（单例）
        $result1 = file_service();
        $result2 = file_service();
        $this->assertSame($result1, $result2);
    }
}
