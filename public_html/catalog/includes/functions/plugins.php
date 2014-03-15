<?php
function tep_upload_plugin(){

$customer_id = (int) tep_get_from_session('customer_id');

$allowedExts = array("zip");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);


if (($_FILES["file"]["size"] < 200000)
&& in_array($extension, $allowedExts))
  {
  if ($_FILES["file"]["error"] > 0)
    {
    echo "Return Code: " . $_FILES["file"]["error"] . "<br>";    
    $_SESSION['plugin_error'] = "An error occured during plugin installation.";
    }
  else
    {
    
	if (!file_exists("plugin/temp/".$customer_id)) {
    		mkdir("plugin/temp/".$customer_id, 0777, true);
	}

      move_uploaded_file($_FILES["file"]["tmp_name"],
      "plugin/temp/".$customer_id."/".$_FILES["file"]["name"]);
      chmod("plugin/temp/".$customer_id."/".$_FILES["file"]["name"], 0755); 
	tep_unzip_file("plugin/temp/".$customer_id."/".$_FILES["file"]["name"], "plugin/temp/".$customer_id);
	
	$plugin_temp_folder = tep_get_temp_plugin_path("plugin/temp/".$customer_id);
	
	if(isset($plugin_temp_folder)){
		$plugin_name = tep_read_plugin_config("plugin/temp/".$customer_id."/".$plugin_temp_folder);
		if(isset($plugin_name)){
			if (!file_exists("plugin/user/".$customer_id."/".$plugin_name)) {
				$checksum_valid = is_checksum_valid("plugin/temp/".$customer_id."/".$_FILES["file"]["name"], $plugin_name);
				if($checksum_valid == true){
					unlink("plugin/temp/".$customer_id."/".$_FILES["file"]["name"]);
					rename("plugin/temp/".$customer_id."/".$plugin_folder."/", "plugin/temp/".$customer_id."/".$plugin_name."/");
					tep_recurse_copy("plugin/temp/".$customer_id, "plugin/user/".$customer_id);
					$_SESSION['plugins'][$plugin_name]['plugin_path'] = "plugin/user/".$customer_id."/".$plugin_name;	
					chmod("plugin/user/".$customer_id."/".$plugin_name, 0755); 	
				} else {
					$_SESSION['plugin_error'] = "The file you have uploaded contains invalid content.";	
					$_SESSION['plugins'][$plugin_name] = null;
				}
			} else {
				$_SESSION['plugin_error'] = "Plugin already exists on the server.";	
				$_SESSION['plugins'][$plugin_name] = null;
							
			} 
		}
	}
	deleteDir("plugin/temp/".$customer_id);
        
    }
  }
else
  {
	$_SESSION['plugin_error'] = "An error occured during plugin installation.";
  }

}


function tep_recurse_copy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                tep_recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
}

function tep_get_temp_plugin_path($path){
	if ($handle = opendir($path)) {
        	while (false !== ($entry = readdir($handle))) {
            		if ($entry != "." && $entry != ".." && is_dir($path.'/'.$entry)) {
				$plugin_folder = $entry;
			}
            	}        
	} else {
 		$_SESSION['plugin_error'] = "Plugin could not be opened.";
  	}
  closedir($handle);
return $plugin_folder;

}

function tep_read_plugin_config($plugin_path){
$config_path = $plugin_path."/plugin.properties";
if (file_exists($config_path)) { 
    if (preg_match_all('/^(?!#)(.+)=(.*)$/m', file_get_contents($config_path), $cfg)) {
        $cfg = array_combine($cfg[1], $cfg[2]);
	foreach($cfg as $name => $val){
		if(strcmp($name, "plugin_identifier") == 0){
			$plugin_name = $val;
			if (isset($_SESSION['plugins'][$val])){
			    $_SESSION['plugin_error'] = "Plugin already installed.";
				unlink($plugin_path);
			} else {					
					$_SESSION['plugins'][trim($val)] = $cfg;        
					$_SESSION['plugins'][trim($val)]['plugin_path'] = $plugin_path;
				  	$_SESSION['plugin_error'] = "Plugin installed successfully.";  	
					return $plugin_name;				
			}
		} 
	}

    } else {
	$_SESSION['plugin_error'] = "Plugin could not be installed. Invalid content2.";
	unlink($plugin_path);
   }
} else {
	$_SESSION['plugin_error'] = "Plugin could not be installed. Invalid content. ".$config_path;
	unlink($plugin_path);
}
}

function read_plugin_descriptions($plugins_download_path){
	$config_path = $plugins_download_path."/plugins.properties";
	if (file_exists($config_path)) { 
    		if (preg_match_all('/^(?!#)(.+)=(.*)$/m', file_get_contents($config_path), $cfg)) {
	        	$cfg = array_combine($cfg[1], $cfg[2]);
			foreach($cfg as $name => $value){				
				list($plugin_name, $property) = explode('.', $name); 
				$result[$plugin_name][$property] = $value;	
			}
			return $result;
    		}
	} 
}


