<?php	

if(isset($_POST['plugin_data'])) {
    	$data =  json_decode($_POST['plugin_data'], true);		
	$pluginName = $data['plugin'];	
	$key = $data['key'];
	$newData = $data['data'];

	require('includes/application_top.php');

	$all = $_SESSION['plugins']['Converter']['plugin_data'];
	foreach($all as $key => $val){
		echo "Key = ".$key." value = ".$val;
	}
	echo"================================";
	if(isset($pluginName)){
		
		$_SESSION['plugins'][$pluginName]['plugin_data'][$key] = $newData;		
	} 
	
	$all = $_SESSION['plugins']['Converter']['plugin_data'];
	foreach($all as $key => $val){
		echo "Key = ".$key." value = ".$val;
	}
	
  } 
?>
