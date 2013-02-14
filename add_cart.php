<?php 
session_start();
header("Location:view_cart.php");

//Includes
include 'includes/db_config.php';
include 'includes/db_connect.php';

//Check if the cart id has a value or not.
//If there is no value a new cart must be created then an item added to it
//If there is a value then we must see if this item is already in the cart,if it is already 
//   in the cart add 1 to the quantity, if it is not in the cart then we add it to the cart.
//After the page completes its stuff the user is sent to view_cart.php to review the cart
if(empty($_SESSION['cart_id']))
{
	$accessed = time();
	$sql = mysql_query("INSERT INTO carts (accessed) VALUES('$accessed')");
	$cart_id = mysql_insert_id();
	
	//Since there used to not be a cart id we need to session it
	$_SESSION['cart_id'] = $cart_id;
}
else
{
	//Grab the sessioned cart id
	$cart_id = $_SESSION['cart_id'];	
}

//So now there definitely is a cart id value
//Lets grab the product id based upon the price sent
$prod_id = $_POST['prod_id'];
$price_id = $_POST['pricing'];

//Post data is most likely, however if there is no post data try request(So you can have links that can add to cart)
//Useful for if you just need to add a single item that only has 1 possible price
if(empty($price_id))
{
	$price_id = $_REQUEST['pricing'];
}

/*
//Find the product that this particular price is for
$prod_sql = mysql_query("SELECT prod_id FROM pricing WHERE price_id='$price_id'");
$prod_info = mysql_fetch_array ($prod_sql);
$prod_id = $prod_info['prod_id'];
*/

//Now lets insert the item into the table of items that are in someones cart
$prod_sql = mysql_query("SELECT prod_qty FROM in_cart WHERE cart_id='$cart_id' AND prod_id='$prod_id' AND prod_price='$price_id'");

//No existing product in this particular cart with this particular pricing so insert a new one
//Can't have a result of more than 1, so anything that isn't 1 means we don't have this item in the cart yet
if(mysql_num_rows($prod_sql) !== 1)
{
	$sql = mysql_query("INSERT INTO in_cart (cart_id, prod_id, prod_price, prod_qty) VALUES('$cart_id', '$prod_id', '$price_id', '1')");
}
//This product at this price is already in the current cart so lets update that quantity
else
{
	//We need to check the quantity and update it since this product is already in the cart
	$prod_rows = mysql_fetch_array($prod_sql);
	$prod_qty = $prod_rows['prod_qty'];
	$prod_qty = '1';
	
	$sql = mysql_query("UPDATE in_cart SET prod_qty=$prod_qty WHERE cart_id='$cart_id' AND prod_id='$prod_id' AND prod_price='$price_id'");
}
?>
