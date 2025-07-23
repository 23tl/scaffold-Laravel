<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Addon;

class AddonMakeCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'addon:make {name : 插件名称} 
                            {--author= : 插件作者}
                            {--description= : 插件描述}
                            {--addon-version=1.0.0 : 插件版本}';

    /**
     * 命令描述
     */
    protected $description = '创建一个新的插件';

    /**
     * 执行命令
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $author = $this->option('author') ?: 'Unknown';
        $description = $this->option('description') ?: '';
        $version = $this->option('addon-version');

        // 验证插件名称
        if (!$this->isValidAddonName($name)) {
            $this->error('插件名称只能包含字母、数字和下划线，且必须以字母开头。');
            return 1;
        }

        $addonPath = app_path('addons/' . $name);

        // 检查插件是否已存在
        if (File::exists($addonPath)) {
            $this->error("插件 '{$name}' 已经存在。");
            return 1;
        }

        // 创建插件目录
        File::makeDirectory($addonPath, 0755, true);

        // 生成插件文件
        $this->generateAddonFiles($addonPath, $name, $author, $description, $version);

        // 创建数据库记录
        $this->createAddonRecord($name, $author, $description, $version, $addonPath);

        $this->info("插件 '{$name}' 创建成功！");
        $this->info("插件路径: {$addonPath}");
        $this->line('');
        $this->line('下一步:');
        $this->line("1. 运行数据库迁移: php artisan migrate");
        $this->line("2. 激活插件: php artisan addon:activate {$name}");
        $this->line("3. 编辑插件文件实现具体功能");

        return 0;
    }

    /**
     * 验证插件名称
     */
    protected function isValidAddonName(string $name): bool
    {
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $name);
    }

    /**
     * 生成插件文件
     */
    protected function generateAddonFiles(string $addonPath, string $name, string $author, string $description, string $version): void
    {
        // 生成主插件文件
        $this->generateMainAddonFile($addonPath, $name, $author, $description, $version);
        
        // 生成配置文件
        $this->generateConfigFile($addonPath, $name);
        
        // 生成README文件
        $this->generateReadmeFile($addonPath, $name, $author, $description, $version);
        
        // 生成控制器
        $this->generateControllerFile($addonPath, $name);
        
        // 生成路由文件
        $this->generateRoutesFile($addonPath, $name);
        
        // 生成模型
        $this->generateModelFile($addonPath, $name);
        
        // 生成请求验证类
        $this->generateRequestFile($addonPath, $name);
        
        // 生成服务类
        $this->generateServiceFile($addonPath, $name);
        
        // 生成数据库迁移
        $this->generateMigrationFile($addonPath, $name);
    }

    /**
     * 生成主插件文件
     */
    protected function generateMainAddonFile(string $addonPath, string $name, string $author, string $description, string $version): void
    {
        $content = <<<PHP
<?php

namespace App\\Addons\\{$name};

use App\\Plugins\\BasePlugin;
use App\\Plugins\\HookManager;
use Illuminate\\Support\\Facades\\Log;

/**
 * {$name} 插件
 * 
 * @author {$author}
 * @version {$version}
 */
class {$name} extends BasePlugin
{
    /**
     * 插件名称
     */
    protected string \$name = '{$name}';
    
    /**
     * 插件版本
     */
    protected string \$version = '{$version}';
    
    /**
     * 插件描述
     */
    protected string \$description = '{$description}';
    
    /**
     * 插件作者
     */
    protected string \$author = '{$author}';
    
    /**
     * 插件依赖
     */
    protected array \$dependencies = [];
    
    /**
     * 插件激活时调用
     */
    public function activate(): void
    {
        Log::info('Addon activated: ' . \$this->name);
        
        // 在这里添加插件激活时的逻辑
        // 例如：创建数据库表、初始化配置等
    }
    
    /**
     * 插件停用时调用
     */
    public function deactivate(): void
    {
        Log::info('Addon deactivated: ' . \$this->name);
        
        // 在这里添加插件停用时的逻辑
        // 例如：清理缓存、移除临时文件等
    }
    
    /**
     * 注册钩子
     */
    public function registerHooks(HookManager \$hookManager): void
    {
        // 注册动作钩子示例
        \$hookManager->addAction('user.created', [\$this, 'onUserCreated'], 10, 1);
        
        // 注册过滤器钩子示例
        \$hookManager->addFilter('api.response.data', [\$this, 'filterApiResponse'], 10, 2);
        
        // 注册自定义钩子
        \$hookManager->addAction('{$name}.custom_action', [\$this, 'handleCustomAction']);
    }
    
    /**
     * 用户创建时的处理
     */
    public function onUserCreated(\$user): void
    {
        Log::info('User created event triggered', ['user_id' => \$user->id]);
        
        // 在这里添加用户创建时的处理逻辑
    }
    
    /**
     * 过滤API响应数据
     */
    public function filterApiResponse(\$data, \$request)
    {
        // 在这里添加API响应数据的过滤逻辑
        // 例如：添加额外字段、格式化数据等
        
        return \$data;
    }
    
    /**
     * 处理自定义动作
     */
    public function handleCustomAction(): void
    {
        Log::info('Custom action triggered for ' . \$this->name);
        
        // 在这里添加自定义动作的处理逻辑
    }
    
    /**
     * 获取插件信息
     */
    public function getInfo(): array
    {
        return [
            'name' => \$this->name,
            'version' => \$this->version,
            'description' => \$this->description,
            'author' => \$this->author,
            'dependencies' => \$this->dependencies,
        ];
    }
}
PHP;

        File::put($addonPath . '/' . $name . '.php', $content);
    }

    /**
     * 生成配置文件
     */
    protected function generateConfigFile(string $addonPath, string $name): void
    {
        $content = <<<PHP
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | {$name} 插件配置
    |--------------------------------------------------------------------------
    |
    | 这里定义了 {$name} 插件的配置选项
    |
    */

    // 插件是否启用
    'enabled' => true,
    
    // 插件设置
    'settings' => [
        'example_setting' => 'example_value',
        'cache_enabled' => true,
        'debug_mode' => false,
    ],
    
    // 插件权限
    'permissions' => [
        'manage_{$name}' => '管理{$name}插件',
        'view_{$name}' => '查看{$name}插件',
        'edit_{$name}' => '编辑{$name}插件',
    ],
    
    // 数据库配置
    'database' => [
        'table_prefix' => '{$name}_',
        'connection' => 'default',
    ],
];
PHP;

        File::put($addonPath . '/config.php', $content);
    }

    /**
     * 生成README文件
     */
    protected function generateReadmeFile(string $addonPath, string $name, string $author, string $description, string $version): void
    {
        $content = <<<MD
# {$name} 插件

## 描述

{$description}

## 版本

{$version}

## 作者

{$author}

## 安装

1. 使用命令创建插件：
   ```bash
   php artisan addon:make {$name}
   ```

2. 运行数据库迁移：
   ```bash
   php artisan migrate
   ```

3. 激活插件：
   ```bash
   php artisan addon:activate {$name}
   ```

## 使用

插件激活后会自动注册相关钩子和功能。

### API 接口

- `GET /api/addons/{$name}/info` - 获取插件信息
- `GET /api/addons/{$name}/data` - 获取插件数据
- `POST /api/addons/{$name}/data` - 创建插件数据
- `PUT /api/addons/{$name}/data/{id}` - 更新插件数据
- `DELETE /api/addons/{$name}/data/{id}` - 删除插件数据

### Web 界面

- `/addons/{$name}` - 插件主页
- `/addons/{$name}/settings` - 插件设置页
- `/addons/{$name}/manage` - 插件管理页

## 配置

插件配置文件位于 `config.php`，可以根据需要修改配置选项。

## 数据库

插件使用独立的数据表存储数据，表名为 `{$name}_data`。

## 钩子

### 动作钩子

- `user.created`: 用户创建时触发
- `{$name}.custom_action`: 自定义动作

### 过滤器钩子

- `api.response.data`: 过滤API响应数据

## 开发

### 目录结构

```
{$name}/
├── {$name}.php              # 主插件文件
├── config.php               # 配置文件
├── routes.php               # 路由文件
├── README.md                # 说明文档
├── Controllers/             # 控制器目录
│   └── {$name}Controller.php
├── Models/                  # 模型目录
│   └── {$name}Data.php
├── Requests/                # 请求验证目录
│   └── {$name}Request.php
├── Services/                # 服务目录
│   └── {$name}Service.php
└── Migrations/              # 数据库迁移目录
    └── create_{$name}_data_table.php
```

### 添加新功能

1. 在主插件文件中添加新的方法
2. 在 `registerHooks` 方法中注册相应的钩子
3. 重新激活插件以应用更改

### 调试

插件会自动记录日志，可以在 Laravel 日志文件中查看插件相关的日志信息。

## 许可证

MIT
MD;

        File::put($addonPath . '/README.md', $content);
    }

    /**
     * 生成控制器文件
     */
    protected function generateControllerFile(string $addonPath, string $name): void
    {
        $content = <<<PHP
<?php

namespace App\\Addons\\{$name}\\Controllers;

use App\\Http\\Controllers\\Controller;
use App\\Http\\Responses\\ApiResponse;
use Illuminate\\Http\\Request;
use App\\Addons\\{$name}\\Models\\{$name}Data;
use App\\Addons\\{$name}\\Requests\\{$name}Request;
use App\\Addons\\{$name}\\Services\\{$name}Service;

/**
 * {$name} 插件控制器
 */
class {$name}Controller extends Controller
{
    protected {$name}Service \$service;

    public function __construct({$name}Service \$service)
    {
        \$this->service = \$service;
    }

    /**
     * 获取插件信息
     */
    public function info()
    {
        return ApiResponse::success([
            'name' => '{$name}',
            'status' => 'active',
            'message' => '{$name} 插件运行正常',
            'version' => '1.0.0',
        ]);
    }

    /**
     * 获取插件数据列表
     */
    public function index(Request \$request)
    {
        \$perPage = \$request->get('per_page', 15);
        \$data = \$this->service->getPaginatedData(\$perPage);
        
        return ApiResponse::success(\$data);
    }

    /**
     * 创建插件数据
     */
    public function store({$name}Request \$request)
    {
        \$data = \$this->service->createData(\$request->validated());
        
        return ApiResponse::success(\$data, '数据创建成功');
    }

    /**
     * 获取单个插件数据
     */
    public function show(\$id)
    {
        \$data = \$this->service->getDataById(\$id);
        
        if (!\$data) {
            return ApiResponse::error('数据不存在', 404);
        }
        
        return ApiResponse::success(\$data);
    }

    /**
     * 更新插件数据
     */
    public function update({$name}Request \$request, \$id)
    {
        \$data = \$this->service->updateData(\$id, \$request->validated());
        
        if (!\$data) {
            return ApiResponse::error('数据不存在', 404);
        }
        
        return ApiResponse::success(\$data, '数据更新成功');
    }

    /**
     * 删除插件数据
     */
    public function destroy(\$id)
    {
        \$result = \$this->service->deleteData(\$id);
        
        if (!\$result) {
            return ApiResponse::error('数据不存在', 404);
        }
        
        return ApiResponse::success([], '数据删除成功');
    }

    /**
     * 插件主页
     */
    public function home()
    {
        \$stats = \$this->service->getStats();
        
        return view('addons.{$name}.home', compact('stats'));
    }

    /**
     * 插件设置页
     */
    public function settings()
    {
        \$config = config('addons.{$name}', []);
        
        return view('addons.{$name}.settings', compact('config'));
    }

    /**
     * 插件管理页
     */
    public function manage()
    {
        \$data = \$this->service->getAllData();
        
        return view('addons.{$name}.manage', compact('data'));
    }

    /**
     * 执行插件动作
     */
    public function action(Request \$request)
    {
        \$action = \$request->get('action');
        \$params = \$request->get('params', []);
        
        // 根据动作执行相应的逻辑
        switch (\$action) {
            case 'test':
                return ApiResponse::success(['message' => '测试动作执行成功']);
            default:
                return ApiResponse::error('未知动作', 400);
        }
    }

    /**
     * 测试过滤器
     */
    public function testFilter(Request \$request)
    {
        \$data = \$request->all();
        
        // 应用过滤器
        \$filtered = apply_filters('{$name}.test_filter', \$data, \$request);
        
        return ApiResponse::success(\$filtered);
    }

    /**
     * 获取插件配置
     */
    public function getConfig()
    {
        \$config = config('addons.{$name}', []);
        
        return ApiResponse::success(\$config);
    }

    /**
     * 设置插件配置
     */
    public function setConfig(Request \$request)
    {
        \$validated = \$request->validate([
            'config' => 'required|array',
        ]);
        
        // 这里可以添加保存配置的逻辑
        
        return ApiResponse::success([], '配置保存成功');
    }
}
PHP;

        $controllerPath = $addonPath . '/Controllers';
        File::makeDirectory($controllerPath, 0755, true);
        File::put($controllerPath . '/' . $name . 'Controller.php', $content);
    }

    /**
     * 生成路由文件
     */
    protected function generateRoutesFile(string $addonPath, string $name): void
    {
        $lowerName = Str::lower($name);
        $content = <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;
use App\\Addons\\{$name}\\Controllers\\{$name}Controller;

/*
|--------------------------------------------------------------------------
| {$name} 插件路由
|--------------------------------------------------------------------------
|
| 这里定义了 {$name} 插件的路由
|
*/

// API 路由
Route::prefix('api')->group(function () {
    // 插件信息
    Route::get('/info', [{$name}Controller::class, 'info']);
    
    // 数据管理 CRUD
    Route::get('/data', [{$name}Controller::class, 'index']);
    Route::post('/data', [{$name}Controller::class, 'store']);
    Route::get('/data/{id}', [{$name}Controller::class, 'show']);
    Route::put('/data/{id}', [{$name}Controller::class, 'update']);
    Route::delete('/data/{id}', [{$name}Controller::class, 'destroy']);
    
    // 插件动作
    Route::post('/action', [{$name}Controller::class, 'action']);
    
    // 测试过滤器
    Route::post('/test-filter', [{$name}Controller::class, 'testFilter']);
    
    // 配置管理
    Route::get('/config', [{$name}Controller::class, 'getConfig']);
    Route::post('/config', [{$name}Controller::class, 'setConfig']);
});

// Web 路由
Route::prefix('web')->group(function () {
    // 插件主页
    Route::get('/', [{$name}Controller::class, 'home'])->name('addon.{$lowerName}.home');
    
    // 插件设置页
    Route::get('/settings', [{$name}Controller::class, 'settings'])->name('addon.{$lowerName}.settings');
    
    // 插件管理页
    Route::get('/manage', [{$name}Controller::class, 'manage'])->name('addon.{$lowerName}.manage');
});
PHP;

        File::put($addonPath . '/routes.php', $content);
    }

    /**
     * 生成模型文件
     */
    protected function generateModelFile(string $addonPath, string $name): void
    {
        $tableName = Str::snake($name) . '_data';
        $content = <<<PHP
<?php

namespace App\\Addons\\{$name}\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\SoftDeletes;

/**
 * {$name} 数据模型
 */
class {$name}Data extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 数据表名
     */
    protected \$table = '{$tableName}';

    /**
     * 可批量赋值的属性
     */
    protected \$fillable = [
        'name',
        'value',
        'metadata',
        'is_active',
        'category',
        'sort_order',
    ];

    /**
     * 属性转换
     */
    protected \$casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 默认属性值
     */
    protected \$attributes = [
        'is_active' => true,
        'sort_order' => 0,
        'metadata' => '{}',
    ];

    /**
     * 查询作用域：激活状态
     */
    public function scopeActive(\$query)
    {
        return \$query->where('is_active', true);
    }

    /**
     * 查询作用域：按分类
     */
    public function scopeByCategory(\$query, \$category)
    {
        return \$query->where('category', \$category);
    }

    /**
     * 查询作用域：按排序
     */
    public function scopeOrdered(\$query)
    {
        return \$query->orderBy('sort_order')->orderBy('created_at');
    }

    /**
     * 根据名称查找数据
     */
    public static function findByName(string \$name)
    {
        return static::where('name', \$name)->first();
    }

    /**
     * 设置元数据
     */
    public function setMetadata(string \$key, \$value): void
    {
        \$metadata = \$this->metadata ?: [];
        \$metadata[\$key] = \$value;
        \$this->metadata = \$metadata;
        \$this->save();
    }

    /**
     * 获取元数据
     */
    public function getMetadata(string \$key, \$default = null)
    {
        \$metadata = \$this->metadata ?: [];
        return \$metadata[\$key] ?? \$default;
    }

    /**
     * 激活数据
     */
    public function activate(): bool
    {
        \$this->is_active = true;
        return \$this->save();
    }

    /**
     * 停用数据
     */
    public function deactivate(): bool
    {
        \$this->is_active = false;
        return \$this->save();
    }
}
PHP;

        $modelPath = $addonPath . '/Models';
        File::makeDirectory($modelPath, 0755, true);
        File::put($modelPath . '/' . $name . 'Data.php', $content);
    }

    /**
     * 生成请求验证类
     */
    protected function generateRequestFile(string $addonPath, string $name): void
    {
        $content = <<<PHP
<?php

namespace App\\Addons\\{$name}\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

/**
 * {$name} 请求验证类
 */
class {$name}Request extends FormRequest
{
    /**
     * 确定用户是否有权限进行此请求
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 获取应用于请求的验证规则
     */
    public function rules(): array
    {
        \$rules = [
            'name' => 'required|string|max:255',
            'value' => 'nullable|string',
            'metadata' => 'nullable|array',
            'is_active' => 'boolean',
            'category' => 'nullable|string|max:100',
            'sort_order' => 'integer|min:0',
        ];

        // 根据请求方法调整验证规则
        if (\$this->isMethod('POST')) {
            // 创建时的特殊规则
            \$rules['name'] .= '|unique:' . config('addons.{$name}.database.table_prefix', '{$name}_') . 'data,name';
        } elseif (\$this->isMethod('PUT') || \$this->isMethod('PATCH')) {
            // 更新时的特殊规则
            \$id = \$this->route('id');
            \$rules['name'] .= '|unique:' . config('addons.{$name}.database.table_prefix', '{$name}_') . 'data,name,' . \$id;
        }

        return \$rules;
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'name.required' => '名称字段是必需的',
            'name.string' => '名称必须是字符串',
            'name.max' => '名称不能超过255个字符',
            'name.unique' => '该名称已经存在',
            'value.string' => '值必须是字符串',
            'metadata.array' => '元数据必须是数组格式',
            'is_active.boolean' => '激活状态必须是布尔值',
            'category.string' => '分类必须是字符串',
            'category.max' => '分类不能超过100个字符',
            'sort_order.integer' => '排序必须是整数',
            'sort_order.min' => '排序不能小于0',
        ];
    }

    /**
     * 获取验证属性的自定义名称
     */
    public function attributes(): array
    {
        return [
            'name' => '名称',
            'value' => '值',
            'metadata' => '元数据',
            'is_active' => '激活状态',
            'category' => '分类',
            'sort_order' => '排序',
        ];
    }

    /**
     * 配置验证器实例
     */
    public function withValidator(\$validator): void
    {
        \$validator->after(function (\$validator) {
            // 自定义验证逻辑
            if (\$this->has('metadata') && !empty(\$this->metadata)) {
                foreach (\$this->metadata as \$key => \$value) {
                    if (!is_string(\$key)) {
                        \$validator->errors()->add('metadata', '元数据的键必须是字符串');
                        break;
                    }
                }
            }
        });
    }
}
PHP;

        $requestPath = $addonPath . '/Requests';
        File::makeDirectory($requestPath, 0755, true);
        File::put($requestPath . '/' . $name . 'Request.php', $content);
    }

    /**
     * 生成服务类
     */
    protected function generateServiceFile(string $addonPath, string $name): void
    {
        $content = <<<PHP
<?php

namespace App\\Addons\\{$name}\\Services;

use App\\Addons\\{$name}\\Models\\{$name}Data;
use Illuminate\\Pagination\\LengthAwarePaginator;
use Illuminate\\Support\\Collection;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Log;

/**
 * {$name} 服务类
 */
class {$name}Service
{
    /**
     * 获取所有数据
     */
    public function getAllData(): Collection
    {
        return {$name}Data::ordered()->get();
    }

    /**
     * 获取分页数据
     */
    public function getPaginatedData(int \$perPage = 15): LengthAwarePaginator
    {
        return {$name}Data::ordered()->paginate(\$perPage);
    }

    /**
     * 根据ID获取数据
     */
    public function getDataById(int \$id): ?{$name}Data
    {
        return {$name}Data::find(\$id);
    }

    /**
     * 根据名称获取数据
     */
    public function getDataByName(string \$name): ?{$name}Data
    {
        return {$name}Data::findByName(\$name);
    }

    /**
     * 创建数据
     */
    public function createData(array \$data): {$name}Data
    {
        try {
            \$item = {$name}Data::create(\$data);
            
            Log::info('{$name} data created', ['id' => \$item->id, 'name' => \$item->name]);
            
            return \$item;
        } catch (\\Exception \$e) {
            Log::error('Failed to create {$name} data', ['error' => \$e->getMessage(), 'data' => \$data]);
            throw \$e;
        }
    }

    /**
     * 更新数据
     */
    public function updateData(int \$id, array \$data): ?{$name}Data
    {
        try {
            \$item = \$this->getDataById(\$id);
            
            if (!\$item) {
                return null;
            }
            
            \$item->update(\$data);
            
            Log::info('{$name} data updated', ['id' => \$item->id, 'name' => \$item->name]);
            
            return \$item->fresh();
        } catch (\\Exception \$e) {
            Log::error('Failed to update {$name} data', ['id' => \$id, 'error' => \$e->getMessage(), 'data' => \$data]);
            throw \$e;
        }
    }

    /**
     * 删除数据
     */
    public function deleteData(int \$id): bool
    {
        try {
            \$item = \$this->getDataById(\$id);
            
            if (!\$item) {
                return false;
            }
            
            \$result = \$item->delete();
            
            Log::info('{$name} data deleted', ['id' => \$id]);
            
            return \$result;
        } catch (\\Exception \$e) {
            Log::error('Failed to delete {$name} data', ['id' => \$id, 'error' => \$e->getMessage()]);
            throw \$e;
        }
    }

    /**
     * 批量删除数据
     */
    public function batchDelete(array \$ids): int
    {
        try {
            \$count = {$name}Data::whereIn('id', \$ids)->delete();
            
            Log::info('{$name} data batch deleted', ['count' => \$count, 'ids' => \$ids]);
            
            return \$count;
        } catch (\\Exception \$e) {
            Log::error('Failed to batch delete {$name} data', ['ids' => \$ids, 'error' => \$e->getMessage()]);
            throw \$e;
        }
    }

    /**
     * 激活数据
     */
    public function activateData(int \$id): bool
    {
        \$item = \$this->getDataById(\$id);
        
        if (!\$item) {
            return false;
        }
        
        return \$item->activate();
    }

    /**
     * 停用数据
     */
    public function deactivateData(int \$id): bool
    {
        \$item = \$this->getDataById(\$id);
        
        if (!\$item) {
            return false;
        }
        
        return \$item->deactivate();
    }

    /**
     * 更新排序
     */
    public function updateSortOrder(array \$sortData): bool
    {
        try {
            DB::transaction(function () use (\$sortData) {
                foreach (\$sortData as \$item) {
                    {$name}Data::where('id', \$item['id'])
                        ->update(['sort_order' => \$item['sort_order']]);
                }
            });
            
            return true;
        } catch (\\Exception \$e) {
            Log::error('Failed to update sort order', ['error' => \$e->getMessage()]);
            return false;
        }
    }

    /**
     * 获取统计信息
     */
    public function getStats(): array
    {
        return [
            'total' => {$name}Data::count(),
            'active' => {$name}Data::active()->count(),
            'inactive' => {$name}Data::where('is_active', false)->count(),
            'categories' => {$name}Data::distinct('category')->count('category'),
        ];
    }

    /**
     * 搜索数据
     */
    public function searchData(string \$keyword, int \$perPage = 15): LengthAwarePaginator
    {
        return {$name}Data::where(function (\$query) use (\$keyword) {
            \$query->where('name', 'like', "%{\$keyword}%")
                  ->orWhere('value', 'like', "%{\$keyword}%")
                  ->orWhere('category', 'like', "%{\$keyword}%");
        })->ordered()->paginate(\$perPage);
    }

    /**
     * 按分类获取数据
     */
    public function getDataByCategory(string \$category, int \$perPage = 15): LengthAwarePaginator
    {
        return {$name}Data::byCategory(\$category)->ordered()->paginate(\$perPage);
    }
}
PHP;

        $servicePath = $addonPath . '/Services';
        File::makeDirectory($servicePath, 0755, true);
        File::put($servicePath . '/' . $name . 'Service.php', $content);
    }

    /**
     * 生成数据库迁移文件
     */
    protected function generateMigrationFile(string $addonPath, string $name): void
    {
        $tableName = Str::snake($name) . '_data';
        $className = 'Create' . Str::studly($name) . 'DataTable';
        $timestamp = date('Y_m_d_His');
        $fileName = $timestamp . '_create_' . $tableName . '_table.php';
        
        $content = <<<PHP
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

/**
 * {$name} 数据表迁移
 */
return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name')->unique()->comment('名称');
            \$table->text('value')->nullable()->comment('值');
            \$table->json('metadata')->nullable()->comment('元数据');
            \$table->boolean('is_active')->default(true)->comment('是否激活');
            \$table->string('category', 100)->nullable()->comment('分类');
            \$table->integer('sort_order')->default(0)->comment('排序');
            \$table->timestamps();
            \$table->softDeletes();
            
            // 索引
            \$table->index(['is_active']);
            \$table->index(['category']);
            \$table->index(['sort_order']);
            \$table->index(['created_at']);
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
PHP;

        $migrationPath = $addonPath . '/Migrations';
        File::makeDirectory($migrationPath, 0755, true);
        File::put($migrationPath . '/' . $fileName, $content);
        
        // 同时复制到Laravel的migrations目录
        $laravelMigrationPath = database_path('migrations');
        File::copy($migrationPath . '/' . $fileName, $laravelMigrationPath . '/' . $fileName);
    }

    /**
     * 创建插件数据库记录
     */
    protected function createAddonRecord(string $name, string $author, string $description, string $version, string $path): void
    {
        try {
            $addon = new Addon();
            $addon->name = $name;
            $addon->version = $version;
            $addon->description = $description;
            $addon->author = $author;
            $addon->main_file = $name . '.php';
            $addon->namespace = "App\\Addons\\{$name}\\{$name}";
            $addon->status = 'inactive';
            $addon->is_installed = true;
            $addon->dependencies = [];
            $addon->config = [
                'enabled' => true,
                'settings' => [
                    'example_setting' => 'example_value',
                ],
            ];
            $addon->routes = [];
            $addon->installed_at = now();
            $addon->save();
            
            $this->info("数据库记录创建成功: {$name}");
        } catch (\Exception $e) {
            $this->error("创建数据库记录失败: " . $e->getMessage());
        }
    }
}