<?php
//Smarty include and create
include('../includes/Smarty.class.php');
$smarty = new Smarty;
$smarty->caching = 0; 

//Output an error page thing, because installation is not complete
$smarty->template_dir = "../templates/default/";
$smarty->assign('navi_page', 'navi_error.tpl');

//Assign the smarty variables for the page
$smarty->assign('store_title','Cube Crusher Install 2 of 3 ~ Admin Account Creation');
$smarty->assign('store_url','../'); //Using the relative position
$smarty->assign('page_name','~Cube Crusher Install~<br>2 of 3 ~ Admin Account Creation');
$smarty->assign('long_descrip','
		<form name="store_config" method="post" action="install3.php">
		Admin Name: <input name="ad_name" type="text" value=""><br>
		Admin Password: <input name="ad_pass" type="text" value=""><br>
		Admin Email Address: <input name="ad_email" type="text" value=""><br>
		Store URL Location: <input name="serv_loc" type="text" value="http://www.yoursite.com/folder/to/storefront.php"><br>
		(Folder where storefront.php resides on your server, Ex. http://www.yoursite.com/Cube Crusher/)
		<input type="submit" name="Submit" value="Next">
		</form>
		');

//Output the top of the page
$smarty->display('header.tpl');
$smarty->display('content_top.tpl');
		
//Create the database configuration file
if ($_POST['Submit'])
	{
	extract($_POST);
	
	$file_to_write = 'db_config.php';
	
	$content ='<?php
	//Configure the Database Variables for your server
	$db_host = \''.$db_host.'\';
	$db_username = \''.$db_user.'\';
	$db_password = \''.$db_pass.'\';
	$db_name = \''.$db_name.'\';
	?>';
	
	if($file = fopen("../includes/$file_to_write", 'w'))
		{
		fwrite($file, $content);
		fclose($file);
		
		echo "<b>Success: Database configuration file $file_to_write created</b> <br>";
		
		//Attempt to connect and create database
		$connect = mysql_connect("$db_host","$db_user","$db_pass");
		if (!$connect)
			{
			echo "Could not connect to server, please try to manually run Cube Crusher.sql <br> <b>".mysql_error()."</b> <br>";
			}
		
		if (mysql_query("CREATE DATABASE $db_name",$connect))
			{
			echo "<b>Success: Database $db_name created </b> <br><br>";
			
			//Select the database
			mysql_select_db("$db_name", $connect);
			
			// Now try to create tables and insert the data from Cube Crusher.sql
			set_time_limit(0);
			$sql_statements = file_get_contents ("Cube Crusher.sql");
			$array_sql = preg_split('/;[\n\r]+/',$sql_statements);
			
			while(list($var,$query) = each($array_sql))
				{
				if(trim($query) != NULL)
					{
					if (!mysql_query($query))
						{
						echo "<li><b>Error #$var</b>: <br> $query <br>".mysql_error()."</li><br><br>";
						}
					}
				}
			}
		else
			{
			echo "Error creating database $db_name, if the database already exists please disregard this message, otherwise create 
			the database and please try to manually run Cube Crusher.sql <br> <b>".mysql_error()."</b> <br><br>";
			echo "The script will now attempt to create the tables and insert data anyways, in the event that the database already exists.<br>
				If you see no error messages then the script was successful. <br><br>";
			
			//Select the database
			mysql_select_db("$db_name", $connect);
			
			// Now try to create tables and insert the data from Cube Crusher.sql
			set_time_limit(0);
			$sql_statements = file_get_contents ("Cube Crusher.sql");
			$array_sql = preg_split('/;[\n\r]+/',$sql_statements);
			
			while(list($var,$query) = each($array_sql))
				{
				if(trim($query) != NULL)
					{
					if (!mysql_query($query))
						{
						echo "<li><b>Error #$var</b>: <br> $query <br>".mysql_error()."</li><br><br>";
						}
					}
				}
			}
		}
	else
		{
		echo "<br><b>ERROR: Failed to create $file_to_wrote please check your folder permissions and try again</b> <br><br>";
		}
	}
	
//Output the bottom of the page
$smarty->display('content_bott.tpl');
$smarty->display('footer.tpl');
?>