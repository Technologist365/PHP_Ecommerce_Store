<?php 
session_start();

//Smarty include and create
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
		if(file_exists('install/') == FALSE)
		{	
			//Configuration and database includes
			include 'includes/config.php';
			include 'includes/header.php';
	
		//Grab the category Id from the URL if there is one
		$prod_id = $_REQUEST['prod_id'];
		
		//Grab info about the product since a specific product is being looked at in greater detail
		$prod_sql = mysql_query("SELECT prod_name, long_descrip, release_year, prod_pic, free FROM products WHERE prod_id=$prod_id");
		$prod_info = mysql_fetch_array ($prod_sql);
		$prod_pic = $prod_info['prod_pic'];
		
		//Assign the page name
		$page_name = $prod_info['prod_name'].' ('.$prod_info['release_year'].')';
		$smarty->assign('page_name', $page_name;
		
		//Output the top of the page
		$smarty->display('header.tpl');
		$smarty->display('content_top.tpl');
		
			//Here we get the pricing stuff and construct a form for each product, probably not the best way to do this, but the easiest
			//The query for the pricing for the product
			$prod_price = mysql_query("SELECT price_id, cost, name FROM pricing WHERE prod_id=$prod_id");

			//mySQL Error echo
			if(!$prod_price)
			{
				echo "$prod_price : ".mysql_error();
			}
			elseif (mysql_num_rows($prod_price) == FALSE && $prod_free!='1')
			{
				$error = "<h1>There are no prices for the following product $prod_name, this is perhaps a bug, please contact the administrator.</h1>";
			}
			else
			{
				//Reset pricings to NULL, otherwise it will keep adding more buttons
				$free = NULL;
				$single = NULL;
				$pricing = NULL;
				
				//Beginning of the pricing variable to be echoed later
				if ($prod_free == '1')
				{
					$free = '
					<h4>Free</h4>
					<a href="download.php?prod_id='.$prod_id.'&price_id=0">Free Download</a>
					<br><br>
					';
					
					$smarty->assign('free', $free);
				}

				//If there is only 1 price then don't do a drop down because it would look silly for just one price
				if(mysql_num_rows($prod_price) == 1)
				{
					$price_rows = mysql_fetch_array($prod_price);
					$price_id = $price_rows['price_id'];
					$cost = $price_rows['cost'];
					
					//Using a URL to submit the data, all we need is the price and cost as smarty vars
					$smarty->assign('single', $price_id);
					$smarty->assign('cost', $cost);
				}
				//More than one price so we should show a drop down of the different pricing options
				else
				{
					$pricing = $pricing.'
					<h4>Pricing</h4>
					<form method="post" action="add_cart.php">
					<select name="pricing">
					';
					
					//Here we start looping through all the different product prices grabbing their info from the database
					while($price_rows = mysql_fetch_array($prod_price))
					{
						$price_id = $price_rows['price_id'];
						$cost = $price_rows['cost'];
						$price_name = $price_rows['name'];
						$pricing = $pricing.'<option value="'.$price_id.'">$'.$cost.' - '.$price_name.'</option>';
					}
						
					$pricing = $pricing.'
					<input type="submit" name="submit" value="Add to Cart">
					</form>';
					
					//Assign smarty variable for the pricing
					$smarty->assign('pricing', $pricing);
				}
			}
		
		//Assign smarty variables
		$smarty->assign('long_descrip', $prod_info['long_descrip']);	
		$smarty->assign('release_year', $prod_info['release_year']);
		
		//Display the product information and such, if there is a picture, display it one way, otherwise display the other way
		if($prod_pic != NULL)
			{
			$smarty->assign('prod_pic', $prod_pic);
			$smarty->display('product_pic.tpl');
			}
		else
			{
			$smarty->display('product_nopic.tpl');
			}
		
		/*
		//Get config information regarding commenting
		$comment_sql = mysql_query("SELECT display_free, display_purchase, make_free, make_purchase, comment_new_first FROM config_enum");
		$comment_info = mysql_fetch_array ($comment_sql);
		$display_free = $comment_info['display_free'];
		$display_purchase = $comment_info['display_purchase'];
		$make_free = $comment_info['make_free'];
		$make_purchase = $comment_info['make_purchase'];
		$comment_new_first = $comment_info['comment_new_first'];
		
		//Check if user can view comments or not
		if($prod_free == 1 && $display_free == 1)
		{
			$view_comments = 1;
		}
		elseif($prod_free == 1 && $display_free == 2 && $user != NULL)
		{
			$view_comments = 1;
		}
		elseif($prod_free == 1 && $display_free == 3 && $user != NULL)
		{
			//NOT IMPLEMENTED
			//Check if the user has downloaded this file yet or not, then determine whether they can view or not
		}
		elseif($prod_free == 0 && $display_purchase == 1)
		{
			$view_comments = 1;
		}
		elseif($prod_free == 0 && $display_purchase == 2 && $user != NULL)
		{
			$view_comments = 1;
		}
		elseif($prod_free == 0 && $display_purchase == 3 && $user != NULL)
		{
			//NOT IMPLEMENTED
			//Check if the user has purchased this file yet or not, then determine whether they can view or not
		}
		else
		{
			$view_comments = 0;
		}
		
		if($view_comments == 1)
		{
			//Begin comments div
			$smarty->display('comment_begin.tpl');
			
			//User can view comments so grab the comment information from the database
			if($comment_new_first == 1)
			{
				//Newest first, so largest id comments on top
				$comments = mysql_query("SELECT user_id, date, comment FROM comments WHERE product_id=$prod_id ORDER BY comment_id DESC");	
			}
			else
			{
				//Display in the order they were posted, newest is last
				$comments = mysql_query("SELECT user_id, date, comment FROM comments WHERE product_id=$prod_id");
			}
						
			//mySQL Error echo
			if(!$comments)
			{
				echo "$comments: ".mysql_error();
			}
			elseif (mysql_num_rows($comments) == FALSE)
			{
				echo "<h1>There was an unknown error retrieving the comments</h1>";
			}
			else
			{
				//Here we loop through to grab the comments to stick onto the page
				while($comment_rows = mysql_fetch_array($comments))
				{
					//Find the user's name
					$user_id = $comment_rows['user_id'];
					
					$name_sql = mysql_query("SELECT username FROM users WHERE userid=$user_id");
					$name_info = mysql_fetch_array ($name_sql);
					
					//Smarty Variables
					$smarty->assign('username',$name_info['username']);
					$smarty->assign('datetime',$comment_rows['datetime']);
					$smarty->assign('comment',$comment_rows['comment']);
					
					//Output the data
					$smarty->display('comment_data.tpl');
				}
			}
			
			//Check if user can make comments or not
			if($prod_free == 1 && $make_free == 1)
			{
				$make_comments = 1;
			}
			elseif($prod_free == 1 && $make_free == 2 && $user != NULL)
			{
				$make_comments = 1;
			}
			elseif($prod_free == 1 && $make_free == 3 && $user != NULL)
			{
				//NOT IMPLEMENTED
				//Check if the user has downloaded this file yet or not, then determine whether they can view or not
			}
			elseif($prod_free == 0 && $make_purchase == 1)
			{
				$make_comments = 1;
			}
			elseif($prod_free == 0 && $make_purchase == 2 && $user != NULL)
			{
				$make_comments = 1;
			}
			elseif($prod_free == 0 && $make_purchase == 3 && $user != NULL)
			{
				//NOT IMPLEMENTED
				//Check if the user has purchased this file yet or not, then determine whether they can view or not
			}
			else
			{
				$make_comments = 0;
			}
			
			if($make_comments == 1)
			{
				//Stick the form in
				$smarty->display('comment_form.tpl');
			}
		
			//End the comments div
			$smarty->display('comment_end.tpl');
		}
		*/
		
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
