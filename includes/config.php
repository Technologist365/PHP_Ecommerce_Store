<?php
/*****************************************************************
~~~~~~~~~~~~~~~~~Store information for the script~~~~~~~~~~~~~~~~~
*****************************************************************/
//Create the array
$var = array();

//Query the database and grab all the text variables in the table, stick them into an array
$config_text_query = "SELECT * FROM config_text WHERE var_name='store_url' OR var_name='store_title' OR var_name='cubecrusher_version'";
$config_text_rows = mysql_query($config_text_query);

//mySQL Error echo
if(!$config_text_rows)
	{
	echo "$config_text_rows: ".mysql_error();
	}
elseif (mysql_num_rows($config_text_rows) == FALSE)
	{
	echo "<h1>Script configuration not present!</h1> Perhaps you need to install?";
	}
else
	{
	//Stick all the text variables into an array
	while($config_text_vars = mysql_fetch_array($config_text_rows))
		{
		$var[$config_text_vars['var_name']] = $config_text_vars['value'];
		}
	}

/*****************************************************************
~~Assign some of the configuration variables to smarty variables~~
*****************************************************************/
//URL where /store/ is on your server, for example http://www.somewebsite.com/store/
//The trailing slash is required, DO NOT FORGET IT
$store_url = $var['store_url'];
$smarty->assign('store_url', $var['store_url']);

//Store title, appears in the <title> tags
$store_title = $var['store_title'];
$smarty->assign('store_title', $store_title);

//Store version, appears in the footer on all pages
$cubecrusher_version = $var['cubecrusher_version'];
$smarty->assign('cubecrusher_version', $cubecrusher_version);
?>