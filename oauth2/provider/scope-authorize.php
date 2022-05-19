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

use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$oauthLogin = $oauthLogin ?? null;
$offline_requested = $offline_requested ?? false;
$scopes = $scopes ?? [];
$scopeString = $scopeString ?? "";
$offline_access_date = $offline_access_date ?? "";
$userRole = $userRole ?? UuidUserAccount::USER_ROLE_PATIENT;
$clientName = $clientName ?? "";

if ($oauthLogin !== true) {
    $message = xlt("Error. Not authorized");
    SessionUtil::oauthSessionCookieDestroy();
    echo $message;
    exit();
}

$scopesByResource = [];
$otherScopes = [];
$scopeDescriptions = [];
$hiddenScopes = [];
$scopeRepository = new ScopeRepository();
foreach ($scopes as $scope) {
    // if there are any other scopes we want hidden we can put it here.
    if (in_array($scope, ['openid'])) {
        $hiddenScopes[] = $scope;
    } else if (in_array($scope, $scopeRepository->fhirRequiredSmartScopes())) {
        $otherScopes[$scope] = $scopeRepository->lookupDescriptionForScope($scope, $userRole == UuidUserAccount::USER_ROLE_PATIENT);
        continue;
    }

    $parts = explode("/", $scope);
    $context = reset($parts);
    $resourcePerm = $parts[1] ?? "";
    $resourcePermParts = explode(".", $resourcePerm);
    $resource = $resourcePermParts[0] ?? "";
    $permission = $resourcePermParts[1] ?? "";

    if (!empty($resource)) {
        $scopesByResource[$resource] = $scopesByResource[$resource] ?? ['permissions' => []];

        $scopesByResource[$resource]['permissions'][$scope] = $scopeRepository->lookupDescriptionForScope($scope, $userRole == UuidUserAccount::USER_ROLE_PATIENT);
    }
}
// sort by the resource
ksort($scopesByResource);

?>
<html>
<head>
    <title><?php echo xlt("OpenEMR Authorization"); ?></title>
    <?php Header::setupHeader(); ?>
    <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/u2f-api.js"></script>
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
</head>
<body class="container-fluid bg-dark mt-0 mt-sm-5 mt-lg-3">
<form method="post" name="userLogin" id="userLogin" action="<?php echo $redirect ?>">
    <div class="row w-100">
        <div class="col-12 col-sm-10 col-lg-7 bg-light text-dark mt-2 mt-sm-5 mt-lg-3 ml-auto mr-auto">
            <div class="text-md-center mt-2">
                <h4 class="mb-4 mt-1"><?php echo xlt("Authorizing for Application"); ?> <strong><?php echo text($clientName); ?></strong></h4>
            </div>
            <div class="row w-100 mb-3">
                <div class="col-sm-8">
                    <div class="card">
                        <div class="card-body pt-1">
                            <h5 class="card-title text-sm-center"><?php echo xlt("Grant this application access to do the following"); ?></h5>
                            <hr />
                            <h6><?php echo xlt("Resource Permissions"); ?></h6>
                                <div class="list-group pl-2 mt-1">
                                    <?php foreach ($scopesByResource as $resource => $scopeCollection) : ?>
                                        <label class="list-group-item m-0">
                                            <strong><?php echo xlt($resource); ?></strong><br />
                                            <?php foreach ($scopeCollection['permissions'] as $scope => $permission) : ?>
                                                <input type="checkbox" class='app-scope' name="scope[<?php echo attr($scope); ?>]" value="<?php echo attr($scope); ?>" checked>
                                                <?php echo text($permission); ?>
                                                <br />
                                            <?php endforeach; ?>
                                            <details><summary><small>(<?php echo xlt("Scopes granted"); ?>)</small></summary>
                                                <ul>
                                                    <?php foreach ($scopeCollection['permissions'] as $scope => $permission) : ?>
                                                    <li><?php echo text($scope); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </details>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <h6><?php echo xlt("Other Permissions"); ?></h6>
                                <div class="list-group pl-2 mt-1">
                                    <?php foreach ($otherScopes as $scope => $description) : ?>
                                    <label class="list-group-item m-0">
                                        <input type="checkbox" class='app-scope' name="scope[<?php echo attr($scope); ?>]" value="<?php echo attr($scope); ?>" checked>
                                        <?php echo text($description); ?>
                                        <details><summary><small>(<?php echo xlt("Scopes granted"); ?>)</small></summary><?php echo text($scope); ?></details>
                                    </label>
                                    <?php endforeach; ?>
                                    <?php foreach ($hiddenScopes as $scope) : ?>
                                    <input type="hidden" class='app-scope' name="scope[<?php echo attr($scope); ?>]" value="<?php echo attr($scope); ?>" checked>
                                    <?php endforeach; ?>
                                </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 mt-3 mt-sm-0">
                    <div class="card">
                        <div class="card-body pt-1">
                            <h5 class="card-title text-sm-center"><?php echo xlt("Identity Information Requested"); ?></h5>
                            <hr />
                            <p><?php echo xlt("This application is requesting access to the following information about you"); ?></p>
                            <ul class="pl-2 mt-1">
                                <?php {
                                foreach ($claims as $key => $value) {
                                    $key_n = explode('_', $key);
                                    if (stripos($scopeString, $key_n[0]) === false) {
                                        continue;
                                    }
                                    if ((int)$value === 1) {
                                        $value = 'True';
                                    }
                                    if ($key == 'fhirUser') {
                                        echo "<li class='col-text'><strong>" . xlt("Permission to retrieve information about the current logged-in user")
                                            . "</strong> " . text($userAccount['firstname'] ?? '') . ' ' . text($userAccount['lastname']) . "</li>";
                                    } else {
                                        $key = ucwords(str_replace("_", " ", $key));
                                        echo "<li class='col-text'><strong>" . text($key) . ":</strong>  " . text($value) . "</li>";
                                    }
                                }
                                } ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (true == $offline_requested) : ?>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <p>
                                <?php echo xlt("This application has requested offline access to your data. This permission will allow the data you authorize below to be accessed for an extended period of time"); ?>
                            </p>
                            <p><?php echo xlt("Offline access end date"); ?>: <strong><?php echo text($offline_access_date); ?></strong></p>
                            <p><?php echo xlt("If you do not want to allow this application to have offline access to your data, uncheck the Offline Access permission"); ?></p>
                            <label class="list-group-item m-0">
                                <input type="checkbox" class='app-scope' name="scope[offline_access]" value="offline_access" checked>
                                <?php echo xlt("Offline Access"); ?>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('oauth2')); ?>" />
            <hr />
            <div class="row mb-2">
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
        </div>
    </div>
</form>
</body>
</html>