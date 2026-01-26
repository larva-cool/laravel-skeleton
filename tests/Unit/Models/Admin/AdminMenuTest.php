<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Admin;

use App\Models\Admin\AdminMenu;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 管理员菜单模型测试
 */
class AdminMenuTest extends TestCase
{
    /**
     * 测试模型基本配置
     */
    #[Test]
    #[TestDox('测试模型基本配置')]
    public function test_model_basic_configuration(): void
    {
        $adminMenu = new AdminMenu;

        // 测试表名
        $this->assertEquals('admin_menus', $adminMenu->getTable());

        // 测试可填充字段
        $expectedFillable = ['id', 'parent_id', 'title', 'icon', 'key', 'href', 'type', 'order'];
        $this->assertEquals($expectedFillable, $adminMenu->getFillable());
    }

    /**
     * 测试字段类型转换
     */
    #[Test]
    #[TestDox('测试字段类型转换')]
    public function test_field_casts(): void
    {
        $adminMenu = new AdminMenu;
        $casts = $adminMenu->getCasts();

        $this->assertEquals('integer', $casts['id']);
        $this->assertEquals('integer', $casts['parent_id']);
        $this->assertEquals('string', $casts['title']);
        $this->assertEquals('string', $casts['icon']);
        $this->assertEquals('string', $casts['key']);
        $this->assertEquals('string', $casts['href']);
        $this->assertEquals('integer', $casts['type']);
        $this->assertEquals('integer', $casts['order']);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
    }

    /**
     * 测试 parent 关联关系
     */
    #[Test]
    #[TestDox('测试 parent 关联关系')]
    public function test_parent_relation(): void
    {
        $adminMenu = new AdminMenu;
        $relation = $adminMenu->parent();

        // 测试关联类型
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsTo', $relation);

        // 测试关联的模型和外键
        $this->assertEquals(AdminMenu::class, get_class($relation->getRelated()));
        $this->assertEquals('parent_id', $relation->getForeignKeyName());
    }

    /**
     * 测试 children 关联关系
     */
    #[Test]
    #[TestDox('测试 children 关联关系')]
    public function test_children_relation(): void
    {
        $adminMenu = new AdminMenu;
        $relation = $adminMenu->children();

        // 测试关联类型
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $relation);

        // 测试关联的模型和外键
        $this->assertEquals(AdminMenu::class, get_class($relation->getRelated()));
        $this->assertEquals('parent_id', $relation->getForeignKeyName());
    }

    /**
     * 测试 getKeys 方法
     */
    #[Test]
    #[TestDox('测试 getKeys 方法')]
    public function test_get_keys_method(): void
    {
        // 模拟缓存
        $cacheKey = md5('1,2,3');
        Cache::shouldReceive('get')->with($cacheKey)->andReturn(['menu1', 'menu2', 'menu3']);

        // 调用方法
        $result = AdminMenu::getKeys([1, 2, 3]);

        // 验证结果
        $this->assertEquals(['menu1', 'menu2', 'menu3'], $result);
    }

    /**
     * 测试 getDefaultMenus 方法
     */
    #[Test]
    #[TestDox('测试 getDefaultMenus 方法')]
    public function test_get_default_menus_method(): void
    {
        // 调用方法
        $result = AdminMenu::getDefaultMenus();

        // 验证结果是数组
        $this->assertIsArray($result);
    }
}
