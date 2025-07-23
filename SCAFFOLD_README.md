# Laravel 脚手架文档

## 概述

这是一个基于 Laravel 12 的企业级脚手架，提供了完整的分层架构、统一的API响应格式、JSON日志管理和异常处理机制。

## 架构设计

### 分层架构

```
┌─────────────────┐
│   Controller    │  控制器层：处理HTTP请求，参数验证
└─────────────────┘
         │
┌─────────────────┐
│    Request      │  请求验证层：统一的参数验证
└─────────────────┘
         │
┌─────────────────┐
│     Logic       │  业务逻辑层：复杂业务逻辑处理
└─────────────────┘
         │
┌─────────────────┐
│    Service      │  服务层：数据操作和基础业务
└─────────────────┘
         │
┌─────────────────┐
│     Model       │  模型层：数据模型定义
└─────────────────┘
         │
┌─────────────────┐
│   Response      │  响应层：统一的API响应格式
└─────────────────┘
```

### 核心组件

#### 1. 统一API响应格式 (`app/Http/Responses/ApiResponse.php`)

提供统一的JSON响应格式：

```json
{
    "success": true,
    "code": 200,
    "message": "操作成功",
    "data": {},
    "timestamp": "2024-01-01T00:00:00.000000Z"
}
```

支持的响应类型：
- `success()` - 成功响应
- `error()` - 错误响应
- `paginate()` - 分页响应
- `validationError()` - 验证错误响应
- `unauthorized()` - 未授权响应
- `forbidden()` - 禁止访问响应
- `notFound()` - 资源未找到响应

#### 2. 基础控制器 (`app/Http/Controllers/Controller.php`)

继承自Laravel基础控制器，集成了API响应方法：

```php
class YourController extends Controller
{
    public function index()
    {
        $data = ['key' => 'value'];
        return $this->success($data, '获取成功');
    }
}
```

#### 3. 请求验证层 (`app/Http/Requests/BaseRequest.php`)

统一的请求验证基类，自动返回JSON格式的验证错误：

```php
class CreateUserRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ];
    }
}
```

#### 4. 服务层 (`app/Services/BaseService.php`)

提供基础的CRUD操作和数据库事务管理：

```php
class UserService extends BaseService
{
    protected function getModel(): Model
    {
        return new User();
    }
    
    // 继承了 create, update, delete, findById 等方法
}
```

#### 5. 业务逻辑层 (`app/Logic/BaseLogic.php`)

处理复杂的业务逻辑，提供数据预处理、验证和后处理钩子：

```php
class UserLogic extends BaseLogic
{
    protected function validateBeforeCreate(array $data): array
    {
        // 自定义创建前验证逻辑
        return ['success' => true];
    }
    
    protected function afterCreate($result, array $data): void
    {
        // 创建后处理逻辑
    }
}
```

#### 6. 异常处理 (`app/Exceptions/BusinessException.php`)

统一的业务异常处理：

```php
// 抛出业务异常
throw BusinessException::notFound('用户不存在');
throw BusinessException::validationFailed('参数错误', $errors);
throw BusinessException::businessError('业务逻辑错误');
```

#### 7. JSON日志管理

配置了JSON格式的日志输出，便于日志分析和监控：

- 默认使用 `json_daily` 通道
- 自动记录API请求和响应日志
- 过滤敏感信息（密码、token等）

## 使用指南

### 1. 创建新的功能模块

以创建文章(Article)模块为例：

#### 步骤1：创建模型
```bash
php artisan make:model Article -m
```

#### 步骤2：创建Service
```php
// app/Services/ArticleService.php
class ArticleService extends BaseService
{
    protected function getModel(): Model
    {
        return new Article();
    }
}
```

#### 步骤3：创建Logic
```php
// app/Logic/ArticleLogic.php
class ArticleLogic extends BaseLogic
{
    protected function getService(): BaseService
    {
        return new ArticleService();
    }
}
```

#### 步骤4：创建Request验证
```php
// app/Http/Requests/Article/CreateArticleRequest.php
class CreateArticleRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ];
    }
}
```

