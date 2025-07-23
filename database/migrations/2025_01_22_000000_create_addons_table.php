<?php

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
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('插件名称');
            $table->string('version')->comment('插件版本');
            $table->string('description')->nullable()->comment('插件描述');
            $table->string('author')->nullable()->comment('插件作者');
            $table->json('dependencies')->nullable()->comment('插件依赖');
            $table->string('main_file')->comment('主文件路径');
            $table->string('namespace')->comment('插件命名空间');
            $table->boolean('is_active')->default(false)->comment('是否激活');
            $table->boolean('is_installed')->default(false)->comment('是否已安装');
            $table->json('config')->nullable()->comment('插件配置');
            $table->json('routes')->nullable()->comment('插件路由配置');
            $table->string('status')->default('inactive')->comment('插件状态: active, inactive, error');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamp('installed_at')->nullable()->comment('安装时间');
            $table->timestamp('activated_at')->nullable()->comment('激活时间');
            $table->timestamps();
            
            $table->index(['is_active', 'status']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addons');
    }
};