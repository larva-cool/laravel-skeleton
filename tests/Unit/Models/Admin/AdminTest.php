<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Admin;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminRole;
use App\Models\User;
use App\Models\User\LoginHistory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(Admin::class)]
class AdminTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('测试 Admin 模型是否正确继承 Authenticatable')]
    public function test_admin_extends_authenticatable()
    {
        $admin = new Admin;

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $admin);
        $this->assertInstanceOf('Illuminate\Contracts\Auth\Authenticatable', $admin);
    }

    #[Test]
    #[TestDox('测试 Admin 模型的可填充属性')]
    public function test_admin_fillable_attributes()
    {
        $admin = new Admin;
        $fillable = $admin->getFillable();

        $this->assertIsArray($fillable);
        $this->assertContains('user_id', $fillable);
        $this->assertContains('username', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('socket_id', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('is_super', $fillable);
        $this->assertContains('last_login_ip', $fillable);
        $this->assertContains('login_count', $fillable);
        $this->assertContains('last_login_at', $fillable);
    }

    #[Test]
    #[TestDox('测试 Admin 模型与 User 模型的关联关系')]
    public function test_admin_belongs_to_user()
    {
        $admin = new Admin;
        $relation = $admin->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
        $this->assertEquals(User::class, $relation->getRelated()::class);
    }

    #[Test]
    #[TestDox('测试 Admin 模型与 LoginHistory 模型的关联关系')]
    public function test_admin_morph_many_login_histories()
    {
        $admin = new Admin;
        $relation = $admin->loginHistories();

        $this->assertInstanceOf(MorphMany::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('user_type', $relation->getMorphType());
        $this->assertEquals('id', $relation->getLocalKeyName());
        $this->assertEquals(LoginHistory::class, $relation->getRelated()::class);
    }

    #[Test]
    #[TestDox('测试 Admin 模型与 AdminRole 模型的关联关系')]
    public function test_admin_belongs_to_many_admin_roles()
    {
        $admin = new Admin;
        $relation = $admin->roles();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('admin_role_users', $relation->getTable());
        $this->assertEquals('user_id', $relation->getForeignPivotKeyName());
        $this->assertEquals('role_id', $relation->getRelatedPivotKeyName());
        $this->assertEquals(AdminRole::class, $relation->getRelated()::class);
    }

    #[Test]
    #[TestDox('测试 Admin 模型的 avatar 访问器')]
    public function test_admin_avatar_accessor()
    {
        // 创建用户
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '13800138000',
            'password' => bcrypt('password'),
        ]);

        // 创建管理员
        $admin = Admin::create([
            'user_id' => $user->id,
            'username' => 'test_admin',
            'password' => bcrypt('password'),
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'phone' => '13800138000',
            'status' => 1,
        ]);

        $this->assertNotNull($admin->avatar);
        $this->assertIsString($admin->avatar);
    }

    #[Test]
    #[TestDox('测试 Admin 模型的 isSuperAdmin 方法')]
    public function test_admin_is_super_admin_method()
    {
        // 创建用户
        $user1 = User::create([
            'name' => 'Test User 1',
            'email' => 'test1@example.com',
            'phone' => '13800138000',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'phone' => '13800138001',
            'password' => bcrypt('password'),
        ]);

        // 创建普通管理员
        $admin = Admin::create([
            'user_id' => $user1->id,
            'username' => 'test_admin',
            'password' => bcrypt('password'),
            'name' => 'Test Admin',
            'email' => 'test1@example.com',
            'phone' => '13800138000',
            'status' => 1,
            'is_super' => false,
        ]);

        // 创建超级管理员
        $superAdmin = Admin::create([
            'user_id' => $user2->id,
            'username' => 'super_admin',
            'password' => bcrypt('password'),
            'name' => 'Super Admin',
            'email' => 'test2@example.com',
            'phone' => '13800138001',
            'status' => 1,
            'is_super' => true,
        ]);

        // 测试普通管理员
        $this->assertFalse($admin->isSuperAdmin());

        // 测试超级管理员
        $this->assertTrue($superAdmin->isSuperAdmin());
    }

    #[Test]
    #[TestDox('测试 Admin 模型的表名')]
    public function test_admin_table_name()
    {
        $admin = new Admin;

        $this->assertEquals('admin_users', $admin->getTable());
    }

    #[Test]
    #[TestDox('测试 Admin 模型的主键')]
    public function test_admin_primary_key()
    {
        $admin = new Admin;

        $this->assertEquals('id', $admin->getKeyName());
        $this->assertTrue($admin->getIncrementing());
        $this->assertEquals('int', $admin->getKeyType());
    }

    #[Test]
    #[TestDox('测试 Admin 模型的 getRoleIds 方法')]
    public function test_admin_get_role_ids_method()
    {
        // 创建用户
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '13800138000',
            'password' => bcrypt('password'),
        ]);

        // 创建管理员
        $admin = Admin::create([
            'user_id' => $user->id,
            'username' => 'test_admin',
            'password' => bcrypt('password'),
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'phone' => '13800138000',
            'status' => 1,
        ]);

        $roleIds = $admin->getRoleIds();

        $this->assertIsArray($roleIds);
    }

    #[Test]
    #[TestDox('测试 Admin 模型的 routeNotificationForPhone 方法')]
    public function test_admin_route_notification_for_phone_method()
    {
        // 创建用户
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '13800138000',
            'password' => bcrypt('password'),
        ]);

        // 创建管理员
        $admin = Admin::create([
            'user_id' => $user->id,
            'username' => 'test_admin',
            'password' => bcrypt('password'),
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'phone' => '13800138000',
            'status' => 1,
        ]);

        $phone = $admin->routeNotificationForPhone(null);

        $this->assertEquals('13800138000', $phone);

        // 测试无手机号的情况（通过直接设置属性）
        $adminWithoutPhone = Admin::create([
            'user_id' => $user->id,
            'username' => 'test_admin_2',
            'password' => bcrypt('password'),
            'name' => 'Test Admin 2',
            'email' => 'test2@example.com',
            'phone' => '13800138001', // 必须提供手机号才能创建
            'status' => 1,
        ]);

        // 创建后设置为 null
        $adminWithoutPhone->phone = null;
        $adminWithoutPhone->save();

        $phoneWithout = $adminWithoutPhone->routeNotificationForPhone(null);

        $this->assertNull($phoneWithout);
    }
}
