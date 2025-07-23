# Laravel 插件系统

这是一个基于 Laravel 12 的完整插件系统，参考了 WordPress 的插件架构设计，包含两套插件管理机制：

1. **Plugins** - 轻量级插件机制（文件存储）
2. **Addons** - 完整插件系统（数据库管理）

本项目实现了一个类似 WordPress 的插件机制，允许开发者创建可插拔的功能模块。系统已完全实现并经过测试，支持插件的动态加载、激活、停用和管理。

## 功能特性

### 核心功能
- 🔌 **双重插件系统**: Plugins（文件存储）+ Addons（数据库管理）
- 🔄 **插件管理**: 动态加载、激活、停用插件
- 🪝 **钩子系统**: 支持动作钩子(Actions)和过滤器钩子(Filters)
- 📦 **插件生成**: 快速生成插件模板
- 🔒 **安全机制**: 插件签名验证、文件类型限制
- ⚡ **性能优化**: 插件缓存、按需加载

### 管理工具
- 🎛️ **Web管理界面**: 提供完整的插件和扩展管理界面
- 🔧 **API接口**: 支持Plugin和Addon的RESTful API
- 💾 **数据库管理**: Addon系统支持数据库存储和管理
- 🔄 **目录同步**: 自动同步文件系统和数据库状态

## 系统架构

### Plugin系统（轻量级）
位于 `app/Plugins/` 目录，使用文件存储管理插件状态。

#### 核心组件
- **PluginManager**: 插件加载、激活、停用管理
- **HookManager**: 钩子系统实现
- **BasePlugin**: 插件基类
- **PluginInterface**: 插件接口定义

### Addon系统（完整版）
位于 `app/addons/` 目录，使用数据库管理插件状态和信息。

#### 核心组件
- **PluginManager**: 完整的插件管理器，支持数据库操作、插件加载、路由注册
- **BasePlugin**: 插件基类，实现 PluginInterface 接口
- **Addon Model**: 数据库模型，存储插件信息和状态
- **AddonServiceProvider**: 服务提供者，注册Addon服务和全局辅助函数
- **命令行工具**: `addon:make`, `addon:sync`, `addon:list`, `addon:activate`, `addon:deactivate`

#### 数据库表结构
```sql
addons表字段：
- name: 插件名称
- version: 版本号
- description: 描述
- author: 作者
- dependencies: 依赖插件（JSON数组）
- main_file: 主文件路径
- namespace: 命名空间
- is_active: 是否激活
- is_installed: 是否已安装
- config: 配置信息（JSON）
- routes: 路由配置（JSON）
- status: 当前状态（active/inactive/error/installing/uninstalling）
- error_message: 错误信息
- installed_at: 安装时间
- activated_at: 激活时间
```

### 详细组件说明

#### 1. PluginManager (插件管理器)
- 负责插件的发现、加载、激活和停用
- 管理插件状态和依赖关系
- 位置: `app/Plugins/PluginManager.php`

#### 2. HookManager (钩子管理器)
- 实现动作钩子和过滤器钩子机制
- 支持优先级和参数限制
- 位置: `app/Plugins/HookManager.php`

#### 3. BasePlugin (基础插件类)
- 所有插件的基类
- 提供通用功能和接口实现
- 位置: `app/Plugins/BasePlugin.php`

#### 4. PluginInterface (插件接口)
- 定义插件必须实现的方法
- 位置: `app/Plugins/Contracts/PluginInterface.php`

## 快速开始

### 1. 创建 Addon 插件

```bash
# 创建一个新的 Addon 插件
php artisan addon:make MyAddon --author="Your Name" --description="插件描述" --addon-version="1.0.0"
```

### 2. Addon 插件结构

```
app/addons/MyAddon/
├── MyAddon.php           # 主插件文件
├── config.php            # 插件配置
├── routes.php            # 路由文件
├── README.md             # 插件说明
├── Controllers/          # 控制器目录
│   └── MyAddonController.php
├── Models/               # 模型目录
│   └── MyAddonData.php
├── Services/             # 服务目录
│   └── MyAddonService.php
├── Requests/             # 请求验证目录
│   └── MyAddonRequest.php
└── Migrations/           # 数据库迁移目录
    └── create_my_addon_data_table.php
```

### 3. 创建 Plugin 插件（轻量级）

