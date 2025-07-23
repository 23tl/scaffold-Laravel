<?php

namespace App\Addons\TestNewAddon\Services;

use App\Addons\TestNewAddon\Models\TestNewAddonData;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * TestNewAddon 服务类
 */
class TestNewAddonService
{
    /**
     * 获取所有数据
     */
    public function getAllData(): Collection
    {
        return TestNewAddonData::ordered()->get();
    }

    /**
     * 获取分页数据
     */
    public function getPaginatedData(int $perPage = 15): LengthAwarePaginator
    {
        return TestNewAddonData::ordered()->paginate($perPage);
    }

    /**
     * 根据ID获取数据
     */
    public function getDataById(int $id): ?TestNewAddonData
    {
        return TestNewAddonData::find($id);
    }

    /**
     * 根据名称获取数据
     */
    public function getDataByName(string $name): ?TestNewAddonData
    {
        return TestNewAddonData::findByName($name);
    }

    /**
     * 创建数据
     */
    public function createData(array $data): TestNewAddonData
    {
        try {
            $item = TestNewAddonData::create($data);
            
            Log::info('TestNewAddon data created', ['id' => $item->id, 'name' => $item->name]);
            
            return $item;
        } catch (\Exception $e) {
            Log::error('Failed to create TestNewAddon data', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    /**
     * 更新数据
     */
    public function updateData(int $id, array $data): ?TestNewAddonData
    {
        try {
            $item = $this->getDataById($id);
            
            if (!$item) {
                return null;
            }
            
            $item->update($data);
            
            Log::info('TestNewAddon data updated', ['id' => $item->id, 'name' => $item->name]);
            
            return $item->fresh();
        } catch (\Exception $e) {
            Log::error('Failed to update TestNewAddon data', ['id' => $id, 'error' => $e->getMessage(), 'data' => $data]);
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
            
            Log::info('TestNewAddon data deleted', ['id' => $id]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to delete TestNewAddon data', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * 批量删除数据
     */
    public function batchDelete(array $ids): int
    {
        try {
            $count = TestNewAddonData::whereIn('id', $ids)->delete();
            
            Log::info('TestNewAddon data batch deleted', ['count' => $count, 'ids' => $ids]);
            
            return $count;
        } catch (\Exception $e) {
            Log::error('Failed to batch delete TestNewAddon data', ['ids' => $ids, 'error' => $e->getMessage()]);
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
                    TestNewAddonData::where('id', $item['id'])
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
            'total' => TestNewAddonData::count(),
            'active' => TestNewAddonData::active()->count(),
            'inactive' => TestNewAddonData::where('is_active', false)->count(),
            'categories' => TestNewAddonData::distinct('category')->count('category'),
        ];
    }

    /**
     * 搜索数据
     */
    public function searchData(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return TestNewAddonData::where(function ($query) use ($keyword) {
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
        return TestNewAddonData::byCategory($category)->ordered()->paginate($perPage);
    }
}