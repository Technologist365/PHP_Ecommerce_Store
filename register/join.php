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

		$smarty->assign('page_name', 'Register an Account');
		
		//Output the top of the page
		$smarty->display('header.tpl');
		$smarty->display('content_top.tpl');
		
			//Create success variable, set to NULL, if all is ok it gets a value, we can then not show the form based on this.
			$success = NULL;
			
			//Grab the variables posted from the form, sanitize the input
			//Account login data
			$username = mysql_real_escape_string($_POST['username']);
			$email1 = mysql_real_escape_string($_POST['email_address1']);
			$email2 = mysql_real_escape_string($_POST['email_address2']);
			$pass1 = mysql_real_escape_string($_POST['pass1']);
			$pass2 = mysql_real_escape_string($_POST['pass2']);
			
			//Real data
			$first = mysql_real_escape_string($_POST['first']);
			$last = mysql_real_escape_string($_POST['last']);
			$phone = mysql_real_escape_string($_POST['phone']);
			$ssn = mysql_real_escape_string($_POST['ssn']);
			
			//Start validating things
			if (!$email1 && !$email2 && !$username && !$pass1 && !$pass2 && !$first && !$last && !$phone && !$ssn)
			{
				$error = 'Please register an account.';
			}
			elseif(!$username || !$email1 || !$email2 || !$pass1 || !$pass2 || !$first || !$last || !$phone || !$ssn)
			{
				$error = '<br>Please fix the following errors:<br>';
				
				//Account Login Data
				if(!$username)
					{
					$error = $error.'&nbsp;&nbsp;&nbsp; - Desired Username is a required field. Please try again.<br>';
					}
				if(!$email1)
					{
					$error = $error.'&nbsp;&nbsp;&nbsp; - Email Address is a required field. Please try again.<br>';
					}	
				if(!$email2)
					{
					$error = $error.'&nbsp;&nbsp;&nbsp; - You must enter your email address again. Please try again.<br>';
					}
				if(!$pass1)
					{
					$error = $error.'&nbsp;&nbsp;&nbsp; - You must enter a password. Please try again.<br>';
					}
				if(!$pass2)
					{
					$error = $error.'&nbsp;&nbsp;&nbsp; - You must enter your password again. Please try again.<br>';
					}
					
				//Real person data
				if(!$first)
					{
					$error = $error.'&nbsp;&nbsp;&nbsp; - You must enter your first name. Please try again.<br>';
					}
				if(!$last)
					{
					$error = $error.'&nbsp;&nbsp;&nbsp; - You must enter your last name. Please try again.<br>';
					}
				if(!$phone)
					{
					$error = $error.'&nbsp;&nbsp;&nbsp; - You must enter your phone number. Please try again.<br>';
					}
				if(!$ssn)
					{
					$error = $error.'&nbsp;&nbsp;&nbsp; - You must enter your social security number so that I can steal your identity. Please try again.<br>';
					}
			}
			else
				{
				//Query database with the username, see if it is in there
				$sql_username_check = mysql_query("SELECT username FROM users WHERE username='$username'");
				$username_check = mysql_num_rows($sql_username_check);
				
				//If the username is already in the database, don't allow it	
				if($username_check > 0)
					{
					$error = '<br>Please fix the following errors:<br>';
					$error = $error.'&nbsp;&nbsp;&nbsp; - That username has already been used by another member. Please enter a different Username.<br>';
					unset($username);	
					}
			
				//Check if the two emails are equal if they are check against the database, otherwise skip the database check
				if($email1 != $email2)
					{
					if($error != '0')
						{
						$error = '<br>Please fix the following errors:<br>';
						$error = $error.'&nbsp;&nbsp;&nbsp; - The two entered email addresses do not match. Please try again.<br>';	
						}
					else
						{
						$error = $error.'&nbsp;&nbsp;&nbsp; - The two entered email addresses do not match. Please try again.<br>';
						}
					}
				else
					{	
					//Query database with the email address, see if it is in there
					$sql_email_check = mysql_query("SELECT email_address FROM users WHERE email_address='$email1'");
					$email_check = mysql_num_rows($sql_email_check);
					
					//Enter whatever value you see fit here, or remove this if, it will allow the entered email to bypass the email check error state 
					if($email1 == 'bedfordd@egmods.com')
						{
						$email_check = '0';
						}
				
					//If the email or username is already in the database, don't allow it	
					if($email_check > 0)
						{
						//If this is the first error we set the error variable.
						if($error != '0')
						{
							$error = '<br>Please fix the following errors:<br>';
						}
						
						//Regardless of first error or not, this should be added to error
						$error = $error.'&nbsp;&nbsp;&nbsp; - Your email address has already been used by another member. Please enter a different Email address.<br>';
						
						//The email is bad, unset the variables for it
						unset($email1);
						unset($email2);
						}
					}
				
				//Check if the two passwords are equal
				if($pass1 != $pass2)
				{
					//Set error variable since not yet set
					if($error != '0')
					{
						$error = '<br>Please fix the following errors:<br>';
					}
					
					//Error message for user
					$error = $error.'&nbsp;&nbsp;&nbsp; - The two entered passwords do not match. Please try again.<br>';
				}
				
				//Check that the password is not less than 6 characters, you may change this to whatever you want, or remove it
				if(strlen($pass1) < 6)
				{
					//Set error variable
					if($error != '0')
					{
						$error = '<br>Please fix the following errors:<br>';
					}
					
					//Error message for user
					$error = $error.'&nbsp;&nbsp;&nbsp; - Your password may not be less than 6 characters.<br>';
				}
				
				//Everything should be ok, so now lets make the salt, insert the account into the database, then email the user
				if($error == 0)
					{
					//Lets make a salt for each individual user that registers, I've shoved it into a function incase you would like to use it in something else
					//Just copy the function out from here and use it in whatever else you feel like
					function makesalt() 
						{
						$salt = "abcdefghjkmnpqrstuvwxyz0123456789";
						srand((double)microtime()*1000000); 
						$i = 0;
						
						while ($i <= 7) 
							{
							$num = rand() % 33;
							$temp = substr($salt, $num, 1);
							$salty = $salty . $temp;
							$i++;
							}
							
						return $salty;
						}
					$salt = makesalt();
					
					//Stick the salt in front of the password, then encrypt it
					//This will prevent anyone who happens to get the md5 hash from the database from checking if the hash is something common
					//If they get the salt also, they can't remove it from the hash
					//They will only be able to bruteforce the password, which will take a lot of time
					$saltpass = $salt.$pass1;
					$password = sha1($saltpass);
					
					//Insert user data then grab unique id for unser that was inserted
					$user_insert = mysql_query("INSERT INTO users (email_address, username, password, salt, first_name, last_name, phone, ssn, privs) VALUES('$email1', '$username', '$password', '$salt', '$first', '$last', '$phone', '$ssn', '0')") or die (mysql_error());
					$userid = mysql_insert_id();
					
					//Figure out what type of activation is in use
					//0 = No activation, 1 = User Activation, 2 = Admin Activation
					$act_type_sql = mysql_query("SELECT value FROM config_enum WHERE var_name='activation_type'");
					$act_type_row = mysql_fetch_array ($act_type_sql);
					$act_type = $act_type_row['value'];

					if($act_type == '0')
						{
						$active = '1';
						$active_message = "
						You may now log in to your account with the following information and access new features in our shop.
						Username: $username
						Password: $pass2
						";
						$subject = "$store_title Account Registration";
						$message = "Your account is registered and activated, you may now login. <br> 
						An email titled \"$subject\" has been sent to $email1 containing your login details in case you forget them.";
						}
					elseif($act_type == '1')
						{
						$active = '0';
						$active_message = "
						You are two steps away from logging in and accessing your account.
						
						To activate your membership, please click here: $store_url/register/activate.php?id=$userid&code=$salt
						
						Once you activate your memebership, you will be able to login with the following information:
						Username: $username
						Password: $pass2
						";
						$subject = "$store_title Account Registration";
						$message = "Your account is registered but is not yet activated.<br> 
						An email titled \"$subject\" has been sent to $email1 containing instructions on how to activate your account.";
						}
					else
						{
						$active = '0';
						$active_message = "
						Your account is not yet active. The administrator must approve each new registration. Please wait for an email from the administrator telling you your account has been activate
						
						After your account is activated you will be able to login with the following information:
						Username: $username
						Password: $pass2
						";
						$message = 'Your account is registered but is not yet activated.<br> 
						The administrator must activate all new accounts, please wait for an email from the administrator telling you your account has been activated.';
						}
					
					if(!$user_insert)
						{
						$error = 'There has been an error creating your account. Please contact the administrator.';
						} 
					else 
						{
						//Update user account with the proper activation level
						$activation = mysql_query("UPDATE users SET activated='$active' WHERE userid='$userid'");
						$success = 1;
						
						//Send email to user
						mail($email1, $subject, $active_message, "From: Account Registration<$admin_email>");
						echo "<b>$message</b><br><br>";
						}
					}
				}
					
			if($error != '0')
				{
				echo "<br><h1>$error</h1>";
				}		
		
			if($success == NULL)
			{
			//First part is for account login data
			//Second part is for real live person data
			echo "		
			<form name=\"register\" method=\"post\" action=\"join.php\">
		 		<table> 
		    		<tr> 
		      			<td>Desired Username:</td>
		      			<td><input name=\"username\" type=\"text\" value=\"$username\"></td>
		    		</tr>
		
		    		<tr> 
		      			<td>Email Address:</td>
		      			<td><input name=\"email_address1\" type=\"text\" value=\"$email1\"></td>
		    		</tr>
					
					<tr> 
		      			<td>Confirm Email Address:</td>
		      			<td><input name=\"email_address2\" type=\"text\" value=\"$email2\"></td>
		    		</tr>
					
					<tr> 
		      			<td>Password:</td>
		      			<td><input name=\"pass1\" type=\"password\" value=\"\"></td>
		    		</tr>
					
					<tr> 
		      			<td>Confirm Password:</td>
		      			<td><input name=\"pass2\" type=\"password\" value=\"\"></td>
		    		</tr>
					
					<tr>
						<td></td>
						<td></td>
					</tr>
					
					<tr> 
		      			<td>First Name:</td>
		      			<td><input name=\"first\" type=\"text\" value=\"$first\"></td>
		    		</tr>
					
					<tr> 
		      			<td>Last Name:</td>
		      			<td><input name=\"last\" type=\"text\" value=\"$last\"></td>
		    		</tr>
					
					<tr> 
		      			<td>Phone Number:</td>
		      			<td><input name=\"phone\" type=\"text\" value=\"$phone\"></td>
		    		</tr>
					
					<tr> 
		      			<td>Social Security Number:</td>
		      			<td><input name=\"ssn\" type=\"text\" value=\"$ssn\"></td>
		    		</tr>
		
		    		<tr> 
		      			<td><input type=\"submit\" name=\"Submit\" value=\"Join Now!\"></td>
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