```bash
# 创建一个新的 Plugin 插件
php artisan plugin:make MyPlugin --author="Your Name" --description="插件描述" --plugin-version="1.0.0"
```

### 4. Plugin 插件结构

```
app/Plugins/MyPlugin/
├── MyPlugin.php          # 主插件文件
├── config.php            # 插件配置
├── README.md             # 插件说明
├── Controllers/          # 控制器目录
│   └── ExampleController.php
└── routes.php            # 路由文件
```

### 3. 插件示例

```php
<?php

namespace App\Plugins\MyPlugin;

use App\Plugins\BasePlugin;

class MyPlugin extends BasePlugin
{
    protected string $name = 'MyPlugin';
    protected string $version = '1.0.0';
    protected string $description = '我的第一个插件';
    protected string $author = 'Your Name';

    protected function onActivate(): void
    {
        // 插件激活时执行的代码
        $this->log('插件已激活');
    }

    protected function onDeactivate(): void
    {
        // 插件停用时执行的代码
        $this->log('插件已停用');
    }

    protected function onRegisterHooks(): void
    {
        // 注册钩子
        $this->addAction('user.created', [$this, 'onUserCreated']);
        $this->addFilter('api.response.data', [$this, 'filterApiResponse']);
    }

    public function onUserCreated($user)
    {
        // 用户创建时的处理逻辑
        $this->log("新用户创建: {$user->name}");
    }

    public function filterApiResponse($data)
    {
        // 过滤API响应数据
        $data['plugin_processed'] = true;
        return $data;
    }
}
```

## 管理命令

### Plugin 命令

```bash
# 创建插件
php artisan plugin:make PluginName --author="作者" --description="描述" --plugin-version="1.0.0"

# 列出所有插件
php artisan plugin:list

# 只查看已激活的插件
php artisan plugin:list --active

# 激活插件
php artisan plugin:activate MyPlugin

# 停用插件
php artisan plugin:deactivate MyPlugin

# 同步插件
php artisan plugin:sync
```

### Addon 命令

```bash
# 创建 Addon（推荐使用）
php artisan addon:make AddonName --author="作者" --description="描述" --addon-version="1.0.0"

# 列出所有 Addon
php artisan addon:list

# 激活 Addon
php artisan addon:activate AddonName

# 停用 Addon
php artisan addon:deactivate AddonName

# 删除 Addon 插件（包括文件和数据库记录）
php artisan addon:remove AddonName

# 强制删除 Addon 插件（不询问确认）
php artisan addon:remove AddonName --force

# 同步 Addon（扫描目录并更新数据库）
php artisan addon:sync

# 运行 Addon 数据库迁移
php artisan migrate --path=app/addons/AddonName/Migrations
```

## 钩子系统

插件系统提供了强大的钩子机制，允许插件在特定事件发生时执行代码或修改数据。

### 注册钩子

在插件的 `onRegisterHooks()` 方法中注册钩子：

```php
// 在 MyAddon.php 中
protected function onRegisterHooks(): void
{
    // 注册动作钩子
    $this->addAction('user.created', [$this, 'onUserCreated']);
    $this->addAction('custom.action', [$this, 'handleCustomAction']);
    
    // 注册过滤器钩子
    $this->addFilter('api.response.data', [$this, 'filterApiResponse']);
    $this->addFilter('user.data', [$this, 'filterUserData']);
}

// 钩子处理方法
public function onUserCreated($user)
{
    // 用户创建时的处理逻辑
    $this->log('User created: ' . $user->name);
}

public function filterApiResponse($data, $context = null)
{
    // 修改 API 响应
    $data['addon_info'] = [
        'name' => $this->getName(),
        'version' => $this->getVersion()
    ];
    return $data;
}
```

### 动作钩子 (Actions)
动作钩子允许在特定时机执行代码，不返回值。

```php
// 注册动作钩子
add_action('user.created', function($user) {
    // 处理用户创建事件
});

// 执行动作钩子
do_action('user.created', $user);
```

### 过滤器钩子 (Filters)
过滤器钩子允许修改数据并返回修改后的值。

```php
// 注册过滤器钩子
add_filter('api.response.data', function($data) {
    $data['timestamp'] = now();
    return $data;
});

// 应用过滤器钩子
$data = apply_filters('api.response.data', $originalData);
```

### 核心钩子

