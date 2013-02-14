    {if $unfilled}
    <p>Please fill out the below form as completely as possible.<br>
    Please include the following details where possible. Your full name, order number, and customer login name.</p>
    <br>
    {/if}
    
    {if $con_error}
    <h1>Incorrect Captcha, please try again.</h1>
    <br>
    {/if}
    
    {if $success}
    <h1>Your message has been successfully sent.</h1>
    <br>
    {/if}
    
    <p>Fields with an asterisk * are required.</p>
    <br>
                
    <form method="post" action="contact.php">
    <table width="">
      <tr>
        <td width="100px" align="left" valign="top">Name*</td>
        <td width="" align="left" valign="top"><input name="name" type="text" value="{$name}"></td>
      </tr>
      
      <tr>
        <td width="100px" align="left" valign="top">E-Mail*</td>
        <td width="" align="left" valign="top"><input name="email" type="text" value="{$email}"></td>
      </tr>
      
      <tr>
        <td width="100px" align="left" valign="top">Topic</td>
        <td width="" align="left" valign="top">
          <select name="topic">
            <option value="Order Problem" selected="selected">Order Problem</option>
            <option value="Order Status">Order Status</option>
            <option value="Website Problem">Website Problem</option>
            <option value="Product Request">Product Request</option>
            <option value="Other">Other</option>
          </select>
        </td>
      </tr>

      <tr>
        <td width="100px" align="left" valign="top">Message*</td>
        <td width="" align="left" valign="top"><textarea name="message" cols="40" rows="10">{$message}</textarea></td>
      </tr>

      <tr>
        <td>Spam Block</td>
        <td width="" align="left" valign="top">{$recaptcha}</td>
      </tr>
          
      <tr>  
        <td></td>
        <td><input type="submit" name="Submit" value="Send Message"></td>
      </tr>
    </table>
    </form>