<?php

namespace App\Plugins;

use Illuminate\Support\Facades\Log;

/**
 * 钩子管理器
 * 实现类似WordPress的动作钩子和过滤器钩子机制
 */
class HookManager
{
    /**
     * 动作钩子
     */
    protected array $actions = [];
    
    /**
     * 过滤器钩子
     */
    protected array $filters = [];
    
    /**
     * 当前执行的钩子
     */
    protected array $currentHook = [];
    
    /**
     * 添加动作钩子
     * 
     * @param string $hook 钩子名称
     * @param callable $callback 回调函数
     * @param int $priority 优先级（数字越小优先级越高）
     * @param int $acceptedArgs 接受的参数数量
     */
    public function addAction(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->actions[$hook][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $acceptedArgs,
        ];
        
        // 按优先级排序
        if (isset($this->actions[$hook])) {
            ksort($this->actions[$hook]);
        }
    }
    
    /**
     * 添加过滤器钩子
     * 
     * @param string $hook 钩子名称
     * @param callable $callback 回调函数
     * @param int $priority 优先级
     * @param int $acceptedArgs 接受的参数数量
     */
    public function addFilter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->filters[$hook][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $acceptedArgs,
        ];
        
        // 按优先级排序
        if (isset($this->filters[$hook])) {
            ksort($this->filters[$hook]);
        }
    }
    
    /**
     * 执行动作钩子
     * 
     * @param string $hook 钩子名称
     * @param mixed ...$args 传递给钩子的参数
     */
    public function doAction(string $hook, ...$args): void
    {
        if (!isset($this->actions[$hook])) {
            return;
        }
        
        $this->currentHook[] = $hook;
        
        try {
            foreach ($this->actions[$hook] as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    $this->executeCallback(
                        $callback['callback'],
                        $args,
                        $callback['accepted_args']
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error("Error executing action hook '{$hook}': " . $e->getMessage());
        } finally {
            array_pop($this->currentHook);
        }
    }
    
    /**
     * 应用过滤器钩子
     * 
     * @param string $hook 钩子名称
     * @param mixed $value 要过滤的值
     * @param mixed ...$args 额外参数
     * @return mixed 过滤后的值
     */
    public function applyFilters(string $hook, $value, ...$args): mixed
    {
        if (!isset($this->filters[$hook])) {
            return $value;
        }
        
        $this->currentHook[] = $hook;
        
        try {
            foreach ($this->filters[$hook] as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    $allArgs = array_merge([$value], $args);
                    $value = $this->executeCallback(
                        $callback['callback'],
                        $allArgs,
                        $callback['accepted_args']
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error("Error executing filter hook '{$hook}': " . $e->getMessage());
        } finally {
            array_pop($this->currentHook);
        }
        
        return $value;
    }
    
    /**
     * 执行回调函数
     */
    protected function executeCallback(callable $callback, array $args, int $acceptedArgs)
    {
        // 限制参数数量
        if ($acceptedArgs > 0 && count($args) > $acceptedArgs) {
            $args = array_slice($args, 0, $acceptedArgs);
        }
        
        return call_user_func_array($callback, $args);
    }
    
    /**
     * 移除动作钩子
     * 
     * @param string $hook 钩子名称
     * @param callable $callback 回调函数
     * @param int $priority 优先级
     */
    public function removeAction(string $hook, callable $callback, int $priority = 10): bool
    {
        if (!isset($this->actions[$hook][$priority])) {
            return false;
        }
        
        foreach ($this->actions[$hook][$priority] as $index => $item) {
            if ($item['callback'] === $callback) {
                unset($this->actions[$hook][$priority][$index]);
                
                // 如果该优先级下没有回调了，删除该优先级
                if (empty($this->actions[$hook][$priority])) {
                    unset($this->actions[$hook][$priority]);
                }
                
                // 如果该钩子下没有任何回调了，删除该钩子
                if (empty($this->actions[$hook])) {
                    unset($this->actions[$hook]);
                }
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 移除过滤器钩子
     * 
     * @param string $hook 钩子名称
     * @param callable $callback 回调函数
     * @param int $priority 优先级
     */
    public function removeFilter(string $hook, callable $callback, int $priority = 10): bool
    {
        if (!isset($this->filters[$hook][$priority])) {
            return false;
        }
        
        foreach ($this->filters[$hook][$priority] as $index => $item) {
            if ($item['callback'] === $callback) {
                unset($this->filters[$hook][$priority][$index]);
                
                // 如果该优先级下没有回调了，删除该优先级
                if (empty($this->filters[$hook][$priority])) {
                    unset($this->filters[$hook][$priority]);
                }
                
                // 如果该钩子下没有任何回调了，删除该钩子
                if (empty($this->filters[$hook])) {
                    unset($this->filters[$hook]);
                }
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 移除所有钩子
     * 
     * @param string $hook 钩子名称
     * @param int|null $priority 优先级，null表示移除所有优先级
     */
    public function removeAllActions(string $hook, ?int $priority = null): void
    {
        if ($priority !== null) {
            unset($this->actions[$hook][$priority]);
            if (empty($this->actions[$hook])) {
                unset($this->actions[$hook]);
            }
        } else {
            unset($this->actions[$hook]);
        }
    }
    
    /**
     * 移除所有过滤器
     * 
     * @param string $hook 钩子名称
     * @param int|null $priority 优先级，null表示移除所有优先级
     */
    public function removeAllFilters(string $hook, ?int $priority = null): void
    {
        if ($priority !== null) {
            unset($this->filters[$hook][$priority]);
            if (empty($this->filters[$hook])) {
                unset($this->filters[$hook]);
            }
        } else {
            unset($this->filters[$hook]);
        }
    }
    
    /**
     * 检查是否有动作钩子
     * 
     * @param string $hook 钩子名称
     * @param callable|null $callback 特定的回调函数
     */
    public function hasAction(string $hook, ?callable $callback = null): bool
    {
        if (!isset($this->actions[$hook])) {
            return false;
        }
        
        if ($callback === null) {
            return true;
        }
        
        foreach ($this->actions[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $item) {
                if ($item['callback'] === $callback) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * 检查是否有过滤器钩子
     * 
     * @param string $hook 钩子名称
     * @param callable|null $callback 特定的回调函数
     */
    public function hasFilter(string $hook, ?callable $callback = null): bool
    {
        if (!isset($this->filters[$hook])) {
            return false;
        }
        
        if ($callback === null) {
            return true;
        }
        
        foreach ($this->filters[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $item) {
                if ($item['callback'] === $callback) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * 获取当前执行的钩子
     */
    public function getCurrentHook(): ?string
    {
        return end($this->currentHook) ?: null;
    }
    
    /**
     * 检查是否正在执行某个钩子
     */
    public function isDoingHook(string $hook): bool
    {
        return in_array($hook, $this->currentHook);
    }
    
    /**
     * 获取所有已注册的动作钩子
     */
    public function getAllActions(): array
    {
        return array_keys($this->actions);
    }
    
    /**
     * 获取所有已注册的过滤器钩子
     */
    public function getAllFilters(): array
    {
        return array_keys($this->filters);
    }
}