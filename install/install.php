<?php
//Smarty include and create
include('../includes/Smarty.class.php');
$smarty = new Smarty;
$smarty->caching = 0; 

//Output an error page thing, because installation is not complete
$smarty->template_dir = "../templates/default/";
$smarty->assign('navi_page', 'navi_error.tpl');

//Assign the smarty variables for the page
$smarty->assign('store_title','Cube Crusher Install 1 of 3 ~ Database Configuration');
$smarty->assign('store_url','../'); //Using the relative position
$smarty->assign('page_name','~Cube Crusher Install~<br>1 of 3 ~ Database Configuration');
$smarty->assign('long_descrip','
		<form name="database" method="post" action="install2.php">
		Database Host: <input name="db_host" type="text" value=""><br>
		Database User: <input name="db_user" type="text" value=""><br>
		Database Password: <input name="db_pass" type="text" value=""><br>
		Database Name: <input name="db_name" type="text" value=""><br>
		<input type="submit" name="Submit" value="Next">
		</form>
		');

//Output the top of the page
$smarty->display('header.tpl');
$smarty->display('content_top.tpl');

//Output the bottom of the page
$smarty->display('content_bott.tpl');
$smarty->display('footer.tpl');
?>