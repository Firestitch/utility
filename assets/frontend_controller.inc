(function (){
    'use strict';

    angular.module('app')
    .controller('{$controller}Ctrl', function($scope, $state{{if $object}}, {{$object}}Service{{/if}}{{if $interface=="form" || in_array("order",$options)}}, fsAlert{{/if}}{{if in_array("order",$options)}}, fsArray{{/if}}{{if $interface=="form" && $object}}, {{$object}}{{/if}}{{if $view_format=="modal"}}, fsModal{{if $parent && $parent.state}}, fsLister{{/if}}{{/if}}) {

{{if $interface=="form"}}		$scope.{{$object}} = {{$object}} || {};
{{/if}}{{if $interface=="lister"}}		$scope.lister = {
			id: '{{$state}}',
			data: function(query) {
                return {{$object}}Service
                .gets(angular.merge(query,{}),{ key: null })
                .then(function(result) {
                    return { data: result.{{$plural_object}}, paging: result.paging };
                });
            },{{if $child}}
            topActions: [
                {
                    click: function() {
                    	$scope.update({});
                    },
                    label: 'Add {{$object}}'
                }
            ],
{{/if}}			filters: [
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
            ],{if in_array("order",$options)}

            paging: false,
            sort: {
                stop: function(item,list) {
                	var data = { {{$object}}_ids: fsArray.list(list,'id') };
                	{{$object}}Service.order(data)
                	.then(function() {
                		fsAlert.success('Ordering Saved');
                	});
                }
            },
{/if}            columns: [
                {   title: 'Name',
                    value: '{{if $child}}{{if $child.view_format=="modal" || $child.view_format=="drawer"}}<a href ng-click="click(data)" ng-hide="data.state==\'deleted\'">{{else}}<a href="{{preg_replace("/:.*/","",$child.url)}}{literal}{{data.id}}{/literal}" ng-hide="data.state==\'deleted\'">{{/if}}{literal}{{data.name}}{/literal}</a>{{else}}<div ng-show="data.state!=\'deleted\'">{literal}{{data.name}}{/literal}</div>{{/if}}\
                        	<div ng-show="data.state==\'deleted\'">{literal}{{data.name}}{/literal}</div>'
                    {{if $child && ($child.view_format=="modal" || $child.view_format=="drawer") && $child.state}},
                    scope: {
                    	click: function(data) {
                    		$scope.update(data);
                    	}
                    }{{/if}}
                }
            ],
            actions: [{{if $child}}
                {
                    label: 'Edit',
                    icon: 'edit',
                    click: function(data) {
                    	$scope.update(data);
                    },
                    show: function(data) {
                    	return data.state!='deleted';
                    }
                },{{/if}}
                {
                    delete: {
                        content: 'Are you sure you would like to delete the {{$object}}?',
                        ok: function(data) {
                            return {{$object}}Service.delete(data.id);
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
            ]
        };
{{if $child && ($child.view_format=="modal" || $child.view_format=="drawer")}}		$scope.update = function({{$object}}) {
        	$state.go('{{$child.state}}',{ {{$object}}: angular.copy({{$object}}), time: Date.now() },{ notify: false });
        }
{{/if}}
{{/if}}{{if $interface=="form"}}
		$scope.save = function() { {{if in_array("draft",$options)}}
			if($scope.{{$object}}.state=='draft') {
				$scope.{{$object}}.state = 'active';
			}
{{/if}}
			{{$object}}Service.save($scope.{{$object}})
            .then(function() {
            	fsAlert.success('Saved Changes');
                {{if $view_format=='modal'}}{{if $parent && $parent.state}}fsLister.reload('{{$parent.state}}');
                {{/if}}fsModal.hide();{{/if}}
            });
		}
{{/if}}
	});
})();