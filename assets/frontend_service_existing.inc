{if in_array("get",$methods)}		function {$namespace}Get({$api_singular}_id, id, data, options) {
            return fsApi.get('{$service}s/' + {$api_singular}_id + '/{$namespace}/' + id, data, fsApi.options({ key: '{$model}', apply: { key: '{$model}', function: create }},options));
        }

        function {$namespace}Gets({$api_singular}_id, data, options) {
            return fsApi.get('{$service}s/' + {$api_singular}_id + '/{$namespace}', data, fsApi.options({ key: '{$plural_model}', apply: { key: '{$plural_model}', function: create, array: true }},options));
        }

{/if}{if in_array("put",$methods)}
        function {$namespace}Put({$api_singular}_id, data, options) {
            return fsApi.put('{$service}s/' + {$api_singular}_id + '/{$namespace}/' + data.id, data, fsApi.options({ key: '{$model}' },options));
        }

{/if}{if in_array("post",$methods)}
        function {$namespace}Post({$api_singular}_id, data, options) {
            return fsApi.post('{$service}s/' + {$api_singular}_id + '/{$namespace}', data, fsApi.options({ key: '{$model}' },options));
        }

{/if}{if in_array("delete",$methods)}
        function {$namespace}Delete({$api_singular}_id, id, data) {
            return fsApi.delete('{$service}s/' + {$api_singular}_id + '/{$namespace}/' + id, data, { key: '{$model}' });
        }

{/if}