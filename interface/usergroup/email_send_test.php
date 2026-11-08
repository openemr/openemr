<?php

/**
 * Admin page for testing email send paths
 *
 * Lets a superadmin send a test email and choose which sending path to exercise:
 * - Direct MyMailer::send()
 * - Queue via MyMailer::emailServiceQueue()
 * - Queue via MyMailer::emailServiceQueueTemplatedEmail()
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . '/../globals.php');

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Services\Email\EmailSendMethod;
use OpenEMR\Services\Email\EmailTestResult;
use OpenEMR\Services\Email\EmailTestService;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

if (!AclMain::aclCheckCore('admin', 'super')) {
    AccessDeniedHelper::denyWithTemplate(
        'ACL check failed for admin/super: Email Send Test',
        xl('Email Send Test'),
    );
}

$requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD') ?: 'GET';
$isPost = $requestMethod === 'POST';

/** @var list<EmailTestResult> $results */
$results = [];
$submittedRecipient = '';
$submittedSender = '';
/** @var list<string> $submittedMethods */
$submittedMethods = [];

if ($isPost) {
    $csrfToken = filter_input(INPUT_POST, 'csrf_token_form');
    if (!is_string($csrfToken) || !CsrfUtils::verifyCsrfToken($csrfToken, session: $session)) {
        CsrfUtils::csrfNotVerified();
    }

    $rawSender = filter_input(INPUT_POST, 'sender');
    $submittedSender = is_string($rawSender) ? trim($rawSender) : '';
    $rawRecipient = filter_input(INPUT_POST, 'recipient');
    $submittedRecipient = is_string($rawRecipient) ? trim($rawRecipient) : '';

    // filter_input cannot handle array inputs; use filter_input_array
    $postData = filter_input_array(INPUT_POST) ?: [];
    $rawMethods = $postData['methods'] ?? [];
    $submittedMethods = is_array($rawMethods) ? array_filter($rawMethods, is_string(...)) : [];

    $methods = [];
    foreach ($submittedMethods as $value) {
        $method = EmailSendMethod::tryFrom($value);
        if ($method instanceof EmailSendMethod) {
            $methods[] = $method;
        }
    }

    if ($submittedSender === '' || $submittedRecipient === '' || $methods === []) {
        $results = [];
    } else {
        $service = new EmailTestService(ServiceContainer::getLogger());
        $results = $service->test($submittedSender, $submittedRecipient, $methods);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Email Send Test'); ?></title>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top">
    <div class="container mt-3">
        <h2><?php echo xlt('Email Send Test'); ?></h2>
        <p class="text-muted"><?php echo xlt('Send a test email to verify that each email code path is working correctly.'); ?></p>

        <?php if ($results !== []) : ?>
            <div class="mb-3">
                <?php foreach ($results as $result) : ?>
                    <div class="alert <?php echo $result->success ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                        <strong><?php echo text($result->method->label()); ?>:</strong>
                        <?php echo text($result->message); ?>
                        <small class="text-muted ml-2">(<?php echo text($result->timestamp->format('H:i:s')); ?>)</small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($isPost && ($submittedSender === '' || $submittedRecipient === '' || $methods === [])) : ?>
            <div class="alert alert-warning" role="alert">
                <?php echo xlt('Please provide a sender address, recipient address, and select at least one send method.'); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" onsubmit="return top.restoreSession()">
            <input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>">

            <div class="form-group">
                <label for="sender"><?php echo xlt('Sender Email'); ?></label>
                <input type="email" class="form-control" id="sender" name="sender"
                       value="<?php echo attr($submittedSender); ?>"
                       placeholder="noreply@example.com" required>
                <small class="form-text text-muted"><?php echo xlt('The From address for the test email. Use an address from your domain to avoid spam filters.'); ?></small>
            </div>

            <div class="form-group">
                <label for="recipient"><?php echo xlt('Recipient Email'); ?></label>
                <input type="email" class="form-control" id="recipient" name="recipient"
                       value="<?php echo attr($submittedRecipient); ?>"
                       placeholder="admin@example.com" required>
            </div>

            <div class="form-group">
                <label><?php echo xlt('Send Methods'); ?></label>
                <?php foreach (EmailSendMethod::cases() as $method) : ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="methods[]"
                               value="<?php echo attr($method->value); ?>"
                               id="method_<?php echo attr($method->value); ?>"
                               <?php echo in_array($method->value, $submittedMethods, true) || !$isPost ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="method_<?php echo attr($method->value); ?>">
                            <?php echo text($method->label()); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-primary"><?php echo xlt('Send Test Email'); ?></button>
        </form>
    </div>
</body>
</html>
