<?php 
session_start();

//Smarty include and create
//Initially set to the default template, but if another is selected, then we switch to it.
include('../../includes/Smarty.class.php');
$smarty = new Smarty;
$smarty->caching = 0; 
$smarty->template_dir = "../../templates/default/"; 
$smarty->assign('store_url','../../'); //Using the relative position for now

//Initialize $error to NULL
$error = NULL;

if(file_exists('../../includes/db_config.php') == TRUE)
{
	include '../../includes/db_config.php';
	include '../../includes/db_connect.php';
	
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
		if(file_exists('../../install/') == FALSE)
		{	
			//Configuration and database includes
			include '../../includes/config.php';
			include '../../includes/header.php';
					
		//Output the top of the page
		$smarty->display('header.tpl');
		$smarty->display('content_top.tpl');
		
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		
		foreach ($_POST as $key => $value)
			{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
			}
		
		//Check if the store is set to test mode or live mode
		
		
		if($paypal_live == '1')
			{
			$paypal_url = 'ssl://paypal.com';
			}
		else
			{
			$paypal_url = 'ssl://sandbox.paypal.com';
			}
				
		// post back to PayPal system to validate
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ("$paypal_url", 443, $errno, $errstr, 30);
		
		// assign posted variables to local variables
		/* Don't think I need these
		$item_name = $_POST['item_name'];
		$item_number = $_POST['item_number'];
		But just in case I won't delete them */
		$payment_status = $_POST['payment_status'];
		$payment_amount = $_POST['mc_gross'];
		$currency = $_POST['mc_currency'];
		$transaction_id = $_POST['txn_id'];
		$receiver_email = $_POST['receiver_email'];
		$payer_email = $_POST['payer_email'];
		
		echo "Payment Gross :: $payment_amount<br>";
		
		if (!$fp) 
			{
			// HTTP ERROR
			} 
		else
			{
			fputs ($fp, $header . $req);
			
			while (!feof($fp))
				{
				$res = fgets ($fp, 1024);
				if (strcmp ($res, "VERIFIED") == 0)
					{
					//Make sure this order has not already been processed check for the existence of this trasaction id for this payment method
					//We are checking for this method just to ensure there is no collision with another payment method also having used the same transaction id
					$transaction_sql = mysql_query("SELECT tran_num FROM transactions WHERE tran_id='$transaction_id' AND method_id='$method_id'");
					$transaction_row = mysql_fetch_array ($transaction_sql);
					$previous_tran = $transaction_row['tran_num'];
					
					if($previous_tran != NULL)
						{
						//This transaction id is already in the database
						$error = 'This order appears to have been processed already. Please verify that the items you have purchased are in your purchases page.';
						}
					else
						{
						//Store the transaction id in the database
						mysql_query("INSERT INTO transactions (paypal_id) VALUES('$transaction_id')") or die (mysql_error());
						}
						
					//Make sure the payment has already completed, otherwise we will wait for IPN.
					if($payment_status != "Completed")
						{
						$error = 'Your payment has not yet finished processing, please check back when you are sure your payment has went through.<br>';
						}
					
					/*Make sure the currency is what it is supposed to be.
						5 Euros and 5 Dollars are very different amounts.*/
					if($currency != $paypal_currency)
						{
						//They don't match, return an error
						$error = 'An uknown error has occured while processing your transaction.';
						}
					
					//Make sure that the payment was sent to the proper account.
					$business = $keyarray["business"];
					
					if("$business" != "$paypal_business")
						{
						//They don't match, return an error
						$error = 'An uknown error has occured while processing your transaction.';
						}
					
					//Make sure that the gross payment amount is equal to what everything in the cart costs.
					//Pretty much use the code for the header
					$cart_id = $_REQUEST['cart_id'];
					
					/*
					if(!$cart_id)
					{
						$cart_id = $_REQUEST['custom'];
					}
					*/
					
					$cart_query = mysql_query("SELECT * FROM in_cart WHERE cart_id='$cart_id'");
					
					if(!$cart_query)
						{
						$error = '$cart_query: mysql_error()';
						}
					  
					elseif (mysql_num_rows($cart_query) == FALSE)
						{
						$error = 'There was an unknown error retrieving the cart data.';
						}
						
					else
						{
						//Get administrator person's email address
						$admin_sql = mysql_query("SELECT value FROM config_text WHERE var_name='admin_email'");
						$admin_info = mysql_fetch_array ($admin_sql);
						$admin_email = $admin_info['value'];
							
						$cart_cost = '0.00';
						$cart_count = '0';
						
						//Here we loop through to grab the products to add their prices
						while($cart_rows = mysql_fetch_array($cart_query))
							{
							$prod_id = $cart_rows['prod_id'];
							$prod_qty = $cart_rows['prod_qty'];
							$prod_price = $cart_rows['prod_price'];
							
							//Get the product name and stock
							$prod_sql = mysql_query("SELECT prod_name, stock, stock_warning FROM products WHERE prod_id='$prod_id'");
							$prod_info = mysql_fetch_array ($prod_sql);
							$prod_name = $prod_info['prod_name'];
							$stock = $prod_info['stock'];
							$stock_warning = $prod_info['stock_warning'];
							
							//Product info for email
							$prod_email = $prod_email."$prod_qty - $prod_name \n";
							
							//Pricing information for the product
							$price_sql = mysql_query("SELECT cost, name FROM pricing WHERE price_id='$prod_price'");
							$price_info = mysql_fetch_array ($price_sql);
							$unit_cost = $price_info['cost'];
											
							//Cost of x quantity of items
							$prod_cost = $unit_cost * $prod_qty;
							
							//Total cost of everything in cart
							$total = $total + $prod_cost;
							
							//Figure out quantity of all items in the cart
							$cart_count = $cart_count + $prod_qty;
							
							//Update our stock and then check if it has fallen beneath our warning level
							$stock = $stock - $prod_qty;
							mysql_query("UPDATE products (stock) VALUES ('$stock') WHERE prod_id='$prod_id'");
							
							if($stock <= $stock_warning)
							{
								//Shoot off an email to the admin
								$subject = "Low Stock - $prod_name : Below $stock_warning units";
								$message = "Stock low for $prod_name, only $stock units remaining which is below the warning level of $stock_warning units. Please update stock values once new units have arrived. Otherwise the shipping overlord and meatbag drones will be most displeased once stock is fully depleted.";
								mail("$admin_email", "$subject", "$message", "From: Overlord of Stock <$admin_email>");
							}
							
							
							//BEGIN of sales
							//Need to edit this up as a "sales or discounts" type of thing that gets all info from database
							//Rather than the hardcoded every 5th is deducted
							
							//Number of items at a price, use price as key and add quantity to it
							$saveings["$unit_cost"]["count"] = $saveings["$unit_cost"]["count"] + $prod_qty;
							
							$price_array = $saveings["$unit_cost"]["count"];
							
							//Set $item_savings to 0 to make sure we don't have a leftover value from a previous loop
							$item_savings = 0;
								
							//How many times can I divide by 5?
							$save_count = $saveings["$unit_cost"]["count"] / 5;
								
							//Round downwards using floor
							$save_count = floor($save_count);
							
							//Make sure counts are different before adding to savings total, then store save count
							if($saveings["$unit_cost"]["savings"] == NULL)
							{
								//Store it, since the new saved amount is bigger
								$saveings["$unit_cost"]["savings"] = $save_count;
								
								//Savings for this particular price
								$item_savings = $unit_cost * $save_count;
							}
							
							else if($saveings["$unit_cost"]["savings"] < $save_count)
							{
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
							}//Ends the while loop that figures cost and quantity

			
						//Shipping Information
						$shipping_sql = mysql_query("SELECT country FROM carts WHERE cart_id=$cart_id");
						$shipping_row = mysql_fetch_array ($shipping_sql);
						$country = $shipping_row['country'];
		
						//BEGIN of Shipping Costs
						//If shippinh to US, cheapest. Canada add $10 to each, if International add $20 to each
						if($country = 'United States')
						{
							//If < $50 then $5.99 if >= $50 && < $100 then $11.99 if >= $100 then $0.00
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
						}
						elseif($country == 'Canada')
						{
							if($total >= 0 && $total < 50)
							{
								$shipping = 15.00;
							}
							elseif($total >= 50 && $total < 100)
							{
								$shipping = 20.00;
							}
							elseif($total >= 100)
							{
								$shipping = 10.00;
							}
						}
						else
						{
							if($total >= 0 && $total < 50)
							{
								$shipping = 25.00;
							}
							elseif($total >= 50 && $total < 100)
							{
								$shipping = 30.00;
							}
							elseif($total >= 100)
							{
								$shipping = 20.00;
							}
						}
						
						
						//For testing with tiny items before going live
						if($total < 1)
						{
							$shipping = 0.00;
						}
						//END of Shipping costs

							
						//Subtotal
						$subtotal = $total - $savings + $shipping;
						}
					
					if("$payment_amount" != "$subtotal")
						{
						//They don't match, return an error
						$error = "The amount you paid does not match what you should have paid for your items.<br> Amount you paid:$payment_amount <br> Amount you should have paid:$subtotal";
						}
						
					/*	For Debugging Purposes, the important variables you should be receiving from paypal
					echo "Transaction Id: $keyarray[txn_id] <br>
					Payment Status: $keyarray[payment_status] <br>
					Currency: $keyarray[mc_currency] <br>
					Payment Gross: $keyarray[payment_gross] <br>
					Business: $keyarray[business] <br>";
					*/
					
					if($error != '0')
					{
						echo "<h1>An error has occured while processing your payment. Please contact the administrator.</h1>
							<b>Error:</b> $error";
					}
						
					else
					{
						//Everything went ok, or so we think. So now we add all the items in the cart to the purchases table.
						$userid_sql = mysql_query("SELECT userid FROM carts WHERE cart_id='$cart_id'");
						$userid_info = mysql_fetch_array ($userid_sql);
						$userid = $userid_info['userid'];
						
						if(!empty($userid))
						{
							$cart_query = mysql_query("SELECT * FROM in_cart WHERE cart_id='$cart_id'");
							if(!$cart_query)
							{
								$error = '$cart_query: mysql_error()';
							}
							  
							elseif (!mysql_num_rows($cart_query))
							{
								$error = 'There was an unknown error retrieving the cart data.';
							}
							
							else
							{
								//Here we loop through to grab the products and figure out their download count
								//We also add them to the purchases table
								while($cart_rows = mysql_fetch_array($cart_query))
								{
									$prod_id = $cart_rows['prod_id'];
									$prod_qty = $cart_rows['prod_qty'];
									$prod_price = $cart_rows['prod_price'];
									
									//Get the product info for the current product
									$prod_sql = mysql_query("SELECT dl_count FROM products WHERE prod_id='$prod_id'");
									$prod_info = mysql_fetch_array ($prod_sql);
									$dl_count = $prod_info['dl_count'];
									
									if($dl_count != '-1')
									{
										$total_downloads = $prod_qty * $dl_count;
									}
									else
									{
										$total_downloads = '-1';
									}
									
									//Now we insert into the purchases table
									mysql_query("INSERT INTO purchases (prod_id, prod_qty, prod_price, remain_downloads, userid) 
											VALUES('$prod_id', '$prod_qty', '$prod_price', '$total_downloads', '$userid')") or die (mysql_error());
								}//Ends the while loop that figures cost and quantity
							}
						}	
							
							//SET THE CART TO PURCHASED
							mysql_query("UPDATE carts (pay_confirm) VALUES ('1') WHERE cart_id='$cart_id'");
							
							//Get shipping person's email address
							$shipper_sql = mysql_query("SELECT value FROM config_text WHERE var_name='shipper_email'");
							$shipper_info = mysql_fetch_array ($shipper_sql);
							$shipper_email = $shipper_info['value'];

							//Subject is Order Number, shipping level, and quantity of items
							$shipper_subject = "Order #$cart_id - $shipping_level - $cart_count items";
							
							//Get information for who we are shipping to
							$purchaser_sql = mysql_query("SELECT name, phone, email, address1, address2, city, state, zip, special FROM carts WHERE cart_id='$cart_id'");
							$purchaser_info = mysql_fetch_array ($purchaser_sql);
							$purch_name = $purchaser_info['name'];
							$purch_phone = $purchaser_info['phone'];
							$purch_email = $purchaser_info['email'];
							$purch_add1 = $purchaser_info['address1'];
							$purch_add2 = $purchaser_info['address2'];
							$purch_city = $purchaser_info['city'];
							$purch_state = $purchaser_info['state'];
							$purch_zip = $purchaser_info['zip'];
							$purch_special = $purchaser_info['special'];
							
							//Purchaser subject for the receipt
							$purchase_subject = "Receipt from $store_title for Order #$cart_id";
							
							//Message is the buyers address and the items ordered and their quantities
							$message = "Order #$cart_id - $cart_count items. \n\n" .
									"Quantity - Product Name \n" .
									"$prod_email".
									"Total - $total \n" .
									"Savings - $savings \n" .
									"Shipping - $shipping \n" .
									"Subtotal - $subtotal \n\n" .
									"Ship To: $purch_name \n" .
									"$purch_add1 \n" .
									"$purch_add2 \n" .
									"$purch_city, $purch_state, $purch_zip \n" .
									"Special Instruction: $purch_special";
							
							//Email Shipping Person Regarding Confirmed Payment
							//Also look up the definition of Robotic if you don't believe I can use that in there. I'm pretty sure I can, let me know if I'm wrong.
							//echo "Shipper Email: $shipper_email<br>";
							mail("$shipper_email", "$shipper_subject", "$message", "From: Robotic Shipping Overlord <$admin_email>");
							//echo "Admin Email: $admin_email<br>";
							mail("$admin_email", "$shipper_subject", "$message", "From: Robotic Shipping Overlord <$shipper_email>");
							
							//Email User Regarding Confirmed Payment
							//echo "Purchaser Email: $purch_email<br>";
							mail("$purch_email", "$purchase_subject", "$message", "From: $store_title Order Status <$shipper_email>");
						
						//All went well, echo this
						echo "<h2>Thank you for your purchase!</h2><br>
							Your transaction has been completed, and a receipt has been emailed to you with the details of your purchase. <br>
							Your order will be processed and shipped out as soon as possible.<br>";
					}
				else if (strcmp ($res, "INVALID") == 0)
					{
					// log for manual investigation
					}
				}
			}
		fclose ($fp);
		
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