#### 动作钩子
- `app.booted` - 应用启动完成
- `request.starting` - 请求开始处理
- `request.terminating` - 请求处理结束
- `user.created` - 用户创建
- `user.updated` - 用户更新
- `user.deleted` - 用户删除
- `api.before_response` - API响应前
- `api.after_response` - API响应后

#### 过滤器钩子
- `api.response.data` - API响应数据
- `user.data` - 用户数据
- `config.value` - 配置值
- `view.data` - 视图数据
- `mail.content` - 邮件内容

## API 接口

### Plugin 管理 API

#### 获取插件列表
```http
GET /api/plugins
GET /api/plugins?active_only=true
```

#### 获取插件信息
```http
GET /api/plugins/{name}
```

#### 激活插件
```http
POST /api/plugins/activate
Content-Type: application/json

{
    "name": "MyPlugin"
}
```

#### 停用插件
```http
POST /api/plugins/deactivate
Content-Type: application/json

{
    "name": "MyPlugin"
}
```

### 批量操作
```http
POST /api/plugins/batch
Content-Type: application/json

{
    "action": "activate",
    "plugins": ["Plugin1", "Plugin2"]
}
```

### 获取统计信息
```http
GET /api/plugins/stats
```

### 获取钩子信息
```http
GET /api/plugins/hooks
```

### 测试钩子
```http
POST /api/plugins/hooks/test
Content-Type: application/json

{
    "type": "action",
    "hook": "test.action",
    "data": {}
}
```

### Addon 管理 API

#### 获取 Addon 列表
```http
GET /api/addons
GET /api/addons?active_only=true
```

#### 获取 Addon 信息
```http
GET /api/addons/{name}
```

#### 激活 Addon
```http
POST /api/addons/activate
Content-Type: application/json

{
    "name": "MyAddon"
}
```

#### 停用 Addon
```http
POST /api/addons/deactivate
Content-Type: application/json

{
    "name": "MyAddon"
}
```

### Addon 自定义 API

每个 Addon 插件都可以定义自己的 API 接口，访问路径为：`/api/addons/{AddonName}/api/{endpoint}`

#### 示例：TestNewAddon 插件 API

```http
# 获取插件信息
GET /api/addons/TestNewAddon/api/info

# 获取插件数据（支持分页）
GET /api/addons/TestNewAddon/api/data
GET /api/addons/TestNewAddon/api/data?page=1&per_page=10

# 创建数据
POST /api/addons/TestNewAddon/api/data
Content-Type: application/json

{
    "name": "测试数据",
    "value": "测试值"
}

# 更新数据
PUT /api/addons/TestNewAddon/api/data/{id}
Content-Type: application/json

{
    "name": "更新的数据",
    "value": "更新的值"
}

# 删除数据
DELETE /api/addons/TestNewAddon/api/data/{id}

# 自定义动作
POST /api/addons/TestNewAddon/api/action
Content-Type: application/json

{
    "action": "custom_action",
    "data": {}
}

# 测试过滤器
GET /api/addons/TestNewAddon/api/test-filter

# 获取配置
GET /api/addons/TestNewAddon/api/config

# 更新配置
POST /api/addons/TestNewAddon/api/config
Content-Type: application/json

{
    "key": "value"
}
```

## Web 管理界面

系统提供了完整的 Web 管理界面，用于管理插件。

### 访问路径

#### Plugin 管理界面
- 插件管理：`/admin/plugins`
- 插件详情：`/admin/plugins/{name}`
- 插件设置：`/admin/plugins/{name}/settings`

#### Addon 管理界面
- Addon 管理：`/admin/addons`
- Addon 详情：`/admin/addons/{name}`
- Addon 设置：`/admin/addons/{name}/settings`

#### Addon 自定义 Web 界面
每个 Addon 插件都可以定义自己的 Web 界面，访问路径为：`/addons/{AddonName}/web/{page}`

示例：TestNewAddon 插件 Web 界面
- 主页：`/addons/TestNewAddon/web/home`
- 设置页：`/addons/TestNewAddon/web/settings`
- 管理页：`/addons/TestNewAddon/web/manage`

### 功能特性
- 📊 插件统计信息
- 📋 插件列表管理
- 🔄 插件激活/停用
- 🧪 钩子测试功能
- 依赖关系检查
- 数据库迁移管理

## 配置选项

