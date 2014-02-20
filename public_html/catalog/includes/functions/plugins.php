<?php
function tep_upload_plugin(){

$customer_id = (int) tep_get_from_session('customer_id');

$allowedExts = array("zip");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);

$_SESSION['test_message']="testing 12345";

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
    if (file_exists("plugin/user/".$customer_id."/".$_FILES["file"]["name"]))
      {
      echo $_FILES["file"]["name"] . " already exists. ";
 	$_SESSION['plugin_error'] = "That plugin that your are trying to install already exists.";
      }
    else
      {
	if (!file_exists("plugin/user/".$customer_id)) {
    		mkdir("plugin/user/".$customer_id, 0777, true);
	}

      move_uploaded_file($_FILES["file"]["tmp_name"],
      "plugin/user/".$customer_id."/".$_FILES["file"]["name"]);
      chmod("plugin/user/".$customer_id."/".$_FILES["file"]["name"], 0755); 
	tep_unzip_file("plugin/user/".$customer_id."/".$_FILES["file"]["name"], "plugin/user/".$customer_id);

	$path_info = pathinfo("plugin/user/".$customer_id."/".$_FILES["file"]["name"]);
	tep_read_plugin_config("plugin/user/".$customer_id."/".$path_info['filename']);
      }
    }
  }
else
  {
  echo "Invalid file";
	$_SESSION['plugin_error'] = "An error occured during plugin installation.";
  }

}

function tep_read_plugin_config($plugin_path){
$config_path = $plugin_path."/plugin.properties";
if (file_exists($config_path)) { 
    if (preg_match_all('/^(?!#)(.+)=(.*)$/m', file_get_contents($config_path), $cfg)) {
        $cfg = array_combine($cfg[1], $cfg[2]);
	foreach($cfg as $name => $val){
		if(strcmp($name, "plugin_identifier") == 0){
			if (isset($_SESSION['plugins'][$val])){
			    $_SESSION['plugin_error'] = "Plugin already installed.";
				unlink($plugin_path);
			} else {
					$cfg['plugin_path'] = $plugin_path;
					$_SESSION['plugins'][trim($val)] = $cfg;        
				
				  	$_SESSION['plugin_error'] = "Plugin installed successfully.";  					
			}
		} 
	}

    } else {
	$_SESSION['plugin_error'] = "Plugin could not be installed. Invalid content2.";
	unlink($plugin_path);
   }
} else {
$_SESSION['plugin_error'] = "Plugin could not be installed. Invalid content1. ".$config_path;
	unlink($plugin_path);
}
}

function tep_initialize_plugins(){
$customer_id = (int) tep_get_from_session('customer_id');
$plugin_path = 'plugin/user/'.$customer_id;
if ($handle = opendir('plugin/user/'.$customer_id)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
		      tep_read_plugin_config($plugin_path.'/'.$entry);
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
	foreach($all_plugins as $name => $one_plugin){		
		$plugin_path = $one_plugin['plugin_path'];
		$files_list = $one_plugin[$current_file_name];
		generate_content($files_list, $plugin_path);
		
		$files_list = $one_plugin['*'];
		generate_content($files_list, $plugin_path);
		
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
if ($handle = opendir('plugin/downloads')) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                echo "<tr>";		
		echo "<td>".$entry."</td>";
		echo "<td>Test Description</td>";
		echo "<td>".tep_draw_button("download", null, 'plugin/downloads/'.$entry, null, null, 'true')."</td>";            
		echo "</tr>";
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
		echo "<td>".$entry."</td>";
		echo "<td>Test Description</td>";
		echo "<td>".tep_draw_button("Uninstall", null, 'uninstall_plugin.php?pluginName='.$entry)."</td>";            
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
	$customer_id = (int) tep_get_from_session('customer_id');
	deleteDir('plugin/user/'.$customer_id.'/'.$HTTP_GET_VARS['pluginName']);
	unset($_SESSION['plugins'][$HTTP_GET_VARS['pluginName']]);	
	
}

function deleteDir($dirPath) {    
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            self::deleteDir($file);
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
unlink($zip_file_path);
}


?>

