  <tr>
	<td><center><input name="{$in_id}" type="text" maxlength="3" size="3" value="{$prod_qty}"></center></td>
	<td><b>{$prod_name} ({$release_year}) - {$license}</b> {$short_descrip}</td>
	<td><center>{$cost}</center></td>
	<td><center>${$prod_cost}</center></td>
	<input type="hidden" name="in_cart[]" value="{$in_id}">
  </tr>

  <tr>
    <td class="row_separator" colspan="4"></td>
  </tr>