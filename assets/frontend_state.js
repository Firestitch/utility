.state('{{$state}}', {
		url: '{{$url}}',{{if $view_format=='page'}}
		controller: '{{$controller}}Ctrl',
		templateUrl: 'views/{{$view}}.html',{{/if}}
		params: { {{if $interface=='form'}}
			id: { squash: true, value: null },
			{{$object}}: { squash: true, value: null },{{/if}}
			time: { squash: true, value: null }
		},{{if $interface=="form" && $view_format=='page'}}
		resolve: {
			{{$object}}: function($stateParams, {{$object}}Service, $q) {
							return $q(function(resolve) {
								if($stateParams.id) {
									return {{$object}}Service.get($stateParams.id).then(resolve);
								}{{if in_array("draft",$options)}}
								if(!$stateParams.{{$object}} || !$stateParams.{{$object}}.id) {
									return {{$object}}Service.post({ state: 'draft' }).then(resolve);
								}
{{/if}}									return resolve($stateParams.{{$object}} || {});
							}).then(function({{$object}}) {
								if({{$object}}) {
									$stateParams.id = {{$object}}.id;
								}
								return {{$object}} || {};
							});
			},
			time: function($stateParams) { return $stateParams.time; }
		}
{{/if}}{{if $view_format=='modal'}}
		onEnter: function(fsModal, $state, $stateParams, {{$object}}Service) {
			fsModal
			.show(	'{{$controller}}Ctrl',
					'views/{{$view}}.html',
					{
						resolve: {
							{{$object}}: function($stateParams, {{$object}}Service, $q) {
								return $q(function(resolve) {
									if($stateParams.id) {
										return {{$object}}Service.get($stateParams.id).then(resolve);
									}{{if in_array("draft",$options)}}
									if(!$stateParams.{{$object}} || !$stateParams.{{$object}}.id) {
										return {{$object}}Service.post({ state: 'draft' }).then(resolve);
									}
{{/if}}									return resolve($stateParams.{{$object}} || {});
								}).then(function({{$object}}) {
									if({{$object}}) {
										$stateParams.id = {{$object}}.id;
									}
									return {{$object}} || {};
								});
							}
						}
					})
			.finally(function() {
				{{if $parent.state}}$state.go('{{$parent.state}}');{{/if}}
			});
		}{{/if}}
	})

