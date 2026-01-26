<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Traits;

use App\Models\Traits\MultiFieldAggregate;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * MultiFieldAggregate trait 测试
 */
#[TestDox('MultiFieldAggregate trait 测试')]
class MultiFieldAggregateTest extends TestCase
{
    /**
     * 测试 trait 是否被正确使用
     */
    #[Test]
    #[TestDox('测试 trait 是否被正确使用')]
    public function test_trait_usage()
    {
        // 创建一个使用 MultiFieldAggregate trait 的测试类
        $testClass = new class
        {
            use MultiFieldAggregate;
        };

        // 验证 trait 是否被正确使用
        $usedTraits = array_keys(class_uses_recursive(get_class($testClass)));
        $this->assertContains('App\Models\Traits\MultiFieldAggregate', $usedTraits);

        // 验证方法是否存在
        $reflection = new \ReflectionClass($testClass);
        $this->assertTrue($reflection->hasMethod('scopeSumMultipleFields'));
        $this->assertTrue($reflection->hasMethod('scopeCountMultipleFields'));
        $this->assertTrue($reflection->hasMethod('scopeAverageMultipleFields'));
    }

    /**
     * 测试方法签名是否正确
     */
    #[Test]
    #[TestDox('测试方法签名是否正确')]
    public function test_method_signatures()
    {
        // 创建一个使用 MultiFieldAggregate trait 的测试类
        $testClass = new class
        {
            use MultiFieldAggregate;
        };

        // 验证 scopeSumMultipleFields 方法签名
        $reflection = new \ReflectionClass($testClass);
        $method = $reflection->getMethod('scopeSumMultipleFields');
        $this->assertEquals('array', $method->getReturnType()?->getName());
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);

