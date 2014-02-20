
<div class="ui-widget infoBoxContainer" id="currencyConverter" style="width: 160px;">
	
	<div class="ui-widget-content infoBoxContents" id="message" style="width:150px; text-align: left;">
		<table>
			<tr>
				<td>Rate</td>
				<td><input type="text" style="width:50px" id="newRate"/></td>
				<td align="center"><span id="updateButton" title="Update Ratio">&nbsp;</span></td>
			</tr>
			<tr>
				<td>Total</td>
				<td id="convertedTotal" type="Number" >1111111</td>
				<td>
					<select id="currencySelector">
						<?php 
							$dataList = $_SESSION['plugins']['Converter']['plugin_data'];
							foreach($dataList as $key => $data){
						 ?>
								<option value = "<?php echo $data; ?>"><?php echo $key; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
		</table>
	</div>	
</div>


