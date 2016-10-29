(function (){
    'use strict';

    angular.module('app')
    .controller('{$controller}Ctrl', function($scope,$state,{{$object}}Service{{if $lister=='modal'}},fsModal{{/if}}{{if $edit}},fsAlert,fsModal,{{$object}}{{/if}}) {

{{if $edit}}		$scope.{{$object}} = {{$object}};
{{/if}}{{if $lister}}		$scope.lister = {
			data: function(query, cb) {
                {{$object}}Service
                .gets(angular.merge(query,{}),{ key: null })
                .then(function(result) {
                    cb(result.{{$object}}s,result.paging);
                });
            },{{if $lister=='page' || $lister=='modal' || $lister=='drawer'}}
            topActions: [
                {
                    click: function() {
                    	$scope.update({});
                    },
                    label: 'Add {{$object}}'
                }
            ],{{/if}}
            filters: [
                {
                    name: 'keyword',
                    type: 'text',
                    label: 'Search'
                },
                {
                    name: 'state',
                    label: 'Show Deleted',
                    type: 'checkbox',
                    unchecked: 'active',
                    checked: 'deleted'
                }
            ],
            columns: [
                {   title: 'Name',
                    value: function(data) {
                        return '<a href ng-click="click(data)" ng-hide="data.state==\'deleted\'">{literal}{{data.name}}{/literal}</a>\
                        		<div ng-show="data.state==\'deleted\'">{literal}{{data.name}}{/literal}</span>';
                    },
                    scope: {
                    	click: function(data) {
                    		$scope.update(data);
                    	}
                    }
                }
            ]{{if $lister=='page' || $lister=='modal' || $lister=='drawer'}},
            actions: [
                {
                    label: 'Edit',
                    icon: 'edit',
                    click: function(data) {
                    	$scope.update(data);
                    },
                    show: function(data) {
                    	return data.state!='deleted';
                    }
                },
                {
                    delete: {
                        content: 'Are you sure you would like to delete the {{$object}}?',
                        ok: function(data) {
                            return {{$object}}Service.delete(data.id)
                        }
                    },
                    show: function(data) {
                    	return data.state!='deleted';
                    }
                },
                {
                    label: 'Restore',
                    icon: 'restore',
                    click: function(data) {
                    	{{$object}}Service.put({ id: data.id, state: 'active' })
                    	.then(function() {
                    		$scope.{{$object}}.instance.reload();
                    	});
                    },
                    show: function(data) {
                    	return data.state=='deleted';
                    }
                }
            ]{{/if}}
        };{{/if}}
{{if !$edit && $lister}}
		$scope.update = function({{$object}}) {
        	$state.go('{{rtrim($state,'s')}}',{ {{$object}}: angular.copy({{$object}}), time: Date.now() }{{if $lister=='modal' || $lister=='drawer'}},{ notify: false }{{/if}});
        }
{{/if}}{{if $edit}}
		$scope.save = function() {
            {{$object}}Service
            .save($scope.{{$object}})
            .then(function() {
            	fsAlert.success('Saved Changes');
                {{if $view_format=='modal'}}fsModal.hide();{{/if}}
            });
		}{{/if}}
    });
})();