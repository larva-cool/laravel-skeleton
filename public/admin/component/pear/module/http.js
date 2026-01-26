layui.define(['layer', 'util'], function (exports) {
    "use strict";

    var $ = layui.$;
    var layer = layui.layer;
    var MOD_NAME = 'http';

    var http = {
        version: '1.0.0',
        config: {
            baseUrl: '',                // 接口基础路径（如 /api）
            timeout: 15000,             // 超时时间（毫秒）
            loading: true,              // 是否显示加载层
            loadingMsg: '处理中...',     // 加载提示文字
            acceptJson: true,           // 是否强制 JSON 响应
            loginUrl: '/admin/login',   // 默认登录页路径
            defaultContentType: 'application/json', // 默认 Content-Type
            // 成功判断：服务端 code=0 为成功
            checkSuccess: function (res) {
                return res.code !== undefined ? res.code === 0 : !res.message;
            },
            // 错误消息提取：优先取 errors，其次取 message
            getErrorMsg: function (res) {
                if (res.errors && typeof res.errors === 'object') {
                    var firstError = Object.values(res.errors)[0];
                    return Array.isArray(firstError) ? firstError[0] : firstError;
                }
                return res.message || '操作失败';
            }
        },

        // 全局配置
        set: function (options) {
            if (!$.isPlainObject(options)) return this;
            $.extend(true, this.config, options);
            return this;
        },

        // 从 Cookie 获取最新 CSRF Token（Laravel XSRF-TOKEN）
        _getCsrfToken: function () {
            var cookieValue = document.cookie
                .split('; ')
                .find(function (row) { return row.startsWith('XSRF-TOKEN='); })
                ?.split('=')[1];
            return cookieValue ? decodeURIComponent(cookieValue) : '';
        },

        // 构建完整 URL（拼接 baseUrl）
        _buildUrl: function (url) {
            var baseUrl = this.config.baseUrl;
            if (baseUrl && !baseUrl.endsWith('/') && !url.startsWith('/')) {
                baseUrl += '/';
            }
            return baseUrl + url;
        },

        // 构建请求头（动态 CSRF + 自定义头）
        _buildHeaders: function (customHeaders) {
            customHeaders = customHeaders || {};
            var headers = {
                'X-CSRF-TOKEN': this._getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': this.config.defaultContentType + ';charset=UTF-8'
            };
            if (this.config.acceptJson) {
                headers['Accept'] = 'application/json';
            }
            return $.extend({}, headers, customHeaders);
        },

        // 处理请求参数（GET 拼接 URL，POST 转换格式）
        _handleParams: function (method, url, data) {
            if (data == null || $.isEmptyObject(data)) {
                return { url: url, body: null };
            }

            var methodUpper = method.toUpperCase();
            // GET 请求：参数拼接到 URL
            if (methodUpper === 'GET') {
                var params = new URLSearchParams();
                var appendParam = function (key, value) {
                    if (Array.isArray(value)) {
                        value.forEach(function (v) {
                            params.append(key + '[]', v);
                        });
                    } else {
                        params.append(key, value);
                    }
                };
                Object.entries(data).forEach(function (entry) {
                    var key = entry[0], value = entry[1];
                    appendParam(key, value);
                });
                var paramsStr = params.toString();
                return {
                    url: url + (url.includes('?') ? '&' : '?') + paramsStr,
                    body: null
                };
            }

            // POST/PUT 等请求：根据 Content-Type 转换 body
            var contentType = this._buildHeaders()['Content-Type'];
            var body = data;

            // JSON 格式（默认）
            if (contentType.includes('application/json')) {
                body = JSON.stringify(data);
            }
            // 表单格式
            else if (contentType.includes('application/x-www-form-urlencoded')) {
                body = new URLSearchParams();
                Object.entries(data).forEach(function (entry) {
                    var key = entry[0], value = entry[1];
                    if (Array.isArray(value)) {
                        value.forEach(function (v) {
                            body.append(key + '[]', v);
                        });
                    } else {
                        body.append(key, value);
                    }
                });
            }
            // 文件上传格式
            else if (contentType.includes('multipart/form-data')) {
                if (!(body instanceof FormData)) {
                    body = new FormData();
                    Object.entries(data).forEach(function (entry) {
                        var key = entry[0], value = entry[1];
                        if (Array.isArray(value)) {
                            value.forEach(function (v) {
                                body.append(key + '[]', v);
                            });
                        } else {
                            body.append(key, value);
                        }
                    });
                }
            }

            return { url: url, body: body };
        },

        // 核心请求方法（重构：用 async/await 处理响应，统一错误格式）
        request: function (url, options) {
            options = options || {};
            var that = this;
            var config = $.extend(true, {}, this.config, {
                showDefaultErrorMsg: true // 控制默认错误提示开关
            }, options);
            var method = (options.method || 'GET').toUpperCase();

            // 处理参数和 URL
            var params = this._handleParams(
                method,
                this._buildUrl(url),
                options.data
            );
            var fullUrl = params.url;
            var body = params.body;

            // 显示加载层
            var loadingIndex = null;
            if (config.loading) {
                loadingIndex = layer.load(2, {
                    shade: config.loadingShade,
                    content: config.loadingMsg
                });
            }

            // 构建 fetch 配置
            var headers = this._buildHeaders(options.headers);
            var fetchOptions = {
                method: method,
                headers: headers,
                credentials: 'same-origin',
                signal: AbortSignal.timeout(config.timeout)
            };
            // 处理 FormData（避免手动设置 Content-Type）
            if (body instanceof FormData) {
                fetchOptions.body = body;
                delete fetchOptions.headers['Content-Type'];
            } else if (body) {
                fetchOptions.body = body;
            }

            // 用 async/await 重构响应处理，清晰区分错误类型
            var promise = (async function () {
                try {
                    const response = await fetch(fullUrl, fetchOptions);
                    if (loadingIndex) layer.close(loadingIndex);

                    // 1. 处理 422 表单验证错误（优先解析响应体）
                    if (response.status === 422) {
                        const res = await response.json().catch(function () {
                            // 解析失败时返回默认结构
                            return { message: '表单验证失败', errors: {} };
                        });
                        res.code = 1;
                        // 抛出统一格式的业务错误（含 errors）
                        throw {
                            type: 'business',
                            res: res,
                            msg: config.getErrorMsg(res)
                        };
                    }

                    // 2. 处理其他特殊状态码
                    if (response.status === 419) {
                        layer.msg('页面已过期，请刷新重试', { icon: 5 }, function () {
                            window.location.reload();
                        });
                        throw { type: 'csrf_expire', message: 'CSRF Token已过期' };
                    }
                    if (response.status === 401) {
                        layer.msg('请先登录', { icon: 5 }, function () {
                            window.location.href = config.loginUrl;
                        });
                        throw { type: 'unauthorized', message: '未授权访问' };
                    }
                    if (response.status === 403) {
                        layer.msg('没有操作权限', { icon: 5 });
                        throw { type: 'forbidden', message: '权限不足' };
                    }
                    if (response.status === 404) {
                        layer.msg('接口不存在', { icon: 5 });
                        throw { type: 'not_found', message: '资源未找到' };
                    }

                    // 3. 处理非 2xx 状态码（服务器错误）
                    if (!response.ok) {
                        throw { type: 'server_error', message: `服务器错误: ${response.status}` };
                    }

                    // 4. 解析正常响应
                    const res = await response.json().catch(function () {
                        return response.text().then(function (text) {
                            return { __text: text };
                        });
                    });
                    if (res.__text) {
                        throw { type: 'format_error', message: `响应格式错误: ${res.__text}` };
                    }

                    // 5. 业务逻辑判断（code=0 为成功）
                    if (!config.checkSuccess(res)) {
                        throw {
                            type: 'business',
                            res: res,
                            msg: config.getErrorMsg(res)
                        };
                    }

                    // 触发成功回调
                    if (typeof options.success === 'function') {
                        options.success(res);
                    }
                    return res;

                } catch (error) {
                    // 统一错误处理
                    if (loadingIndex) layer.close(loadingIndex);

                    // 非业务错误且开启默认提示时，显示 layer 提示
                    if (config.showDefaultErrorMsg && error.type !== 'business') {
                        const errorMsg = error.message || '请求失败，请重试';
                        layer.msg(errorMsg, { icon: 5 });
                    }

                    // 触发自定义 error 回调
                    if (typeof options.error === 'function') {
                        options.error(error);
                    }
                    return Promise.reject(error);
                }
            })();

            return promise;
        },

        // RESTful 快捷方法
        get: function (url, data, options) {
            options = options || {};
            return this.request(url, $.extend({}, options, { method: 'GET', data: data }));
        },
        post: function (url, data, options) {
            options = options || {};
            return this.request(url, $.extend({}, options, { method: 'POST', data: data }));
        },
        put: function (url, data, options) {
            options = options || {};
            return this.request(url, $.extend({}, options, { method: 'PUT', data: data }));
        },
        patch: function (url, data, options) {
            options = options || {};
            return this.request(url, $.extend({}, options, { method: 'PATCH', data: data }));
        },
        delete: function (url, data, options) {
            options = options || {};
            return this.request(url, $.extend({}, options, { method: 'DELETE', data: data }));
        },

        // 文件上传快捷方法
        upload: function (url, data, options) {
            options = options || {};
            return this.post(url, data, $.extend({}, options, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }));
        },

        // 表单提交快捷方法（422 错误仅显示第一个错误内容，无前缀和字段名）
        formPost: function (url, data, options) {
            const defaultOptions = {
                showDefaultErrorMsg: false, // 关闭默认提示，避免重复
                reloadOnSuccess: true,      // 成功后是否刷新页面
                loadingMsg: '处理中...',

                // 成功回调：用 layer.msg 提示，关闭弹窗/刷新页面
                success: function (res) {
                    const successMsg = res.message || '操作成功';
                    layer.msg(successMsg, {
                        icon: 6, // 成功图标
                        time: 1500 // 1.5秒后自动关闭
                    }, function () {
                        const frameIndex = parent.layer.getFrameIndex(window.name);
                        if (frameIndex) {
                            parent.layer.close(frameIndex); // 弹窗场景关闭弹窗
                        } else if (defaultOptions.reloadOnSuccess) {
                            window.location.reload(); // 普通页面刷新
                        }
                    });
                },

                // 错误回调：422 错误仅显示第一个错误内容（无字段名和前缀）
                error: function (err) {
                    // 1. 处理 422 业务错误（仅显示第一个错误内容）
                    if (err.type === 'business' && err.res?.errors) {
                        // 提取第一个错误信息（直接使用 err.msg，已在 getErrorMsg 中处理）
                        layer.msg(err.msg, {
                            icon: 5, // 错误图标
                            time: 2000 // 2秒后自动关闭
                        });
                        return;
                    }

                    // 2. 处理其他错误（非 422 业务错误、服务器错误等）
                    const errorMsg = err.msg || err.message || '操作失败';
                    layer.msg(errorMsg, {
                        icon: 5,
                        time: 2000
                    });
                }
            };

            // 合并用户配置（用户配置优先级更高）
            const finalOptions = $.extend(true, {}, defaultOptions, options);
            return this.post(url, data, finalOptions);
        }
    };

    exports(MOD_NAME, http);
});
