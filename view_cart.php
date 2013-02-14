<?php 
session_start();

//Smarty include and create
//Initially set to the default template, but if another is selected, then we switch to it.
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
	
	//Check if the set template is not default, if it isn't, set the template dir to whatever it is set to
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

		$smarty->assign('page_name', 'Your Cart');
		$smarty->assign('long_descrip', 'Please note that the shipping price below is only accurate if you live in the United states, expect the price to be $10 to $20 higher depending upon your actual location.');
		
		//Output the top of the page
		$smarty->display('header.tpl');
		$smarty->display('content_top.tpl');
		//Beginning of product category thing
		//$smarty->display('category_top.tpl'); 	
		
		//The query for the products of the category
		$cart_query = mysql_query("SELECT * FROM in_cart WHERE cart_id='$cart_id'");
			
		//mySQL Error echo
		if(!$cart_query)
			{
			$error = '$cart_query: mysql_error()';
			}
		elseif (mysql_num_rows($cart_query) == FALSE)
			{
			echo "<h1>You have no items in your cart.</h1>";
			}
		else
			{
			//Output the beginning of the table
			$smarty->display('cart_begin.tpl');
			
			//Initialize $cart_count and $cart_cost to 0
			$cart_count = '0';
			$cart_cost = '0';
			
			//Initialize $save_price as an array
			$save_price = array();
			
			//Here we loop through to grab the products to add their stuff together and display their basic info
			while($cart_rows = mysql_fetch_array($cart_query))
				{
				$prod_id = $cart_rows['prod_id'];
				$prod_qty = $cart_rows['prod_qty'];
				$prod_price = $cart_rows['prod_price'];
				
				//Get the product info for the current product
				$prod_sql = mysql_query("SELECT prod_name, short_descrip, release_year FROM products WHERE prod_id='$prod_id'");
				$prod_info = mysql_fetch_array ($prod_sql);
				
				//Pricing information for the product
				$price_sql = mysql_query("SELECT cost, name FROM pricing WHERE price_id='$prod_price'");
				$price_info = mysql_fetch_array ($price_sql);
				$unit_cost = $price_info['cost'];
				
				//Cost of x quantity of items
				$prod_cost = $unit_cost * $prod_qty;
				
				//Total cost of everything in cart
				$total = $total + $prod_cost;
				
				//Figure out quantity of all items in the cart
				//$cart_count = $cart_count + $prod_qty;
				
				
				/*
				//BEGIN of sales
				//Need to edit this up as a "sales or discounts" type of thing that gets all info from database
				//Rather than the hardcoded every 5th is deducted
				
				//Number of items at a price, use price as key and add quantity to it
				$saveings["$unit_cost"]["count"] = $saveings["$unit_cost"]["count"] + $prod_qty;
				
				$price_array = $saveings["$unit_cost"]["count"];
				
				//echo "Price Counter :: $unit_cost - $price_array<br>";
				
				//Set $item_savings to 0 to make sure we don't have a leftover value from a previous loop
				$item_savings = 0;
					
				//How many times can I divide by 5?
				$save_count = $saveings["$unit_cost"]["count"] / 5;
					
				//echo "Save Count :: $save_count<br>";
					
				//Round downwards using floor
				$save_count = floor($save_count);
				
				//Make sure counts are different before adding to savings total, then store save count
				if($saveings["$unit_cost"]["savings"] == NULL)
				{
					//echo "No Items at Price <br>";
					
					//Store it, since the new saved amount is bigger
					$saveings["$unit_cost"]["savings"] = $save_count;
					
					//Savings for this particular price
					$item_savings = $unit_cost * $save_count;
				}
				
				else if($saveings["$unit_cost"]["savings"] < $save_count)
				{
					//echo "More Items at Price <br>";
					
					//Get difference of save counts and calculate that savings
					//$array_qty = $price["$unit_cost"]['1'];
					$save_difference = $save_count - $saveings["$unit_cost"]["savings"];
					
					//Store it, since the new saved amount is bigger
					$saveings["$unit_cost"]["savings"] = $save_count;
					
					//Additional savings for this particular price
					$item_savings = $unit_cost * $save_difference;
				}
				
				//Add to the overall savings
				$savings = $savings + $item_savings;
				//END of sales
				*/
				
				//Assign smarty vars
				$smarty->assign('in_id',$cart_rows['in_id']); //The id number of the item in the cart
				$smarty->assign('prod_qty',$prod_qty); //Quantity of product in the cart
				$smarty->assign('prod_name',$prod_info['prod_name']);
				$smarty->assign('short_descrip',$prod_info['short_descrip']);
				$smarty->assign('release_year', $prod_info['release_year']);
				$smarty->assign('license',$price_info['name']); //Name of the price amount
				$smarty->assign('cost',$unit_cost); //Cost of 1 item
				$smarty->assign('prod_cost',$prod_cost); //Cost of $prod_qty of products
				
				//Output the table with the info
				//We also need a hidden field that is an array that has the id of the item in the cart
				//Don't really have a better way of explaining it.
				$smarty->display('cart_data.tpl');	
				}//Ends the while loop that figures cost and quantity
			
			//BEGIN of Shipping Costs
			//If < $50 then $5.00 if >= $50 && < $100 then $10.00 if >= $100 then $0.00
			if($total >= 0 && $total < 50)
			{
				$shipping = 6.00;
			}
			elseif($total >= 50 && $total < 100)
			{
				$shipping = 12.00;
			}
			elseif($total >= 100)
			{
				$shipping = 0.00;
			}
			//END of Shipping costs

			
			//Calculate subtotal
			//$subtotal = $total - $savings + $shipping;
			$subtotal = $total + $shipping;
			
			//Smarty Variables for totals
			$smarty->assign('total',$total); //Cost of shipping	
			//$smarty->assign('savings',$savings); //Cost of shipping
			$smarty->assign('shipping',$shipping); //Cost of shipping
			$smarty->assign('subtotal',$subtotal); //Cost of shipping
			
			//Output the totals section of the cart
			//Also Confirm Cart & Continue Button, brings you to shipping form to ask for shipping information
			$smarty->display('cart_end.tpl');
		}	
			
			/* Not required for now, using Paypal
			//Figure out the allowable payment methods
			//Method type of 1 is a payment method, method type of 2 is a shipping method
			$pay_methods = mysql_query("SELECT name, accept_icon, directory, method_file FROM methods_pay WHERE type='1' AND active='1'");
			
			//mySQL Error echo
			if(!$pay_methods)
				{
				$error = '$pay_methods: mysql_error()';
				}
			elseif (mysql_num_rows($pay_methods) == FALSE)
				{
				echo "<h1>There are no payment methods.</h1>";
				}
			else
				{
				while($method_rows = mysql_fetch_array($pay_methods))
					{
					$smarty->assign('method_name', $method_rows['name']);
					$smarty->assign('method_icon', $method_rows['accept_icon']);
					$smarty->assign('method_dir', $method_rows['directory']);
					$smarty->assign('method_file', $method_rows['method_file']);

					//Output the method
					$smarty->display('method_payment.tpl');
					}//Ends the while loop that figures out allowable payment methods
				}//End mysql error checking	
			*/
		
		//End of Product category thing
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