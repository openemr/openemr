<?php require "../../../globals.php"?>

<!doctype html>
<html lang="en">
    <head>
        <title>Patient Dashboard</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="./app/assets/css/app.css" type="text/css">
        <?php if($_SESSION["language_direction"] == "rtl"): ?>
        <link rel="stylesheet" href="./app/node_modules/bootstrap-v4-rtl/dist/css/bootstrap-rtl.min.css" type="text/css">
        <?php endif;?>
    </head>

    </script>
    <body dir="<?php echo $_SESSION["language_direction"];?>">

        <div id="app">
        </div>
        <script>
        var webroot = '<?php echo $GLOBALS['webroot'] ?>';
        var dateFormat = '<?php echo DateFormatRead('validateJS'); ?>'
        </script>
        <script type="text/javascript" src="./app/assets/bundle/main.bundle.js" ></script>

    </body>
</html>
