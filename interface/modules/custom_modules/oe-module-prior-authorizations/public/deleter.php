<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022.
 *  license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    use OpenEMR\Common\Csrf\CsrfUtils;
    use OpenEMR\Common\Acl\AclMain;

    require_once dirname(__FILE__, 5) . '/globals.php';

if (!AclMain::aclCheckCore('admin', 'practice')) {
    echo xlt('Unauthorized');
    die;
}

if (!CsrfUtils::verifyCsrfToken($_GET['csrf_token_form'])) {
    CsrfUtils::csrfNotVerified();
}
sqlQuery("delete from `module_prior_authorizations` where `id` = ?", [$_GET['id']]);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo xlt('Delete Record') ?></title>
</head>
<body>
    <p><?php echo "<br> <br>" .  xlt("If you are seeing this message the record was deleted. Click done, pls"); ?></p>
</body>
</html>


