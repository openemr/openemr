<?php require_once 'coffee_store_settings.php';?>
<!doctype html>
<html>
  <head>
    <title>Your Store</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <h1>Your Store</h1>
    <h2>Order</h2>
    <table>
      <tfoot>
        <tr>
          <td>Total</td>
          <td>$<?php echo $amount?></td>
        </tr>
      </tfoot>
      <tbody>
        <tr>
          <td><?php echo ucfirst($size)?> Coffee</td>
          <td>$<?php echo $price?></td>
        </tr>
        <tr>
          <td>Tax (9.5%)</td>
          <td>$<?php echo $tax?></td>
        </tr>
      </tbody>
    </table>
    <form method="post" action="checkout_form.php">
      <input type="hidden" name="size" value="<?php echo $size?>">
      <input type="image" src="purchase.png" class="purchase">
     
    </form>
  </body>
</html>
