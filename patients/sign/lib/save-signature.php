<?php
/** 
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */
 
$sanitize_all_escapes=true;
$fake_register_globals=false;

$ignoreAuth = true;
require_once ( "../../../interface/globals.php" );
require_once 'sigconvert.php';
$errors = array ();
$signer = filter_input( INPUT_POST, 'signer', FILTER_SANITIZE_STRING );
$type = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
$pid = filter_input( INPUT_POST, 'pid', FILTER_SANITIZE_STRING );
$output = filter_input( INPUT_POST, 'output', FILTER_UNSAFE_RAW );
$user = filter_input( INPUT_POST, 'user', FILTER_UNSAFE_RAW );

if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
    if( $type == 'admin-signature' ) $signer = $user;
    if( ! json_decode( $output ) ){
        exit();
    }
/* Don't need at present
    if( $pid > 0 ) $resizedFile = './../../patient_documents/signed/current/' . $pid . '_master.png';
    else $resizedFile = './../../patient_documents/signed/current/' . $signer . '_master.png';
 */
    $svgsig = '';
    if( empty( $errors ) ){
        try{
            $svg = new sigToSvg( $output, array (
                    'penWidth' => 6
            ) );
            $svgsig = $svg->getImage();
            $r = $svg->max[1] / $svg->max[0];
            $x = round( $svg->max[0] * $r );
            $y = round( $svg->max[1] * $r );
            $img = sigJsonToImage( $output, array (
                    'imageSize' => array (
                            $svg->max[0],
                            $svg->max[1]
                    )
            ) );
            ob_start();
            imagepng( $img );
            $image = ob_get_contents();
            ob_clean();
            $image_png = smart_resize_image( null, $image, $svg->max[0], 75, true, 'return', false, false, 100, false );
            //imagepng( $image_png, $resizedFile, 0 );
            imagepng( $image_png );
            $image = ob_get_contents();
            ob_end_clean();
            imagedestroy( $img );
            imagedestroy( $image_png );
            $image_data = base64_encode( $image );
        } catch( Exception $e ){
            die( $e->getMessage() );
        }
    }
    // No validation errors exist, so we can start the database stuff
    if( empty( $errors ) ){
        $sig_hash = sha1( $output );
        $created = time();
        $ip = $_SERVER['REMOTE_ADDR'];
        $status = 'filed';
        $lastmod = date( 'Y-m-d H:i:s' );

        $qstr = "UPDATE onsite_signatures SET pid=?,lastmod=?,status=?, user=?, signature=?, sig_hash=?, ip=?,sig_image=? WHERE pid=? && user=?";
       try{
       	$rcnt = sqlQuery( $qstr, array($pid,$lastmod,$status,$user,$svgsig,$sig_hash,$ip,$image_data,$pid,$user) );
        } catch( Exception $e ){
            print $e . message;
        }
        if( $rcnt == false ){
            $qstr = "INSERT INTO onsite_signatures (pid,lastmod,status,type,user,signator, signature, sig_hash, ip, created, sig_image) VALUES (?,?,?,?,?,?,?,?,?,?,?) ";
            try{
            	sqlStatement( $qstr, array($pid , $lastmod, $status,$type, $user, $signer, $svgsig, $sig_hash, $ip, $created, $image_data) );
            } catch( Exception $e ){
                print $e . message;
            }
        }
    }
    print json_encode( 'Done' );
}
