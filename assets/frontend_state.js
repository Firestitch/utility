.state('{{$state}}', { {{if $url && $view_format!='modal'}}
		url: '{{$url}}',{{/if}}{{if $view_format=='page'}}
		controller: '{{$controller}}Ctrl',
		templateUrl: 'views/{{$view}}.html',{{/if}}
		params: { {{if $interface=='form'}}
			{{$object}}: null,{{/if}}
			time: null{{if $params}},{{foreach from=$params item=$param name=params}}
			{{$param}}: null{{if !$smarty.foreach.params.last}},{{/if}}{{/foreach}}{{/if}}
		},
		data: {
			permissions: []
		},
		resolve: {literal}{{/literal}{{if $object && $interface=="form"}}
			{{$object}}: function(appService, $stateParams, {{$object}}Service) {
				return appService.stateModel({{$object}}Service,$stateParams,'{{$object}}'{{if $state_model_options}},{{$state_model_options}}{{/if}});
			}{{/if}}
		}{{if $view_format=='modal'}},
		onEnter: function($stateParams,fsModal{{if $object}},{{$object}},fsLister{{/if}}) {
			fsModal
			.show(	'{{$controller}}Ctrl',
					'views/{{$view}}.html'{{if $object}},
					{
						resolve: {
							{{$object}}: function() { return {{$object}}; }
						}
					{literal}}{/literal}{{/if}}){{if $object}}
			.then(function() {
				if($stateParams.{{$object}} && $stateParams.{{$object}}.new) {
					fsLister.reload('{{$parent.state}}');
				} else {
					angular.extend($stateParams.{{$object}},{{$object}});
				}
			}){{/if}};
		}{{/if}}{{if $view_format=='drawer'}},
		onEnter: function(fsDrawer{{if $object}}, {{$object}}{{/if}}) {
			fsDrawer
			.create({	controller: '{{$controller}}Ctrl',
						templateUrl: 'views/{{$view}}.html'{{if $object}},
						{
							resolve: {
								{{$object}}: function() { return {{$object}}; }
							}
						{literal}}{/literal}{{/if}});
		}{{/if}}
	})