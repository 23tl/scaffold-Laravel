# Laravel 插件化脚手架

一个基于 Laravel 12 的现代化脚手架项目，专为快速开发和插件化架构设计。

## 🚀 特性

- **插件化架构** - 支持动态加载和管理插件
- **统一API响应** - 标准化的API响应格式和异常处理
- **完整的业务层** - Logic、Service、Controller 三层架构
- **API日志记录** - 自动记录API请求和响应日志
- **插件管理系统** - 完整的插件生命周期管理
- **现代化开发** - 基于 Laravel 11 最新特性

## 📁 项目结构

```
app/
├── Addons/          # 插件目录
├── Http/            # HTTP层（控制器、中间件、请求验证）
├── Logic/           # 业务逻辑层
├── Services/        # 服务层
├── Plugins/         # 插件管理核心
└── Providers/       # 服务提供者
```

## 🔧 快速开始

1. **安装依赖**
   ```bash
   composer install
   npm install
   ```

2. **环境配置**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **数据库迁移**
   ```bash
   php artisan migrate
   ```

4. **启动开发服务器**
   ```bash
   php artisan serve
   ```

## 📦 插件开发

### 创建插件

插件位于 `app/Addons/` 目录下，每个插件包含：

- `{PluginName}Plugin.php` - 插件主类
- `routes.php` - 路由定义
- `config.php` - 插件配置
- 控制器、模型等业务文件

### 插件示例

查看 `app/Addons/Dome/` 目录了解完整的插件结构示例。

## 🛠️ API 规范

所有API响应遵循统一格式：

```json
{
  "code": 200,
  "message": "操作成功",
  "data": {},
  "timestamp": "2025-01-01 00:00:00"
}
```

## 📚 文档

- [插件系统详细文档](PLUGIN_SYSTEM.md)
- [脚手架说明](SCAFFOLD_README.md)

## 🤝 贡献

欢迎提交 Issue 和 Pull Request 来改进这个项目。

## 📄 许可证

本项目基于 MIT 许可证开源。