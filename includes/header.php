<?php
//Error needs to be set to 0
$error = '0';

//Here I grab the cart id and userid, and find out how many items are in the cart
$cart_id = $_SESSION['cart_id'];
$userid = $_SESSION['userid'];

//Assign them to Smarty variables as well, we use them during the checkout
$smarty->assign('cart_id', $cart_id);
$smarty->assign('userid', $userid);

//Check if the cartid and the userid are set or not, if both of them are not set then cart count and cost should be 0 since there is no cart
if(empty($_SESSION['cart_id']) && empty($_SESSION['userid']))
	{
	$smarty->assign('cart_count', '0');
	$smarty->assign('cart_cost', '0.00');
	}
else
	{
	//Do some error checking, check if there is either a logged in user or a cart id, if so try to grab the cart from the database
	//If there is a userid but no cart then skip the other crap and set cart_count and cart_cost to zero
	$skip = '0';
	
	if(!empty($userid))
		{
		//Check if the user has an associated cart id
		$cart_check = mysql_query("SELECT * FROM carts WHERE userid='$userid' AND purchased='0'");
		
		if(mysql_num_rows($cart_check) == FALSE)
			{
			$skip = '1';
			$smarty->assign('cart_count', '0');
			$smarty->assign('cart_cost', '0.00');
			}
		else
			{
			$check_rows = mysql_fetch_array($cart_check);
			$cart_id = $check_rows['cart_id'];
			$_SESSION['cart_id'] = $cart_id;
			}
		}
	if(!empty($cart_id))
		{
		$cart_check = mysql_query("SELECT purchased, userid FROM carts WHERE cart_id='$cart_id'");
		$check_rows = mysql_fetch_array($cart_check);
		
		//Check if the cart has already been purchased, if so then unset the sessioned cart id
		$purchased = $check_rows['purchased'];
		
		if($purchased == '1')
			{
			$skip = '1';
			$smarty->assign('cart_count', '0');
			$smarty->assign('cart_cost', '0.00');
			
			unset($_SESSION['cart_id']);
			}
		else
			{
			$skip = '0';
			
			//Check if there is a user logged in, if so set their userid for the cart
			if(!empty($userid))
				{
				mysql_query("UPDATE carts SET userid='$userid' WHERE cart_id='$cart_id'");
				}
			}
		}
		
	if($skip == '0')
		{
		$cart_query = mysql_query("SELECT * FROM in_cart WHERE cart_id='$cart_id'");
		
		//mySQL Error echo
		if(!$cart_query)
			{
			$error = '$cart_query: mysql_error()';
			}
		  
		elseif (!mysql_num_rows($cart_query))
			{
			$error = 'There was an unknown error retrieving the cart';
			}
		
		else
			{
			$cart_count = '0';
			$cart_cost = '0.00';
			
			//Here we loop through to grab the products to add their stuff together
			while($cart_rows = mysql_fetch_array($cart_query))
				{
				$prod_id = $cart_rows['prod_id'];
				$prod_qty = $cart_rows['prod_qty'];
				$prod_price = $cart_rows['prod_price'];
					
				//Now we get the price
				$prod_sql = mysql_query("SELECT cost FROM pricing WHERE price_id='$prod_price'");
				$prod_info = mysql_fetch_array ($prod_sql);
				$cost = $prod_info['cost'];
				
				$prod_cost = $cost * $prod_qty;
				$cart_cost = $cart_cost + $prod_cost;
				$cart_count = $cart_count + $prod_qty;
				}//Ends the while loop that figures cost and quantity
				
			//assign the cart count and cost
			$smarty->assign('cart_count', $cart_count);
			$smarty->assign('cart_cost', $cart_cost);
			}
		}
	}

//Here I check if the user is logged in or not, I display one or the other navigation links
if(empty($_SESSION['userid']))
	{
	$smarty->assign('navi_page', 'navi_out.tpl');
	}
else
	{
	$smarty->assign('navi_page', 'navi_logged.tpl');
	}
?>