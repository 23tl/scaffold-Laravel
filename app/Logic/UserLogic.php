<?php

namespace App\Logic;

use App\Logic\BaseLogic;
use App\Services\BaseService;
use App\Services\UserService;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Log;
use Exception;

class UserLogic extends BaseLogic
{
    /**
     * 用户服务
     *
     * @var UserService
     */
    protected $userService;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->userService = $this->service;
    }

    /**
     * 获取服务实例
     *
     * @return BaseService
     */
    protected function getService(): BaseService
    {
        return new UserService();
    }

    /**
     * 创建数据预处理
     *
     * @param array $data
     * @return array
     */
    protected function preprocessCreateData(array $data): array
    {
        // 可以在这里添加额外的数据处理逻辑
        // 例如：设置默认值、数据转换等
        
        return $data;
    }

    /**
     * 更新数据预处理
     *
     * @param array $data
     * @param $existingRecord
     * @return array
     */
    protected function preprocessUpdateData(array $data, $existingRecord): array
    {
        // 可以在这里添加更新前的数据处理逻辑
        
        return $data;
    }

    /**
     * 创建前验证
     *
     * @param array $data
     * @return array
     */
    protected function validateBeforeCreate(array $data): array
    {
        try {
            // 检查邮箱是否已存在
            if ($this->userService->emailExists($data['email'])) {
                return [
                    'success' => false,
                    'message' => '邮箱已被注册'
                ];
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            Log::error('用户创建前验证失败', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => '验证失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 更新前验证
     *
     * @param int $id
     * @param array $data
     * @param $existingRecord
     * @return array
     */
    protected function validateBeforeUpdate(int $id, array $data, $existingRecord): array
    {
        try {
            // 如果更新邮箱，检查邮箱是否已被其他用户使用
            if (isset($data['email']) && $this->userService->emailExists($data['email'], $id)) {
                return [
                    'success' => false,
                    'message' => '邮箱已被其他用户注册'
                ];
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            Log::error('用户更新前验证失败', [
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => '验证失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 删除前验证
     *
     * @param int $id
     * @param $existingRecord
     * @return array
     */
    protected function validateBeforeDelete(int $id, $existingRecord): array
    {
        try {
            // 可以在这里添加删除前的业务逻辑验证
            // 例如：检查用户是否有关联数据、是否为管理员等
            
            // 示例：防止删除ID为1的用户（假设为超级管理员）
            if ($id === 1) {
                return [
                    'success' => false,
                    'message' => '不能删除超级管理员账户'
                ];
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            Log::error('用户删除前验证失败', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => '验证失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 创建后处理
     *
     * @param $result
     * @param array $data
     */
    protected function afterCreate($result, array $data): void
    {
        try {
            // 可以在这里添加创建后的业务逻辑
            // 例如：发送欢迎邮件、记录操作日志、触发事件等
            
            Log::info('用户创建成功', [
                'user_id' => $result->id,
                'email' => $result->email,
                'name' => $result->name
            ]);
            
            // 示例：这里可以触发用户注册事件
            // event(new UserRegistered($result));
            
        } catch (Exception $e) {
            Log::error('用户创建后处理失败', [
                'user_id' => $result->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 更新后处理
     *
     * @param $result
     * @param array $data
     * @param $existingRecord
     */
    protected function afterUpdate($result, array $data, $existingRecord): void
    {
        try {
            // 可以在这里添加更新后的业务逻辑
            
            Log::info('用户更新成功', [
                'user_id' => $result->id,
                'updated_fields' => array_keys($data),
                'old_email' => $existingRecord->email,
                'new_email' => $result->email
            ]);
            
            // 示例：如果邮箱发生变化，可以发送确认邮件
            if (isset($data['email']) && $data['email'] !== $existingRecord->email) {
                // 发送邮箱变更确认邮件
                Log::info('用户邮箱已变更', [
                    'user_id' => $result->id,
                    'old_email' => $existingRecord->email,
                    'new_email' => $result->email
                ]);
            }
            
        } catch (Exception $e) {
            Log::error('用户更新后处理失败', [
                'user_id' => $result->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 删除后处理
     *
     * @param int $id
     * @param $existingRecord
     */
    protected function afterDelete(int $id, $existingRecord): void
    {
        try {
            // 可以在这里添加删除后的业务逻辑
            // 例如：清理相关数据、记录操作日志等
            
            Log::info('用户删除成功', [
                'user_id' => $id,
                'deleted_email' => $existingRecord->email,
                'deleted_name' => $existingRecord->name
            ]);
            
            // 示例：这里可以清理用户相关的数据
            // $this->cleanupUserRelatedData($id);
            
        } catch (Exception $e) {
            Log::error('用户删除后处理失败', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 根据邮箱获取用户
     *
     * @param string $email
     * @return array
     */
    public function getUserByEmail(string $email): array
    {
        try {
            $user = $this->userService->findByEmail($email);
            
            if (!$user) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => '用户不存在'
                ];
            }
            
            return [
                'success' => true,
                'data' => $user,
                'message' => '获取用户成功'
            ];
        } catch (Exception $e) {
            Log::error('根据邮箱获取用户失败', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'data' => null,
                'message' => '获取用户失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 修改用户密码
     *
     * @param int $id
     * @param string $oldPassword
     * @param string $newPassword
     * @return array
     */
    public function changePassword(int $id, string $oldPassword, string $newPassword): array
    {
        try {
            $user = $this->userService->findById($id);
            
            if (!$user) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => '用户不存在'
                ];
            }
            
            // 验证旧密码
            if (!$this->userService->verifyPassword($user, $oldPassword)) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => '原密码错误'
                ];
            }
            
            // 更新密码
            $result = $this->userService->updatePassword($id, $newPassword);
            
            Log::info('用户密码修改成功', ['user_id' => $id]);
            
            return [
                'success' => true,
                'data' => $result,
                'message' => '密码修改成功'
            ];
        } catch (Exception $e) {
            Log::error('修改用户密码失败', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'data' => null,
                'message' => '密码修改失败: ' . $e->getMessage()
            ];
        }
    }
}