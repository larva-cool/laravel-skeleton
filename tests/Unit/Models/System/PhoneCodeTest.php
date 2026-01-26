<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\System;

use App\Models\System\PhoneCode;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 手机验证码模型测试
 */
#[CoversClass(PhoneCode::class)]
class PhoneCodeTest extends TestCase
{
    /**
     * 测试常量定义
     */
    #[Test]
    public function testConstants(): void
    {
        $this->assertEquals(1, PhoneCode::USED_STATE);
        $this->assertEquals('send_at', PhoneCode::CREATED_AT);
        $this->assertNull(PhoneCode::UPDATED_AT);
    }

    /**
     * 测试 fillable 属性
     */
    #[Test]
    public function testFillable(): void
    {
        $phoneCode = new PhoneCode;
        $fillable = $phoneCode->getFillable();

        $this->assertContains('scene', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('code', $fillable);
        $this->assertContains('ip', $fillable);
        $this->assertContains('state', $fillable);
        $this->assertContains('verify_count', $fillable);
        $this->assertContains('usage_at', $fillable);
        $this->assertContains('send_at', $fillable);
        $this->assertContains('result', $fillable);
    }

    /**
     * 测试 casts 属性
     */
    #[Test]
    public function testCasts(): void
    {
        $phoneCode = new PhoneCode;
        $casts = $phoneCode->getCasts();

        $this->assertEquals('integer', $casts['id']);
        $this->assertEquals('string', $casts['scene']);
        $this->assertEquals('string', $casts['ip']);
        $this->assertEquals('integer', $casts['state']);
        $this->assertEquals('string', $casts['code']);
        $this->assertEquals('integer', $casts['verify_count']);
        $this->assertEquals('datetime', $casts['send_at']);
        $this->assertEquals('datetime', $casts['usage_at']);
    }

    /**
     * 测试 user 关联
     */
    #[Test]
    public function testUserRelation(): void
    {
        $phoneCode = new PhoneCode;
        $relation = $phoneCode->user();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsTo', $relation);
    }

    /**
     * 测试 prunable 方法
     */
    #[Test]
    public function testPrunable(): void
    {
        $phoneCode = new PhoneCode;
        $result = $phoneCode->prunable();

        $this->assertInstanceOf(Builder::class, $result);
    }

    /**
     * 测试表名
     */
    #[Test]
    public function testTableName(): void
    {
        $phoneCode = new PhoneCode;
        $this->assertEquals('phone_codes', $phoneCode->getTable());
    }
}
