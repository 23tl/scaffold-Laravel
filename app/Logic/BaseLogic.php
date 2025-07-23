<?php

namespace App\Logic;

use App\Services\BaseService;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseLogic
{
    /**
     * 服务实例
     *
     * @var BaseService
     */
    protected $service;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->service = $this->getService();
    }

    /**
     * 获取服务实例
     *
     * @return BaseService
     */
    abstract protected function getService(): BaseService;

    /**
     * 处理列表逻辑
     *
     * @param array $params
     * @return array
     */
    public function handleList(array $params = []): array
    {
        try {
            $perPage = $params['per_page'] ?? 15;
            $data = $this->service->paginate($perPage);
            
            return [
                'success' => true,
                'data' => $data,
                'message' => '获取列表成功'
            ];
        } catch (Exception $e) {
            Log::error('处理列表逻辑失败', [
                'logic' => get_class($this),
                'params' => $params,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'data' => null,
                'message' => '获取列表失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 处理详情逻辑
     *
     * @param int $id
     * @return array
     */
    public function handleDetail(int $id): array
    {
        try {
            $data = $this->service->findById($id);
            
            if (!$data) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => '记录不存在'
                ];
            }
            
            return [
                'success' => true,
                'data' => $data,
                'message' => '获取详情成功'
            ];
        } catch (Exception $e) {
            Log::error('处理详情逻辑失败', [
                'logic' => get_class($this),
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'data' => null,
                'message' => '获取详情失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 处理创建逻辑
     *
     * @param array $data
     * @return array
     */
    public function handleCreate(array $data): array
    {
        try {
            // 数据预处理
            $processedData = $this->preprocessCreateData($data);
            
            // 创建前验证
            $validation = $this->validateBeforeCreate($processedData);
            if (!$validation['success']) {
                return $validation;
            }
            
            // 执行创建
            $result = $this->service->create($processedData);
            
            // 创建后处理
            $this->afterCreate($result, $processedData);
            
            return [
                'success' => true,
                'data' => $result,
                'message' => '创建成功'
            ];
        } catch (Exception $e) {
            Log::error('处理创建逻辑失败', [
                'logic' => get_class($this),
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'data' => null,
                'message' => '创建失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 处理更新逻辑
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function handleUpdate(int $id, array $data): array
    {
        try {
            // 检查记录是否存在
            $existingRecord = $this->service->findById($id);
            if (!$existingRecord) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => '记录不存在'
                ];
            }
            
            // 数据预处理
            $processedData = $this->preprocessUpdateData($data, $existingRecord);
            
            // 更新前验证
            $validation = $this->validateBeforeUpdate($id, $processedData, $existingRecord);
            if (!$validation['success']) {
                return $validation;
            }
            
            // 执行更新
            $result = $this->service->update($id, $processedData);
            
            // 更新后处理
            $this->afterUpdate($result, $processedData, $existingRecord);
            
            return [
                'success' => true,
                'data' => $result,
                'message' => '更新成功'
            ];
        } catch (Exception $e) {
            Log::error('处理更新逻辑失败', [
                'logic' => get_class($this),
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'data' => null,
                'message' => '更新失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 处理删除逻辑
     *
     * @param int $id
     * @return array
     */
    public function handleDelete(int $id): array
    {
        try {
            // 检查记录是否存在
            $existingRecord = $this->service->findById($id);
            if (!$existingRecord) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => '记录不存在'
                ];
            }
            
            // 删除前验证
            $validation = $this->validateBeforeDelete($id, $existingRecord);
            if (!$validation['success']) {
                return $validation;
            }
            
            // 执行删除
            $result = $this->service->delete($id);
            
            // 删除后处理
            $this->afterDelete($id, $existingRecord);
            
            return [
                'success' => true,
                'data' => null,
                'message' => '删除成功'
            ];
        } catch (Exception $e) {
            Log::error('处理删除逻辑失败', [
                'logic' => get_class($this),
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'data' => null,
                'message' => '删除失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 创建数据预处理
     *
     * @param array $data
     * @return array
     */
    protected function preprocessCreateData(array $data): array
    {
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
        return ['success' => true];
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
        return ['success' => true];
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
        return ['success' => true];
    }

    /**
     * 创建后处理
     *
     * @param $result
     * @param array $data
     */
    protected function afterCreate($result, array $data): void
    {
        // 子类可以重写此方法
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
        // 子类可以重写此方法
    }

    /**
     * 删除后处理
     *
     * @param int $id
     * @param $existingRecord
     */
    protected function afterDelete(int $id, $existingRecord): void
    {
        // 子类可以重写此方法
    }
}