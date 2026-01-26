<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\System;

use App\Enum\StatusSwitch;
use App\Models\System\Dict;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(Dict::class)]
#[TestDox('字典模型测试')]
class DictTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('测试创建字典记录')]
    public function test_create_dict()
    {
        // 创建一个基本的字典记录
        $dict = Dict::create([
            'name' => '测试字典',
            'code' => 'test_dict',
            'description' => '这是一个测试字典',
            'status' => StatusSwitch::ENABLED->value,
            'order' => 10,
        ]);

        // 验证记录是否成功创建
        $this->assertDatabaseHas('dicts', [
            'id' => $dict->id,
            'name' => '测试字典',
            'code' => 'test_dict',
            'description' => '这是一个测试字典',
            'status' => StatusSwitch::ENABLED->value,
            'order' => 10,
        ]);
    }

    #[Test]
    #[TestDox('测试更新字典记录')]
    public function test_update_dict()
    {
        // 创建一个字典记录
        $dict = Dict::create([
            'name' => '测试字典',
            'code' => 'test_dict',
            'description' => '这是一个测试字典',
        ]);

        // 更新记录
        $dict->update([
            'name' => '更新后的字典',
            'description' => '这是更新后的字典描述',
        ]);

        // 验证更新是否成功
        $this->assertDatabaseHas('dicts', [
            'id' => $dict->id,
            'name' => '更新后的字典',
            'description' => '这是更新后的字典描述',
        ]);
    }

    #[Test]
    #[TestDox('测试软删除字典记录')]
    public function test_soft_delete_dict()
    {
        // 创建一个字典记录
        $dict = Dict::create([
            'name' => '测试字典',
            'code' => 'test_dict',
        ]);

        // 软删除记录
        $dict->delete();

        // 验证记录是否被软删除
        $this->assertSoftDeleted('dicts', [
            'id' => $dict->id,
        ]);

        // 尝试通过ID查找，应该找不到
        $this->expectException(ModelNotFoundException::class);
        Dict::findOrFail($dict->id);

        // 但可以在软删除的记录中找到
        $deletedDict = Dict::withTrashed()->find($dict->id);
        $this->assertNotNull($deletedDict);
        $this->assertTrue($deletedDict->trashed());
    }

    #[Test]
    #[TestDox('测试恢复被删除的字典记录')]
    public function test_restore_deleted_dict()
    {
        // 创建并软删除一个字典记录
        $dict = Dict::create([
            'name' => '测试字典',
            'code' => 'test_dict',
        ]);
        $dict->delete();

        // 恢复记录
        $dict->restore();

        // 验证记录是否被恢复
        $restoredDict = Dict::find($dict->id);
        $this->assertNotNull($restoredDict);
        $this->assertFalse($restoredDict->trashed());
    }

    #[Test]
    #[TestDox('测试字典的父子关系')]
    public function test_parent_child_relationship()
    {
        // 创建父字典
        $parentDict = Dict::create([
            'name' => '父字典',
            'code' => 'parent_dict',
        ]);

        // 创建子字典
        $childDict = Dict::create([
            'name' => '子字典',
            'code' => 'child_dict',
            'parent_id' => $parentDict->id,
        ]);

        // 验证关系
        $this->assertInstanceOf(Dict::class, $childDict->parent);
        $this->assertEquals($parentDict->id, $childDict->parent->id);
        $this->assertCount(1, $parentDict->children);
        $this->assertEquals($childDict->id, $parentDict->children->first()->id);

        // 验证子ID是否被正确设置
        $parentDict->refresh();
        $this->assertEquals($childDict->id, $parentDict->child_ids);
    }

    #[Test]
    #[TestDox('测试获取子字典ID列表')]
    public function test_get_children_ids()
    {
        // 创建父字典
        $parentDict = Dict::create([
            'name' => '父字典',
            'code' => 'parent_dict',
        ]);

        // 创建两个子字典
        $child1 = Dict::create([
            'name' => '子字典1',
            'code' => 'child_dict1',
            'parent_id' => $parentDict->id,
        ]);
        $child2 = Dict::create([
            'name' => '子字典2',
            'code' => 'child_dict2',
            'parent_id' => $parentDict->id,
        ]);

        // 测试getChildrenIds方法
        $childrenIds = $parentDict->getChildrenIds();
        $this->assertCount(2, $childrenIds);
        $this->assertContains($child1->id, $childrenIds);
        $this->assertContains($child2->id, $childrenIds);
    }

    #[Test]
    #[TestDox('测试静态方法获取子字典ID')]
    public function test_get_child_ids_static()
    {
        // 创建父字典
        $parentDict = Dict::create([
            'name' => '父字典',
            'code' => 'parent_dict',
        ]);

        // 创建两个子字典
        $child1 = Dict::create([
            'name' => '子字典1',
            'code' => 'child_dict1',
            'parent_id' => $parentDict->id,
        ]);
        $child2 = Dict::create([
            'name' => '子字典2',
            'code' => 'child_dict2',
            'parent_id' => $parentDict->id,
        ]);

        // 测试getChildIds静态方法
        $childIds = Dict::getChildIds($parentDict->id);
        $this->assertEquals($child1->id.','.$child2->id, $childIds);
    }

    #[Test]
    #[TestDox('测试通过ID获取字典名称')]
    public function test_get_name_by_id()
    {
        // 创建一个字典记录
        $dict = Dict::create([
            'name' => '测试字典',
            'code' => 'test_dict',
        ]);

        // 测试getNameById静态方法
        $name = Dict::getNameById($dict->id);
        $this->assertEquals('测试字典', $name);

        // 测试不存在的ID
        $nonExistentName = Dict::getNameById(9999);
        $this->assertNull($nonExistentName);
    }

    #[Test]
    #[TestDox('测试通过ID获取字典代码')]
    public function test_get_code_by_id()
    {
        // 创建一个字典记录
        $dict = Dict::create([
            'name' => '测试字典',
            'code' => 'test_dict',
        ]);

        // 测试getCodeById静态方法
        $code = Dict::getCodeById($dict->id);
        $this->assertEquals('test_dict', $code);

        // 测试不存在的ID
        $nonExistentCode = Dict::getCodeById(9999);
        $this->assertNull($nonExistentCode);
    }

    #[Test]
    #[TestDox('测试缺少必填字段时创建字典失败')]
    public function test_create_dict_without_required_fields()
    {
        // 尝试创建没有name和code的记录
        $this->expectException(\Illuminate\Database\QueryException::class);
        Dict::create([]);
    }

    #[Test]
    #[TestDox('测试字典状态验证')]
    public function test_dict_status_validation()
    {
        // 创建一个状态为0的字典记录
        $dict = Dict::create([
            'name' => '测试字典',
            'code' => 'test_dict',
            'status' => StatusSwitch::ENABLED->value,
        ]);
        $this->assertEquals(StatusSwitch::ENABLED, $dict->status);

        // 更新状态为1
        $dict->update(['status' => StatusSwitch::DISABLED->value]);
        $this->assertEquals(StatusSwitch::DISABLED, $dict->status);
    }
}
