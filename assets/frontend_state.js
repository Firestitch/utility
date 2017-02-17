.state('{{$state}}', { {{if $url}}
		url: '{{$url}}',{{/if}}{{if $view_format=='page'}}
		controller: '{{$controller}}Ctrl',
		templateUrl: 'views/{{$view}}.html',{{/if}}
		params: { {{if $interface=='form'}}
			id: { squash: true, value: null },
			{{$object}}: { squash: true, value: null },{{/if}}
			time: { squash: true, value: null }{{if $params}},{{foreach from=$params item=$param name=params}}
			{{$param}}: { squash: true, value: null }{{if !$smarty.foreach.params.last}},{{/if}}
{{/foreach}}{{/if}}		},
		data: {
			permissions: []
		}{{if $interface=="form" && $view_format=='page'}},
		resolve: {
			{{if $object}}{{$object}}: function($stateParams, {{$object}}Service, aclService, $q) {
							return $q(function(resolve) {
								if($stateParams.id) {
									return aclService.require({{$object}}Service.get($stateParams.id)).then(resolve);
								}{{if in_array("draft",$options)}}
								if(!$stateParams.{{$object}} || !$stateParams.{{$object}}.id) {
									return {{$object}}Service.post({ state: 'draft' }).then(resolve);
								}
{{/if}}
								return resolve($stateParams.{{$object}} || {});
							}).then(function({{$object}}) {
								if({{$object}}) {
									$stateParams.id = {{$object}}.id;
								}
								return {{$object}} || {};
							});
			},{{/if}}
			time: function($stateParams) { return $stateParams.time; }
		}{{/if}}{{if $view_format=='modal'}},
		onEnter: function(fsModal, $state, $stateParams{{if $object}}, {{$object}}Service{{/if}}, aclService, $q) {
			fsModal
			.show(	'{{$controller}}Ctrl',
					'views/{{$view}}.html',
					{
						{{if $object}}resolve: {
							{{$object}}: function() {
								return $q(function(resolve) {
									if($stateParams.id) {
										return aclService.require({{$object}}Service.get($stateParams.id)).then(resolve);
									}{{if in_array("draft",$options)}}
									if(!$stateParams.{{$object}} || !$stateParams.{{$object}}.id) {
										return {{$object}}Service.post({ state: 'draft' }).then(resolve);
									}
						}
						}
{{/if}}
									return resolve($stateParams.{{$object}} || {});
								}).then(function({{$object}}) {
									if({{$object}}) {
										$stateParams.id = {{$object}}.id;
									}
									return {{$object}} || {};
								});
							}
						}{{/if}}
					});
		}{{/if}}{{if $view_format=='drawer'}},
		onEnter: function(fsDrawer, $state, $stateParams{{if $object}}, {{$object}}Service{{/if}}, aclService, $q, fsLister) {
			fsDrawer
			.create({	controller: '{{$controller}}Ctrl',
						templateUrl: 'views/{{$view}}.html',
						resolve: {
							{{if $object}}{{$object}}: function() {
								return $q(function(resolve) {
									if($stateParams.id) {
										return aclService.require({{$object}}Service.get($stateParams.id)).then(resolve);
									}{{if in_array("draft",$options)}}
									if(!$stateParams.{{$object}} || !$stateParams.{{$object}}.id) {
										return {{$object}}Service.post({ state: 'draft' }).then(resolve);
									}
{{/if}}
									return resolve($stateParams.{{$object}} || {});
								}).then(function({{$object}}) {
									if({{$object}}) {
										$stateParams.id = {{$object}}.id;
									}
									return {{$object}} || {};
								});
							}{{/if}}
						}{{if $parent.interface=="lister"}},
						close: function() {
							fsLister.reload('{{$parent.state}}');
						}{{/if}}
					});
		}{{/if}}
	})