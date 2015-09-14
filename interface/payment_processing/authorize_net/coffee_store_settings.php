<?php

require_once 'config.php';


$coffee_store_relay_url = $site_root . "process_sale.php";

/**
 * Sets the possible coffee choices.
 */
$prices_array = array(
  "small" => "1.99",
  "medium" => "2.99",
  "large" => "3.99",
);
$size = "small"; // Set Default Size
if (isset($_POST['size']) && isset($prices_array[$_POST['size']])) {
  $size = $_POST['size'];
}
$price = $prices_array[$size]; // Set Price
$tax = number_format($price * .095,2); // Set tax
$amount = number_format($price + $tax,2); // Set total amount