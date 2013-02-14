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

	$smarty->assign('page_name', 'Change Your Password');
	
	//Output the top of the page
	$smarty->display('header.tpl');
	$smarty->display('content_top.tpl');
	
		if($user == NULL)
			{
			echo "<h1>You must be logged in to change your password.</h1>";	
			}
			
		else
			{
			$sql = mysql_query("SELECT password, salt FROM users WHERE username='$user'");
			$row = mysql_fetch_array ($sql);
			$check_old_pass = $row['password'];
			$salt = $row['salt'];
			
			$pass1 = mysql_real_escape_string($_POST['pass1']);
			$pass2 = mysql_real_escape_string($_POST['pass2']);
			
			$old_pass = mysql_real_escape_string($_POST['old_pass']);
			$old_pass = sha1($salt.$old_pass);
			$check_old_pass = sha1($salt.$check_old_pass);
			
			if (!$pass1 || !$pass2 || !$old_pass)
				{
				$error = 'You need to enter all of the information if you want us to change your password. Please try again.';
				}
			else 
				{
				if ($pass1 != $pass2)
					{
					$error = 'The passwords you have entered do not appear to be the same. Please try again.';
					}
				  
				elseif (strlen($pass1) < 6)
					{
					$error = 'Your password must be greater than 6 characters in length. Please try again.';
					}
			
				elseif ($old_pass != $check_old_pass)
					{
					$error = 'The old password you entered, does not match the records in our database. Please try again.';
					}
				
				elseif($email != $check_email)
					{
					$error = 'The e-mail address that you entered does not match the records in our database. Please try again.';
					}
				
				else
					{
					//Md5, Str Rev, Base 64 Encode, Str Rev, Md5, Str Rev
					$pass1 = sha1($salt.$pass1);
			
					mysql_query("UPDATE users SET password='$pass1' WHERE username='$user'");
					$error = 'Your password has been successfully updated.<br>Please log in with the new password.';
					}
				}
			
			if($error != '0')
				{
				echo "<br><h1>$error</h1>";
				}	
			echo"
			<form name=\"changepass\" method=\"post\" action=\"change_pass.php\">
			  <table>
				<tr> 
				  <td colspan=\"2\"><center><b>Change Your Password</b></center></td>
				  <td>&nbsp;</td>
				</tr>
				
				<tr> 
				  <td>Old Password</td>
				  <td><input name=\"old_pass\" type=\"password\" value=\"$old_pass\"><br></td>
				</tr>
				
				<tr> 
				  <td>New Password</td>
				  <td><input name=\"pass1\" type=\"password\" value=\"$pass1\"><br></td>
				</tr>
				
				<tr> 
				  <td>Confirm New Password</td>
				  <td><input name=\"pass2\" type=\"password\" value=\"$pass2\"><br></td>
				</tr>
				
				<tr>
				  <td><input type=\"submit\" name=\"Submit\" value=\"Change my Password\"></td>
				  <td>&nbsp;</td>
				</tr>
			  </table>
			</form>
			";
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
