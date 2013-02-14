<?php 
session_start();
//Includes
include 'includes/db_config.php';
include 'includes/db_connect.php';

$user = $_SESSION['username'];
$userid = $_SESSION['userid'];
$error = '0';

$prod_id = $_REQUEST['prod_id'];
$price_id = $_REQUEST['price_id'];

if($price_id == '0')
	{
	//Check whether it is free or not
	$prod_sql = mysql_query("SELECT free, free_link FROM products WHERE prod_id='$prod_id'");
	$prod_free = mysql_fetch_array ($prod_sql);
	$is_free = $prod_free['free'];
	}
else
	{
	$is_free = '0';
	}
	
if($is_free == '1')
	{
	$link = $prod_free['free_link'];
	
	if(is_file($link))  
		{  
		//Required for Internet Exploder
		if(ini_get('zlib.output_compression'))
			{
			ini_set('zlib.output_compression', 'Off');
			}  
		
		$extension = strtolower(substr(strrchr($link,'.'),1));
		
		//Get the file mime type based upon the extension
		switch($extension)  
			{
			case "exe": $type = "application/octet-stream"; break; 
			case "mp3": $type = "audio/mpeg"; break; 
			case "pdf": $type = "application/pdf"; break;
			case "rar": $type = " application/x-rar-compressed"; break;
			case "zip": $type = "application/zip"; break;  
			default: $type = "application/force-download";  
			}
			
			header('Pragma: public');   // required  
			header('Expires: 0');       // no cache  
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');  
			header('Cache-Control: private',false);  
			header('Content-Type: '.$type);  
			header('Content-Disposition: attachment; filename="'.basename($link).'"');  
			header('Content-Transfer-Encoding: binary');  
			header('Content-Length: '.filesize($link));    // provide file size  
			readfile($link);       // push it out  
			exit();   
		}
	}
else
	{	
	if($user == NULL)
		{
		echo "<h1>You must be logged in to download a file.</h1>";	
		}
		
	else
		{
		/*The User is logged in, so we need to get the variables from the URL after that we need to make sure they purchased the item
		 and still have downloads left. If they still have downloads then we output the file*/
						
		//Get the remaining downloads so we can subtract 1
		$prod_sql = mysql_query("SELECT remain_downloads FROM purchases WHERE prod_id='$prod_id' AND prod_price='$price_id' AND userid='$userid'");
		$prod_info = mysql_fetch_array ($prod_sql);
		$remain_downloads = $prod_info['remain_downloads'];
		
		if($remain_downloads != '-1')
			{
			if($remain_downloads == '0')
				{
				$error = 'You have no remaining downloads for this file!';
				}
			else
				{
				$remain_downloads = $remain_downloads - 1;
				
				mysql_query("UPDATE purchases SET remain_downloads='$remain_download' WHERE prod_id='$prod_id' AND prod_price='$price_id' AND userid='$userid'");
				}
			}
		
		//Get the download Link
		$link_sql = mysql_query("SELECT link FROM pricing WHERE price_id='$price_id'");
		$link_info = mysql_fetch_array ($link_sql);
		$link = $link_info['link'];
		
		if($error == '0')
			{ 
			//Make sure that the link is definitely of a file
			if(is_file($link))  
				{  
				//Required for Internet Exploder
				if(ini_get('zlib.output_compression'))
					{
					ini_set('zlib.output_compression', 'Off');
					}  
				
				$extension = strtolower(substr(strrchr($link,'.'),1));
				
				//Get the file mime type based upon the extension
				switch($extension)  
					{
					case "exe": $type = "application/octet-stream"; break; 
					case "mp3": $type = "audio/mpeg"; break; 
					case "pdf": $type = "application/pdf"; break;
					case "rar": $type = " application/x-rar-compressed"; break;
					case "zip": $type = "application/zip"; break;  
					default: $type = "application/force-download";  
					}
					
				header('Pragma: public');   // required  
				header('Expires: 0');       // no cache  
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');  
				header('Cache-Control: private',false);  
				header('Content-Type: '.$type);  
				header('Content-Disposition: attachment; filename="'.basename($link).'"');  
				header('Content-Transfer-Encoding: binary');  
				header('Content-Length: '.filesize($link));    // provide file size  
				readfile($link);       // push it out  
				exit(); 
				}  
			}
		else
			{
			echo "<h1>$error</h1>";
			}
		}
	}
?>
