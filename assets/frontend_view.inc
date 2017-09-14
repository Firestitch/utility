{{if $view_format=='modal'}}<md-dialog aria-label="{{$state}}" id="md-dialog-{{str_replace(".","-",$state)}}">
    <md-toolbar>
        <div class="md-toolbar-tools">{{if $interface=="form"}}
            {literal}{{{/literal}{{$object}}.state=='active' ? 'Edit {{$object|capitalize}}' : 'Add {{$object|capitalize}}'}}{{else}}
			Title{{/if}}
        </div>
    </md-toolbar>
{{if !$interface}}
	<md-dialog-content>
		<div class="md-dialog-content">
			{{if $tabs}}<fs-tabnav fs-selected="tab">
{foreach from=$tabs item=name key=key}				<fs-tabnav-item fs-name="{{$key}}">{{$name}}</fs-tabnav-item>
{/foreach}				<fs-tabnav-content>
{foreach from=$tabs item=name key=key}					<fs-tabnav-pane fs-name="{{$key}}">
{{if $object}}						<md-input-container class="md-block">
							<label>Name</label>
							<input type="text" ng-model="{{$object}}.name" md-autofocus required>
						</md-input-container>{{/if}}
					</fs-tabnav-pane>
{/foreach}
				</fs-tabnav-content>
			</fs-tabnav>{{/if}}
		</div>
  	</md-dialog-content>
    <md-dialog-actions>
        <md-button type="button" ng-click="cancel()">Cancel</md-button>
    </md-dialog-actions>
{{/if}}{{if $interface=="form"}}
    <form fs-validate="save()">
        <md-dialog-content>
        	<div class="md-dialog-content">
        		{{if $tabs}}<fs-tabnav fs-selected="tab">
{foreach from=$tabs item=name key=key}	    			<fs-tabnav-item fs-name="{{$key}}">{{$name}}</fs-tabnav-item>
{/foreach}        	<fs-tabnav-content>
{foreach from=$tabs item=name key=key}        			<fs-tabnav-pane fs-name="{{$key}}">
{{if $object}}        				<md-input-container class="md-block">
				            	<label>Name</label>
				                <input type="text" ng-model="{{$object}}.name" md-autofocus required>
				           	</md-input-container>{{/if}}
						</fs-tabnav-pane>
						{/foreach}
					</fs-tabnav-content>
				</fs-tabnav>
        		{{else}}
	            <md-input-container class="md-block">
	            	<label>Name</label>
	                <input type="text" ng-model="{{$object}}.name" md-autofocus required>
	           	</md-input-container>
	           	{{/if}}
	        </div>
        </md-dialog-content>
        <md-dialog-actions>
            <md-button type="button" ng-click="cancel()">Cancel</md-button>
            <md-button type="submit" class="md-accent">Save</md-button>
        </md-dialog-actions>
    </form>
{{/if}}{{if $interface=="lister"}}
	<md-dialog-content>
		<div class="md-dialog-content">
			{{if $tabs}}<fs-tabnav fs-selected="tab">
{foreach from=$tabs item=name key=key}    			<fs-tabnav-item fs-name="{{$key}}">{{$name}}</fs-tabnav-item>
{/foreach}				<fs-tabnav-content>
{foreach from=$tabs item=name key=key}					<fs-tabnav-pane fs-name="{{$key}}">
{{if $object}}			            <md-input-container class="md-block">
			            	<label>Name</label>
			                <input type="text" ng-model="{{$object}}.name" md-autofocus required>
			           	</md-input-container>{{/if}}
					</fs-tabnav-pane>
					{/foreach}
				</fs-tabnav-content>
			</fs-tabnav>
{{/if}}		<fs-lister ls-options="lister"{{if in_array("order",$options)}} ls-sort{{/if}}></fs-lister>
		</div>
  	</md-dialog-content>
    <md-dialog-actions>
        <md-button type="button" ng-click="cancel()">Cancel</md-button>
        <md-button type="submit" class="md-accent">Save</md-button>
    </md-dialog-actions>
{{/if}}
</md-dialog>
{{/if}}{{if $view_format!='modal'}}{{if $interface=='form'}}
<form fs-validate="save()">
{{/if}}{{if $tabs}}<fs-tabnav fs-selected="tab">
{foreach from=$tabs item=name key=key}	<fs-tabnav-item fs-name="{{$key}}">{{$name}}</fs-tabnav-item>
{/foreach}	<fs-tabnav-content>
{foreach from=$tabs item=name key=key}		<fs-tabnav-pane fs-name="{{$key}}">{/foreach}{{/if}}{{if $interface=='lister'}}
		<fs-lister ls-options="lister"{{if in_array("order",$options)}} ls-sort{{/if}}></fs-lister>
{{else}}
{{if $object}}			<md-input-container class="md-block">
				<label>Name</label>
				<input type="text" ng-model="{{$object}}.name" md-autofocus required>
			</md-input-container>{{/if}}
{{/if}}{{if $tabs}}	</fs-tabnav-content>
</fs-tabnav>{{/if}}
{{if $interface=='form'}}
    <md-button type="submit" class="md-accent">{literal}{{{/literal} {{$object}}.id ? 'Save' : 'Add' {literal}}}{{/literal}</md-button>
</form>
{{/if}}
{{/if}}