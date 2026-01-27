<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

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
        Schema::create('ai_agents', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('名称');
            $table->string('description')->nullable()->comment('描述');
            $table->string('model')->comment('模型');
            $table->text('prompt')->comment('系统提示词');
            $table->integer('max_tokens')->default(4096)->comment('模型回答最大长度（单位 token）。取值范围为 [1, 4096]。');
            $table->float('temperature')->default(0.7)->comment('取值范围为 [0, 2]。');
            $table->float('top_p')->default(0.5)->comment('取值范围为 [0, 1]。');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['model']);
            $table->comment('AI 智能体');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_agents');
    }
};
