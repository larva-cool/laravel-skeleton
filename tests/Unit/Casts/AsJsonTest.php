<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Casts;

use App\Casts\AsJson;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(AsJson::class)]
class AsJsonTest extends TestCase
{
    #[Test]
    #[TestDox('测试 get 方法从 JSON 字符串转换为数组')]
    public function test_get_method_converts_json_string_to_array(): void
    {
        $cast = new AsJson;
        $model = $this->createMock(Model::class);
        $jsonString = '{"key": "value", "number": 123}';

        $result = $cast->get($model, 'test', $jsonString, []);

        $this->assertIsArray($result);
        $this->assertEquals(['key' => 'value', 'number' => 123], $result);
    }

    #[Test]
    #[TestDox('测试 get 方法处理 null 值')]
    public function test_get_method_returns_empty_array_for_null_value(): void
    {
        $cast = new AsJson;
        $model = $this->createMock(Model::class);

        $result = $cast->get($model, 'test', null, []);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    #[TestDox('测试 get 方法处理空字符串')]
    public function test_get_method_returns_empty_array_for_empty_string(): void
    {
        $cast = new AsJson;
        $model = $this->createMock(Model::class);

        $result = $cast->get($model, 'test', '', []);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    #[TestDox('测试 set 方法从数组转换为 JSON 字符串')]
    public function test_set_method_converts_array_to_json_string(): void
    {
        $cast = new AsJson;
        $model = $this->createMock(Model::class);
        $arrayValue = ['key' => 'value', 'number' => 123];

        $result = $cast->set($model, 'test', $arrayValue, []);

        $this->assertIsString($result);
        $this->assertJson($result);
        $this->assertEquals(json_encode($arrayValue), $result);
    }

    #[Test]
    #[TestDox('测试 set 方法处理非数组值')]
    public function test_set_method_converts_non_array_to_json_string(): void
    {
        $cast = new AsJson;
        $model = $this->createMock(Model::class);
        $objectValue = (object) ['key' => 'value'];

        $result = $cast->set($model, 'test', $objectValue, []);

        $this->assertIsString($result);
        $this->assertJson($result);
        $this->assertEquals(json_encode((array) $objectValue), $result);
    }

    #[Test]
    #[TestDox('测试 set 方法处理空数组')]
    public function test_set_method_returns_null_for_empty_array(): void
    {
        $cast = new AsJson;
        $model = $this->createMock(Model::class);
        $emptyArray = [];

        $result = $cast->set($model, 'test', $emptyArray, []);

        $this->assertNull($result);
    }

    #[Test]
    #[TestDox('测试 set 方法处理 null 值')]
    public function test_set_method_returns_null_for_null_value(): void
    {
        $cast = new AsJson;
        $model = $this->createMock(Model::class);

        $result = $cast->set($model, 'test', null, []);

        $this->assertNull($result);
    }
}
