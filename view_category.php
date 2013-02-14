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
		$cat_id = $_REQUEST['cat_id'];
		
		//Grab info about category, since this is a specific category view
		$store_sql = mysql_query("SELECT cat_name, long_descrip FROM category WHERE cat_id=$cat_id");
		$store_home = mysql_fetch_array ($store_sql);
		$smarty->assign('page_name', $store_home['cat_name']);
		$smarty->assign('long_descrip', $store_home['long_descrip']);
		
		//Output the top of the page
		$smarty->display('header.tpl');
		$smarty->display('content_top.tpl');
		
		//Beginning of product category thing
		//$smarty->display('category_top.tpl'); 
		$smarty->display('contain_begin.tpl'); 
		
		//The query for the getting which products belong to this category
		$prod_cats = mysql_query("SELECT prod_id FROM product_relations WHERE cat_id=$cat_id");
		
		//mySQL Error echo
		if(!$prod_cats)
		{
			echo "$prod_cats: ".mysql_error();
		}
		elseif (mysql_num_rows($prod_cats) == FALSE)
		{
			echo "<h1>There are no products, this is perhaps a bug, please contact the administrator.</h1>";
		}
		else
		{
			//Initialize $counter to 1
			$counter = 1;
			
			//Here we start looping through all the different products in the category
			while($products = mysql_fetch_array($prod_cats))
			{
				//Query for product information
				$prod_id = $products['prod_id'];
				
				$product_query = mysql_query("SELECT prod_name, prod_note, short_descrip, release_year, prod_thumb, free, downloads, comments FROM products WHERE prod_id=$prod_id AND active='1' LIMIT 1");
				$product_check = mysql_num_rows($product_query);
				$prod_rows = mysql_fetch_array($product_query);
				
				//Only perform below operations if we successfully get this product from the database
				if($product_check == '1')
				{
					//Product information, set to smarty variables
					$prod_name = $prod_rows['prod_name'];
					$prod_note = $prod_rows['prod_note'];
					$short_descrip = $prod_rows['short_descrip'];
					$long_descrip = $prod_rows['long_descrip'];
					$release_year = $prod_rows['release_year'];
					$prod_thumb = $prod_rows['prod_thumb'];
					$prod_free = $prod_rows['free'];
					$prod_downloads = $prod_downloads['downloads'];
					$prod_comments = $prod_comments['comments'];
					
					/*
					if($long_descrip != NULL)
					{
						$short_descrip = $short_descrip.'<br><a href="view_product.php?prod_id='.$prod_id.'">[More Information]</a>';
					}
					*/
					
					$smarty->assign('prod_id', $prod_id);
					$smarty->assign('prod_name', $prod_name);
					$smarty->assign('prod_note', $prod_note);
					$smarty->assign('prod_short', $short_descrip);
					$smarty->assign('release_year', $release_year);
					$smarty->assign('prod_thumb', $prod_thumb);
					$smarty->assign('prod_downloads', $prod_downloads);
					$smarty->assign('prod_comments', $prod_comments);
					
					//Here we get the pricing stuff and construct a form for each product, probably not the best way to do this, but the easiest
					//The query for the pricing for the product
					$prod_price = mysql_query("SELECT price_id FROM pricing_relations WHERE prod_id=$prod_id");
			
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
							//Get price id, then query price table to cost and name
							$price_id = mysql_fetch_array($prod_price);
							$price = mysql_query("SELECT cost, name FROM pricing WHERE price_id=$price_id");
							$price_rows = mysql_fetch_array($price);
							
							//Variables from database
							$cost = $price_rows['cost'];
							$price_name = $price_rows['name'];
							
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
							<input type="hidden" name="prod_id" value="'.$prod_id.'">
							<select name="pricing">
							';
							
							//Here we start looping through all the different product prices grabbing their info from the database
							while($price_rows = mysql_fetch_array($prod_price))
							{
								//Get price id, then query price table to cost and name
								$price_id = $price_rows['price_id'];
								$price = mysql_query("SELECT cost, name FROM pricing WHERE price_id=$price_id");
								$price_rows = mysql_fetch_array($price);
							
								//Variables from database
								$cost = $price_rows['cost'];
								$price_name = $price_rows['name'];
								$pricing = $pricing.'<option value="'.$price_id.'">$'.$cost.' - '.$price_name.'</option>';
							}
							
							$pricing = $pricing.'
							<input type="submit" name="submit" value="Add to Cart">
							</form>';
						}
						
						//Assign counter
						$smarty->assign('counter', $counter);
						
						//Check and increment count
						if ($counter == 3)
						{
							$counter = 1;
						}
						else
						{
							$counter++;
						}
						
						//Assign the smarty varible
						$smarty->assign('pricing', $pricing);
					}
					
					//If there is a product thumbnail, display it, else dont display it
					if($prod_thumb != NULL)
					{
						$smarty->display('product_thumb.tpl');
					}
					else
					{
						$smarty->display('product_nothumb.tpl');
					}
				}
			}//Ends the while loop that sticks in categories
		}//Ends the mysql error checks
		
		//End of Product category thing
		$smarty->display('contain_end.tpl'); 
		//$smarty->display('category_bott.tpl');
		
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