
<div id="header">
	<div class="navbar navbar-default">

	  	<div class="navbar-header">
		    <a class="navbar-brand" href="/"> <?=SERVER_UTIL::get_server_host()?>	</a>
		</div>

		<div class="collapse navbar-collapse">
	    	<ul class="nav navbar-nav">

	    		<li><a href="javascript:;" class="update-link" data-url="/generate/db">DB &amp; Models</a></li>

				<li class="dropdown">
		    		<a href="#" class="dropdown-toggle" data-toggle="dropdown">Models<b class="caret"></b></a>
		        	<ul class="dropdown-menu">
						<li><a href="/generate/modelrelation">Relationship</a></li>
						<li><a href="javascript:;" class="update-link" data-url="/generate/cmodel">C-Model Addons</a></li>
		        	</ul>
		    	</li>

				<li><a href="/generate/api">API</a></li>

				<li class="dropdown">
		    		<a href="#" class="dropdown-toggle" data-toggle="dropdown">Backend <b class="caret"></b></a>
		        	<ul class="dropdown-menu">
						<li><a href="/generate/mvc/">View / Action</a></li>
						<li><a href="/generate/listview">List / View</a></li>
		        	</ul>
		    	</li>

				<li class="dropdown">
		    		<a href="#" class="dropdown-toggle" data-toggle="dropdown">Frontend <b class="caret"></b></a>
		        	<ul class="dropdown-menu">
						<li><a href="/generate/vc">View / Controller</a></li>

		        	</ul>
		    	</li>

		    	<li class="dropdown">
		    		<a href="#" class="dropdown-toggle" data-toggle="dropdown">Service <b class="caret"></b></a>
		        	<ul class="dropdown-menu">
						<li><a href="/generate/service">New Service</a></li>
						<li><a href="/generate/serviceexisting">Existing Service</a></li>
		        	</ul>
		    	</li>

		    	<li class="dropdown">
		    		<a href="#" class="dropdown-toggle" data-toggle="dropdown">Controller <b class="caret"></b></a>
		        	<ul class="dropdown-menu">
						<li><a href="/generate/controller">New Controller</a></li>
		        	</ul>
		    	</li>

		    	<li class="dropdown">
		    		<a href="#" class="dropdown-toggle" data-toggle="dropdown">Directive <b class="caret"></b></a>
		        	<ul class="dropdown-menu">
						<li><a href="/generate/directive">New Directive</a></li>
		        	</ul>
		    	</li>

			</ul>
		</div>

	</div>
</div>