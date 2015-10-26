<!doctype html>
<html>
  <head>
    <title>Your Store</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <h1>Your Store</h1>
    <h2>Refund Processed</h2>
    <h3>Your transaction ID:</h3>
    <div class="id"><?php echo htmlentities($_GET['transaction_id'])?></div>
    <form method="get" action="index.php">
      <input type="submit" class="submit" value="Start Over">
    </form>
  </body>
</html>
