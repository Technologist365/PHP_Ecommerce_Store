<?php 
session_start();

//Smarty include and create
//Initially set to the default template, but if another is selected, then we switch to it.
include('includes/Smarty.class.php');
$smarty = new Smarty;
$smarty->caching = 0; 
$smarty->template_dir = "/templates/default/"; 
$smarty->assign('store_url',''); //Using the relative position for now

//Initialize $error to NULL
$error = NULL;

if(file_exists('includes/db_config.php') == TRUE)
{
	include 'includes/db_config.php';
	include 'includes/db_connect.php';
	
	//Check if the set template is not default, if it isn't, set the template dir to whatever it is set to
	$template_sql = mysql_query("SELECT value FROM config_text WHERE var_name='template'");
	$template_row = mysql_fetch_array ($template_sql);
	$template = $template_row['value'];
	
	if($template != 'default')
	{
		$template_dir = '/templates/'.$template.'/';
		$smarty->template_dir = $template_dir; 
	}
	
	//Try to grab the installed variable from the database
	$install_sql = mysql_query("SELECT value FROM config_enum WHERE var_name='installed'");
	$install_row = mysql_fetch_array ($install_sql);
	$installed = $install_row['value'];
	
	if($installed == '1')
	{
		if(file_exists('install/') == FALSE)
		{	
			//Configuration and database includes
			include 'includes/config.php';
			include 'includes/header.php';

		$smarty->assign('page_name', 'Shipping Details');
		$smarty->assign('long_descrip','Please enter your shipping details below.');
		
		//Output the top of the page
		$smarty->display('header.tpl');
		$smarty->display('content_top.tpl');
		//Beginning of product category thing
		$smarty->display('category_top.tpl');
		
			//Get shipping information from database if it exists
			$shipping_sql = mysql_query("SELECT name, phone, email, address1, address2, city, state, zip, special FROM user_addresses WHERE userid=$userid");
			$shipping_row = mysql_fetch_array ($shipping_sql);
			$name = $shipping_row['name'];
			$phone = $shipping_row['phone'];
			$email = $shipping_row['email'];
			$address1 = $shipping_row['address1'];
			$address2 = $shipping_row['address2'];
			$city = $shipping_row['city'];
			$state = $shipping_row['state'];
			$zip = $shipping_row['zip'];
			$special = $shipping_row['special'];
			
			//Assign smarty variables for output to the form
			$smarty->assign('name', $name);
			$smarty->assign('phone', $phone);
			$smarty->assign('email', $email);
			$smarty->assign('address1', $address1);
			$smarty->assign('address2', $address2);
			$smarty->assign('city', $city);
			$smarty->assign('state', $state);
			$smarty->assign('zip', $zip);
			$smarty->assign('special', $special);
		
		//Check if the ship error variable was set or not
		//There was an error with the shipping information that was sent to the add_shipping page.
		//The user has been redirected back to here and so lets grab whatever was submitted, populate, and let them finish.
		if(!empty($_SESSION['ship_error']))
		{
			//If/Else checking for inputted data
			//Check if required are set, if not, let the user know
			if(!$name || !$email || !$country || !$address1 || !$city || !$state || !$zip)
			{
				echo "<h1>Please fill out ALL fields marked with a *.</h1><br>";
			}
		}
		//No error, so use this to let the user know what all that they MUST fill out on the form
		else
		{
			//Use this for special instructions instead
			$smarty->assign('special', 'If you have any special instructions for me regarding this shipment let me know here.');
		}
		
		//Output the beginning of the table
		$smarty->display('ship_begin.tpl');
		
		//Output the form for getting shipping information
		$smarty->display('ship_form.tpl');
		
		//Output the end of the cart
		$smarty->display('ship_end.tpl');		
		
		//End of Product category thing
		$smarty->display('category_bott.tpl');
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