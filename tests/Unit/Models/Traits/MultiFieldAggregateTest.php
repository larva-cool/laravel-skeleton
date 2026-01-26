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
}
