<?php
session_start();
header("Location:methods_pay/example/checkout.php");

//Includes
include 'includes/db_config.php';
include 'includes/db_connect.php';

//Continue if there is a cart id
if(isset($_SESSION['cart_id']))
{
	//Grab the cart id
	$cart_id = $_SESSION['cart_id'];
	$userid = $_SESSION['userid'];

	//Get Post Data
	$name = $_POST['name'];
	$phone = $_POST['phone'];
	$email = $_POST['email'];
	$address1 = $_POST['address1'];
	$address2 = $_POST['address2'];
	$country = $_POST['country'];
	$city = $_POST['city'];
	$state = $_POST['state'];
	$zip = $_POST['zip'];
	$special = $_POST['special'];

	//Check if required are set, if not, set ship_error session variable, check on payment page and if bad redirect to shipping.php
	if(!$name || !$address1 || !$country || !$city || !$state || !$zip)
	{
		$_SESSION['ship_error'] = '1';
		
		//For Debug
		//echo "Shipping Error";
	}

	//Check if an address exists yet or not
	$shipping_sql = mysql_query("SELECT name, phone, email, address1, address2, city, state, zip, special FROM user_addresses WHERE userid=$userid");
	$shipping_row = mysql_num_rows($shipping_sql);
	
	//If false, no address, so insert
	if($shipping_row == FALSE)
	{
		//Insert into database
		mysql_query("INSERT INTO user_addresses (userid, name, phone, email, address1, address2, country, city, state, zip, special) VALUES ('$userid', '$name', '$phone', '$email', '$address1', '$address2', '$country', '$city', '$state', '$zip', '$special')") OR die(mysql_error());
	}
	else
	{
		//Update existing address with Shipping Information that was sent
		mysql_query("UPDATE user_addresses SET name='$name', phone='$phone', email='$email', address1='$address1', address2='$address2', country='$country', city='$city', state='$state', zip='$zip', special='$special' WHERE userid='$userid'") OR die(mysql_error());
	}
}
?>