<?php
/**
 * Controller to provide client with new RSA public keys to transmit encrypted data.
 *
 * <pre>
 * This is a simple wrapper so a browser can interface with the rsa_key_manager class.
 * When the browser requests this page, a new key pair is generated.
 * The public key is transmitted to the browser as a javascript compatible string, while the private key is stored
 * in the database so the server can decode encrypted messages sent by the browser in subsequent requests.
 * 
 * A typical data flow will be as follows.
 * 1. User fills out form on the browser and submits.  
 * 2. Browser makes ajax request to this page.
 * 3. Server generates a public key that it returns to the browser and a corresponding private key which it stores in the database.
 * 3. Browser uses library/js/rsa.js routines to encrypt the sensitive data with the public key retrieved in step 2.
 * 4. Browser does POST or GET to server.  REQUEST includes the public key used as well as the encrypted data.
 * 5. Server uses public key in REQUEST to lookup corresponding private key and initialize rsa_key_manager
 * 6. Server can decrypt messages from the browser (such as clear text passwords).
 * 7. Server deletes that key pair from the database, so subsequent requests attempting to reuse the same message will fail.
 *      (Deletion/garbage collection actually happens as part of initialization in step 5)
 * </pre>
 * 
 * 
 * Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */
    $ignoreAuth=true;  // A user needs to be able to get an RSA public key for transmission of password to the server before authentication.
    
    //SANITIZE ALL ESCAPES
    $sanitize_all_escapes=true;

    //STOP FAKE REGISTER GLOBALS
    $fake_register_globals=false;
    
    require_once("../../interface/globals.php");
    require_once("../authentication/rsa.php");
    
    
    $key_manager=new rsa_key_manager;
    $key_manager->initialize();
    echo $key_manager->get_pubKeyJS();
?>
