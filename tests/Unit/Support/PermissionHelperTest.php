<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminMenu;
use App\Models\Admin\AdminRole;
use App\Support\PermissionHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(PermissionHelper::class)]
class PermissionHelperTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('测试获取超级管理员权限')]
    public function test_get_permissions_for_super_admin()
    {
        // 创建超级管理员用户
        $user = Admin::create([
            'username' => 'superadmin',
            'password' => bcrypt('password'),
            'is_super' => true,
            'phone' => '13800138000',
        ]);

        // 调用方法
        $permissions = PermissionHelper::getPermissions($user);

        // 验证结果
        $this->assertEquals(['*'], $permissions);
    }

    #[Test]
    #[TestDox('测试获取具有全部权限角色的用户权限')]
    public function test_get_permissions_with_all_permission_role()
    {
        // 创建角色
        $role = AdminRole::create([
            'name' => 'Admin Role',
            'rules' => '*',
        ]);

        // 创建普通用户
        $user = Admin::create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_super' => false,
            'phone' => '13800138001',
        ]);

        // 为用户分配角色
        $user->roles()->attach($role->id);

        // 调用方法
        $permissions = PermissionHelper::getPermissions($user);

        // 验证结果
        $this->assertEquals(['*'], $permissions);
    }

    #[Test]
    #[TestDox('测试获取普通用户权限')]
    public function test_get_permissions_for_normal_user()
    {
        // 创建菜单
        $menu1 = AdminMenu::create(['key' => 'dashboard', 'title' => 'Dashboard', 'type' => 1]);
        $menu2 = AdminMenu::create(['key' => 'users/list', 'title' => 'User List', 'type' => 1]);
        $menu3 = AdminMenu::create(['key' => 'users/create', 'title' => 'Create User', 'type' => 1]);
        $menu4 = AdminMenu::create(['key' => 'users/edit', 'title' => 'Edit User', 'type' => 1]);

        // 创建角色
        $role1 = AdminRole::create([
            'name' => 'Role 1',
            'rules' => $menu1->id.','.$menu2->id,
        ]);
        $role2 = AdminRole::create([
            'name' => 'Role 2',
            'rules' => $menu3->id.','.$menu4->id,
        ]);

        // 创建普通用户
        $user = Admin::create([
            'username' => 'user',
            'password' => bcrypt('password'),
            'is_super' => false,
            'phone' => '13800138002',
        ]);

        // 为用户分配角色
        $user->roles()->attach([$role1->id, $role2->id]);

        // 调用方法
        $permissions = PermissionHelper::getPermissions($user);

        // 验证结果
        $expected = ['dashboard', 'users.list', 'users.create', 'users.edit'];
        $this->assertEquals($expected, $permissions);
    }

    #[Test]
    #[TestDox('测试获取权限规则')]
    public function test_get_rules()
    {
        // 创建角色
        $role1 = AdminRole::create([
            'name' => 'Role 1',
            'rules' => '1,2',
        ]);
        $role2 = AdminRole::create([
            'name' => 'Role 2',
            'rules' => '3,4',
        ]);

        // 调用方法
        $rules = PermissionHelper::getRules([$role1->id, $role2->id]);

        // 验证结果
        $expected = ['1', '2', '3', '4'];
        $this->assertEquals($expected, $rules);
    }

    #[Test]
    #[TestDox('测试获取空角色ID的权限规则')]
    public function test_get_rules_with_empty_role_ids()
    {
        // 调用方法
        $rules = PermissionHelper::getRules([]);

        // 验证结果
        $this->assertEquals([], $rules);
    }

    #[Test]
    #[TestDox('测试获取包含空规则的权限规则')]
    public function test_get_rules_with_empty_rules()
    {
        // 创建角色
        $role1 = AdminRole::create([
            'name' => 'Role 1',
            'rules' => '1,2',
        ]);
        $role2 = AdminRole::create([
            'name' => 'Role 2',
            'rules' => '',
        ]);
        $role3 = AdminRole::create([
            'name' => 'Role 3',
            'rules' => '3,4',
        ]);

        // 调用方法
        $rules = PermissionHelper::getRules([$role1->id, $role2->id, $role3->id]);

        // 验证结果
        $expected = ['1', '2', '3', '4'];
        $this->assertEquals($expected, $rules);
    }

    #[Test]
    #[TestDox('测试移除空菜单')]
    public function test_empty_filter()
    {
        // 准备测试数据
        $menus = [
            ['type' => 1, 'children' => []],
            ['type' => 0, 'children' => []],
            [
                'type' => 0,
                'children' => [
                    ['type' => 1, 'children' => []],
                ],
            ],
            ['type' => 1, 'children' => [['type' => 1, 'children' => []]]],
        ];

        // 调用方法
        $result = PermissionHelper::emptyFilter($menus);

        // 验证结果
        $this->assertCount(3, $result);
        $this->assertEquals(1, $result[0]['type']);
        $this->assertEquals(0, $result[1]['type']);
        $this->assertEquals(1, $result[2]['type']);
    }

    #[Test]
    #[TestDox('测试移除不包含指定数据的数组')]
    public function test_remove_not_contain()
    {
        // 准备测试数据
        $array = [
            ['id' => 1, 'name' => 'Menu 1', 'children' => []],
            ['id' => 2, 'name' => 'Menu 2', 'children' => []],
            [
                'id' => 3,
                'name' => 'Menu 3',
                'children' => [
                    ['id' => 4, 'name' => 'Submenu 1', 'children' => []],
                    ['id' => 5, 'name' => 'Submenu 2', 'children' => []],
                ],
            ],
        ];

        // 调用方法
        PermissionHelper::removeNotContain($array, 'id', [1, 4]);

        // 重新索引数组
        $array = array_values($array);

        // 验证结果
        $this->assertCount(2, $array);
        $this->assertEquals(1, $array[0]['id']);
        $this->assertEquals(3, $array[1]['id']);
        $this->assertCount(1, $array[1]['children']);
        $this->assertEquals(4, $array[1]['children'][0]['id']);
    }

    #[Test]
    #[TestDox('测试判断数组是否包含指定数据')]
    public function test_array_contain()
    {
        // 准备测试数据
        $array1 = ['id' => 1, 'name' => 'Menu 1', 'children' => []];
        $array2 = [
            'id' => 2,
            'name' => 'Menu 2',
            'children' => [
                ['id' => 3, 'name' => 'Submenu 1', 'children' => []],
            ],
        ];
        $array3 = ['id' => 4, 'name' => 'Menu 3', 'children' => []];
        $nonArray = 'not an array';

        // 测试直接包含
        $result1 = PermissionHelper::arrayContain($array1, 'id', [1]);
        $this->assertTrue($result1);

        // 测试子数组包含
        $result2 = PermissionHelper::arrayContain($array2, 'id', [3]);
        $this->assertTrue($result2);

        // 测试不包含
        $result3 = PermissionHelper::arrayContain($array3, 'id', [1]);
        $this->assertFalse($result3);

        // 测试非数组
        $result4 = PermissionHelper::arrayContain($nonArray, 'id', [1]);
        $this->assertFalse($result4);
    }
}
