{{if $view_format=='modal'}}<md-dialog aria-label="{{$path}}" id="md-dialog-{{$path}}">
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>{{{{$object}}.state=='active' ? 'Edit {{$object|capitalize}}' : 'Add {{$object|capitalize}}'}}</h2>
        </div>
    </md-toolbar>
    <form fs-validate="save()">
        <md-dialog-content>
            <div class="md-dialog-content">
{{if $edit}}
                <md-input-container class="md-block">
                    <label>Name</label>
                    <input type="text" ng-model="{{$object}}.name" required>
                </md-input-container>{{/if}}
{{if $lister}}              <fs-lister ls-options="lister"></fs-lister>{{/if}}
            </div>
        </md-dialog-content>
        <md-dialog-actions>
            <md-button type="button" ng-click="hide()">Cancel</md-button>
            <md-button type="submit" class="md-accent">Save</md-button>
        </md-dialog-actions>
    </form>
</md-dialog>
{{else}}
{{if $lister}}
<fs-lister ls-options="lister"></fs-lister>
{{/if}}
{{/if}}
{{if $edit && $view_format=='page'}}
<form fs-validate="save()">
    <md-input-container class="md-block">
        <label>Name</label>
        <input type="text" ng-model="{{$object}}.name" required>
    </md-input-container>

    <md-button type="submit" class="md-accent">Save</md-button>
</form>
{{/if}}

