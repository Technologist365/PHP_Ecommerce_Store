<?php
		if(!isset($_REQUEST['logmeout']))
			{
			echo "
			<p>Are you sure you want to logout?</p>	
			<br>
			<center><p><a href=logout.php?logmeout>Yes</a> | <a href=\./index.php\">No</a></p></center>
			"; 
			} 
			
		else 
			{
			session_destroy();
			if(!session_is_registered('username'))
				{
				echo "<center><p class=\"ERROR\">You have been logged out!</p></center><br>";
				}
			}
?>