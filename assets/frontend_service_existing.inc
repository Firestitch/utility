        function {$method}Get({$api_singular}_id, id, data, options) {
            return fsApi.get('{$service}s/' + {$api_singular}_id + '/{$method}/' + id, data, fsApi.options({ key: '{$model}', apply: { key: '{$model}', function: create }},options));
        }

        function {$method}Gets({$api_singular}_id, data, options) {
            return fsApi.get('{$service}s/' + {$api_singular}_id + '/{$method}', data, fsApi.options({ key: '{$plural_model}', apply: { key: '{$plural_model}', function: create, array: true }},options));
        }

        function {$method}Put({$api_singular}_id, data, options) {
            return fsApi.put('{$service}s/' + {$api_singular}_id + '/{$method}/' + data.id, data, fsApi.options({ key: '{$model}' },options));
        }

        function {$method}Post({$api_singular}_id, data, options) {
            return fsApi.post('{$service}s/' + {$api_singular}_id + '/{$method}', data, fsApi.options({ key: '{$model}' },options));
        }

        function {$method}Remove({$api_singular}_id, id, data) {
            return fsApi.delete('{$service}s/' + {$api_singular}_id + '/{$method}/' + id, data, { key: '{$model}' });
        }