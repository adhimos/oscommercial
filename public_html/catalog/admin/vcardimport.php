<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');


 



  

  require(DIR_WS_INCLUDES . 'template_top.php');
?>
	<form action="vcardimport.php" method="post"
	 enctype="multipart/form-data">
	 <p>
	 	Vcard import:<br />
	 	<input type="file" name="card" /><br/>
	 	<input type="submit" value="Import" /><br/>
	 </p>
	 
	 </form>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>