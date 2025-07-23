<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dome 插件配置
    |--------------------------------------------------------------------------
    |
    | 这里定义了 Dome 插件的配置选项
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
        'manage_Dome' => '管理Dome插件',
        'view_Dome' => '查看Dome插件',
        'edit_Dome' => '编辑Dome插件',
    ],
    
    // 数据库配置
    'database' => [
        'table_prefix' => 'Dome_',
        'connection' => 'default',
    ],
];