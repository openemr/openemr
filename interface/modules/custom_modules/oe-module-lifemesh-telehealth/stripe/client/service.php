<!--
  ~
  ~ @package      OpenEMR
  ~ @link               https://www.open-emr.org
  ~
  ~ @author    Sherwin Gaddis <sherwingaddis@gmail.com>
  ~ @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
  ~ @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
  ~
  -->
<?php
$ignoreAuth = true;
// Set $sessionAllowWrite to true to prevent session concurrency issues during authorization related code
$sessionAllowWrite = true;

require_once "../../../../../globals.php";
use OpenEMR\Core\Header;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php Header::setupHeader(); ?>
    <meta charset="utf-8" />
    <title>Stripe Checkout Sample</title>
    <meta name="description" content="A demo of Stripe Payment Intents" />

    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="css/normalize.css" />
    <link rel="stylesheet" href="css/global.css" />
    <!-- Load Stripe.js on your website. -->
    <script src="https://js.stripe.com/v3/"></script>

</head>

<body>
<div class="container">
<div class="togethere-background"></div>
<div class="sr-root">
    <div class="sr-main">
        <header class="sr-header">
            <div class="sr-header__logo"></div>
        </header>
        <h1>Choose a telehealth plan</h1>

        <div class="price-table-container">
            <section>
                <form action="../server/create-checkout-session.php" method="POST">
                    <input type="hidden" id="basicPrice" name="priceId" value="price_1J7AZwGznLM7QeknX6RjNLQ4">
                    <div class="name">Starter package</div>
                    <div class="price">$10</div>
                    <div class="duration">per month</div>
                    <button class="btn btn-primary" id="basic-plan-btn">Select</button>
                </form>
            </section>
            <section>
                <form action="../server/create-checkout-session.php" method="POST">
                    <input type="hidden" id="proPrice" name="priceId" value="price_1JE70mGznLM7Qeknl5dDNWi4">
                    <div class="name">Professional package</div>
                    <div class="price">$18</div>
                    <div class="duration">per month</div>
                    <button class="btn btn-primary" id="pro-plan-btn">Select</button>
                </form>
            </section>
        </div>
    </div>
</div>
</div>
<div id="error-message" class="error-message"></div>
</body>
</html>

