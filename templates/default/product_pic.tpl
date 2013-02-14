<center>
<img src="{$store_url}images/products/{$prod_pic}" alt="{$prod_name}">
</center>

<br>

<div class="container">
<div class="prod_row">
    <div class="prod_info">
	{$long_descrip}
	
	{if $release_year != NULL}Release Year: {$release_year}<br>{/if}
	</div>
</div>
    
<br>

    <center>
    {if $free != NULL}{$free}<br>{/if}
        
    {if $single != NULL}
        <b>${$cost}</b><br>
        <a href="add_cart.php?pricing={$single}"><img src="images/add_to_cart.png" alt="Add to cart"></a><br>
    {/if}
        
    {if $pricing != NULL}{$pricing}<br>{/if}
    </center>
</div>