<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

 include 'anet_php_sdk/AuthorizeNet.php';
       $sale = new AuthorizeNetAIM(MERCHANT_API_LOGIN_ID, MERCHANT_TRANSACTION_KEY);
       $response = $sale->authorizeAndCapture("9.99", '6011000000000012', '04/16');