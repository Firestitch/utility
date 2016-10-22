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
            create: create
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
            return fsApi.get('{$service}s', data, fsApi.options({ key: '{$service}s', apply: { key: '{$service}s', function: create, array: true }},options));
        }

        function get(id, data, options) {
            return fsApi.get('{$service}s/' + id, data, fsApi.options({ key: '{$service}', apply: { key: '{$service}', function: create }},options));
        }

        function put(data, options) {
            return fsApi.put('{$service}s/' + data.id, data, fsApi.options({ key: '{$service}' },options));
        }

        function post(data, options) {
            return fsApi.post('{$service}s', data, fsApi.options({ key: '{$service}' },options));
        }

        function remove(id, data) {
            return fsApi.delete('{$service}s/' + id, data, { key: '{$service}' });
        }

    });
})();
