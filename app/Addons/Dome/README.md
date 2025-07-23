# Dome 插件

## 描述

测试插件

## 版本

1.0.0

## 作者

test

## 安装

1. 使用命令创建插件：
   ```bash
   php artisan addon:make Dome
   ```

2. 运行数据库迁移：
   ```bash
   php artisan migrate
   ```

3. 激活插件：
   ```bash
   php artisan addon:activate Dome
   ```

## 使用

插件激活后会自动注册相关钩子和功能。

### API 接口

- `GET /api/addons/Dome/info` - 获取插件信息
- `GET /api/addons/Dome/data` - 获取插件数据
- `POST /api/addons/Dome/data` - 创建插件数据
- `PUT /api/addons/Dome/data/{id}` - 更新插件数据
- `DELETE /api/addons/Dome/data/{id}` - 删除插件数据

### Web 界面

- `/addons/Dome` - 插件主页
- `/addons/Dome/settings` - 插件设置页
- `/addons/Dome/manage` - 插件管理页

## 配置

插件配置文件位于 `config.php`，可以根据需要修改配置选项。

## 数据库

插件使用独立的数据表存储数据，表名为 `Dome_data`。

## 钩子

### 动作钩子

- `user.created`: 用户创建时触发
- `Dome.custom_action`: 自定义动作

### 过滤器钩子

- `api.response.data`: 过滤API响应数据

## 开发

### 目录结构

```
Dome/
├── Dome.php              # 主插件文件
├── config.php               # 配置文件
├── routes.php               # 路由文件
├── README.md                # 说明文档
├── Controllers/             # 控制器目录
│   └── DomeController.php
├── Models/                  # 模型目录
│   └── DomeData.php
├── Requests/                # 请求验证目录
│   └── DomeRequest.php
├── Services/                # 服务目录
│   └── DomeService.php
└── Migrations/              # 数据库迁移目录
    └── create_Dome_data_table.php
```

### 添加新功能

1. 在主插件文件中添加新的方法
2. 在 `registerHooks` 方法中注册相应的钩子
3. 重新激活插件以应用更改

### 调试

插件会自动记录日志，可以在 Laravel 日志文件中查看插件相关的日志信息。

## 许可证

MIT