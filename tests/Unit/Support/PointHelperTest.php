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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

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
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // 创建一个测试用户
        $this->user = User::create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'name' => 'Test User',
        ]);
    }

    /**
     * 测试增加用户积分
     */
    #[Test]
    public function test_incr()
    {
        // Mock settings function
        $this->mockSettings();

        // Create a mock source model
        $source = Mockery::mock('Illuminate\Database\Eloquent\Model');
        $source->shouldReceive('getKey')->andReturn(1);
        $source->shouldReceive('getMorphClass')->andReturn('App\Models\User');

        // Increase points
        $trade = PointHelper::incr($this->user->id, 100, $source, PointType::TYPE_SIGN_IN, '签到获得积分');

        // Verify the trade was created
        $this->assertNotNull($trade);
        $this->assertEquals($this->user->id, $trade->user_id);
        $this->assertEquals(100, $trade->points);
        $this->assertEquals('App\Models\User', $trade->source_type);
        $this->assertEquals(1, $trade->source_id);
        $this->assertEquals(PointType::TYPE_SIGN_IN, $trade->type);
        $this->assertEquals('签到获得积分', $trade->description);

        // Verify a PointRecord was created
        $pointRecord = PointRecord::where('user_id', $this->user->id)->where('points', 100)->first();
        $this->assertNotNull($pointRecord);
        $this->assertEquals('签到获得积分', $pointRecord->description);

        // Verify user's available points was updated
        $user = User::find($this->user->id);
        $this->assertEquals(100, $user->available_points);
    }

    /**
     * 测试减少用户积分（积分足够）
     */
    #[Test]
    public function test_decr_with_sufficient_points()
    {
        // Mock settings function
        $this->mockSettings();

        // Create a mock source model
        $source = Mockery::mock('Illuminate\Database\Eloquent\Model');
        $source->shouldReceive('getKey')->andReturn(1);
        $source->shouldReceive('getMorphClass')->andReturn('App\Models\User');

        // First increase points
        PointHelper::incr($this->user->id, 100, $source, PointType::TYPE_SIGN_IN, '签到获得积分');

        // Then decrease points
        $result = PointHelper::decr($this->user->id, 50, $source, PointType::TYPE_RECOVERY, '测试消耗积分');

        // Verify the operation was successful
        $this->assertTrue($result);

        // Verify a negative PointTrade was created
        $trade = PointTrade::where('user_id', $this->user->id)->where('points', -50)->first();
        $this->assertNotNull($trade);
        $this->assertEquals(PointType::TYPE_RECOVERY, $trade->type);
        $this->assertEquals('测试消耗积分', $trade->description);

        // Verify user's available points was updated
        $user = User::find($this->user->id);
        $this->assertEquals(50, $user->available_points);
    }

    /**
     * 测试减少用户积分（积分不足）
     */
    #[Test]
    public function test_decr_with_insufficient_points()
    {
        // Mock settings function
        $this->mockSettings();

        // Create a mock source model
        $source = Mockery::mock('Illuminate\Database\Eloquent\Model');
        $source->shouldReceive('getKey')->andReturn(1);
        $source->shouldReceive('getMorphClass')->andReturn('App\Models\User');

        // Try to decrease points without having any
        $this->expectException(InsufficientPointsException::class);
        $this->expectExceptionMessage('积分不足，当前可用积分: 0');

        PointHelper::decr($this->user->id, 50, $source, PointType::TYPE_RECOVERY, '测试消耗积分');
    }

    /**
     * 测试处理过期积分
     */
    #[Test]
    public function test_handling_expired()
    {
        // Mock settings function
        $this->mockSettings();

        // Create a mock source model
        $source = Mockery::mock('Illuminate\Database\Eloquent\Model');
        $source->shouldReceive('getKey')->andReturn(1);
        $source->shouldReceive('getMorphClass')->andReturn('App\Models\User');

        // Create an expired point record
        $expiredTime = Carbon::now()->subDay();
        $record = PointRecord::create([
            'user_id' => $this->user->id,
            'points' => 100,
            'description' => 'Test Points',
            'expired_at' => $expiredTime,
        ]);

        // Update user's available points
        PointHelper::updatePointTotal($this->user->id);

        // Handle expired points
        PointHelper::handlingExpired($this->user->id);

        // Verify the expired record was deleted
        $deletedRecord = PointRecord::find($record->id);
        $this->assertNull($deletedRecord);

        // Verify a negative PointTrade was created for expired points
        $trade = PointTrade::where('user_id', $this->user->id)->where('points', -100)->first();
        $this->assertNotNull($trade);
        $this->assertEquals(PointType::TYPE_RECOVERY, $trade->type);
        $this->assertEquals('过期回收', $trade->description);

        // Verify user's available points was updated
        $user = User::find($this->user->id);
        $this->assertEquals(0, $user->available_points);
    }

    /**
     * 测试更新用户可用积分总额
     */
    #[Test]
    public function test_update_point_total()
    {
        // Create some point records
        PointRecord::create([
            'user_id' => $this->user->id,
            'points' => 50,
            'description' => 'Test Points 1',
            'expired_at' => Carbon::now()->addDays(30),
        ]);

        PointRecord::create([
            'user_id' => $this->user->id,
            'points' => 50,
            'description' => 'Test Points 2',
            'expired_at' => Carbon::now()->addDays(30),
        ]);

        // Update point total
        PointHelper::updatePointTotal($this->user->id);

        // Verify user's available points was updated
        $user = User::find($this->user->id);
        $this->assertEquals(100, $user->available_points);
    }

    /**
     * Mock the settings function
     */
    protected function mockSettings()
    {
        // Mock the settings function
        if (!function_exists('settings')) {
            function settings($key, $default = null) {
                return $default;
            }
        }
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
