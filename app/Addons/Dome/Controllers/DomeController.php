<?php

namespace App\Addons\Dome\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use App\Addons\Dome\Models\DomeData;
use App\Addons\Dome\Requests\DomeRequest;
use App\Addons\Dome\Services\DomeService;

/**
 * Dome 插件控制器
 */
class DomeController extends Controller
{
    protected ?DomeService $service = null;

    public function __construct()
    {
        // 暂时移除依赖注入来测试
    }

    /**
     * 获取插件信息
     */
    public function info()
    {
        return ApiResponse::success([
            'message' => 'Hello from Dome plugin!',
            'status' => 'success',
            'plugin' => 'Dome'
        ]);
    }

    /**
     * 获取插件数据列表
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $data = $this->service->getPaginatedData($perPage);
        
        return ApiResponse::success($data);
    }

    /**
     * 创建插件数据
     */
    public function store(DomeRequest $request)
    {
        $data = $this->service->createData($request->validated());
        
        return ApiResponse::success($data, '数据创建成功');
    }

    /**
     * 获取单个插件数据
     */
    public function show($id)
    {
        $data = $this->service->getDataById($id);
        
        if (!$data) {
            return ApiResponse::error('数据不存在', 404);
        }
        
        return ApiResponse::success($data);
    }

    /**
     * 更新插件数据
     */
    public function update(DomeRequest $request, $id)
    {
        $data = $this->service->updateData($id, $request->validated());
        
        if (!$data) {
            return ApiResponse::error('数据不存在', 404);
        }
        
        return ApiResponse::success($data, '数据更新成功');
    }

    /**
     * 删除插件数据
     */
    public function destroy($id)
    {
        $result = $this->service->deleteData($id);
        
        if (!$result) {
            return ApiResponse::error('数据不存在', 404);
        }
        
        return ApiResponse::success([], '数据删除成功');
    }

    /**
     * 插件主页
     */
    public function home()
    {
        $stats = $this->service->getStats();
        
        return view('addons.Dome.home', compact('stats'));
    }

    /**
     * 插件设置页
     */
    public function settings()
    {
        $config = config('addons.Dome', []);
        
        return view('addons.Dome.settings', compact('config'));
    }

    /**
     * 插件管理页
     */
    public function manage()
    {
        $data = $this->service->getAllData();
        
        return view('addons.Dome.manage', compact('data'));
    }

    /**
     * 执行插件动作
     */
    public function action(Request $request)
    {
        $action = $request->get('action');
        $params = $request->get('params', []);
        
        // 根据动作执行相应的逻辑
        switch ($action) {
            case 'test':
                return ApiResponse::success(['message' => '测试动作执行成功']);
            default:
                return ApiResponse::error('未知动作', 400);
        }
    }

    /**
     * 测试过滤器
     */
    public function testFilter(Request $request)
    {
        $data = $request->all();
        
        // 应用过滤器
        $filtered = apply_filters('Dome.test_filter', $data, $request);
        
        return ApiResponse::success($filtered);
    }

    /**
     * 获取插件配置
     */
    public function getConfig()
    {
        $config = config('addons.Dome', []);
        
        return ApiResponse::success($config);
    }

    /**
     * 设置插件配置
     */
    public function setConfig(Request $request)
    {
        $validated = $request->validate([
            'config' => 'required|array',
        ]);
        
        // 这里可以添加保存配置的逻辑
        
        return ApiResponse::success([], '配置保存成功');
    }
}