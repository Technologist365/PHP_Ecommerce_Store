<?php 
session_start();

//Smarty include and create
include('../includes/Smarty.class.php');
$smarty = new Smarty;
$smarty->caching = 0; 
$smarty->template_dir = "..//templates/default/"; 
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
		$template_dir = '/templates/'.$template.'/';
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

			$smarty->assign('page_name', 'Login to an Account');
		
			//Output the top of the page
			$smarty->display('header.tpl');
			$smarty->display('content_top.tpl');

			//Get privledge information about account from the database
			$priv_query = mysql_query("SELECT privs FROM users WHERE userid='$user'");
			$priv_row = mysql_fetch_array($priv_query);
			$privledges = $priv_row['privs'];
		
			if($user != NULL && $privledges == '1')
			{
				//This is an admin account, so we get the text and enum config information to display out in modifiable forms
				//Create the array for holding text variables
				$var_text = array();

				//Query the database and grab all the text variables in the table, stick them into an array
				$config_text_query = "SELECT * FROM config_text";
				$config_text_rows = mysql_query($config_text_query);

				//mySQL Error echo
				if(!$config_text_rows)
				{
					echo "$config_text_rows: ".mysql_error();
				}
				elseif (mysql_num_rows($config_text_rows) == FALSE)
				{
					echo "<h1>No text variables!?</h1> This is a bit odd, anyone have an idea why this would happen, it was fine earlier?";
				}
				else
				{
					//Stick all the text variables into an array
					while($config_text_vars = mysql_fetch_array($config_text_rows))
					{
						$var_text[$config_text_vars['var_name']] = $config_text_vars['value'];
					}
				}

				//Assign some of the variables to smarty variables
				//URL where /store/ is on your server, for example http://www.somewebsite.com/store/
				$smarty->assign('store_url', $var['store_url']);

				//Store title, appears in the <title> tags
				$smarty->assign('store_title', $store_title);


				//Create the array for holding enum variables
				$var_enum = array();

				//Query the database and grab all the text variables in the table, stick them into an array
				$config_enum_query = "SELECT * FROM config_enum";
				$config_enum_rows = mysql_query($config_enum_query);

				//mySQL Error echo
				if(!$config_enum_rows)
				{
					echo "$config_enum_rows: ".mysql_error();
				}
				elseif (mysql_num_rows($config_enum_rows) == FALSE)
				{
					echo "<h1>No enum variables!?</h1> This is a bit odd, anyone have an idea why this would happen, it was fine earlier?";
				}
				else
				{
					//Stick all the enum variables into an array
					while($config_enum_vars = mysql_fetch_array($config_enum_rows))
					{
						$var_enum[$config_enum_vars['var_name']] = $config_enum_vars['value'];
					}
				}

				//Assign some of the variables to smarty variables

			}
			else
			{
				//Not an admin account
				echo "<h1>You must be logged in to an admin account to use the admin area.</h1>";	
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