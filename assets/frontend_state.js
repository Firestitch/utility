.state('{{$state}}', { {{if $url}}
		url: '{{$url}}',{{/if}}{{if $view_format=='page'}}
		controller: '{{$controller}}Ctrl',
		templateUrl: 'views/{{$view}}.html',{{/if}}
		params: { {{if $interface=='form'}}
			id: null,
			{{$object}}: null,{{/if}}
			time: null{{if $params}},{{foreach from=$params item=$param name=params}}
			{{$param}}: null{{if !$smarty.foreach.params.last}},{{/if}}
{{/foreach}}{{/if}}
		},
		data: {
			permissions: []
		},
		resolve: {literal}{{/literal}{{if $object && $interface=="form"}}
			{{$object}}: function($stateParams, {{$object}}Service, aclService, $q) {
				return $q(function(resolve) {
					if($stateParams.id) {
						return aclService.require({{$object}}Service.get($stateParams.id)).then(resolve);
					}{{if in_array("draft",$options)}}
					if(!$stateParams.{{$object}} || !$stateParams.{{$object}}.id) {
						return {{$object}}Service.post({ state: 'draft' }).then(resolve);
					}
{{/if}}					return resolve($stateParams.{{$object}} || {});
				}).then(function({{$object}}) {
					if({{$object}}) {
						$stateParams.id = {{$object}}.id;
					}
					return {{$object}} || {};
				});
			},{{/if}}
			time: function($stateParams) { return $stateParams.time; }
		}{{if $view_format=='modal'}},
		onEnter: function(fsModal{{if $object}}, {{$object}}{{/if}}) {
			fsModal
			.show(	'{{$controller}}Ctrl',
					'views/{{$view}}.html'{{if $object}},
					{
						resolve: {
							{{$object}}: function() { return {{$object}}; }
						}
					{literal}}{/literal}{{/if}});
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