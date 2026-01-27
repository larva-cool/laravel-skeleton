<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\BooleanRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(BooleanRequest::class)]
class BooleanRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('验证有效的请求数据通过验证')]
    public function test_valid_request_passes_validation()
    {
        $request = new BooleanRequest;

        $validData = [
            'id' => 1,
            'status' => true,
        ];

        $validator = $this->app['validator']->make($validData, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    #[TestDox('验证缺少 id 字段的请求失败')]
    public function test_request_fails_without_id()
    {
        $request = new BooleanRequest;

        $invalidData = [
            'status' => true,
        ];

        $validator = $this->app['validator']->make($invalidData, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('id', $validator->errors()->toArray());
    }

    #[Test]
    #[TestDox('验证 id 字段不是整数的请求失败')]
    public function test_request_fails_with_non_integer_id()
    {
        $request = new BooleanRequest;

        $invalidData = [
            'id' => 'string',
            'status' => true,
        ];

        $validator = $this->app['validator']->make($invalidData, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('id', $validator->errors()->toArray());
    }

    #[Test]
    #[TestDox('验证缺少 status 字段的请求失败')]
    public function test_request_fails_without_status()
    {
        $request = new BooleanRequest;

        $invalidData = [
            'id' => 1,
        ];

        $validator = $this->app['validator']->make($invalidData, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    #[Test]
    #[TestDox('验证 status 字段不是布尔值的请求失败')]
    public function test_request_fails_with_non_boolean_status()
    {
        $request = new BooleanRequest;

        $invalidData = [
            'id' => 1,
            'status' => 'string',
        ];

        $validator = $this->app['validator']->make($invalidData, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    #[Test]
    #[TestDox('验证类型转换方法返回正确的转换规则')]
    public function test_casts_method_returns_correct_casts()
    {
        $request = new BooleanRequest;

        $casts = $request->casts();

        $this->assertIsArray($casts);
        $this->assertEquals('integer', $casts['id']);
        $this->assertEquals('boolean', $casts['status']);
    }

    #[Test]
    #[TestDox('验证默认授权方法返回 true')]
    public function test_default_authorize_method_returns_true()
    {
        $request = new BooleanRequest;

        $this->assertTrue($request->authorize());
    }
}
