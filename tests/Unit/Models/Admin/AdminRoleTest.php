<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Admin;

use App\Models\Admin\AdminRole;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 管理员角色模型测试
 */
class AdminRoleTest extends TestCase
{
    /**
     * 测试模型基本配置
     */
    #[Test]
    #[TestDox('测试模型基本配置')]
    public function test_model_basic_configuration(): void
    {
        $adminRole = new AdminRole;

        // 测试表名
        $this->assertEquals('admin_roles', $adminRole->getTable());

        // 测试可填充字段
        $expectedFillable = ['id', 'name', 'desc', 'rules'];
        $this->assertEquals($expectedFillable, $adminRole->getFillable());
    }

    /**
     * 测试字段类型转换
     */
    #[Test]
    #[TestDox('测试字段类型转换')]
    public function test_field_casts(): void
    {
        $adminRole = new AdminRole;
        $casts = $adminRole->getCasts();

        $this->assertEquals('integer', $casts['id']);
        $this->assertEquals('string', $casts['name']);
        $this->assertEquals('string', $casts['desc']);
        $this->assertEquals('string', $casts['rules']);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
    }
}
