<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Logic\UserLogic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 用户逻辑层
     *
     * @var UserLogic
     */
    protected $userLogic;

    /**
     * 构造函数
     *
     * @param UserLogic $userLogic
     */
    public function __construct(UserLogic $userLogic)
    {
        $this->userLogic = $userLogic;
    }

    /**
     * 获取用户列表
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $params = $request->only(['per_page', 'page']);
        $result = $this->userLogic->handleList($params);
        
        if ($result['success']) {
            return $this->paginate($result['data'], $result['message']);
        }
        
        return $this->error($result['message']);
    }

    /**
     * 创建用户
     *
     * @param CreateUserRequest $request
     * @return JsonResponse
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $result = $this->userLogic->handleCreate($request->validated());
        
        if ($result['success']) {
            return $this->success($result['data'], $result['message'], 201);
        }
        
        return $this->error($result['message']);
    }

    /**
     * 获取用户详情
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result = $this->userLogic->handleDetail($id);
        
        if ($result['success']) {
            return $this->success($result['data'], $result['message']);
        }
        
        return $this->notFound($result['message']);
    }

    /**
     * 更新用户
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $result = $this->userLogic->handleUpdate($id, $request->validated());
        
        if ($result['success']) {
            return $this->success($result['data'], $result['message']);
        }
        
        if (str_contains($result['message'], '不存在')) {
            return $this->notFound($result['message']);
        }
        
        return $this->error($result['message']);
    }

    /**
     * 删除用户
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->userLogic->handleDelete($id);
        
        if ($result['success']) {
            return $this->success(null, $result['message']);
        }
        
        if (str_contains($result['message'], '不存在')) {
            return $this->notFound($result['message']);
        }
        
        return $this->error($result['message']);
    }
}