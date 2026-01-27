<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Enum\PointType;
use App\Exceptions\InsufficientPointsException;
use App\Models\Point\PointRecord;
use App\Models\Point\PointTrade;
use App\Models\User;
use App\Support\PointHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

// Mock the settings function
if (! function_exists('settings')) {
    function settings($key, $default = null)
    {
        return $default;
    }
}

/**
 * 积分助手测试
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(PointHelper::class)]
class PointHelperTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试用户
     */
    protected User $user;

    /**
     * 测试来源模型
     */
    protected Model $source;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 创建测试用户
        $this->user = User::create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'name' => 'Test User',
            'available_points' => 0,
        ]);

        // 创建测试来源模型
        $this->source = User::create([
            'username' => 'sourceuser',
            'password' => bcrypt('password123'),
            'name' => 'Source User',
        ]);
    }

    /**
     * 测试增加用户积分功能
     */
    #[Test]
    #[TestDox('测试增加用户积分功能')]
    public function test_incr_increases_user_points()
    {
        // 增加用户积分
        $trade = PointHelper::incr($this->user->id, 100, $this->source, PointType::TYPE_SIGN_IN, '签到获得积分');

        // 验证交易记录创建成功
        $this->assertNotNull($trade);
        $this->assertEquals($this->user->id, $trade->user_id);
        $this->assertEquals(100, $trade->points);
        $this->assertEquals($this->source->id, $trade->source_id);
        $this->assertEquals(get_class($this->source), $trade->source_type);
        $this->assertEquals(PointType::TYPE_SIGN_IN, $trade->type);
        $this->assertEquals('签到获得积分', $trade->description);

        // 验证积分记录创建成功
        $pointRecord = PointRecord::where('user_id', $this->user->id)->first();
        $this->assertNotNull($pointRecord);
        $this->assertEquals(100, $pointRecord->points);

        // 验证用户可用积分更新成功
        $updatedUser = User::find($this->user->id);
        $this->assertEquals(100, $updatedUser->available_points);
    }

    /**
     * 测试减少用户积分功能（积分足够）
     */
    #[Test]
    #[TestDox('测试减少用户积分功能（积分足够）')]
    public function test_decr_decreases_user_points_when_sufficient()
    {
        // 先增加积分
        PointHelper::incr($this->user->id, 100, $this->source, PointType::TYPE_SIGN_IN, '签到获得积分');

        // 减少用户积分
        $result = PointHelper::decr($this->user->id, 50, $this->source, PointType::TYPE_RECOVERY, '测试消耗积分');

        // 验证操作成功
        $this->assertTrue($result);

        // 验证负积分交易记录创建成功
        $trade = PointTrade::where('user_id', $this->user->id)->where('points', -50)->first();
        $this->assertNotNull($trade);
        $this->assertEquals(PointType::TYPE_RECOVERY, $trade->type);
        $this->assertEquals('测试消耗积分', $trade->description);

        // 验证用户可用积分更新成功
        $updatedUser = User::find($this->user->id);
        $this->assertEquals(50, $updatedUser->available_points);
    }

    /**
     * 测试减少用户积分功能（积分不足）
     */
    #[Test]
    #[TestDox('测试减少用户积分功能（积分不足）')]
    public function test_decr_throws_exception_when_insufficient_points()
    {
        // 尝试减少用户积分（用户当前积分为0）
        $this->expectException(InsufficientPointsException::class);
        $this->expectExceptionMessage('积分不足，当前可用积分: 0');

        PointHelper::decr($this->user->id, 50, $this->source, PointType::TYPE_RECOVERY, '测试消耗积分');
    }

    /**
     * 测试减少用户积分功能（积分刚好足够）
     */
    #[Test]
    #[TestDox('测试减少用户积分功能（积分刚好足够）')]
    public function test_decr_decreases_all_points_when_exactly_sufficient()
    {
        // 先增加积分
        PointHelper::incr($this->user->id, 50, $this->source, PointType::TYPE_SIGN_IN, '签到获得积分');

        // 减少用户积分（刚好等于当前积分）
        $result = PointHelper::decr($this->user->id, 50, $this->source, PointType::TYPE_RECOVERY, '测试消耗积分');

        // 验证操作成功
        $this->assertTrue($result);

        // 验证用户可用积分更新成功
        $updatedUser = User::find($this->user->id);
        $this->assertEquals(0, $updatedUser->available_points);
    }

    /**
     * 测试减少用户积分功能（需要拆分积分记录）
     */
    #[Test]
    #[TestDox('测试减少用户积分功能（需要拆分积分记录）')]
    public function test_decr_splits_point_record_when_partially_used()
    {
        // 先增加两次积分，创建两条积分记录
        PointHelper::incr($this->user->id, 60, $this->source, PointType::TYPE_SIGN_IN, '签到获得积分1');
        PointHelper::incr($this->user->id, 60, $this->source, PointType::TYPE_SIGN_IN, '签到获得积分2');

        // 减少用户积分（需要使用部分第二条记录）
        $result = PointHelper::decr($this->user->id, 100, $this->source, PointType::TYPE_RECOVERY, '测试消耗积分');

        // 验证操作成功
        $this->assertTrue($result);

        // 验证用户可用积分更新成功
        $updatedUser = User::find($this->user->id);
        $this->assertEquals(20, $updatedUser->available_points);

        // 验证积分记录数量（应该剩下一条记录）
        $pointRecords = PointRecord::where('user_id', $this->user->id)->get();
        $this->assertCount(1, $pointRecords);
        $this->assertEquals(20, $pointRecords->first()->points);
    }

    /**
     * 测试处理过期积分功能
     */
    #[Test]
    #[TestDox('测试处理过期积分功能')]
    public function test_handling_expired_processes_expired_points()
    {
        // 创建一个过期的积分记录
        $expiredRecord = PointRecord::create([
            'user_id' => $this->user->id,
            'points' => 100,
            'description' => '过期积分',
            'expired_at' => Carbon::now()->subDay(), // 昨天过期
        ]);

        // 创建一个未过期的积分记录
        PointRecord::create([
            'user_id' => $this->user->id,
            'points' => 50,
            'description' => '未过期积分',
            'expired_at' => Carbon::now()->addDay(), // 明天过期
        ]);

        // 更新用户可用积分
        PointHelper::updatePointTotal($this->user->id);

        // 处理过期积分
        PointHelper::handlingExpired($this->user->id);

        // 验证过期积分记录被删除
        $this->assertNull(PointRecord::find($expiredRecord->id));

        // 验证未过期积分记录仍然存在
        $activeRecords = PointRecord::where('user_id', $this->user->id)->where('expired_at', '>', Carbon::now())->get();
        $this->assertCount(1, $activeRecords);
        $this->assertEquals(50, $activeRecords->first()->points);

        // 验证过期积分交易记录创建成功
        $expiredTrade = PointTrade::where('user_id', $this->user->id)->where('points', -100)->first();
        $this->assertNotNull($expiredTrade);
        $this->assertEquals(PointType::TYPE_RECOVERY, $expiredTrade->type);
        $this->assertEquals('过期回收', $expiredTrade->description);

        // 验证用户可用积分更新成功
        $updatedUser = User::find($this->user->id);
        $this->assertEquals(50, $updatedUser->available_points);
    }

    /**
     * 测试处理过期积分功能（无过期积分）
     */
    #[Test]
    #[TestDox('测试处理过期积分功能（无过期积分）')]
    public function test_handling_expired_handles_no_expired_points()
    {
        // 创建一个未过期的积分记录
        PointRecord::create([
            'user_id' => $this->user->id,
            'points' => 50,
            'description' => '未过期积分',
            'expired_at' => Carbon::now()->addDay(), // 明天过期
        ]);

        // 更新用户可用积分
        PointHelper::updatePointTotal($this->user->id);

        // 处理过期积分（应该没有过期积分）
        PointHelper::handlingExpired($this->user->id);

        // 验证积分记录仍然存在
        $pointRecords = PointRecord::where('user_id', $this->user->id)->get();
        $this->assertCount(1, $pointRecords);

        // 验证没有创建过期回收交易记录
        $expiredTrades = PointTrade::where('user_id', $this->user->id)->where('type', PointType::TYPE_RECOVERY)->get();
        $this->assertCount(0, $expiredTrades);

        // 验证用户可用积分保持不变
        $updatedUser = User::find($this->user->id);
        $this->assertEquals(50, $updatedUser->available_points);
    }

    /**
     * 测试更新用户可用积分总额功能
     */
    #[Test]
    #[TestDox('测试更新用户可用积分总额功能')]
    public function test_update_point_total_updates_user_available_points()
    {
        // 创建多个积分记录
        PointRecord::create([
            'user_id' => $this->user->id,
            'points' => 100,
            'description' => '积分1',
            'expired_at' => Carbon::now()->addDay(), // 明天过期
        ]);

        PointRecord::create([
            'user_id' => $this->user->id,
            'points' => 150,
            'description' => '积分2',
            'expired_at' => Carbon::now()->addDays(2), // 后天过期
        ]);

        // 更新用户可用积分总额
        PointHelper::updatePointTotal($this->user->id);

        // 验证用户可用积分更新成功
        $updatedUser = User::find($this->user->id);
        $this->assertEquals(250, $updatedUser->available_points);
    }

    /**
     * 测试更新用户可用积分总额功能（无积分记录）
     */
    #[Test]
    #[TestDox('测试更新用户可用积分总额功能（无积分记录）')]
    public function test_update_point_total_updates_user_available_points_when_no_records()
    {
        // 更新用户可用积分总额（无积分记录）
        PointHelper::updatePointTotal($this->user->id);

        // 验证用户可用积分更新为0
        $updatedUser = User::find($this->user->id);
        $this->assertEquals(0, $updatedUser->available_points);
    }
}
