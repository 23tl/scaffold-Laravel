<?php

namespace App\Plugins\Exceptions;

use Exception;

/**
 * 插件异常类
 */
class PluginException extends Exception
{
    /**
     * 插件名称
     */
    protected ?string $pluginName = null;
    
    /**
     * 创建插件异常
     * 
     * @param string $message 错误消息
     * @param string|null $pluginName 插件名称
     * @param int $code 错误代码
     * @param Exception|null $previous 上一个异常
     */
    public function __construct(
        string $message = '',
        ?string $pluginName = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        $this->pluginName = $pluginName;
        
        if ($pluginName) {
            $message = "[Plugin: {$pluginName}] {$message}";
        }
        
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * 获取插件名称
     */
    public function getPluginName(): ?string
    {
        return $this->pluginName;
    }
    
    /**
     * 设置插件名称
     */
    public function setPluginName(string $pluginName): self
    {
        $this->pluginName = $pluginName;
        return $this;
    }
}