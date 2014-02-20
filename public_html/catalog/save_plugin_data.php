<?php


if(isset($_POST['plugin_data'])) {
    	$data =  json_decode($_POST['plugin_data'], true);		
	$pluginName = $data['plugin'];	
	$newData = $data['data'];
	if(isset($pluginName)){
		require('includes/application_top.php');

		$pluginData = $_SESSION['plugins'][$pluginName]['plugin_data'];
		if(isset($pluginData)){
			array_push($pluginData, $newData);
			$pluginData = array_unique($pluginData);
		} else { 
			$pluginData = array($newData);
		}
		$_SESSION['plugins'][$pluginName]['plugin_data'] = $pluginData;		
	} 
			
  } 




?>
