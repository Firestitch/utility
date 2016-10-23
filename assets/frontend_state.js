.state('{{$state}}', {
{{if $view_format=='page'}}		url: '{{$url}}/:id',
		controller: '{{$controller}}Ctrl',
		templateUrl: 'views/{{$view}}.html',{{/if}}
		params: {
			id: { squash: true, value: null },
			time: { squash: true, value: null },{{if !$lister}}
			{{$object}}: { squash: true, value: null }{{/if}}
		},{{if $view_format=='page'}}
		resolve: { {{if $edit}}
			{{$object}}: function($q,$stateParams,{{$object}}Service) {
				return $q(function(resolve) {
					var {{$object}} = $stateParams.{{$object}};

					if(!{{$object}}) {
						return {{$object}}Service.get($stateParams.id)
						.then(function({{$object}}) {
							resolve({{$object}});
						});
					}

					resolve({{$object}});
				}).then(function({{$object}}) {
					if({{$object}}) {
						$stateParams.id = {{$object}}.id;
					}
					return {{$object}};
				});
			},{{/if}}
			time: function($stateParams) { return $stateParams.time; }
		}
{{/if}}{{if $view_format=='modal'}}
		onEnter: function(fsModal, $state, $stateParams, {{$object}}Service) {
			fsModal
			.show(	'{{$controller}}Ctrl',
					'views/{{$view}}.html',
					{
						resolve: { {{if $edit}}
							{{$object}}: function() { return $stateParams.{{$object}}; }{{/if}}
						}
					})
			.finally(function() {
				$state.go('{{$state}}s');
			});
		}{{/if}}
	})

