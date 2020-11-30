<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if ($oauthLogin !== true) {
    echo xlt("Error. Not authorized");
    exit();
}

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

?>
<html>
<head>
    <title><?php echo xlt("OpenEMR Authorization"); ?></title>
    <?php Header::setupHeader(); ?>
</head>
<body class="container bg-dark">
    <div class="row h-100 w-100 justify-content-center align-items-center">
        <div class="col-sm-5">
            <div class="card">
                <div class="card-body">
                    <?php if (!$authorize) { ?>
                        <h4 class="card-title mb-4 mt-1"><?php echo xlt("Sign In"); ?></h4>
                    <?php } else { ?>
                        <h4 class="card-title mb-4 mt-1"><?php echo xlt("Requested Information Shared"); ?></h4>
                    <?php } ?>
                    <p>
                    <?php if ($authorize) {
                        foreach ($_SESSION['claims'] as $key => $value) {
                            $key_n = explode('_', $key);
                            if (stripos($_SESSION['scopes'], $key_n[0]) === false) {
                                continue;
                            }
                            if ((int)$value === 1) {
                                $value = 'True';
                            }
                            $key = ucwords(str_replace("_", " ", $key));
                            echo "<label class='col-form-label'><b>" . text($key) . ":</b>  " . text($value) . "</label><br />\n";
                        }
                    } ?>
                    </p>
                    <hr />
                    <form method="post" action="<?php echo $redirect ?>">
                        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('oauth2')); ?>" />
                        <?php if (!$authorize) { ?>
                        <div class="form-group">
                            <input class="form-control" placeholder="<?php echo xla("Email if required"); ?>" type="email" name="email">
                        </div>
                        <div class="form-group"><!-- TODO: remove test values -->
                            <input class="form-control" placeholder="<?php echo xla("Registered username"); ?>" type="text" name="username" value="">
                        </div>
                        <div class="form-group">
                            <input class="form-control" placeholder="******" type="password" name="password" value="">
                        </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-md-12">
                                <?php if ($authorize) { ?>
                                <div class="btn-group">
                                    <button type="submit" name="proceed" value="1" class="btn btn-primary"><?php echo xlt("Authorize"); ?></button>
                                </div>
                                <?php } else { ?>
                                <div class="btn-group">
                                    <button type="submit" name="user_role" class="btn btn-outline-primary" value="api"><i class="fa fa-sign-in-alt"></i><?php echo xlt("Login via OpenEMR"); ?></button>
                                    <button type="submit" name="user_role" class="btn btn-outline-info" value="portal-api"><i class="fa fa-sign-in-alt"></i><?php echo xlt("Login as Patient"); ?></button>
                                </div>
                                <div class="form-check-inline float-right">
                                    <input class="form-check-input" type="checkbox" name="persist_login" id="persist_login" value="1">
                                    <label for="persist_login" class="form-check-label"><?php echo xlt("Remember Me"); ?></label>
                                </div>
                            <?php } ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
