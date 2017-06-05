{{if $view_format=='modal'}}<md-dialog aria-label="{{$state}}" id="md-dialog-{{str_replace(".","-",$state)}}">
    <md-toolbar>
        <div class="md-toolbar-tools">{{if $interface=="form"}}
            {literal}{{{/literal}{{$object}}.state=='active' ? 'Edit {{$object|capitalize}}' : 'Add {{$object|capitalize}}'}}{{else}}
			Title{{/if}}
        </div>
    </md-toolbar>
{{if !$interface}}
	<md-dialog-content>
		<div class="md-dialog-content"></div>
  	</md-dialog-content>
    <md-dialog-actions>
        <md-button type="button" ng-click="hide()">Cancel</md-button>
    </md-dialog-actions>
{{/if}}{{if $interface=="form"}}
    <form fs-validate="save()">
        <md-dialog-content>
        	<div class="md-dialog-content">
	            <md-input-container class="md-block">
	            	<label>Name</label>
	                <input type="text" ng-model="{{$object}}.name" md-autofocus required>
	           	</md-input-container>
	        </div>
        </md-dialog-content>
        <md-dialog-actions>
            <md-button type="button" ng-click="hide()">Cancel</md-button>
            <md-button type="submit" class="md-accent">Save</md-button>
        </md-dialog-actions>
    </form>
{{/if}}{{if $interface=="lister"}}
	<md-dialog-content>
		<div class="md-dialog-content">
			<fs-lister ls-options="lister"{{if in_array("order",$options)}} ls-sort{{/if}}></fs-lister>
		</div>
  	</md-dialog-content>
    <md-dialog-actions>
        <md-button type="button" ng-click="hide()">Cancel</md-button>
        <md-button type="submit" class="md-accent">Save</md-button>
    </md-dialog-actions>
{{/if}}
</md-dialog>
{{/if}}{{if $view_format!='modal'}}
{{if $interface=='lister'}}
<fs-lister ls-options="lister"{{if in_array("order",$options)}} ls-sort{{/if}}></fs-lister>
{{/if}}{{if $interface=='form'}}
<form fs-validate="save()">
    <md-input-container class="md-block">
        <label>Name</label>
        <input type="text" ng-model="{{$object}}.name" md-autofocus required>
    </md-input-container>

    <md-input-container class="md-block">
        <label>Select</label>
        <md-select ng-model="{{$object}}.select">
            <md-option ng-value="item.id" ng-repeat="item in values">{literal}{{item.name}}{/literal}</md-option>
        </md-select>
    </md-input-container>

    <md-button type="submit" class="md-accent">Save</md-button>
</form>{{/if}}{{/if}}
