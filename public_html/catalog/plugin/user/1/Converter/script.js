<?php
if(!isset($_SESSION['plugins']['Converter']['plugin_data']['SGD'])){
 $_SESSION['plugins']['Converter']['plugin_data']['SGD'] = 1.26;
}

if(!isset($_SESSION['plugins']['Converter']['plugin_data']['MYR'])){
$_SESSION['plugins']['Converter']['plugin_data']['MYR'] = 3.29;
}
if(!isset($_SESSION['plugins']['Converter']['plugin_data']['CNY'])){
$_SESSION['plugins']['Converter']['plugin_data']['CNY'] = 6.08;
}
if(!isset($_SESSION['plugins']['Converter']['plugin_data']['TBH'])){
$_SESSION['plugins']['Converter']['plugin_data']['TBH'] = 32.53;
}
if(!isset($_SESSION['plugins']['Converter']['plugin_data']['EUR'])){
$_SESSION['plugins']['Converter']['plugin_data']['EUR'] = 0.73;
}


$customerid = (int) tep_get_from_session('customer_id');
require('plugin/user/'.$customerid.'/Converter/Converter.php');
?>



<script type="text/javascript">

var elements = $("#bodyContent").find("h2:contains('Billing Information')").next().children(':first').find('tr td:nth-child(2)').first();

a = $('<div></div>');
converter = $('#currencyConverter');
converter2 = converter.clone();
converter.remove();
$(elements).append(converter2);


$("#newRate").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
             // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) || 
             // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

$('#currencySelector').change(function(){	
	var ratio = $(this).find("option:selected").val();
	$('#newRate').val(ratio);
		
	var elementWithTotal = elements.children(':first').find('tr:last td:nth-child(2) strong').first();
	var usdTotal = $(elementWithTotal).html().substr(1);

	var convertedValue = usdTotal * ratio;
	convertedValue = Number((convertedValue).toFixed(2));
	$('#convertedTotal').html(convertedValue);
});

$('#currencySelector').change();	

var button = $('#updateButton').button({ icons: { primary: 'ui-icon-refresh'}, text: false });
$(button).click(function(){
	var newRatio = $('#newRate').val();
	$("#currencySelector").find("option:selected").val(newRatio);
	var currency = $("#currencySelector").find("option:selected").text();
	var dataToSend =  {"plugin" : "Converter", "key" : currency, "data" : newRatio}; 
	
	$.ajax({
		type: 'POST',
    		url: 'save_plugin_data_map.php',
    		data: {'plugin_data': JSON.stringify(dataToSend)}
  	});	
	$('#currencySelector').change();
});




</script>
