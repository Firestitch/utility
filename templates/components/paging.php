
<?  if($page_index>0) { ?>
<a href="#" onClick="page_back(); return false;">Prev</a>&nbsp;&nbsp;
<? } ?>
Page <?=($page_number);?> of <?=$page_count;?>
<? if($page_count>1 && ($page_index+1)<$page_count) { ?>
&nbsp;&nbsp;<a href="#" onClick="page_next(); return false;">Next</a>
<? } ?>
<script>

	function page_next() {
		
		var obj = document.getElementById("page_index");
		 
		if(obj.value<<?=$page_count;?>)
			obj.value++;
		
		submit_form();
	}
	
	function page_back() {
		var obj = document.getElementById("page_index");
		 
		if(obj.value>0)
			obj.value--;
		
		submit_form();
	}
</script>

<input type="hidden" value="<?=$page_index;?>" id="page_index" name="page_index">