插件系统的配置文件位于 `config/plugins.php`：

```php
return [
    // 插件目录
    'directory' => app_path('Plugins'),
    
    // 是否自动加载插件
    'auto_load' => true,
    
    // 是否在控制台模式下加载插件
    'load_in_console' => false,
    
    // 缓存设置
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'key_prefix' => 'plugins:',
    ],
    
    // 安全设置
    'security' => [
        'verify_signature' => false,
        'allowed_extensions' => ['php'],
        'forbidden_functions' => ['exec', 'shell_exec', 'system'],
        'max_plugin_size' => 10 * 1024 * 1024, // 10MB
    ],
];
```

## 最佳实践

### 1. 插件命名
- 使用 PascalCase 命名插件类
- 插件目录名与类名保持一致
- 避免与现有插件重名
- 建议格式：`{功能}{类型}Addon`，如 `UserManagementAddon`

### 2. 版本管理
- 遵循语义化版本规范（SemVer）
- 主版本号.次版本号.修订号
- 例如：1.0.0, 1.1.0, 2.0.0
- 在 `getVersion()` 方法中返回正确版本号

### 3. 依赖管理
- 在插件中声明依赖的其他插件
- 在 `getDependencies()` 方法中明确声明插件依赖
- 检查Laravel版本兼容性
- 使用语义化版本号
- 避免循环依赖
- 使用 `checkDependencies()` 方法验证依赖

### 4. 性能优化
- 只在需要时注册钩子
- 避免在钩子中执行重量级操作
- 合理使用缓存
- 延迟加载非必要资源
- 在 `isCompatible()` 方法中进行快速兼容性检查

### 5. 安全考虑
- 验证用户输入
- 避免执行危险函数
- 使用Laravel的安全机制
- 为API接口添加适当的权限验证

### 6. 错误处理
- 使用try-catch包装关键代码
- 在 `activate()` 和 `deactivate()` 方法中处理异常
- 记录错误日志
- 提供友好的错误信息

### 7. 数据库设计
- 为插件数据表添加统一前缀（如插件名小写+下划线）
- 使用 Laravel 迁移文件管理数据库结构
- 在 `install()` 方法中创建必要的数据库表
- 在 `uninstall()` 方法中清理数据库

### 8. 路由设计
- API 路由使用 `/api` 前缀
- Web 路由使用 `/web` 前缀
- 使用 RESTful 设计原则
- 为路由添加适当的中间件

### 9. 配置管理
- 使用 `config.php` 文件定义默认配置
- 通过 `getConfig()` 和 `setConfig()` 方法管理配置
- 支持运行时配置修改
- 提供配置验证机制

## 故障排除

### 常见问题

#### 1. 插件无法激活
**问题症状：**
- 插件状态显示为 'error'
- 激活命令失败
- Web 界面显示错误信息

**解决方案：**
- 检查插件类是否存在且继承 `BaseAddon`
- 验证插件是否实现了 `PluginInterface` 接口
- 确保 `isCompatible()` 方法返回 `true`
- 检查构造函数是否正确调用父类构造函数
- 查看数据库 `addons` 表中的 `error_message` 字段

```bash
# 检查插件状态
php artisan addon:list

# 同步插件（重新扫描）
php artisan addon:sync
```

#### 2. 数据库表不存在
**问题症状：**
- API 接口返回 "no such table" 错误
- 插件功能无法正常使用

**解决方案：**
```bash
# 运行插件迁移
php artisan migrate --path=app/addons/YourAddon/Migrations

# 检查迁移状态
php artisan migrate:status
```

#### 3. 钩子不生效
**问题症状：**
- 注册的钩子回调函数未被调用
- 过滤器不起作用

**解决方案：**
- 确认钩子已在 `registerHooks()` 方法中正确注册
- 检查钩子名称是否正确
- 验证插件是否已激活
- 确保钩子触发点存在

#### 4. 路由冲突
**问题症状：**
- 404 错误
- 路由指向错误的控制器

**解决方案：**
- 检查 `routes.php` 文件中的路由定义
- 使用插件专用的路由前缀
- 避免与系统路由冲突
- 检查控制器类是否存在

#### 5. 插件依赖问题
**问题症状：**
- 插件激活失败
- 依赖检查不通过

**解决方案：**
- 检查 `getDependencies()` 方法返回的依赖列表
- 确保依赖的插件已安装并激活
- 验证版本兼容性

