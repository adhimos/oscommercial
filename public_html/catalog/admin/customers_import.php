<html>
<body>

<form action="customers_import_upload.php" method="post"
      enctype="multipart/form-data">
<?php     
   $session_token= $_SESSION[¨csrftoken¨];
	if (!isset($session_token)) {
	$session_token = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());
	$_SESSION[¨csrftoken¨] = $session_token;	
		}
	if (isset($session_token)) {
      $form .= '<input type="hidden" name="formid" value="' . tep_output_string($session_token) . '" />';
    }
?>
    <label for="file">Filename:</label>
    <input type="file" name="file" id="file"><br>
    <input type="submit" name="submit" value="Submit">
</form>

</body>
</html>