<?php
session_start();
header("Location:success.php");

//Includes
include '../../includes/db_config.php';
include '../../includes/db_connect.php';

	//Grab the cart id
	$cart_id = $_SESSION['cart_id'];
	$userid = $_SESSION['userid'];

	//Get Post Data
	$checked_out = $_POST['checked_out'];
	$cartid = $_POST['cartid'];
	
	//Check if required are set, if not, set ship_error session variable, check on payment page and if bad redirect to shipping.php
	if($checked_out == '1' && $userid != NULL)
	{
		//The query for the products of the category
		$cart_query = mysql_query("SELECT * FROM in_cart WHERE cart_id='$cart_id' || cart_id='$cartid'") OR die(mysql_error());

		//mySQL Error echo
		if(!$cart_query)
		{
			$error = '$cart_query: '.mysql_error();
		}
		elseif (mysql_num_rows($cart_query) == FALSE)
		{
			echo "<h1>You have no items in your cart.</h1>";
		}
		else
		{
			//Here we loop through to grab the products and rent them if possible
			while($cart_rows = mysql_fetch_array($cart_query))
			{
				$prod_id = $cart_rows['prod_id'];
				$prod_price = $cart_rows['prod_price'];
				
				//Get the product info for the current product
				$rent_sql = mysql_query("SELECT rental_id FROM product_rentals WHERE prod_id='$prod_id' && price_id='$prod_price' && in_out_status='1'") OR die(mysql_error());
				$rent_rows = mysql_num_rows($rent_sql);
				
				//Found an object to rent
				if($rent_rows != FALSE)
				{
					$rent_info = mysql_fetch_array ($rent_sql);
					$rental_id = $rent_info['rental_id'];
					
					//MySQL DateTime field formatted date
					$mysql_datetime = date( 'Y-m-d H:i:s');
					
					//Update rental copy with new data
					$sql = mysql_query("UPDATE product_rentals SET in_out_date='$mysql_datetime', in_out_status='0', last_out_by='$userid' WHERE rental_id='$rental_id'") OR die(mysql_error());
					
					//Insert history
					$sql = mysql_query("INSERT INTO rental_history (rental_id, prod_id, price_id, renter_userid, checkout_date) VALUES('$rental_id', '$prod_id', '$prod_price', '$userid', '$mysql_datetime')") OR die(mysql_error());
				}
			}
		}
	}
?>