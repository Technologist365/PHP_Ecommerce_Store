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

			$smarty->assign('page_name', 'Rental History');
			
			//Output the top of the page
			$smarty->display('header.tpl');
			$smarty->display('content_top.tpl');
		
			//If user varialbe is null, then, not logged in
			if($userid == NULL)
			{
				echo "<h1>You must be logged in to view your rental history.</h1>";	
			}
			else
			{
				$username = $_SESSION['username'];
				echo "
				Welcome to your rental history $username.
				";
				
				//The history of rentals for the user
				$history_query = mysql_query("SELECT * FROM rental_history WHERE renter_userid='$userid'");
				
				//mySQL Error echo
				if(!$history_query)
				{
					$error = '$history_query: mysql_error()';
				}
				elseif (mysql_num_rows($history_query) == FALSE)
				{
					echo "<h1>You have no items in your rental history.</h1>";
				}
				else
				{
					//Output the beginning of the table
					$smarty->display('rent_history_begin.tpl');
					
					//Here we loop through to grab the products and rent them if possible
					while($history_rows = mysql_fetch_array($history_query))
					{
						$prod_id = $history_rows['prod_id'];
						$price_id = $history_rows['price_id'];
						$checkout_date = $history_rows['checkout_date'];
						$checkin_date = $history_rows['checkin_date'];
						
						//Get the product info for the current product
						$prod_sql = mysql_query("SELECT prod_name, short_descrip, release_year FROM products WHERE prod_id='$prod_id'");
						$prod_info = mysql_fetch_array($prod_sql);
					
						//Query price table to cost and name
						$price = mysql_query("SELECT name FROM pricing WHERE price_id='$price_id'");
						$price_info = mysql_fetch_array($price);
						
						//Assign smarty vars
						$smarty->assign('prod_name',$prod_info['prod_name']);
						$smarty->assign('short_descrip',$prod_info['short_descrip']);
						$smarty->assign('release_year', $prod_info['release_year']);
						$smarty->assign('license',$price_info['name']); //Name of the price amount
						$smarty->assign('checkout',$checkout_date);
						$smarty->assign('checkin',$checkin_date);
						
						//Rent histroy row
						$smarty->display('rent_history_data.tpl');	
					}
					
					//Bottom of rental history
					$smarty->display('rent_history_end.tpl');	
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