<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TestNewAddon 插件配置
    |--------------------------------------------------------------------------
    |
    | 这里定义了 TestNewAddon 插件的配置选项
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
        'manage_TestNewAddon' => '管理TestNewAddon插件',
        'view_TestNewAddon' => '查看TestNewAddon插件',
        'edit_TestNewAddon' => '编辑TestNewAddon插件',
    ],
    
    // 数据库配置
    'database' => [
        'table_prefix' => 'TestNewAddon_',
        'connection' => 'default',
    ],
];