### 调试技巧

#### 1. 启用调试模式
```php
// 在 .env 文件中
APP_DEBUG=true
LOG_LEVEL=debug
```

#### 2. 查看日志
```bash
# 查看Laravel日志
tail -f storage/logs/laravel.log

# 查看插件专用日志
tail -f storage/logs/plugins.log

# 实时监控日志
php artisan log:monitor
```

#### 3. 使用调试工具
```php
// 在插件代码中添加调试信息
\Log::debug('Plugin debug info', ['data' => $data]);

// 使用dd()函数调试
dd($variable);

// 检查插件状态
$addon = \App\Models\Addon::findByName('YourAddon');
dd($addon->toArray());
```

#### 4. 数据库调试
```sql
-- 检查插件状态
SELECT name, status, is_active, error_message FROM addons;

-- 手动更新插件状态
UPDATE addons SET status = 'active', is_active = 1, error_message = NULL WHERE name = 'YourAddon';
```

#### 5. API 测试
```bash
# 测试插件 API
curl -s http://127.0.0.1:8000/api/addons/YourAddon/api/info | python3 -m json.tool

# 测试管理 API
curl -s http://127.0.0.1:8000/api/addons | python3 -m json.tool
```

### 性能问题
1. 启用插件缓存
2. 减少不必要的钩子注册
3. 优化插件代码逻辑
4. 使用性能分析工具

## 扩展开发

### 完整插件开发示例

以下是一个完整的 Addon 插件开发示例：

#### 1. 创建插件

```bash
php artisan addon:make UserActivityAddon --author="Your Name" --description="用户活动跟踪插件" --addon-version="1.0.0"
```

#### 2. 主插件文件 (UserActivityAddon.php)

```php
<?php

namespace App\Addons\UserActivityAddon;

use App\Core\Addons\BaseAddon;
use Illuminate\Support\Facades\Log;

class UserActivityAddon extends BaseAddon
{
    public function __construct()
    {
        parent::__construct();
        $this->name = 'UserActivityAddon';
        $this->version = '1.0.0';
        $this->description = '用户活动跟踪插件';
        $this->author = 'Your Name';
        $this->dependencies = [];
    }

    public function activate()
    {
        Log::info('UserActivityAddon activated');
        return true;
    }

    public function deactivate()
    {
        Log::info('UserActivityAddon deactivated');
        return true;
    }

    public function registerHooks()
    {
        // 注册用户登录钩子
        hook_manager()->addAction('user.login', [$this, 'onUserLogin']);
        hook_manager()->addAction('user.logout', [$this, 'onUserLogout']);
        
        // 注册API响应过滤器
        hook_manager()->addFilter('api.response', [$this, 'filterApiResponse']);
    }

    public function onUserLogin($user)
    {
        // 记录用户登录活动
        $this->logActivity($user->id, 'login', 'User logged in');
    }

    public function onUserLogout($user)
    {
        // 记录用户登出活动
        $this->logActivity($user->id, 'logout', 'User logged out');
    }

    public function filterApiResponse($response, $context = null)
    {
        // 在API响应中添加插件信息
        if (is_array($response)) {
            $response['addon_info'] = [
                'name' => $this->getName(),
                'version' => $this->getVersion()
            ];
        }
        return $response;
    }

    private function logActivity($userId, $action, $description)
    {
        // 使用插件的数据模型记录活动
        Models\UserActivity::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);
    }

    public function isCompatible(): bool
    {
        return version_compare(app()->version(), '11.0', '>=');
    }

    public function getInfo(): array
    {
        return [
            'name' => $this->getName(),
            'version' => $this->getVersion(),
            'description' => $this->getDescription(),
            'author' => $this->getAuthor(),
            'status' => 'active',
            'features' => [
                'User login tracking',
                'User logout tracking',
                'Activity history',
                'API response filtering'
            ]
        ];
    }
}
```

#### 3. 数据模型 (Models/UserActivity.php)

```php
<?php

namespace App\Addons\UserActivityAddon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class UserActivity extends Model
{
    protected $table = 'user_activity_addon_activities';
    
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getRecentActivities($limit = 10)
    {
        return static::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
```

#### 4. 控制器 (Controllers/UserActivityAddonController.php)

