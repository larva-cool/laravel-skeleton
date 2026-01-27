<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Enum\CoinType;
use App\Exceptions\InsufficientCoinsException;
use App\Models\Coin\CoinTrade;
use App\Models\User;
use App\Support\CoinHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
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

// Mock the UserHelper class
if (! class_exists('App\Support\UserHelper')) {
    class UserHelper
    {
        public static function findById(int $id, bool $throw = false)
        {
            return User::find($id);
        }
    }
}

/**
 * 测试金币助手类
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(CoinHelper::class)]
class CoinHelperTest extends TestCase
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
            'available_coins' => 100,
        ]);

        // 创建测试来源模型
        $this->source = User::create([
            'username' => 'sourceuser',
            'password' => bcrypt('password123'),
            'name' => 'Source User',
        ]);
    }

    /**
     * 测试增加用户金币功能
     */
    #[Test]
    #[TestDox('测试增加用户金币功能')]
    public function test_incr_increases_user_coins()
    {
        // 增加用户金币
        $trade = CoinHelper::incr($this->user->id, 50, $this->source, CoinType::TYPE_SIGN_IN, '签到获得金币');

        // 验证交易记录创建成功
        $this->assertNotNull($trade);
        $this->assertEquals($this->user->id, $trade->user_id);
        $this->assertEquals(50, $trade->coins);
        $this->assertEquals($this->source->id, $trade->source_id);
        $this->assertEquals(get_class($this->source), $trade->source_type);
        $this->assertEquals(CoinType::TYPE_SIGN_IN, $trade->type);
        $this->assertEquals('签到获得金币', $trade->description);

        // 验证用户金币更新成功
        $updatedUser = User::find($this->user->id);
        $this->assertEquals(150, $updatedUser->available_coins);
    }

    /**
     * 测试使用用户对象增加金币功能
     */
    #[Test]
    #[TestDox('测试使用用户对象增加金币功能')]
    public function test_incr_increases_user_coins_with_user_object()
    {
        // 使用用户对象增加金币
        $trade = CoinHelper::incr($this->user, 50, $this->source, CoinType::TYPE_SIGN_IN, '签到获得金币');

        // 验证交易记录创建成功
        $this->assertNotNull($trade);
        $this->assertEquals($this->user->id, $trade->user_id);
        $this->assertEquals(50, $trade->coins);

        // 验证用户金币更新成功
        $updatedUser = User::find($this->user->id);
        $this->assertEquals(150, $updatedUser->available_coins);
    }

    /**
     * 测试增加金币时金币数量为负数的情况
     */
    #[Test]
    #[TestDox('测试增加金币时金币数量为负数的情况')]
    public function test_incr_throws_exception_when_coins_negative()
    {
        // 尝试增加负数金币
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('金币数量必须为正数');

        CoinHelper::incr($this->user->id, -50, $this->source, CoinType::TYPE_SIGN_IN, '测试增加负数金币');
    }

    /**
     * 测试增加金币时金币数量为零的情况
     */
    #[Test]
    #[TestDox('测试增加金币时金币数量为零的情况')]
    public function test_incr_throws_exception_when_coins_zero()
    {
        // 尝试增加零金币
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('金币数量必须为正数');

        CoinHelper::incr($this->user->id, 0, $this->source, CoinType::TYPE_SIGN_IN, '测试增加零金币');
    }

    /**
     * 测试扣除用户金币功能（金币足够）
     */
    #[Test]
    #[TestDox('测试扣除用户金币功能（金币足够）')]
    public function test_decr_decreases_user_coins_when_sufficient()
    {
        // 扣除用户金币
        $trade = CoinHelper::decr($this->user->id, 50, $this->source, CoinType::TYPE_UNKNOWN, '测试扣除金币');

        // 验证交易记录创建成功
        $this->assertNotNull($trade);
        $this->assertEquals($this->user->id, $trade->user_id);
        $this->assertEquals(-50, $trade->coins);
        $this->assertEquals($this->source->id, $trade->source_id);
        $this->assertEquals(get_class($this->source), $trade->source_type);
        $this->assertEquals(CoinType::TYPE_UNKNOWN, $trade->type);
        $this->assertEquals('测试扣除金币', $trade->description);

        // 验证用户金币更新成功
        $updatedUser = User::find($this->user->id);
        $this->assertEquals(50, $updatedUser->available_coins);
    }

    /**
     * 测试使用用户对象扣除金币功能
     */
    #[Test]
    #[TestDox('测试使用用户对象扣除金币功能')]
    public function test_decr_decreases_user_coins_with_user_object()
    {
        // 使用用户对象扣除金币
        $trade = CoinHelper::decr($this->user, 50, $this->source, CoinType::TYPE_UNKNOWN, '测试扣除金币');

        // 验证交易记录创建成功
        $this->assertNotNull($trade);
        $this->assertEquals($this->user->id, $trade->user_id);
        $this->assertEquals(-50, $trade->coins);

        // 验证用户金币更新成功
        $updatedUser = User::find($this->user->id);
        $this->assertEquals(50, $updatedUser->available_coins);
    }

    /**
     * 测试扣除金币时金币不足的情况
     */
    #[Test]
    #[TestDox('测试扣除金币时金币不足的情况')]
    public function test_decr_throws_exception_when_insufficient_coins()
    {
        // 尝试扣除超过用户拥有的金币
        $this->expectException(InsufficientCoinsException::class);

        CoinHelper::decr($this->user->id, 200, $this->source, CoinType::TYPE_UNKNOWN, '测试扣除过多金币');
    }

    /**
     * 测试获取用户当前金币余额功能
     */
    #[Test]
    #[TestDox('测试获取用户当前金币余额功能')]
    public function test_get_current_coins()
    {
        // 获取用户当前金币余额
        $currentCoins = CoinHelper::getCurrentCoins($this->user->id);

        // 验证结果
        $this->assertEquals(100, $currentCoins);
    }

    /**
     * 测试修复用户当前金币余额功能
     */
    #[Test]
    #[TestDox('测试修复用户当前金币余额功能')]
    public function test_fix_current_coins()
    {
        // 创建金币交易记录
        CoinTrade::create([
            'user_id' => $this->user->id,
            'coins' => 50,
            'description' => '签到获得金币',
            'type' => CoinType::TYPE_SIGN_IN,
            'source_id' => $this->user->id,
            'source_type' => User::class,
        ]);

        CoinTrade::create([
            'user_id' => $this->user->id,
            'coins' => -20,
            'description' => '消耗金币',
            'type' => CoinType::TYPE_UNKNOWN,
            'source_id' => $this->user->id,
            'source_type' => User::class,
        ]);

        // 修改用户金币余额为错误值
        $this->user->update(['available_coins' => 0]);

        // 修复用户金币余额
        $result = CoinHelper::fixCurrentCoins($this->user->id);

        // 验证操作成功
        $this->assertTrue($result);

        // 验证用户金币余额修复成功
        $updatedUser = User::find($this->user->id);
        // fixCurrentCoins 方法是计算所有交易记录的总和：50 - 20 = 30
        $this->assertEquals(30, $updatedUser->available_coins);
    }
}
