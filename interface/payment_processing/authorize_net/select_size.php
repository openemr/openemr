<!doctype html>
<html>
  <head>
    <title>Your Store</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <h1>Your Store</h1>
    <h2>Select Size</h2>
    <form method="post" action="review_order.php">
      <fieldset class="centered">
        <div>
          <label class="orange">
            <img src="mug_small.png">
            S
          </label>
          <input type="radio" name="size" value="small">
          <br>
          $1.99
        </div> 
        <div>
          <label class="orange">
            <img src="mug_medium.png">
            M
          </label>
          <input type="radio" name="size" value="medium">
          <br>
          $2.99
        </div> 
        <div>
          <label class="orange">
            <img src="mug_large.png">
            L
          </label>
          <input type="radio" name="size" value="large">
          <br>
          $3.99
        </div> 
      </fieldset>
      <input type="submit" class="submit" value="Continue">
    </form>
  </body>
</html>