```php
<?php

namespace App\Addons\UserActivityAddon\Controllers;

use App\Http\Controllers\Controller;
use App\Addons\UserActivityAddon\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserActivityAddonController extends Controller
{
    public function info(): JsonResponse
    {
        $addon = app('addon.manager')->getAddonInfo('UserActivityAddon');
        return response()->json([
            'success' => true,
            'data' => $addon
        ]);
    }

    public function activities(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $activities = UserActivity::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

    public function userActivities(Request $request, $userId): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $activities = UserActivity::where('user_id', $userId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total_activities' => UserActivity::count(),
            'today_activities' => UserActivity::whereDate('created_at', today())->count(),
            'unique_users' => UserActivity::distinct('user_id')->count(),
            'top_actions' => UserActivity::selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
```

#### 5. 路由文件 (routes.php)

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Addons\UserActivityAddon\Controllers\UserActivityAddonController;

// API 路由
Route::prefix('api')->group(function () {
    Route::get('info', [UserActivityAddonController::class, 'info']);
    Route::get('activities', [UserActivityAddonController::class, 'activities']);
    Route::get('activities/user/{userId}', [UserActivityAddonController::class, 'userActivities']);
    Route::get('stats', [UserActivityAddonController::class, 'stats']);
});

// Web 路由
Route::prefix('web')->group(function () {
    Route::get('dashboard', function () {
        return view('user-activity-addon::dashboard');
    });
    Route::get('activities', function () {
        return view('user-activity-addon::activities');
    });
});
```

#### 6. 数据库迁移 (Migrations/create_user_activity_addon_activities_table.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_activity_addon_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action', 50);
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_activity_addon_activities');
    }
};
```

### 自定义钩子

```php
// 在应用代码中定义新的钩子点
hook_manager()->doAction('custom.event', $data);

// 在插件中监听自定义钩子
hook_manager()->addAction('custom.event', [$this, 'handleCustomEvent']);
```

### 插件间通信

```php
// 获取其他插件实例
$otherAddon = app('addon.manager')->getLoadedAddon('OtherAddon');

// 调用其他插件的方法
if ($otherAddon && app('addon.manager')->isAddonActive('OtherAddon')) {
    $result = $otherAddon->someMethod($data);
}
```

插件系统支持进一步扩展：

- 添加插件市场功能
- 实现插件自动更新
- 支持插件主题系统
- 集成第三方插件仓库

## 贡献指南

我们欢迎社区贡献！请遵循以下指南：

### 提交代码
1. Fork 项目到你的 GitHub 账户
2. 创建功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交你的更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 创建 Pull Request

### 代码规范
- 遵循 PSR-12 编码标准
- 使用有意义的变量和方法名
- 添加适当的 PHPDoc 注释
- 编写单元测试覆盖新功能
- 更新相关文档
- 确保代码通过所有测试

### 插件开发规范
- 插件名称使用 PascalCase
- 遵循本文档中的最佳实践
- 提供完整的 README 文档
- 包含示例代码和使用说明
- 添加适当的错误处理
- 考虑向后兼容性

### 报告问题
- 使用 GitHub Issues 报告 bug
- 提供详细的错误信息和堆栈跟踪
- 包含完整的复现步骤
- 标明环境信息（PHP 版本、Laravel 版本等）
- 如果可能，提供最小复现示例

### 功能请求
- 在 Issues 中详细描述新功能
- 解释功能的用途和价值
- 提供使用场景和示例
- 考虑对现有功能的影响

### 文档贡献
- 修正文档中的错误
- 添加缺失的示例
- 改进文档结构和可读性
- 翻译文档到其他语言

---

## 许可证

本项目采用 MIT 许可证。详情请参阅 [LICENSE](LICENSE) 文件。

## 更新日志

### v1.0.0 (当前版本)
- ✅ 完整的 Addon 插件系统
- ✅ Plugin 轻量级插件支持
- ✅ 钩子系统（Actions & Filters）
- ✅ 数据库管理和迁移
- ✅ API 接口和 Web 管理界面
- ✅ 命令行工具
- ✅ 插件依赖管理
- ✅ 错误处理和日志记录
- ✅ 完整的文档和示例

---

**注意**: 这是一个活跃开发的项目，API 可能会发生变化。请关注更新日志和文档更新。

如有任何问题或建议，请通过 GitHub Issues 联系我们。