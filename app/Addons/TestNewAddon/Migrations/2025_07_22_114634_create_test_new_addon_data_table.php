<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TestNewAddon 数据表迁移
 */
return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        Schema::create('test_new_addon_data', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('名称');
            $table->text('value')->nullable()->comment('值');
            $table->json('metadata')->nullable()->comment('元数据');
            $table->boolean('is_active')->default(true)->comment('是否激活');
            $table->string('category', 100)->nullable()->comment('分类');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index(['is_active']);
            $table->index(['category']);
            $table->index(['sort_order']);
            $table->index(['created_at']);
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('test_new_addon_data');
    }
};