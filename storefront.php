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
	
	if($installed == "1")
	{
		if(file_exists('install/') == FALSE)
		{	
			//Configuration and database includes
			include 'includes/config.php';
			include 'includes/header.php';
		
			//Grab the category Id from the URL if there is one
			$cat_id = $_REQUEST['cat_id'];
		
			//Get the different product categories first, if there is am image for them, display it to the left of the Name and Descrip, if a descrip exists
			//If a category id has been set use a different query, so that you only display that category and sub categories rather than all categories and sub categories
			if($cat_id != NULL)
				{
				$query2 = "SELECT cat_id, cat_name, cat_descrip, cat_thumb, sub_cat FROM category WHERE cat_id=$cat_id AND active='1'";
				
				//Grab info about category, since this is a specific category view
				$store_sql = mysql_query("SELECT cat_name, long_descrip FROM category WHERE cat_id=$cat_id  AND active='1'");
				$store_home = mysql_fetch_array ($store_sql);
				$smarty->assign('long_descrip', $store_home['long_descrip']);
				$smarty->assign('page_name', $store_home['cat_name']);
				}
			else
				{
				//Where category ID is NOT the main store category containing frontpage store information
				$query2 = "SELECT cat_id, cat_name, cat_descrip, cat_thumb, sub_cat FROM category WHERE parent_id='1' AND cat_id!='1' AND active='1'";
				
				//Grab info about store, category 1, the front page, since this is the main store page
				$store_sql = mysql_query("SELECT cat_name, long_descrip FROM category WHERE cat_id='1'");
				$store_home = mysql_fetch_array ($store_sql);
				$smarty->assign('long_descrip', $store_home['long_descrip']);
				$smarty->assign('page_name', $store_home['cat_name']);
				}
			
			//Output the top of the page
			$smarty->display('header.tpl');
			$smarty->display('content_top.tpl');
		
			$prod_cats = mysql_query($query2);
			
			//mySQL Error echo
			if(!$prod_cats)
				{
				echo "$prod_cats: ".mysql_error();
				}
			elseif (mysql_num_rows($prod_cats) == FALSE)
				{
				echo "<h1>There are no product categories, this is perhaps a bug, please contact the administrator.</h1>";
				}
			else
				{
				//Here we start looping through all the different product categories grabbing their info from the database
				while($cat_rows = mysql_fetch_array($prod_cats))
					{
					//Variables from the databasel, assigned to smarty variables
					$cat_id = $cat_rows['cat_id'];
					$cat_name = $cat_rows['cat_name'];
					$cat_descrip = $cat_rows['cat_descrip'];
					$smarty->assign('cat_id',$cat_rows['cat_id']);
					$smarty->assign('cat_name',$cat_rows['cat_name']);
					$smarty->assign('cat_descrip',$cat_rows['cat_descrip']);
					$smarty->assign('cat_thumb',$cat_rows['cat_thumb']);
					$sub_cat = $cat_rows['sub_cat'];
					
					//If there is not a subcategory, just display the category info within the category so it doesnt look blank
					//If there is a subcategory see if there is a short description, if there is, display it with teh category title
					if($sub_cat == '0')
						{
						$smarty->assign('page','view_category.php');
						$smarty->assign('category_info', "<a href=\"view_category.php?cat_id=$cat_id\"><h3>$cat_name</h3></a>");
						}
					else
						{
						$smarty->assign('page','storefront.php');
						
						if($cat_descrip != NULL)
							{
							$smarty->assign('category_info', "<a href=\"storefront.php?cat_id=$cat_id\"><h3>$cat_name $cat_descrip</h3></a>");
							}
						else
							{
							$smarty->assign('category_info', "<a href=\"storefront.php?cat_id=$cat_id\"><h3>$cat_name</h3></a>");
							}
						} 
						
					//Beginning of product category thing
					$smarty->display('category_top.tpl'); 
					$smarty->display('contain_begin.tpl'); 
					
					if($sub_cat == '0')
						{
						//If there is a category thumbnail, display it, else dont display it
						//If there is a category description, display it, else dont display it
						if($cat_rows['cat_thumb'] != NULL)
							{
							$smarty->display('subcat_thumb.tpl');
							}
						else
							{
							$smarty->display('subcat_nothumb.tpl');
							}
						}
					
					//Here begins the sub category query and loop, pretty much the same as the previous stuff for the main categories, the sql query is different mainly
					if($sub_cat == '1')
						{
						//Get the sub categories of the main category
						$sub_cats = mysql_query("SELECT cat_id, cat_name, cat_descrip, cat_thumb FROM category WHERE parent_id=$cat_id  AND active='1'");
						
						//mySQL Error echo
						if(!$sub_cats)
							{
							echo "$sub_cats: mysql_error()";
							}
						elseif (mysql_num_rows($sub_cats) == FALSE)
							{
							echo "<h1>There was an unknown error retrieving the sub categories</h1>";
							}
						else
							{
							//Here we loop through to grab the subcategories for the present main category
							while($sub_rows = mysql_fetch_array($sub_cats))
								{
								$smarty->assign('cat_id',$sub_rows['cat_id']);
								$smarty->assign('cat_name',$sub_rows['cat_name']);
								$smarty->assign('cat_descrip',$sub_rows['cat_descrip']);
								$smarty->assign('cat_thumb',$sub_rows['cat_thumb']);
								$smarty->assign('page','view_category.php');
								
								//If there is a subcategory thumbnail, display it, else dont display it
								//If there is a subcategory description, display it, else dont display it
								if($sub_rows['cat_thumb'] != NULL)
									{
									$smarty->display('subcat_thumb.tpl');
									}
								else
									{
									$smarty->display('subcat_nothumb.tpl');
									}
								}//Ends the while loop that sticks in subcategories
							}//Ends the mysql error checks
						}//End subcategory check
					
					//End of Product category thing
					$smarty->display('contain_end.tpl'); 
					$smarty->display('category_bott.tpl');
				}//Ends the while loop that sticks in categories
				
				/*
				//Initialize $counter to 1
				$counter = 1;
				
				//Beginning of product category thing
				$smarty->display('contain_begin.tpl'); 
			
				//Here we start looping through all the different product categories grabbing their info from the database
				while($cat_rows = mysql_fetch_array($prod_cats))
					{
					//Variables from the databasel, assigned to smarty variables
					$cat_id = $cat_rows['cat_id'];
					$cat_name = $cat_rows['cat_name'];
					$cat_descrip = $cat_rows['cat_descrip'];
					$smarty->assign('cat_id',$cat_rows['cat_id']);
					$smarty->assign('cat_name',$cat_rows['cat_name']);
					$smarty->assign('cat_descrip',$cat_rows['cat_descrip']);
					$smarty->assign('cat_thumb',$cat_rows['cat_thumb']);
					$sub_cat = $cat_rows['sub_cat'];
					
					$smarty->assign('page','view_category.php');
					
					//If there is not a subcategory, just display the category info within the category so it doesnt look blank
					//If there is a subcategory see if there is a short description, if there is, display it with teh category title
					if($sub_cat == '0')
						{
						$smarty->assign('page','view_category.php');
						$smarty->assign('category_info', "<a href=\"view_category.php?cat_id=$cat_id\"><h3>$cat_name</h3></a>");
						}
					else
						{
						$smarty->assign('page','storefront.php');
						
						if($cat_descrip != NULL)
							{
							$smarty->assign('category_info', "<a href=\"storefront.php?cat_id=$cat_id\"><h3>$cat_name</h3></a>$cat_descrip");
							}
						else
							{
							$smarty->assign('category_info', "<a href=\"storefront.php?cat_id=$cat_id\"><h3>$cat_name</h3></a>");
							}
						} 
					
					//Assign counter
					$smarty->assign('counter', $counter);
					
					//Check and increment counter
					if ($counter == 3)
					{
						$counter = 1;
					}
					else
					{
						$counter++;
					}
					
					//If there is a category thumbnail, display it, else dont display it
					//If there is a category description, display it, else dont display it
					if($cat_rows['cat_thumb'] != NULL)
						{
						$smarty->display('subcat_thumb.tpl');
						}
					else
						{
						$smarty->display('subcat_nothumb.tpl');
						}
					
					//Here begins the sub category query and loop, pretty much the same as the previous stuff for the main categories, the sql query is different mainly
					if($sub_cat == '1')
						{
						//Get the sub categories of the main category
						$sub_cats = mysql_query("SELECT cat_id, cat_name, cat_descrip, cat_thumb FROM category WHERE parent_id=$cat_id  AND active='1'");
						
						//mySQL Error echo
						if(!$sub_cats)
							{
							echo "$sub_cats: ".mysql_error();
							}
						elseif (mysql_num_rows($sub_cats) == FALSE)
							{
							echo "<h1>There was an unknown error retrieving the sub categories</h1>";
							}
						else
							{
							//Here we loop through to grab the subcategories for the present main category
							while($sub_rows = mysql_fetch_array($sub_cats))
								{
								$smarty->assign('cat_id',$sub_rows['cat_id']);
								$smarty->assign('cat_name',$sub_rows['cat_name']);
								$smarty->assign('cat_descrip',$sub_rows['cat_descrip']);
								$smarty->assign('cat_thumb',$sub_rows['cat_thumb']);
								$smarty->assign('page','view_category.php');
								
								//If there is a subcategory thumbnail, display it, else dont display it
								//If there is a subcategory description, display it, else dont display it
								if($sub_rows['cat_thumb'] != NULL)
									{
									$smarty->display('subcat_thumb.tpl');
									}
								else
									{
									$smarty->display('subcat_nothumb.tpl');
									}
								}//Ends the while loop that sticks in subcategories
							}//Ends the mysql error checks
						}//End subcategory check
					}//Ends the while loop that sticks in categories
					
				//Beginning of product category thing
				$smarty->display('contain_end.tpl');	
				*/
				}//Ends the mysql error checks
			
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
			'db_config.php file from the /includes directory then restart the install here. <a href="install/install.php">Restart Install</a> <br>' .
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
