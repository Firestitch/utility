        function {$plural_model|replace:'_':''}Get({$api_singular}_id, id, data, options) {
            return fsApi.get('{$service}s/' + {$api_singular}_id + '/{$plural_model|replace:'_':''}/' + id, data, fsApi.options({ key: '{$model}', apply: { key: '{$model}', function: create }},options));
        }

        function {$plural_model|replace:'_':''}Gets({$api_singular}_id, data, options) {
            return fsApi.get('{$service}s/' + {$api_singular}_id + '/{$plural_model|replace:'_':''}', data, fsApi.options({ key: '{$plural_model}', apply: { key: '{$plural_model}', function: create, array: true }},options));
        }

        function {$plural_model|replace:'_':''}Put({$api_singular}_id, data, options) {
            return fsApi.put('{$service}s/' + {$api_singular}_id + '/{$plural_model|replace:'_':''}/' + data.id, data, fsApi.options({ key: '{$model}' },options));
        }

        function {$plural_model|replace:'_':''}Post({$api_singular}_id, data, options) {
            return fsApi.post('{$service}s/' + {$api_singular}_id + '/{$plural_model|replace:'_':''}', data, fsApi.options({ key: '{$model}' },options));
        }

        function {$plural_model|replace:'_':''}Remove({$api_singular}_id, id, data) {
            return fsApi.delete('{$service}s/' + {$api_singular}_id + '/{$plural_model|replace:'_':''}/' + id, data, { key: '{$model}' });
        }