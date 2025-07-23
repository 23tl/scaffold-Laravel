<?php

namespace App\Addons\Dome\Services;

use App\Addons\Dome\Models\DomeData;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Dome 服务类
 */
class DomeService
{
    /**
     * 获取所有数据
     */
    public function getAllData(): Collection
    {
        return DomeData::ordered()->get();
    }

    /**
     * 获取分页数据
     */
    public function getPaginatedData(int $perPage = 15): LengthAwarePaginator
    {
        return DomeData::ordered()->paginate($perPage);
    }

    /**
     * 根据ID获取数据
     */
    public function getDataById(int $id): ?DomeData
    {
        return DomeData::find($id);
    }

    /**
     * 根据名称获取数据
     */
    public function getDataByName(string $name): ?DomeData
    {
        return DomeData::findByName($name);
    }

    /**
     * 创建数据
     */
    public function createData(array $data): DomeData
    {
        try {
            $item = DomeData::create($data);
            
            Log::info('Dome data created', ['id' => $item->id, 'name' => $item->name]);
            
            return $item;
        } catch (\Exception $e) {
            Log::error('Failed to create Dome data', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    /**
     * 更新数据
     */
    public function updateData(int $id, array $data): ?DomeData
    {
        try {
            $item = $this->getDataById($id);
            
            if (!$item) {
                return null;
            }
            
            $item->update($data);
            
            Log::info('Dome data updated', ['id' => $item->id, 'name' => $item->name]);
            
            return $item->fresh();
        } catch (\Exception $e) {
            Log::error('Failed to update Dome data', ['id' => $id, 'error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    /**
     * 删除数据
     */
    public function deleteData(int $id): bool
    {
        try {
            $item = $this->getDataById($id);
            
            if (!$item) {
                return false;
            }
            
            $result = $item->delete();
            
            Log::info('Dome data deleted', ['id' => $id]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to delete Dome data', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * 批量删除数据
     */
    public function batchDelete(array $ids): int
    {
        try {
            $count = DomeData::whereIn('id', $ids)->delete();
            
            Log::info('Dome data batch deleted', ['count' => $count, 'ids' => $ids]);
            
            return $count;
        } catch (\Exception $e) {
            Log::error('Failed to batch delete Dome data', ['ids' => $ids, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * 激活数据
     */
    public function activateData(int $id): bool
    {
        $item = $this->getDataById($id);
        
        if (!$item) {
            return false;
        }
        
        return $item->activate();
    }

    /**
     * 停用数据
     */
    public function deactivateData(int $id): bool
    {
        $item = $this->getDataById($id);
        
        if (!$item) {
            return false;
        }
        
        return $item->deactivate();
    }

    /**
     * 更新排序
     */
    public function updateSortOrder(array $sortData): bool
    {
        try {
            DB::transaction(function () use ($sortData) {
                foreach ($sortData as $item) {
                    DomeData::where('id', $item['id'])
                        ->update(['sort_order' => $item['sort_order']]);
                }
            });
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update sort order', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * 获取统计信息
     */
    public function getStats(): array
    {
        return [
            'total' => DomeData::count(),
            'active' => DomeData::active()->count(),
            'inactive' => DomeData::where('is_active', false)->count(),
            'categories' => DomeData::distinct('category')->count('category'),
        ];
    }

    /**
     * 搜索数据
     */
    public function searchData(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return DomeData::where(function ($query) use ($keyword) {
            $query->where('name', 'like', "%{$keyword}%")
                  ->orWhere('value', 'like', "%{$keyword}%")
                  ->orWhere('category', 'like', "%{$keyword}%");
        })->ordered()->paginate($perPage);
    }

    /**
     * 按分类获取数据
     */
    public function getDataByCategory(string $category, int $perPage = 15): LengthAwarePaginator
    {
        return DomeData::byCategory($category)->ordered()->paginate($perPage);
    }
}