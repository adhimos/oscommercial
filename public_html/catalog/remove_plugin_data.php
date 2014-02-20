<?php


if(isset($_POST['plugin_data'])) {
    	$data =  json_decode($_POST['plugin_data'], true);		
	$pluginName = $data['plugin'];
	$dataToRemove = $data['data'];
	if(isset($pluginName)){
		require('includes/application_top.php');

		$pluginData = $_SESSION['plugins'][$pluginName]['plugin_data'];
		if(isset($pluginData)){
			if (($key = array_search($dataToRemove, $pluginData)) !== false) {
    				unset($pluginData[$key]);
			}
			
		} 
		$_SESSION['plugins'][$pluginName]['plugin_data'] = $pluginData;
	} 
			
  } 




?>
