<form action="{$paypal_url}/us/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_cart">
<input type="hidden" name="upload" value="1">
<input type="hidden" name="business" value="{$paypal_business}">
<input type="hidden" name="currency_code" value="{$paypal_currency}">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="return" value="{$store_url}methods_pay/paypal/paypal_pdt.php?cart_id={$cart_id}&userid={$userid}"> 