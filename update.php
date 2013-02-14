<?php
session_start();
header("Location:view_cart.php");

//Includes
include 'includes/db_config.php';
include 'includes/db_connect.php';

//A foreach loop for each item in the array, get the values and set it as $in_id because they were the ones selected for deletion
foreach($_POST['in_cart'] as $num => $in_id)
{
	//Get the Quantity based on the id in the array
	$quantity = $_POST["$in_id"];
	
	if($quantity > 0)
	{
		//Then we update the database
		$quantity = '1';
		mysql_query("UPDATE in_cart SET prod_qty='$quantity' WHERE in_id='$in_id'");
	}
	else
	{
		//Then we remove the items from teh database
		mysql_query("DELETE FROM in_cart WHERE in_id='$in_id'");
	}
}
?> 