        // 验证 scopeCountMultipleFields 方法签名
        $method = $reflection->getMethod('scopeCountMultipleFields');
        $this->assertEquals('array', $method->getReturnType()?->getName());
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);

        // 验证 scopeAverageMultipleFields 方法签名
        $method = $reflection->getMethod('scopeAverageMultipleFields');
        $this->assertEquals('array', $method->getReturnType()?->getName());
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
    }

    /**
     * 测试 scopeSumMultipleFields 方法
     */
    #[Test]
    #[TestDox('测试 scopeSumMultipleFields 方法')]
    public function test_scope_sum_multiple_fields()
    {
        // 创建一个使用 MultiFieldAggregate trait 的测试类
        $testClass = new class
        {
            use MultiFieldAggregate;
        };

        // 模拟 Builder 实例，不指定具体方法
        $builderMock = $this->getMockBuilder(\Illuminate\Database\Eloquent\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        // 使用 __call 魔术方法来处理 sum 调用
        $builderMock->method('__call')
            ->with($this->anything(), $this->anything())
            ->willReturnCallback(function ($method, $parameters) {
                if ($method === 'sum') {
                    $field = $parameters[0];
                    $values = [
                        'field1' => 100,
                        'field2' => 200,
                        'field3' => 300,
                    ];

                    return $values[$field] ?? 0;
                }

                return null;
            });

        // 使用反射调用 scopeSumMultipleFields 方法
        $reflection = new \ReflectionClass($testClass);
        $method = $reflection->getMethod('scopeSumMultipleFields');
        $result = $method->invoke($testClass, $builderMock, ['field1', 'field2', 'field3']);

        // 验证结果
        $this->assertIsArray($result);
        $this->assertEquals(3, count($result));
        $this->assertEquals(100, $result['field1']);
        $this->assertEquals(200, $result['field2']);
        $this->assertEquals(300, $result['field3']);
    }

    /**
     * 测试 scopeSumMultipleFields 方法 - 空字段数组
     */
    #[Test]
    #[TestDox('测试 scopeSumMultipleFields 方法 - 空字段数组')]
    public function test_scope_sum_multiple_fields_empty()
    {
        // 创建一个使用 MultiFieldAggregate trait 的测试类
        $testClass = new class
        {
            use MultiFieldAggregate;
        };

        // 模拟 Builder 实例
        $builderMock = $this->getMockBuilder(\Illuminate\Database\Eloquent\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        // 使用反射调用 scopeSumMultipleFields 方法
        $reflection = new \ReflectionClass($testClass);
        $method = $reflection->getMethod('scopeSumMultipleFields');
        $result = $method->invoke($testClass, $builderMock, []);

        // 验证结果
        $this->assertIsArray($result);
        $this->assertEquals(0, count($result));
    }

    /**
     * 测试 scopeCountMultipleFields 方法
     */
    #[Test]
    #[TestDox('测试 scopeCountMultipleFields 方法')]
    public function test_scope_count_multiple_fields()
    {
        // 创建一个使用 MultiFieldAggregate trait 的测试类
        $testClass = new class
        {
            use MultiFieldAggregate;
        };

        // 模拟 Builder 实例
        $builderMock = $this->getMockBuilder(\Illuminate\Database\Eloquent\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        // 模拟 whereNotNull 方法返回自身
        $builderMock->method('__call')
            ->with($this->anything(), $this->anything())
            ->willReturnCallback(function ($method, $parameters) use ($builderMock) {
                if ($method === 'whereNotNull') {
                    // 返回自身以支持链式调用
                    return $builderMock;
                } elseif ($method === 'count') {
                    // 根据字段名返回不同的计数结果
                    $field = $parameters[0] ?? '';
                    $values = [
                        'field1' => 10,
                        'field2' => 20,
                        'field3' => 30,
                    ];

                    return $values[$field] ?? 0;
                }

                return null;
            });

        // 使用反射调用 scopeCountMultipleFields 方法
        $reflection = new \ReflectionClass($testClass);
        $method = $reflection->getMethod('scopeCountMultipleFields');
        $result = $method->invoke($testClass, $builderMock, ['field1', 'field2', 'field3']);

        // 验证结果
        $this->assertIsArray($result);
        $this->assertEquals(3, count($result));
        $this->assertEquals(10, $result['field1']);
        $this->assertEquals(20, $result['field2']);
        $this->assertEquals(30, $result['field3']);
    }

    /**
     * 测试 scopeCountMultipleFields 方法 - 空字段数组
     */
    #[Test]
    #[TestDox('测试 scopeCountMultipleFields 方法 - 空字段数组')]
    public function test_scope_count_multiple_fields_empty()
    {
        // 创建一个使用 MultiFieldAggregate trait 的测试类
        $testClass = new class
        {
            use MultiFieldAggregate;
        };

        // 模拟 Builder 实例
        $builderMock = $this->getMockBuilder(\Illuminate\Database\Eloquent\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        // 使用反射调用 scopeCountMultipleFields 方法
        $reflection = new \ReflectionClass($testClass);
        $method = $reflection->getMethod('scopeCountMultipleFields');
        $result = $method->invoke($testClass, $builderMock, []);

        // 验证结果
        $this->assertIsArray($result);
        $this->assertEquals(0, count($result));
    }

    /**
     * 测试 scopeAverageMultipleFields 方法
     */
    #[Test]
    #[TestDox('测试 scopeAverageMultipleFields 方法')]
    public function test_scope_average_multiple_fields()
    {
        // 创建一个使用 MultiFieldAggregate trait 的测试类
        $testClass = new class
        {
            use MultiFieldAggregate;
        };

        // 模拟 Builder 实例
        $builderMock = $this->getMockBuilder(\Illuminate\Database\Eloquent\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        // 模拟 avg 方法返回不同的平均值结果
        $builderMock->method('__call')
            ->with($this->anything(), $this->anything())
            ->willReturnCallback(function ($method, $parameters) {
                if ($method === 'avg') {
                    // 根据字段名返回不同的平均值结果
                    $field = $parameters[0] ?? '';
                    $values = [
                        'field1' => 10.5,
                        'field2' => 20.75,
                        'field3' => 30.25,
                    ];

                    return $values[$field] ?? 0;
                }

                return null;
            });

        // 使用反射调用 scopeAverageMultipleFields 方法
        $reflection = new \ReflectionClass($testClass);
        $method = $reflection->getMethod('scopeAverageMultipleFields');
        $result = $method->invoke($testClass, $builderMock, ['field1', 'field2', 'field3']);

        // 验证结果
        $this->assertIsArray($result);
        $this->assertEquals(3, count($result));
        $this->assertEquals(10.5, $result['field1']);
        $this->assertEquals(20.75, $result['field2']);
        $this->assertEquals(30.25, $result['field3']);
    }

    /**
     * 测试 scopeAverageMultipleFields 方法 - 空字段数组
     */
    #[Test]
    #[TestDox('测试 scopeAverageMultipleFields 方法 - 空字段数组')]
    public function test_scope_average_multiple_fields_empty()
    {
        // 创建一个使用 MultiFieldAggregate trait 的测试类
        $testClass = new class
        {
            use MultiFieldAggregate;
        };

        // 模拟 Builder 实例
        $builderMock = $this->getMockBuilder(\Illuminate\Database\Eloquent\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        // 使用反射调用 scopeAverageMultipleFields 方法
        $reflection = new \ReflectionClass($testClass);
        $method = $reflection->getMethod('scopeAverageMultipleFields');
        $result = $method->invoke($testClass, $builderMock, []);

        // 验证结果
        $this->assertIsArray($result);
        $this->assertEquals(0, count($result));
    }
}
