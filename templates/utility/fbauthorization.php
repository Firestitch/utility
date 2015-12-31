<h2>Facebook Authorization</h2>


<table class="table">
	<tr>
		<td>
			Facebook App ID:
		</td>
		
		<td>
			<?=HTML_UTIL::get_input("faid",$facebook_app_id)?>
		</td>		
	</tr>
	
	<tr>
		<td>
			Facebook Secrect:
		</td>
		
		<td>
			<?=HTML_UTIL::get_input("fs",$facebook_secrect)?>
		</td>		
	</tr>
	
	<tr>
		<td>
			
		</td>
		
		<td>
			<?=HTML_UTIL::get_button("submit","Submit")?>
		</td>		
	</tr>	
</table>