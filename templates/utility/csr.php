


<h1 class="mb15">Certificate Utility</h1>

<h3 class="mb5">Key</h3>




<div class="pt10 cb">
	
	<table>

		<tr>
			<td>
				Key Bit Size
			</td>

			<td>
				<?=HTML_UTIL::dropdown("key_size",array(2048=>2048,1024=>1024))?> 
				<a href="javascript:;" data-type="key" class="generate btn">Generate Key</a>
				<a href="javascript:;" data-type="key" class="download btn">Download Key</a>
			</td>
		</tr>


	</table>

	<div class="">
		<?=HTML_UTIL::textarea("key","",array("id"=>"key","class"=>"w600 h500"))?>
	</div>
</div>

<h3 class="mt15 mb5">CSR</h3>


<div class="pt10">
	
	<table>
		<tr>
			<td>
				2 Letter Country Code:
			</td>

			<td>
				<?=HTML_UTIL::input("country","",array("class"=>"w400","placeholder"=>"CA"))?>
			</td>
		</tr>

		<tr>
			<td>
				State or Province Name:
			</td>

			<td>
				<?=HTML_UTIL::input("state","",array("class"=>"w400","placeholder"=>"eg. Ontario"))?>
			</td>
		</tr>	

		<tr>
			<td>
				Locality Name:
			</td>

			<td>
				<?=HTML_UTIL::input("city","",array("class"=>"w400","placeholder"=>"eg. city"))?>
			</td>
		</tr>	


		<tr>
			<td>
				Organization Name:
			</td>

			<td>
				<?=HTML_UTIL::input("company","",array("class"=>"w400","placeholder"=>"eg. Company Name"))?>
			</td>
		</tr>

		<tr>
			<td>
				Common Name:
			</td>

			<td>
				<?=HTML_UTIL::input("common_name","",array("class"=>"w400","placeholder"=>"eg. www.website.com or *.website.com"))?>
			</td>
		</tr>

		<tr>
			<td>
			
			</td>

			<td>
				<a href="javascript:;" data-type="csr" class="generate btn">Generate CSR</a>
				<a href="javascript:;" data-type="csr" class="download btn">Download CSR</a>
			</td>
		</tr>


	</table>
	 
	<div class="cb">
		
		<?=HTML_UTIL::textarea("csr","",array("id"=>"csr","class"=>"w600 h400"))?>
	
	</div>
</div>

<h3 class="mt15 mb5">Certificate</h3>

<div class="pt10 cb">

	<div class="pb5">
		<a href="javascript:;" data-type="crt" class="generate btn">Generate Certificate</a>
		<a href="javascript:;" data-type="crt" class="download btn">Download Certificate</a>
	</div>
	
	<?=HTML_UTIL::get_textarea("crt","",array("id"=>"crt","class"=>"w600 h400"))?>
	
</div>


<h3 class="mt15 mb5">P12</h3>

<div class="pt10 cb">

	<div class="pt5">
		P12 Password: <?=HTML_UTIL::input("p12-password","")?> 
		<a href="javascript:;" data-type="p12" class="download btn">Download P12</a>
	</div>

</div>


<script>

	$(function() {

		$(".generate").click(function() {
			
			$.post("/utility/docsr/action:generate-" + $(this).data("type"),
					$("form").serializeArray(),
					$.proxy(function(response) {
						if(response.has_success) {
							$("#" + $(this).data("type")).val(response.data.value);
							FF.msg.success("Successfully generated");				
						} else
							FF.msg.error(response.errors);

					},this));
		});

		$(".download").click(function() {
			$("form").attr("action","/utility/docsr/action:download-" + $(this).data("type")).submit();
		});

	});
</script>
