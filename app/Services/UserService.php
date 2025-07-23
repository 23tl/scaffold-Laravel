<?php

namespace App\Services;

use App\Models\User;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class UserService extends BaseService
{
    /**
     * 获取模型实例
     *
     * @return Model
     */
    protected function getModel(): Model
    {
        return new User();
    }

    /**
     * 创建用户
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        try {
            // 密码加密
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            
            // 移除确认密码字段
            unset($data['password_confirmation']);
            
            return parent::create($data);
        } catch (Exception $e) {
            Log::error('创建用户失败', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 更新用户
     *
     * @param int $id
     * @param array $data
     * @return Model|null
     */
    public function update(int $id, array $data): ?Model
    {
        try {
            // 密码加密
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                // 如果密码为空，则不更新密码
                unset($data['password']);
            }
            
            // 移除确认密码字段
            unset($data['password_confirmation']);
            
            return parent::update($id, $data);
        } catch (Exception $e) {
            Log::error('更新用户失败', [
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 根据邮箱查找用户
     *
     * @param string $email
     * @return Model|null
     */
    public function findByEmail(string $email): ?Model
    {
        try {
            return $this->model->where('email', $email)->first();
        } catch (Exception $e) {
            Log::error('根据邮箱查找用户失败', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 验证用户密码
     *
     * @param User $user
     * @param string $password
     * @return bool
     */
    public function verifyPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }

    /**
     * 更新用户密码
     *
     * @param int $id
     * @param string $newPassword
     * @return Model|null
     */
    public function updatePassword(int $id, string $newPassword): ?Model
    {
        try {
            return $this->update($id, ['password' => $newPassword]);
        } catch (Exception $e) {
            Log::error('更新用户密码失败', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 检查邮箱是否已存在
     *
     * @param string $email
     * @param int|null $excludeId 排除的用户ID
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        try {
            $query = $this->model->where('email', $email);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            return $query->exists();
        } catch (Exception $e) {
            Log::error('检查邮箱是否存在失败', [
                'email' => $email,
                'exclude_id' => $excludeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 获取活跃用户
     *
     * @param int $days 天数
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveUsers(int $days = 30)
    {
        try {
            return $this->model
                ->where('updated_at', '>=', now()->subDays($days))
                ->get();
        } catch (Exception $e) {
            Log::error('获取活跃用户失败', [
                'days' => $days,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}