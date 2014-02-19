<?php
/**
 * Created by PhpStorm.
 * User: Diluk
 * Date: 2/19/14
 * Time: 2:41 AM
 */

require('includes/application_top.php');
$ctype = $_GET['ctype'];
//exportCustomers($ctype);
import_customers('xml','');
?>