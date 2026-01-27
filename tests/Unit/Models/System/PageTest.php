<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\System;

use App\Enum\StatusSwitch;
use App\Models\System\Page;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 测试单页管理模型
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(Page::class)]
class PageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试模型使用了软删除特性
     */
    #[Test]
    #[TestDox('测试模型使用了软删除特性')]
    public function test_model_uses_soft_deletes()
    {
        $this->assertContains(SoftDeletes::class, class_uses(Page::class));
    }

    /**
     * 测试模型的表名设置
     */
    #[Test]
    #[TestDox('测试模型的表名设置')]
    public function test_model_has_correct_table_name()
    {
        $page = new Page;
        $this->assertEquals('pages', $page->getTable());
    }

    /**
     * 测试模型的可填充属性
     */
    #[Test]
    #[TestDox('测试模型的可填充属性')]
    public function test_model_has_correct_fillable_attributes()
    {
        $page = new Page;
        $fillable = $page->getFillable();

        $this->assertContains('title', $fillable);
        $this->assertContains('desc', $fillable);
        $this->assertContains('content', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('order', $fillable);
        $this->assertContains('admin_id', $fillable);
    }

    /**
     * 测试模型的默认属性值
     */
    #[Test]
    #[TestDox('测试模型的默认属性值')]
    public function test_model_has_correct_default_attributes()
    {
        $page = new Page;
        $this->assertEquals(StatusSwitch::ENABLED, $page->status);
    }

    /**
     * 测试模型的属性类型转换
     */
    #[Test]
    #[TestDox('测试模型的属性类型转换')]
    public function test_model_has_correct_attribute_casts()
    {
        $page = new Page;
        $casts = $page->getCasts();

        $this->assertEquals('integer', $casts['id']);
        $this->assertEquals('string', $casts['title']);
        $this->assertEquals('string', $casts['desc']);
        $this->assertEquals('string', $casts['content']);
        $this->assertEquals(StatusSwitch::class, $casts['status']);
        $this->assertEquals('integer', $casts['admin_id']);
        $this->assertEquals('integer', $casts['order']);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
    }

    /**
     * 测试创建单页记录
     */
    #[Test]
    #[TestDox('测试创建单页记录')]
    public function test_create_page()
    {
        $page = Page::create([
            'title' => '测试单页',
            'desc' => '测试单页描述',
            'content' => '测试单页内容',
            'status' => StatusSwitch::ENABLED,
            'order' => 1,
            'admin_id' => 1,
        ]);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('测试单页', $page->title);
        $this->assertEquals('测试单页描述', $page->desc);
        $this->assertEquals('测试单页内容', $page->content);
        $this->assertEquals(StatusSwitch::ENABLED, $page->status);
        $this->assertEquals(1, $page->order);
        $this->assertEquals(1, $page->admin_id);
    }

    /**
     * 测试更新单页记录
     */
    #[Test]
    #[TestDox('测试更新单页记录')]
    public function test_update_page()
    {
        $page = Page::create([
            'title' => '测试单页',
            'desc' => '测试单页描述',
            'content' => '测试单页内容',
            'status' => StatusSwitch::ENABLED,
            'order' => 1,
            'admin_id' => 1,
        ]);

        $page->update([
            'title' => '更新后的测试单页',
            'desc' => '更新后的测试单页描述',
            'content' => '更新后的测试单页内容',
            'status' => StatusSwitch::DISABLED,
            'order' => 2,
            'admin_id' => 2,
        ]);

        $this->assertEquals('更新后的测试单页', $page->title);
        $this->assertEquals('更新后的测试单页描述', $page->desc);
        $this->assertEquals('更新后的测试单页内容', $page->content);
        $this->assertEquals(StatusSwitch::DISABLED, $page->status);
        $this->assertEquals(2, $page->order);
        $this->assertEquals(2, $page->admin_id);
    }

    /**
     * 测试软删除单页记录
     */
    #[Test]
    #[TestDox('测试软删除单页记录')]
    public function test_soft_delete_page()
    {
        $page = Page::create([
            'title' => '测试单页',
            'desc' => '测试单页描述',
            'content' => '测试单页内容',
            'status' => StatusSwitch::ENABLED,
            'order' => 1,
            'admin_id' => 1,
        ]);

        $pageId = $page->id;
        $page->delete();

        // 测试记录是否被软删除
        $this->assertNotNull($page->deleted_at);

        // 测试通过ID查询是否能找到（使用withTrashed）
        $deletedPage = Page::withTrashed()->find($pageId);
        $this->assertNotNull($deletedPage);
        $this->assertNotNull($deletedPage->deleted_at);

        // 测试普通查询是否找不到
        $notFoundPage = Page::find($pageId);
        $this->assertNull($notFoundPage);
    }

    /**
     * 测试状态枚举类型
     */
    #[Test]
    #[TestDox('测试状态枚举类型')]
    public function test_status_enum_type()
    {
        $page = Page::create([
            'title' => '测试单页',
            'desc' => '测试单页描述',
            'content' => '测试单页内容',
            'status' => StatusSwitch::ENABLED,
            'order' => 1,
            'admin_id' => 1,
        ]);

        $this->assertInstanceOf(StatusSwitch::class, $page->status);
        $this->assertEquals(StatusSwitch::ENABLED, $page->status);
        $this->assertEquals('ENABLED', $page->status->name);
        $this->assertEquals(1, $page->status->value);
    }

    /**
     * 测试默认状态值
     */
    #[Test]
    #[TestDox('测试默认状态值')]
    public function test_default_status_value()
    {
        $page = Page::create([
            'title' => '测试单页',
            'desc' => '测试单页描述',
            'content' => '测试单页内容',
            'order' => 1,
            'admin_id' => 1,
        ]);

        $this->assertEquals(StatusSwitch::ENABLED, $page->status);
    }
}
