  {if $counter==1}
  <div class="prod_row">
    <div class="prod_left">
        <center>
        <a href="view_product.php?prod_id={$prod_id}"><img src="images/products/{$prod_thumb}" alt="{$prod_name} ({$release_year})"></a>
        
        {if $prod_note != NULL}
            <p class="small">{$prod_note}<br></p>
        {/if}
        
        {if $prod_short != NULL}
            {$prod_short}<br>
        {/if}
        
        {if $free != NULL}{$free}<br>{/if}
        
        {if $single != NULL}
            <b>${$cost}</b><br>
            <a href="add_cart.php?pricing={$single}"><img src="images/add_to_cart.png" alt="Add to cart"></a><br>
        {/if}
        
        {if $pricing != NULL}{$pricing}<br>{/if}
        </center>
    </div>
    {/if}
    
    {if $counter==2}
    <div class="prod_middle">
        <center>
        <a href="view_product.php?prod_id={$prod_id}"><img src="images/products/{$prod_thumb}" alt="{$prod_name} ({$release_year})"></a>
        
        {if $prod_note != NULL}
            <p class="small">{$prod_note}<br></p>
        {/if}
        
        {if $prod_short != NULL}
            {$prod_short}<br>
        {/if}
        
        {if $free != NULL}{$free}<br>{/if}
        
        {if $single != NULL}
            <b>${$cost}</b><br>
            <a href="add_cart.php?pricing={$single}"><img src="images/add_to_cart.png" alt="Add to cart"></a><br>
        {/if}
        
        {if $pricing != NULL}{$pricing}<br>{/if}
        </center>            
    </div>
    {/if}
    
    {if $counter==3}
    <div class="prod_right">
        <center>
        <a href="view_product.php?prod_id={$prod_id}"><img src="images/products/{$prod_thumb}" alt="{$prod_name} ({$release_year})"></a>
        
        {if $prod_note != NULL}
            <p class="small">{$prod_note}<br></p>
        {/if}
        
        {if $prod_short != NULL}
            {$prod_short}<br>
        {/if}
        
        {if $free != NULL}{$free}<br>{/if}
        
        {if $single != NULL}
            <b>${$cost}</b><br>
            <a href="add_cart.php?pricing={$single}"><img src="images/add_to_cart.png" alt="Add to cart"></a><br>
        {/if}
        
        {if $pricing != NULL}{$pricing}<br>{/if}
        </center>
    </div>
  </div>
  
  <div class="div_seperator"><br><br></div>
  {/if}
