<?php
//Smarty include and create
include('../includes/Smarty.class.php');
$smarty = new Smarty;
$smarty->caching = 0; 

//Output an error page thing, because installation is not complete
$smarty->template_dir = "../templates/default/";
$smarty->assign('navi_page', 'navi_error.tpl');

//Assign the smarty variables for the page
$smarty->assign('store_url','../'); //Using the relative position

//Since the config file has been created already, we can include teh db_connect file
include('../includes/db_config.php');
include('../includes/db_connect.php');

/* db_connect.php seems to be not working, so we need to connect using the below code
/// Database Connection
$connection = mysql_connect("$db_host","$db_username","$db_password")
 or die ("Couldn't connect to server");
mysql_select_db("$db_name", $connection)
 or die("Couldn't select database");
*/
 
//Initialize $error to NULL
$error = NULL;

//Do error checking and password hashing and account creating and emailing
if ($_POST['Submit'])
	{
	extract($_POST);
	
	if(!$ad_name || !$ad_email || !$ad_pass || !$serv_loc)
		{
		$error = '<br>Please fix the following errors:<br>';
		
		if(!$ad_name)
			{
			$error = $error.'&nbsp;&nbsp;&nbsp; - Administrator Username is required<br>';
			}
		if(!$ad_email)
			{
			$error = $error.'&nbsp;&nbsp;&nbsp; - Administrator Email Address is required<br>';
			}	
		if(!$ad_pass)
			{
			$error = $error.'&nbsp;&nbsp;&nbsp; - Administrator Password is required<br>';
			}
		if(!$serv_loc)
			{
			$error = $error.'&nbsp;&nbsp;&nbsp; - Store Location is required<br>';
			}
		}
	
	if($error == NULL)
		{	
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
		$saltpass = $salt.$ad_pass;
		//Sha1, more secure than MD5 which is broken, however should maybe use something even more secure, sha256 or sha512 maybe
		$password = sha1($saltpass);
			
		$user_insert = mysql_query("INSERT INTO users (email_address, username, password, salt, activated, privs) VALUES('$ad_email', '$ad_name', '$password', '$salt', '1', '1')") or die (mysql_error());
		
		if(!$user_insert)
			{
			$error = 'There has been an error creating your account. Please try again.';
			}
							
		$active_message = "
		You have successfully installed the Cube Crusher ecommerce store. Below are your login details.
		Username: $ad_name
		Password: $ad_pass";
		$subject = "Cube Crusher Successfully Installed";
		
		mail($ad_email, $subject, $active_message, "From: Cube Crusher Store<bedfordd@egmods.com>");
		$smarty->assign('long_descrip','<b>Cube Crusher has been successfully installed and configured</b><br> 
		An email titled "'.$subject.'" has been sent to '.$ad_email.' containing your login details in case you forget them<br><br>
		Please remember to delete the "/install" folder from your server, then follow this link to your store front. <a href="../storefront.php">Cube Crusher Storefront</a>');
		
		//Set installed = 1
		mysql_query("UPDATE config SET value='1' WHERE var_name='installed'");
		//Set the location of the store script
		mysql_query("UPDATE config SET value='$serv_loc' WHERE var_name='store_url'");
		}
	}
else
	{
	$error = "Error: No Post Data Submitted, please start with install.php";
	}

if($error != NULL)
{
	$error = '<h1>'.$error.'</h1>';
	$smarty->assign('store_title','Cube Crusher Install 3 of 3 ~ !ERROR!');
	$smarty->assign('page_name','~Cube Crusher Install~<br>3 of 3 ~ !ERROR!');
	$smarty->assign('long_descrip',$error);
}
else
{
	$smarty->assign('store_title','Cube Crusher Install 3 of 3 ~ !SUCCESS!');
	$smarty->assign('page_name','~Cube Crusher Install~<br>3 of 3 ~ !SUCCESS!');
}

//Output the top of the page
$smarty->display('header.tpl');
$smarty->display('content_top.tpl');

//Output the bottom of the page
$smarty->display('content_bott.tpl');
$smarty->display('footer.tpl');

sleep(1);    // this does the trick
rename ("../install", "../installb"); //no error 
?>