<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\FileService;
use App\Services\SettingManagerService;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * FileService 测试
 */
#[TestDox('FileService 测试')]
class FileServiceTest extends TestCase
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
            ->willReturnMap([
                ['upload.name_rule', null, 'unique'],
                ['upload.storage', null, 'local'],
            ]);

        // 绑定到服务容器
        $this->app->instance(SettingManagerService::class, $settingManagerMock);

        // 模拟配置
        Config::set('filesystems.default', 'local');
        Config::set('filesystems.disks.local', [
            'driver' => 'local',
            'root' => storage_path('app'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ]);
    }

    /**
     * 测试 make 方法
     */
    #[Test]
    #[TestDox('测试 make 方法')]
    public function test_make()
    {
        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 make 方法
        $fileService = FileService::make();
        $this->assertInstanceOf(FileService::class, $fileService);
    }

    /**
     * 测试 url 方法
     */
    #[Test]
    #[TestDox('测试 url 方法')]
    public function test_url()
    {
        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        $filesystemMock->method('url')
            ->with('test.jpg')
            ->willReturn('http://example.com/storage/test.jpg');
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 url 方法 - 相对路径
        $fileService = new FileService;
        $result = $fileService->url('test.jpg');
        $this->assertIsString($result);

        // 测试 url 方法 - 绝对路径
        $result = $fileService->url('http://example.com/test.jpg');
        $this->assertEquals('http://example.com/test.jpg', $result);
    }

    /**
     * 测试 relativePath 方法
     */
    #[Test]
    #[TestDox('测试 relativePath 方法')]
    public function test_relative_path()
    {
        // 测试 relativePath 方法
        $fileService = new FileService;
        $result = $fileService->relativePath('http://example.com/storage/test.jpg');
        $this->assertIsString($result);
    }

    /**
     * 测试 destroy 方法
     */
    #[Test]
    #[TestDox('测试 destroy 方法')]
    public function test_destroy()
    {
        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        $filesystemMock->method('exists')
            ->with('/test.jpg')
            ->willReturn(true);
        $filesystemMock->method('delete')
            ->with('/test.jpg')
            ->willReturn(true);
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 destroy 方法
        $fileService = new FileService;
        $fileService->destroy('http://example.com/test.jpg');
        $this->assertTrue(true); // 只要不抛出异常就算通过
    }

    /**
     * 测试 temporaryUrl 方法
     */
    #[Test]
    #[TestDox('测试 temporaryUrl 方法')]
    public function test_temporary_url()
    {
        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        $filesystemMock->method('temporaryUrl')
            ->with('test.jpg', $this->anything())
            ->willReturn('http://example.com/temporary/test.jpg');
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 temporaryUrl 方法
        $fileService = new FileService;
        $expiration = Carbon::now()->addHour();
        $result = $fileService->temporaryUrl('test.jpg', $expiration);
        $this->assertIsString($result);
    }

    /**
     * 测试 temporaryUploadUrl 方法
     */
    #[Test]
    #[TestDox('测试 temporaryUploadUrl 方法')]
    public function test_temporary_upload_url()
    {
        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        $filesystemMock->method('temporaryUploadUrl')
            ->with('test.jpg', $this->anything(), [])
            ->willReturn([
                'url' => 'http://example.com/upload',
                'headers' => [],
            ]);
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 temporaryUploadUrl 方法
        $fileService = new FileService;
        $expiration = Carbon::now()->addHour();
        $result = $fileService->temporaryUploadUrl('test.jpg', $expiration, []);
        $this->assertIsArray($result);
    }
}
