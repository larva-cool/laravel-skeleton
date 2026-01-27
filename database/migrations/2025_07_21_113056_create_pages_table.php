<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Enum\StatusSwitch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 500)->comment('标题');
            $table->string('desc', 1000)->nullable()->comment('描述');
            $table->text('content')->comment('内容');
            $table->unsignedTinyInteger('status')->nullable()->default(StatusSwitch::ENABLED)->comment('状态');
            $table->unsignedBigInteger('admin_id')->nullable()->comment('管理员ID');
            $table->integer('order')->default(0)->comment('排序');
            $table->timestamps();
            $table->softDeletes();
            $table->comment('页面');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
