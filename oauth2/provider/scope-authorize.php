<?php

/**
 * scope-authorize.php Handles the display and submission of the scope authorization for the oauth2 form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\SessionUtil;

if ($oauthLogin !== true) {
    $message = xlt("Error. Not authorized");
    SessionUtil::oauthSessionCookieDestroy();
    echo $message;
    exit();
}

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

?>
<html>
<head>
    <title><?php echo xlt("OpenEMR Authorization"); ?></title>
    <?php Header::setupHeader(); ?>
    <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/u2f-api.js"></script>
</head>
<body class="container-fluid bg-dark">
<div class="row h-100 w-100 justify-content-center align-items-center">
    <div class="col-sm-6 bg-light text-dark">
        <div class="text-md-center">
            <h4 class="mb-4 mt-1"><?php echo xlt("Authorizing"); ?></h4>
        </div>
        <hr />
        <div class="row w-100">
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body pt-1">
                        <h5 class="card-title text-sm-center"><?php echo xlt("Scopes"); ?><hr /></h5>
                        <ul class="pl-2 mt-1">
                            <?php {
                                $scopes = explode(' ', $_SESSION['scopes']);
                            foreach ($scopes as $key) {
                                echo "<li class='col-text'><strong>" . text($key) . "</strong>  " . "</li>";
                            }
                            } ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body pt-1">
                        <h5 class="card-title text-sm-center"><?php echo xlt("Claims"); ?><hr /></h5>
                        <ul class="pl-2 mt-1">
                            <?php {
                            foreach ($_SESSION['claims'] as $key => $value) {
                                $key_n = explode('_', $key);
                                if (stripos($_SESSION['scopes'], $key_n[0]) === false) {
                                    continue;
                                }
                                if ((int)$value === 1) {
                                    $value = 'True';
                                }
                                $key = ucwords(str_replace("_", " ", $key));
                                echo "<li class='col-text'><strong>" . text($key) . ":</strong>  " . text($value) . "</li>";
                            }
                            } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <form method="post" name="userLogin" id="userLogin" action="<?php echo $redirect ?>">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('oauth2')); ?>" />
            <hr />
            <div class="row">
                <div class="col-md-12">
                    <div class="btn-group">
                        <button type="submit" name="proceed" value="1" class="btn btn-primary"><?php echo xlt("Authorize"); ?></button>
                    </div>
                    <div class="form-check-inline float-right">
                        <input class="form-check-input" type="checkbox" name="persist_login" id="persist_login" value="1">
                        <label for="persist_login" class="form-check-label"><?php echo xlt("Remember Me"); ?></label>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>