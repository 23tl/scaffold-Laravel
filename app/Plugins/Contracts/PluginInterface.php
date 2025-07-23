<?php

namespace App\Plugins\Contracts;

use App\Plugins\HookManager;

/**
 * 插件接口
 * 所有插件都必须实现此接口
 */
interface PluginInterface
{
    /**
     * 插件激活时调用
     */
    public function activate(): void;
    
    /**
     * 插件停用时调用
     */
    public function deactivate(): void;
    
    /**
     * 注册钩子
     * 
     * @param HookManager $hookManager 钩子管理器
     */
    public function registerHooks(HookManager $hookManager): void;
    
    /**
     * 获取插件名称
     */
    public function getName(): string;
    
    /**
     * 获取插件版本
     */
    public function getVersion(): string;
    
    /**
     * 获取插件描述
     */
    public function getDescription(): string;
    
    /**
     * 获取插件作者
     */
    public function getAuthor(): string;
    
    /**
     * 获取插件依赖
     * 
     * @return array 依赖的插件名称数组
     */
    public function getDependencies(): array;
    
    /**
     * 检查插件是否兼容当前系统
     */
    public function isCompatible(): bool;
}