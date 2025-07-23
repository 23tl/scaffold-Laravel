<?php

namespace App\Addons\Dome\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Dome 请求验证类
 */
class DomeRequest extends FormRequest
{
    /**
     * 确定用户是否有权限进行此请求
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 获取应用于请求的验证规则
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'value' => 'nullable|string',
            'metadata' => 'nullable|array',
            'is_active' => 'boolean',
            'category' => 'nullable|string|max:100',
            'sort_order' => 'integer|min:0',
        ];

        // 根据请求方法调整验证规则
        if ($this->isMethod('POST')) {
            // 创建时的特殊规则
            $rules['name'] .= '|unique:' . config('addons.Dome.database.table_prefix', 'Dome_') . 'data,name';
        } elseif ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // 更新时的特殊规则
            $id = $this->route('id');
            $rules['name'] .= '|unique:' . config('addons.Dome.database.table_prefix', 'Dome_') . 'data,name,' . $id;
        }

        return $rules;
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'name.required' => '名称字段是必需的',
            'name.string' => '名称必须是字符串',
            'name.max' => '名称不能超过255个字符',
            'name.unique' => '该名称已经存在',
            'value.string' => '值必须是字符串',
            'metadata.array' => '元数据必须是数组格式',
            'is_active.boolean' => '激活状态必须是布尔值',
            'category.string' => '分类必须是字符串',
            'category.max' => '分类不能超过100个字符',
            'sort_order.integer' => '排序必须是整数',
            'sort_order.min' => '排序不能小于0',
        ];
    }

    /**
     * 获取验证属性的自定义名称
     */
    public function attributes(): array
    {
        return [
            'name' => '名称',
            'value' => '值',
            'metadata' => '元数据',
            'is_active' => '激活状态',
            'category' => '分类',
            'sort_order' => '排序',
        ];
    }

    /**
     * 配置验证器实例
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // 自定义验证逻辑
            if ($this->has('metadata') && !empty($this->metadata)) {
                foreach ($this->metadata as $key => $value) {
                    if (!is_string($key)) {
                        $validator->errors()->add('metadata', '元数据的键必须是字符串');
                        break;
                    }
                }
            }
        });
    }
}