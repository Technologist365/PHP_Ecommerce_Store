<?php 
session_start();

//ReCaptcha Include
require_once('recaptchalib.php');

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
	
	if($installed == "1")
	{
		if(file_exists('../install/') == FALSE)
		{	
			//Configuration and database includes
			include '../includes/config.php';
			include '../includes/header.php';
			
			//Code that will make and display the ReCaptcha
			$publickey = "6LfBeAUAAAAAAFwuZ0USQPE9GdWEZ7CZXngfm7r0";
			$privatekey = "6LfBeAUAAAAAAEfdt8e_v7sRhXteJ5FvH4c7JQKR";
			$recaptcha = recaptcha_get_html($publickey);
			
			//Grab the administrator email from the database
			$admin_sql = mysql_query("SELECT value FROM config_text WHERE var_name='admin_email'");
			$admin_row = mysql_fetch_array ($admin_sql);
			$admin_email = $admin_row['value'];
	
			//So here we get the variables submitted through the form to this page
			$name = mysql_real_escape_string($_POST['name']);
			$email = mysql_real_escape_string($_POST['email']);
			$topic = mysql_real_escape_string($_POST['topic']);
			$message = mysql_real_escape_string($_POST['message']);
			$con_error = '0';
			
			//Assign Smarty Variables
			$smarty->assign('name', $name);
			$smarty->assign('email', $email);
			$smarty->assign('message', $message);
			$smarty->assign('recaptcha', $recaptcha);
			
			//If they are all blank we just say to compose a message
			if(!$name AND !$email AND !$message)
				{
				$smarty->assign('unfilled', '1');
				}
				
			//Name, Email, Message must be entered or else error
			else
				{
				//$con_error short for Contact error
				if (!$name)
					{
					$con_error = 'You must enter your Name.';
					}
				
				if (!$email)
					{
					if($con_error == '0')
						{
						$con_error = 'You must enter your email address.';
						}
					else
						{
						$con_error = $con_error.'<br>You must enter your email address.';
						}
					}
					
				if (!$message)
					{
					if($con_error == '0')
						{
						$con_error = 'You must enter a message.';
						}
					else
						{
						$con_error = $con_error.'<br>You must enter a message.';
						}
					}

				//Has the recaptcha been filled out correctly?
				if ($_POST["recaptcha_response_field"])
				{
			        $resp = recaptcha_check_answer ($privatekey,
			        $_SERVER["REMOTE_ADDR"],
			        $_POST["recaptcha_challenge_field"],
			        $_POST["recaptcha_response_field"]);
			
			        if ($resp->is_valid)
			        {
			        	//don't need anything special here
			        }
			        else
			        {
						//Set the error code so we can display it
						$con_error = $con_error."$resp->error";
			        }
				}
				
				$smarty->assign('con_error', $con_error);
									
				if($con_error == '0')
					{
					$subject = "$topic from $name";
$message = "
Name: $name
Email Address: $email
Topic: $topic
Message: $message";
					
					mail($admin_email, $subject, $message, "From: $store_title Customer Service<$admin_email> $cubecrusher_version");
						
					$smarty->assign('success', '1');
					}
				else
					{
					$smarty->assign('success', '0');
					}
				}
		
			$smarty->assign('page_name', 'Contact');
			
			//Output the top of the page
			$smarty->display('header.tpl');
			$smarty->display('content_top.tpl');
		
			//Output the contact form template here
			$smarty->display('contact_form.tpl');
			
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
			'db_config.php file from the /includes directory then restart the install here. <a href="../install/install.php">Restart Install</a> <br>' .
			'If you have went through the database configuration and everything was ok, please continue the install from here, and ' .
			'disregard any database related errors. <a href="../install/install2.php">Continue Install</a> <br>');			
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
		'the location of the install.php file on your server. <a href="../install/install.php">Click to Install</a> <br><br>' .
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