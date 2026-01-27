<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\System;

use App\Models\System\AiAgent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(AiAgent::class)]
class AiAgentTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    #[TestDox('测试模型基本属性')]
    public function test_basic_properties()
    {
        // 创建模型实例
        $model = new AiAgent;

        // 测试表名
        $this->assertEquals('ai_agents', $model->getTable());

        // 测试可填充字段
        $fillable = ['id', 'name', 'description', 'model', 'prompt', 'max_tokens', 'temperature', 'top_p'];
        $this->assertEquals($fillable, $model->getFillable());

        // 测试默认值
        $this->assertEquals(4096, $model->max_tokens);
        $this->assertEquals(0.7, $model->temperature);
        $this->assertEquals(0.5, $model->top_p);

        // 测试是否使用了软删除
        $this->assertContains(SoftDeletes::class, class_uses(AiAgent::class));
    }

    #[Test]
    #[TestDox('测试模型创建和保存')]
    public function test_model_creation()
    {
        // 创建测试数据
        $data = [
            'name' => 'Test Agent',
            'description' => 'Test Description',
            'model' => 'gpt-3.5-turbo',
            'prompt' => 'You are a helpful assistant.',
            'max_tokens' => 2048,
            'temperature' => 0.8,
            'top_p' => 0.9,
        ];

        // 创建模型
        $agent = AiAgent::create($data);

        // 验证数据是否正确保存
        $this->assertEquals('Test Agent', $agent->name);
        $this->assertEquals('Test Description', $agent->description);
        $this->assertEquals('gpt-3.5-turbo', $agent->model);
        $this->assertEquals('You are a helpful assistant.', $agent->prompt);
        $this->assertEquals(2048, $agent->max_tokens);
        $this->assertEquals(0.8, $agent->temperature);
        $this->assertEquals(0.9, $agent->top_p);

        // 验证模型是否存在于数据库
        $this->assertDatabaseHas('ai_agents', [
            'name' => 'Test Agent',
            'model' => 'gpt-3.5-turbo',
        ]);
    }

    #[Test]
    #[TestDox('测试软删除功能')]
    public function test_soft_delete()
    {
        // 创建测试数据
        $agent = AiAgent::create([
            'name' => 'Test Agent',
            'description' => 'Test Description',
            'model' => 'gpt-3.5-turbo',
            'prompt' => 'You are a helpful assistant.',
        ]);

        // 验证模型存在
        $this->assertDatabaseHas('ai_agents', ['name' => 'Test Agent']);

        // 删除模型
        $agent->delete();

        // 验证模型不存在于查询结果中
        $this->assertCount(0, AiAgent::where('name', 'Test Agent')->get());

        // 验证模型存在于数据库（软删除）
        $this->assertDatabaseHas('ai_agents', ['name' => 'Test Agent', 'deleted_at' => $agent->deleted_at]);

        // 验证可以通过 withTrashed 找到
        $this->assertCount(1, AiAgent::withTrashed()->where('name', 'Test Agent')->get());
    }

    #[Test]
    #[TestDox('测试 completions 方法')]
    public function test_completions_method()
    {
        // 创建测试模型
        $agent = AiAgent::create([
            'name' => 'Test Agent',
            'description' => 'Test Description',
            'model' => 'gpt-3.5-turbo',
            'prompt' => 'You are a helpful assistant.',
            'max_tokens' => 100,
            'temperature' => 0.7,
            'top_p' => 0.5,
        ]);

        // 测试方法存在
        $this->assertTrue(method_exists($agent, 'completions'));
    }

    #[Test]
    #[TestDox('测试 chat 方法')]
    public function test_chat_method()
    {
        // 创建测试模型
        $agent = AiAgent::create([
            'name' => 'Test Agent',
            'description' => 'Test Description',
            'model' => 'gpt-3.5-turbo',
            'prompt' => 'You are a helpful assistant.',
            'max_tokens' => 100,
            'temperature' => 0.7,
            'top_p' => 0.5,
        ]);

        // 测试方法存在
        $this->assertTrue(method_exists($agent, 'chat'));
    }

    #[Test]
    #[TestDox('测试默认值设置')]
    public function test_default_values()
    {
        // 创建只提供必要字段的模型
        $agent = AiAgent::create([
            'name' => 'Test Agent',
            'description' => 'Test Description',
            'model' => 'gpt-3.5-turbo',
            'prompt' => 'You are a helpful assistant.',
        ]);

        // 验证默认值
        $this->assertEquals(4096, $agent->max_tokens);
        $this->assertEquals(0.7, $agent->temperature);
        $this->assertEquals(0.5, $agent->top_p);

        // 验证数据已保存到数据库
        $this->assertDatabaseHas('ai_agents', [
            'name' => 'Test Agent',
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'top_p' => 0.5,
        ]);
    }
}
