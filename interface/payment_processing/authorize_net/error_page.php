<!doctype html>
<html>
  <head>
    <title>Your Store</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <h1>Your Store</h1>
    <h2>Error!</h2>
    <div class="error">
      <h3>We're sorry, but we can't process your order at this time due to the following error:</h3>
      <?php echo htmlentities($_GET['response_reason_text'])?>
      <table>
        <tr>
          <td>response code</td>
          <td><?php echo htmlentities($_GET['response_code'])?></td>
        </tr>
        <tr>
          <td>response reason code</td>
          <td><?php echo htmlentities($_GET['response_reason_code'])?></td>
        </tr>
      </table>
    </div>
    <form method="get" action="index.php">
      <input type="submit" class="submit" value="Start Over">
    </form>
  </body>
</html>
