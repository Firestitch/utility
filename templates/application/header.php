
<div id="header" class="container">
	<div class="navbar navbar-default">

	  	<div class="navbar-header">
		    <a class="navbar-brand" href="/"> <?=SERVER_UTIL::get_server_host()?>	</a>
		</div>
		
		<div class="pt8 pr8 fr">
			<?=HTML_UTIL::dropdown("application",$applications,$application,array("class"=>"wa"))?>
		</div>	
		<div class="collapse navbar-collapse">
	    	<ul class="nav navbar-nav">

				<li><a href="javascript:;" class="update-link" data-url="/generate/db">DBO/DBQ</a></li>
				
				<li class="dropdown">
		    		<a href="#" class="dropdown-toggle" data-toggle="dropdown">Models <b class="caret"></b></a>
		        	<ul class="dropdown-menu">
						<li><a href="javascript:;" class="update-link" data-url="/generate/model">Creation</a></li>
						<li><a href="javascript:;" class="update-link" data-url="/generate/cmodel">C-Model Addons</a></li>
						<li><a href="/generate/modelrelation">Relationship</a></li>
		        	</ul>
		    	</li>

				<li class="dropdown">
		    		<a href="#" class="dropdown-toggle" data-toggle="dropdown">Views <b class="caret"></b></a>
		        	<ul class="dropdown-menu">
						<li><a href="/generate/mvc/">View / Action</a></li>
						<li><a href="/generate/listview">List / View</a></li>  
		        	</ul>
		    	</li>
		    	
		    	<li><a href="javascript:;" class="update-link" data-url="/generate/api">API</a></
			</ul>
		</div>

	</div>
</div>