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
		
			//Create success variable, set to NULL, if all is ok it gets a value, we can then not show the form based on this.
			$success = NULL;
			
			//Get submitted variables, sanitize
			$username = mysql_real_escape_string($_POST['username']);
			$password = mysql_real_escape_string($_POST['password']);
			
			if(!username && !password)
				{
				$error = 'Please log in.';
				}
			elseif(!$username || !$password)
				{
				$error = 'Please enter all of the information!';
				}
			else
				{
				//Grab the salt of the user from the database
				$salt_sql = mysql_query("SELECT salt FROM users WHERE username='$username'");
				$salt_fetch = mysql_fetch_array ($salt_sql);
				$salt = $salt_fetch['salt'];
			
				//Combine the salt and password into one string, then encrypt it
				$saltpass = $salt.$password;
				$password = sha1($saltpass);
				
				//See if you can get a user from the database
				$usercheck = mysql_query("SELECT userid FROM users WHERE username='$username' AND password='$password' AND activated='1'");
				$login_check = mysql_num_rows($usercheck);
				$id_fetch = mysql_fetch_array ($usercheck);
				$userid = $id_fetch['userid'];
				
				if($login_check == '1')
					{
					$_SESSION['username'] = $username;
					$_SESSION['userid'] = $userid;
					$error = "Welcome $username you have been successfully logged into our store!";
					
					$success = 1;
					} 
			
				else
					{
					$error = 'You could not be logged in. The username or password provided may be incorrect, or the account has not yet been activated.';
					}
				}
			
			if($error != '0')
				{
				echo "<br><h1>$error</h1><br>";
				}
			
			if($success == NULL)
			{
				echo"
				<form name=\"login_form\" id=\"login_form\" method=\"post\" action=\"login.php\">
				  <table> 
					<tr> 
					  <td>Username: </td>
					  <td><input name=\"username\" type=\"text\" value=\"\"></td>
					</tr>
					
					<tr>
					  <td>Password: </td>
					  <td><input name=\"password\" type=\"password\" value=\"\"></td>
					</tr>

					<tr>
					  <td></td>
					  <td><input type=\"submit\" name=\"Submit\" value=\"Login!\"></td>
					</tr>
				  </table>
				</form>
				
				<br>
				
				<a href=\"".$store_url."register/lost_pw.php\">Forgot Password?</a>
				&nbsp;<b>|</b>&nbsp;
				<a href=\"".$store_url."register/lost_un.php\">Forgot Username?</a>
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