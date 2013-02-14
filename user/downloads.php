<?php 
session_start();

//Smarty include and create
include('../includes/Smarty.class.php');
$smarty = new Smarty;
$smarty->caching = 0; 
$smarty->template_dir = "../templates/default/"; 
$smarty->assign('store_url','../'); //Using the relative position for now

//Initialize $error to NULL
$error = NULL;

if(file_exists('../includes/db_config.php') == TRUE)
{
	include '../includes/db_config.php';
	include '../includes/db_connect.php';
	
	//Check if the set template is not default, if it isn't set the template dir to whatever it is set to
	$template_sql = mysql_query("SELECT value FROM config_text WHERE var_name='template'");
	$template_row = mysql_fetch_array ($template_sql);
	$template = $template_row['value'];
	
	if($template != 'default')
	{
		$template_dir = '../templates/'.$template.'/';
		$smarty->template_dir = $template_dir; 
	}
	
	//Try to grab the installed variable from the database
	$install_sql = mysql_query("SELECT value FROM config_enum WHERE var_name='installed'");
	$install_row = mysql_fetch_array ($install_sql);
	$installed = $install_row['value'];
	
	if($installed == '1')
	{
		if(file_exists('../install/') == FALSE)
		{	
			//Configuration and database includes
			include '../includes/config.php';
			include '../includes/header.php';

		$smarty->assign('page_name', 'Your Purchases');
		
		//Output the top of the page
		$smarty->display('header.tpl');
		$smarty->display('content_top.tpl');
		
			if($user == NULL)
				{
				echo "<h1>You must be logged in to view your purchases.</h1>";	
				}
				
			else
				{
				//The query for the products that have been purchased
				$purchase_query = mysql_query("SELECT * FROM purchases WHERE userid='$userid'");
				
				//mySQL Error echo
				if(!$purchase_query)
					{
					$error = '$purchase_query: mysql_error()';
					}
				  
				elseif (mysql_num_rows($purchase_query) == FALSE)
					{
					echo "<h1>You have not yet purchased any items.</h1><br>";
					}
				
				else
					{
					//Beginning of downloading table
					$smarty->display('downloads_begin.tpl');
	
					$purchased_count = '0';
					$purchased_cost = '0.00';
					
					//Here we loop through to grab the products to add their stuff together and display their basic info
					while($purchase_rows = mysql_fetch_array($purchase_query))
						{
						$prod_id = $purchase_rows['prod_id'];
						$prod_qty = $purchase_rows['prod_qty'];
						$prod_price = $purchase_rows['prod_price'];
						$remain_download = $purchase_rows['remain_downloads'];
						
						if($remain_download == '-1')
							{
							$remain_download = 'Unlimited';
							}
							
						//Get the product info for the current product
						$prod_sql = mysql_query("SELECT prod_name, short_descrip, free FROM products WHERE prod_id='$prod_id'");
						$prod_info = mysql_fetch_array ($prod_sql);
						$prod_name = $prod_info['prod_name'];
						$short_descrip = $prod_info['short_descrip'];
						$is_free = $prod_info['free'];
						
						$price_sql = mysql_query("SELECT cost, name FROM pricing WHERE price_id='$prod_price'");
						$price_info = mysql_fetch_array ($price_sql);
						$cost = $price_info['cost'];
						$license = $price_info['name'];
						
						$prod_cost = $cost * $prod_qty;
						
						//Smarty Variables
						$smarty->assign('username',$name_info['username']);
						
						//Output the table with the info
						if($error != 0)
							{
							echo "<h1>$error 1</h1>";
							}
						
						$smarty->display('downloads_data.tpl');
							
						$purchased_count = $purchased_count + $prod_qty;
						}//Ends the while loop that figures cost and quantity
						
					//End of downloads table
					$smarty->display('downloads_end.tpl');
					}
				}
				
		//Output bottom of page
		$smarty->display('content_bott.tpl');
		$smarty->display('footer.tpl');
		}
		else
		{
			$error = 1;
			
			//Assign the smarty variables for the page
			$smarty->assign('store_title', 'CubeCrusher !ERROR!');
			$smarty->assign('page_name', 'ERROR: !The install folder exists!');
			$smarty->assign('long_descrip', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; For your own security I can not allow you to use this store before ' .
				'you remove the /install folder. Then refresh this page and you will be able to use the store. Thank You.');
		}//Ends check for install folder
	}
	else
	{
		$error = 1;
		
		//Assign the smarty variables for the page
		$smarty->assign('store_title', 'CubeCrusher not installed');
		$smarty->assign('page_name', 'Continue Install?');
		$smarty->assign('long_descrip', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; The installastion seems to have been started but is not yet complete. ' .
			'It appears as though you have successfully went through the database configuration, if you have not please delete the ' .
			'db_config.php file from the /includes directory then restart the install here. <a href="/install/install.php">Restart Install</a> <br>' .
			'If you have went through the database configuration and everything was ok, please continue the install from here, and ' .
			'disregard any database related errors. <a href="install/install2.php">Continue Install</a> <br>');			
	}//Ends if completely installed or not check
}
else
{
	$error = 1;
	
	//Assign the smarty variables for the error page
	$smarty->assign('store_title', 'CubeCrusher not installed');
	$smarty->assign('page_name', 'Install CubeCrusher');
	$smarty->assign('long_descrip', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Welcome. You have not yet installed CubeCrusher ' .
		'please do so by following this link to the install script. Or otherwise direct your web browser to ' .
		'the location of the install.php file on your server. <a href="install/install.php">Click to Install</a> <br><br>' .
		'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Please have the following server and database information for the install script. <br>' .
		' - The address of your Mysql server. <br>' .
		' - The database name you intend to use for the store. <br>' .
		' - The associated username and password to access the database. <br>' .
		' - The url to the location where the storefront.php resides on your server.');		
}//Ends check for database configuration file

if($error == '1')
{	
	//Assign smarty variables
	$smarty->assign('navi_page', 'navi_error.tpl');
		
	//Output the page with whatever error the function set
	$smarty->display('header.tpl');
	$smarty->display('content_top.tpl');
	$smarty->display('content_bott.tpl');
	$smarty->display('footer.tpl');
}
?>
