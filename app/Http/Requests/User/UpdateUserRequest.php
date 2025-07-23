<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseRequest
{
    /**
     * 确定用户是否有权限进行此请求
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 获取验证规则
     *
     * @return array
     */
    public function rules(): array
    {
        $userId = $this->route('id') ?? $this->route('user');
        
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'password_confirmation' => 'required_with:password|string|min:8',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     *
     * @return array
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.required' => '用户名不能为空',
            'name.string' => '用户名必须是字符串',
            'name.max' => '用户名不能超过255个字符',
            'email.required' => '邮箱不能为空',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '邮箱已被注册',
            'password.min' => '密码至少需要8个字符',
            'password.confirmed' => '密码确认不匹配',
            'password_confirmation.required_with' => '修改密码时确认密码不能为空',
        ]);
    }

    /**
     * 获取验证字段的自定义属性名称
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => '用户名',
            'email' => '邮箱',
            'password' => '密码',
            'password_confirmation' => '确认密码',
        ];
    }

    /**
     * 准备验证数据
     */
    protected function prepareForValidation()
    {
        // 如果密码为空字符串，则移除密码字段
        if ($this->has('password') && empty($this->input('password'))) {
            $this->request->remove('password');
            $this->request->remove('password_confirmation');
        }
    }
}