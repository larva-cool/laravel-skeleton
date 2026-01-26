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
        Schema::create('dicts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父ID');
            $table->string('name')->comment('字典名称');
            $table->string('description')->nullable()->comment('字典描述');
            $table->string('code')->comment('字典编码');
            $table->text('child_ids')->nullable()->comment('子ID');
            $table->unsignedTinyInteger('status')->default(StatusSwitch::ENABLED->value)->comment('状态');
            $table->unsignedInteger('order')->nullable()->default(99)->comment('排序');
            $table->timestamps();
            $table->softDeletes();
            $table->index('parent_id');
            $table->index('code');
            $table->comment('字典表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dicts');
    }
};
