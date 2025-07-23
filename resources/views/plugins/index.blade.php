<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>插件管理</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8" x-data="pluginManager()">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">插件管理</h1>
                <div class="flex space-x-4">
                    <button @click="loadStats()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        刷新统计
                    </button>
                    <button @click="loadPlugins()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        刷新列表
                    </button>
                </div>
            </div>

            <!-- 统计信息 -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-100 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-800">总插件数</h3>
                    <p class="text-2xl font-bold text-blue-600" x-text="stats.total || 0"></p>
                </div>
                <div class="bg-green-100 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-800">已激活</h3>
                    <p class="text-2xl font-bold text-green-600" x-text="stats.active || 0"></p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-yellow-800">未激活</h3>
                    <p class="text-2xl font-bold text-yellow-600" x-text="stats.inactive || 0"></p>
                </div>
                <div class="bg-purple-100 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-purple-800">钩子数量</h3>
                    <p class="text-2xl font-bold text-purple-600" x-text="(stats.hooks?.actions || 0) + (stats.hooks?.filters || 0)"></p>
                </div>
            </div>

            <!-- 插件列表 -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">插件列表</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">插件名称</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">版本</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">描述</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">作者</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="plugin in plugins" :key="plugin.name">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="plugin.name"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="plugin.version"></td>
                                    <td class="px-6 py-4 text-sm text-gray-500" x-text="plugin.description"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="plugin.author"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="plugin.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            <span x-text="plugin.active ? '已激活' : '未激活'"></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <template x-if="plugin.active">
                                            <button @click="deactivatePlugin(plugin.name)" 
                                                    class="text-red-600 hover:text-red-900">停用</button>
                                        </template>
                                        <template x-if="!plugin.active">
                                            <button @click="activatePlugin(plugin.name)" 
                                                    class="text-green-600 hover:text-green-900">激活</button>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 钩子测试 -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">钩子测试</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">测试动作钩子</h3>
                        <input x-model="testHook.action" type="text" placeholder="钩子名称" 
                               class="w-full p-2 border border-gray-300 rounded mb-2">
                        <textarea x-model="testHook.actionData" placeholder="数据 (JSON格式)" 
                                  class="w-full p-2 border border-gray-300 rounded mb-2" rows="3"></textarea>
                        <button @click="testActionHook()" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            执行动作钩子
                        </button>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">测试过滤器钩子</h3>
                        <input x-model="testHook.filter" type="text" placeholder="钩子名称" 
                               class="w-full p-2 border border-gray-300 rounded mb-2">
                        <textarea x-model="testHook.filterData" placeholder="数据 (JSON格式)" 
                                  class="w-full p-2 border border-gray-300 rounded mb-2" rows="3"></textarea>
                        <button @click="testFilterHook()" 
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            应用过滤器钩子
                        </button>
                    </div>
                </div>
            </div>

            <!-- 消息提示 -->
            <div x-show="message" x-transition 
                 :class="messageType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'"
                 class="border px-4 py-3 rounded mb-4">
                <span x-text="message"></span>
            </div>
        </div>
    </div>

    <script>
        function pluginManager() {
            return {
                plugins: [],
                stats: {},
                message: '',
                messageType: 'success',
                testHook: {
                    action: 'test.action',
                    actionData: '{}',
                    filter: 'test.filter',
                    filterData: '{"value": "test"}'
                },

                init() {
                    this.loadPlugins();
                    this.loadStats();
                },

                async loadPlugins() {
                    try {
                        const response = await fetch('/api/plugins');
                        const data = await response.json();
                        if (data.success) {
                            this.plugins = data.data;
                        }
                    } catch (error) {
                        this.showMessage('加载插件列表失败', 'error');
                    }
                },

                async loadStats() {
                    try {
                        const response = await fetch('/api/plugins/stats');
                        const data = await response.json();
                        if (data.success) {
                            this.stats = data.data;
                        }
                    } catch (error) {
                        this.showMessage('加载统计信息失败', 'error');
                    }
                },

                async activatePlugin(name) {
                    try {
                        const response = await fetch('/api/plugins/activate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ name })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.showMessage(data.message, 'success');
                            this.loadPlugins();
                            this.loadStats();
                        } else {
                            this.showMessage(data.message, 'error');
                        }
                    } catch (error) {
                        this.showMessage('激活插件失败', 'error');
                    }
                },

                async deactivatePlugin(name) {
                    try {
                        const response = await fetch('/api/plugins/deactivate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ name })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.showMessage(data.message, 'success');
                            this.loadPlugins();
                            this.loadStats();
                        } else {
                            this.showMessage(data.message, 'error');
                        }
                    } catch (error) {
                        this.showMessage('停用插件失败', 'error');
                    }
                },

                async testActionHook() {
                    try {
                        const response = await fetch('/api/plugins/hooks/test', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                type: 'action',
                                hook: this.testHook.action,
                                data: JSON.parse(this.testHook.actionData || '{}')
                            })
                        });
                        const data = await response.json();
                        this.showMessage(data.message, data.success ? 'success' : 'error');
                    } catch (error) {
                        this.showMessage('测试动作钩子失败', 'error');
                    }
                },

                async testFilterHook() {
                    try {
                        const response = await fetch('/api/plugins/hooks/test', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                type: 'filter',
                                hook: this.testHook.filter,
                                data: JSON.parse(this.testHook.filterData || '{}')
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.showMessage(`过滤器应用成功，结果: ${JSON.stringify(data.data.filtered)}`, 'success');
                        } else {
                            this.showMessage(data.message, 'error');
                        }
                    } catch (error) {
                        this.showMessage('测试过滤器钩子失败', 'error');
                    }
                },

                showMessage(msg, type = 'success') {
                    this.message = msg;
                    this.messageType = type;
                    setTimeout(() => {
                        this.message = '';
                    }, 5000);
                }
            }
        }
    </script>
</body>
</html>