function tep_initialize_plugins(){
	$customer_id = (int) tep_get_from_session('customer_id');
	$plugin_path = 'plugin/user/'.$customer_id;
	if ($handle = opendir('plugin/user/'.$customer_id)) {
	        while (false !== ($entry = readdir($handle))) {
	            if ($entry != "." && $entry != "..") {
		    	tep_read_plugin_config($plugin_path.'/'.$entry);
			$plugin_data = tep_load_plugin_database($customer_id, $entry);
			$_SESSION['plugins'][$entry]['plugin_data'] = json_decode($plugin_data, true);
		}
        	}
        closedir($handle);
   } else {
 	echo "No plugins available";
  }

tep_session_unregister('plugin_error');
}


function tep_print_plugins(){
echo "Print all plugins.";
echo "Plugin error: ".$_SESSION['plugin_error'];
$var = $_SESSION['plugins'];


foreach($var as $name => $val){
echo "Plugin Name = ".$name."</br>";

foreach($val as $name2 => $val2){
	echo "Property name = ****".$name2."**** value = xx".$val2."xx</br>";

}
echo "</br>";
}
}

function tep_activate_plugin($current_file_path){
	$current_file_name = substr($current_file_path, 1);
	$all_plugins = $_SESSION['plugins'];
	if(isset($all_plugins)){
	foreach($all_plugins as $name => $one_plugin){		
		$plugin_path = $one_plugin['plugin_path'];
		$files_list = $one_plugin[$current_file_name];
		generate_content($files_list, $plugin_path);
		
		$files_list = $one_plugin['*'];
		generate_content($files_list, $plugin_path);
		
	}
}
}

function generate_content($files_list, $plugin_path){
	if(isset($files_list)){
		$token = strtok($files_list, "|");
		while ($token != false)
		{				
			require($plugin_path."/".$token);
			$token = strtok("|");
		}
	}
}


function tep_plugins_list(){
$plugins_path = 'plugin/downloads';
$plugins_properties = read_plugin_descriptions($plugins_path);
if ($handle = opendir($plugins_path)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
		$temp = explode( '.', $entry );
		$extension = array_pop( $temp );
		$fileName = implode( '.', $temp );
		if($extension == "zip"){				
                	echo "<tr>";		
			echo "<td style = 'width:150px;'>".$fileName."</td>";
			echo "<td>".$plugins_properties[strtolower($fileName)]["description"]."</td>";
			echo "<td style = 'width:100px; text-align: center;'>".tep_draw_button("download", null, 'plugin/downloads/'.$entry, null, null, 'true')."</td>";            
			echo "</tr>";
		}
            }
        }
        closedir($handle);
   } else {
 	echo "No plugins available";
  }
}


function tep_installed_plugins_list(){
$plugins_found = false;
$customer_id = (int) tep_get_from_session('customer_id');

if ($handle = opendir('plugin/user/'.$customer_id)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                echo "<tr>";		
		echo "<td style = 'width:150px;'>".$entry."</td>";
		echo "<td>Plugin Installled.</td>";
		echo "<td style = 'width:100px; text-align: center;'>".tep_draw_button("Uninstall", null, 'uninstall_plugin.php?pluginName='.$entry.'&customer_id='.$customer_id)."</td>";            
		echo "</tr>";
		$plugins_found = true;
            }
        }
        closedir($handle);
   } 

	if(!$plugins_found){
 		echo "<tr colspan=3>";		
		echo "<td>No plugin has installed.</td>";
		echo "</tr>";
	}

}

function tep_remove_plugin(){
	global $HTTP_GET_VARS;
	$plugin_name = $HTTP_GET_VARS['pluginName'];
	$customer_id = $HTTP_GET_VARS['customer_id'];
	if(isset($plugin_name) && isset($customer_id)){
		deleteDir('plugin/user/'.$customer_id.'/'.$plugin_name);
		unset($_SESSION['plugins'][$plugin_name]);	
		tep_uninstall_plugin_database($customer_id, $plugin_name);
	}
	
}

function deleteDir($dirPath) {    
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

function tep_unzip_file($zip_file_path, $output_folder){
$zip = new ZipArchive;
$res = $zip->open($zip_file_path);
if ($res === TRUE) {
  $zip->extractTo($output_folder);
  $zip->close();
} else {
  	$_SESSION['plugin_error'] = "Plugin installation failed. Could not extract the archive.";
}
}

function is_checksum_valid($file, $plugin_name){
	$check_sum_file = md5_file($file);		
	$check_sum_db = tep_get_checksum($plugin_name);	
	return ($check_sum_file == $check_sum_db);
}
?>

