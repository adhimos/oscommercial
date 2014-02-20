<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');


  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_DASHBORD);

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<h1><?php echo HEADING_TITLE; ?></h1>

<?php
  if ($messageStack->size('account') > 0) {
    echo $messageStack->output('account');
  }
?>

<div class="contentContainer">
  <h2><?php echo AVAILABLE_PLUGINS; ?></h2>
			
  <div class="ui-widget infoBoxContainer">
	<div class="ui-widget-header ui-corner-top infoBoxHeading">	  
		<table class="productListingHeader" width="100%">
		<tbody>
		<tr>
			<td>Plugin</td>
			<td>Description</td>
			<td>&nbps</td>
		</tr>
		</tbody>
		</table>			
	</div>
  </div>
<div class="ui-widget-content ui-corner-bottom productListTable">
	<table class="productListingData" width="100%">
		<tbody>
		<?php tep_plugins_list(); ?>
		</tbody>
	</table>

</div>

  <h2><?php echo INSTALLED_PLUGIN; ?></h2>

  <div class="ui-widget infoBoxContainer">
	<div class="ui-widget-header ui-corner-top infoBoxHeading">	  
		<table class="productListingHeader" width="100%">
		<tbody>
		<tr>
			<td>Plugin</td>
			<td>Description</td>
			<td>&nbps</td>
		</tr>
		</tbody>
		</table>			
	</div>
  </div>
<div class="ui-widget-content ui-corner-bottom productListTable">
	<table class="productListingData" width="100%">
		<tbody>
		<?php tep_installed_plugins_list(); ?>
		</tbody>
	</table>

</div>

  <h2><?php echo INSTALL_PLUGIN; ?></h2>

  <div class="contentText">
    <form action="install_plugin.php" method="post" enctype="multipart/form-data">
  <input type="file" name="file"/>
  <input type="submit">
  </form>
</br>
<?php
	if(isset($_SESSION['plugin_error'])){
		echo $_SESSION['plugin_error'];
		tep_session_unregister('plugin_error');
	}

?>
  </div>
</div>


<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>









