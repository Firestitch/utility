	<h2>Payment</h2>


	<table>
		<tr>
			<td>
				API Username:
			</td>

			<td>
				<?=HTML_UTIL::get_input("payment_credentials[api_username]",$payment_api_username,array("class"=>"w600"))?>
			</td>		
		</tr>

		<tr>
			<td>
				API Password:
			</td>

			<td>
				<?=HTML_UTIL::get_input("payment_credentials[api_password]",$payment_api_password,array("class"=>"w600"))?>
			</td>		
		</tr>

		<tr>
			<td>
				Signature:
			</td>

			<td>
				<?=HTML_UTIL::get_input("payment_credentials[signature]",$payment_signature,array("class"=>"w600"))?>
			</td>		
		</tr>
		<tr>
			<td>
				Payment Preference:
			</td>

			<td>
				<?=HTML_UTIL::get_dropdown("preference",array("paypal"=>"Paypal"),$payment_preference)?>
			</td>		
		</tr>

		<tr>
			<td>
				Type:
			</td>

			<td>
				<?=HTML_UTIL::get_dropdown("type",array(""=>" - Select a Type - ") + $payment_type_list,$payment_type)?>
			</td>		
		</tr>

		<tbody id="process_odc" class="process"> 
			<tr>
				<td colspan="2">
					<h4>On Demand Creation</h4>
				</td>
			</tr>
			<tr>
				<td>
					Name:
				</td>
				<td>
					<?=HTML_UTIL::get_input("odc[cc_name]",null,array("class"=>"w600"))?>
				</td>
			</tr>			
			<tr>
				<td>
					Card Number:
				</td>
				<td>
					<?=HTML_UTIL::get_input("odc[cc_number]",null,array("class"=>"w600"))?>
				</td>
			</tr>
			<tr>
				<td>
					Expire Date:
				</td>
				<td>
					<?=HTML_UTIL::get_input("odc[cc_expiry_month]",null,array("class"=>"w30 tac"))?> / <?=HTML_UTIL::get_input("odc[cc_expiry_year]",null,array("class"=>"w30 tac"))?> (mm/yyyy)
				</td>
			</tr>
		</tbody>

		<tbody id="process_odp" class="process"> 
			<tr>
				<td colspan="2">
					<h4>On Demand Payment</h4>
				</td>
			</tr>
			<tr>
				<td>
					Reference Id:
				</td>
				<td>
					<?=HTML_UTIL::get_input("odp[reference_id]",null,array("class"=>"w600"))?>
				</td>
			</tr>
			<tr>
				<td>
					Amount:
				</td>
				<td>
					<?=HTML_UTIL::get_input("odp[amount]",null,array("class"=>"w600"))?>
				</td>
			</tr>
		</tbody>

		<tbody id="process_pre" class="process"> 
			<tr>
				<td colspan="2">
					<h4>Get Preapproval Key</h4>
				</td>
			</tr>
			<tr>
				<td>
					Sender's E-mail:
				</td>
				<td>
					<?=HTML_UTIL::get_input("pre[sender_email]",null,array("class"=>"w600"))?>
				</td>
			</tr>

			<tr>
				<td>
					Valid From - To:
				</td>
				<td>
					<?=HTML_UTIL::get_input("pre[date_from]",null,array("class"=>"w200"))?> - <?=HTML_UTIL::get_input("pre[date_to]",null,array("class"=>"w200"))?>
				</td>
			</tr>

			<tr>
				<td>
					Max Total Amount:
				</td>
				<td>
					<?=HTML_UTIL::get_input("pre[amount]",null,array("class"=>"w200"))?>
				</td>
			</tr>
		</tbody>

		<tbody id="process_snd" class="process"> 
			<tr>
				<td colspan="2">
					<h4>Send</h4>
				</td>
			</tr>
			<tr>
				<td>
					Sender:
				</td>
				<td>
					<?=HTML_UTIL::get_input("snd[sender]",null,array("class"=>"w600"))?>
				</td>
			</tr>
			<tr>
				<td>
					Receiver:
				</td>
				<td>
					<?=HTML_UTIL::get_input("snd[receiver]",null,array("class"=>"w600"))?>
				</td>
			</tr>

			<tr>
				<td>
					Amount:
				</td>
				<td>
					<?=HTML_UTIL::get_input("snd[amount]",null,array("class"=>"w600"))?>
				</td>
			</tr>
		</tbody>

		<tr>
			<td>

			</td>

			<td>
				<?=HTML_UTIL::get_button("process","Process",array("type"=>"button"))?>
			</td>		
		</tr>	
	</table>
	<div id="spinner"></div>
	<script>

	$(function() {

		$("input[name='process']").click(function() {
			$("#messages").html('');
			$("#spinner").spin();
			ty = $("#type").val();

			$.post("/utility/dopayment",$("form").serializeArray(),function(response){
				if(response.has_success) {
					
					msgs = [];
					msgs.push("Successfully performed process");

					if(response.data.data) {
						$.each(response.data.data,function(i,v) {
							msgs.push(i + ": " + v);
						});
					}
					
					FF.msg.success(msgs);
					
				} else
					FF.msg.error(response.errors);
					
				$("#spinner").spin(false);	
			});
		});

		$("#type").change(function() {
			$(".process").hide();
			$("#process_" + $("#type").val()).show();	
		});		

	});

	</script>

<style>

	.process {
		display: none;
	}
</style>