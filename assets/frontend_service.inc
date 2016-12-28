(function () {
    'use strict';

    angular.module('app')
    .factory('{$service}Service', function (fsApi) {
        var service = {
            gets: gets,
            get: get,
            put: put,
            post: post,
            save: save,
            'delete': remove,
            create: create{if in_array("order",$options)},
            order: order{/if}

		};

        return service;

        function save(data, options) {
               if(data.id)
                return put(data, options);
            return post(data, options);
        }

        function create(data) {
            data = data || {};
            data.id = data.id || null;
            data.name = data.name || '';
            return data;
        }

        function gets(data, options) {
            return fsApi.get('{$service|plural|lower}', data, fsApi.options({ key: '{$plural_name}', apply: { key: '{$plural_name}', function: create, array: true }},options));
        }

        function get(id, data, options) {
            return fsApi.get('{$service|plural|lower}/' + id, data, fsApi.options({ key: '{$name}', apply: { key: '{$name}', function: create }},options));
        }

        function put(data, options) {
            return fsApi.put('{$service|plural|lower}/' + data.id, data, fsApi.options({ key: '{$name}' },options));
        }

        function post(data, options) {
            return fsApi.post('{$service|plural|lower}', data, fsApi.options({ key: '{$name}' },options));
        }

        function remove(id, data) {
            return fsApi.delete('{$service|plural|lower}/' + id, data, { key: '{$name}' });
        }
{if in_array("order",$options)}

        function order(data) {
            return fsApi.put('{$service|plural|lower}/order', data );
        }{/if}

    });
})();
