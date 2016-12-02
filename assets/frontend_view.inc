{{if $view_format=='modal'}}<md-dialog aria-label="{{$path}}" id="md-dialog-{{$path}}">
    <md-toolbar>
        <div class="md-toolbar-tools">{{if $interface=="form"}}
            <h2>{literal}{{{/literal}{{$object}}.state=='active' ? 'Edit {{$object|capitalize}}' : 'Add {{$object|capitalize}}'}}</h2>{{else}}
			<h2>Title</h2>{{/if}}
        </div>
    </md-toolbar>
{{if $interface=="form"}}
    <form fs-validate="save()">
        <md-dialog-content>
            <div class="md-dialog-content">
                <md-input-container class="md-block">
                    <label>Name</label>
                    <input type="text" ng-model="{{$object}}.name" required>
                </md-input-container>
            </div>
        </md-dialog-content>
        <md-dialog-actions>
            <md-button type="button" ng-click="hide()">Cancel</md-button>
            <md-button type="submit" class="md-accent">Save</md-button>
        </md-dialog-actions>
    </form>
{{/if}}
{{if $interface=="lister"}}
	<md-dialog-content>
		<fs-lister ls-options="lister"></fs-lister>
  	</md-dialog-content>
    <md-dialog-actions>
        <md-button type="button" ng-click="hide()">Cancel</md-button>
        <md-button type="submit" class="md-accent">Save</md-button>
    </md-dialog-actions>
{{/if}}
</md-dialog>
{{/if}}

{{if $view_format!='modal'}}
{{if $interface=='lister'}}
<fs-lister ls-options="lister"></fs-lister>
{{/if}}
{{if $interface=='form'}}
<form fs-validate="save()">
    <md-input-container class="md-block">
        <label>Name</label>
        <input type="text" ng-model="{{$object}}.name" required>
    </md-input-container>

    <md-input-container class="md-block">
        <label>Select</label>
        <md-select ng-model="{{$object}}.select">
            <md-option ng-value="item.id" ng-repeat="item in values">{literal}{{item.name}}{/literal}</md-option>
        </md-select>
    </md-input-container>

    <md-button type="submit" class="md-accent">Save</md-button>
</form>
{{/if}}
{{/if}}