#### 步骤5：创建Controller
```php
// app/Http/Controllers/Api/ArticleController.php
class ArticleController extends Controller
{
    protected $articleLogic;
    
    public function __construct(ArticleLogic $articleLogic)
    {
        $this->articleLogic = $articleLogic;
    }
    
    public function store(CreateArticleRequest $request)
    {
        $result = $this->articleLogic->handleCreate($request->validated());
        
        if ($result['success']) {
            return $this->success($result['data'], $result['message']);
        }
        
        return $this->error($result['message']);
    }
}
```

#### 步骤6：添加路由
```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::apiResource('articles', ArticleController::class);
});
```

### 2. API接口示例

#### 获取用户列表
```
GET /api/v1/users?per_page=10&page=1
```

#### 创建用户
```
POST /api/v1/users
Content-Type: application/json

{
    "name": "张三",
    "email": "zhangsan@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### 更新用户
```
PUT /api/v1/users/1
Content-Type: application/json

{
    "name": "李四",
    "email": "lisi@example.com"
}
```

### 3. 日志查看

日志文件位置：`storage/logs/laravel.log`

日志格式为JSON，包含以下信息：
- 请求信息（URL、方法、参数、IP等）
- 响应信息（状态码、响应时间等）
- 异常信息（错误堆栈、上下文等）

### 4. 异常处理

系统会自动处理以下异常：
- 验证异常 → 422状态码
- 认证异常 → 401状态码
- 授权异常 → 403状态码
- 资源未找到 → 404状态码
- 业务异常 → 自定义状态码
- 系统异常 → 500状态码

## 配置说明

### 环境变量

```env
# 日志配置
LOG_CHANNEL=stack
LOG_STACK=json_daily
LOG_LEVEL=debug
LOG_DAILY_DAYS=14

# 应用配置
APP_DEBUG=true
APP_ENV=local
```

### 中间件配置

API路由自动应用以下中间件：
- `api.logger` - API日志记录
- `SubstituteBindings` - 路由模型绑定

## 最佳实践

1. **分层职责明确**：
   - Controller只处理HTTP请求响应
   - Request只处理参数验证
   - Logic处理业务逻辑
   - Service处理数据操作

2. **异常处理**：
   - 使用BusinessException处理业务异常
   - 在Logic层进行业务验证
   - 在Service层处理数据异常

3. **日志记录**：
   - 关键业务操作记录日志
   - 异常情况详细记录
   - 敏感信息自动过滤

4. **API设计**：
   - 使用RESTful风格
   - 统一的响应格式
   - 合理的HTTP状态码

## 扩展功能

可以根据项目需求扩展以下功能：

1. **认证授权**：集成JWT或Sanctum
2. **缓存机制**：Redis缓存支持
3. **队列任务**：异步任务处理
4. **文件上传**：文件存储和管理

## 技术栈

- **框架**：Laravel 12
- **PHP版本**：8.2+
- **日志**：Monolog (JSON格式)
- **验证**：Laravel Validation
- **异常处理**：Laravel 12新式异常处理（bootstrap/app.php）
- **API响应**：统一JSON格式

## Laravel 12 版本更新说明

### 异常处理变化

Laravel 12 采用了新的异常处理方式，不再使用单独的 `app/Exceptions/Handler.php` 文件，而是在 `bootstrap/app.php` 中使用 `withExceptions()` 方法进行配置：

```php
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (Throwable $e, Request $request) {
        // 异常处理逻辑
    });
})
```

## 目录结构

```
app/
├── Exceptions/
│   └── BusinessException.php      # 业务异常类
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   └── UserController.php # API控制器
│   │   └── Controller.php         # 基础控制器
│   ├── Middleware/
│   │   └── ApiLogger.php          # API日志中间件
│   ├── Requests/
│   │   ├── User/
│   │   │   ├── CreateUserRequest.php
│   │   │   └── UpdateUserRequest.php
│   │   └── BaseRequest.php        # 基础请求类
│   └── Responses/
│       └── ApiResponse.php        # API响应类
├── Logic/
│   ├── BaseLogic.php              # 基础业务逻辑类
│   └── UserLogic.php              # 用户业务逻辑
└── Services/
    ├── BaseService.php            # 基础服务类
    └── UserService.php            # 用户服务
```

这个脚手架为Laravel项目提供了一个坚实的基础架构，有助于快速开发高质量的API应用。