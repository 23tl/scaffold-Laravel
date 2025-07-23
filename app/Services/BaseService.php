<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseService
{
    /**
     * 模型实例
     *
     * @var Model
     */
    protected $model;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = $this->getModel();
    }

    /**
     * 获取模型实例
     *
     * @return Model
     */
    abstract protected function getModel(): Model;

    /**
     * 获取所有记录
     *
     * @param array $columns
     * @return Collection
     */
    public function getAll(array $columns = ['*']): Collection
    {
        try {
            return $this->model->select($columns)->get();
        } catch (Exception $e) {
            Log::error('获取所有记录失败', [
                'model' => get_class($this->model),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 分页获取记录
     *
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param int|null $page
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        try {
            return $this->model->select($columns)->paginate($perPage, $columns, $pageName, $page);
        } catch (Exception $e) {
            Log::error('分页获取记录失败', [
                'model' => get_class($this->model),
                'per_page' => $perPage,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 根据ID查找记录
     *
     * @param int $id
     * @param array $columns
     * @return Model|null
     */
    public function findById(int $id, array $columns = ['*']): ?Model
    {
        try {
            return $this->model->select($columns)->find($id);
        } catch (Exception $e) {
            Log::error('根据ID查找记录失败', [
                'model' => get_class($this->model),
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 根据条件查找记录
     *
     * @param array $conditions
     * @param array $columns
     * @return Model|null
     */
    public function findByConditions(array $conditions, array $columns = ['*']): ?Model
    {
        try {
            return $this->model->select($columns)->where($conditions)->first();
        } catch (Exception $e) {
            Log::error('根据条件查找记录失败', [
                'model' => get_class($this->model),
                'conditions' => $conditions,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 创建记录
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        try {
            DB::beginTransaction();
            $model = $this->model->create($data);
            DB::commit();
            
            Log::info('创建记录成功', [
                'model' => get_class($this->model),
                'id' => $model->id,
                'data' => $data
            ]);
            
            return $model;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('创建记录失败', [
                'model' => get_class($this->model),
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 更新记录
     *
     * @param int $id
     * @param array $data
     * @return Model|null
     */
    public function update(int $id, array $data): ?Model
    {
        try {
            DB::beginTransaction();
            $model = $this->findById($id);
            
            if (!$model) {
                DB::rollBack();
                return null;
            }
            
            $model->update($data);
            DB::commit();
            
            Log::info('更新记录成功', [
                'model' => get_class($this->model),
                'id' => $id,
                'data' => $data
            ]);
            
            return $model->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('更新记录失败', [
                'model' => get_class($this->model),
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 删除记录
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            DB::beginTransaction();
            $model = $this->findById($id);
            
            if (!$model) {
                DB::rollBack();
                return false;
            }
            
            $result = $model->delete();
            DB::commit();
            
            Log::info('删除记录成功', [
                'model' => get_class($this->model),
                'id' => $id
            ]);
            
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('删除记录失败', [
                'model' => get_class($this->model),
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 批量创建记录
     *
     * @param array $data
     * @return bool
     */
    public function createMany(array $data): bool
    {
        try {
            DB::beginTransaction();
            $result = $this->model->insert($data);
            DB::commit();
            
            Log::info('批量创建记录成功', [
                'model' => get_class($this->model),
                'count' => count($data)
            ]);
            
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('批量创建记录失败', [
                'model' => get_class($this->model),
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}