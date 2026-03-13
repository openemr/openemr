<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to method DOMDocument\\:\\:saveXML\\(\\) with incorrect case\\: saveXml$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method XSLTProcessor\\:\\:importStylesheet\\(\\) with incorrect case\\: importStyleSheet$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method XSLTProcessor\\:\\:transformToXml\\(\\) with incorrect case\\: transformToXML$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAttachment\\(\\) with incorrect case\\: AddAttachment$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addReplyTo\\(\\) with incorrect case\\: AddReplyTo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:setFrom\\(\\) with incorrect case\\: SetFrom$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method ADOConnection\\:\\:qStr\\(\\) with incorrect case\\: qstr$#',
    'count' => 3,
    'path' => __DIR__ . '/../../gacl/admin/acl_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method ADOConnection\\:\\:qStr\\(\\) with incorrect case\\: qstr$#',
    'count' => 12,
    'path' => __DIR__ . '/../../gacl/admin/acl_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method ADOConnection\\:\\:qStr\\(\\) with incorrect case\\: qstr$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/assign_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addReplyTo\\(\\) with incorrect case\\: AddReplyTo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:isHTML\\(\\) with incorrect case\\: IsHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:msgHTML\\(\\) with incorrect case\\: MsgHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:setFrom\\(\\) with incorrect case\\: SetFrom$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drugs.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drugs.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Mpdf\\\\Mpdf\\:\\:WriteHTML\\(\\) with incorrect case\\: writeHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAttachment\\(\\) with incorrect case\\: AddAttachment$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addReplyTo\\(\\) with incorrect case\\: AddReplyTo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:setFrom\\(\\) with incorrect case\\: SetFrom$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Mpdf\\\\Mpdf\\:\\:WriteHTML\\(\\) with incorrect case\\: writeHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Events\\\\TelehealthNotificationSendEvent\\:\\:getHtmlBody\\(\\) with incorrect case\\: getHTMLBody$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TeleHealthParticipantInvitationMailerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Events\\\\TelehealthNotificationSendEvent\\:\\:setHtmlBody\\(\\) with incorrect case\\: setHTMLBody$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TeleHealthParticipantInvitationMailerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TeleHealthParticipantInvitationMailerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:isHTML\\(\\) with incorrect case\\: IsHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TeleHealthParticipantInvitationMailerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:msgHTML\\(\\) with incorrect case\\: MsgHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TeleHealthParticipantInvitationMailerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TeleHealthParticipantInvitationMailerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addReplyTo\\(\\) with incorrect case\\: AddReplyTo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:isHTML\\(\\) with incorrect case\\: IsHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:msgHTML\\(\\) with incorrect case\\: MsgHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:setFrom\\(\\) with incorrect case\\: SetFrom$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EmailClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAttachment\\(\\) with incorrect case\\: AddAttachment$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EmailClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addReplyTo\\(\\) with incorrect case\\: AddReplyTo$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EmailClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EmailClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:setFrom\\(\\) with incorrect case\\: SetFrom$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EmailClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAttachment\\(\\) with incorrect case\\: AddAttachment$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addReplyTo\\(\\) with incorrect case\\: AddReplyTo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:setFrom\\(\\) with incorrect case\\: SetFrom$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAttachment\\(\\) with incorrect case\\: AddAttachment$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addReplyTo\\(\\) with incorrect case\\: AddReplyTo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:setFrom\\(\\) with incorrect case\\: SetFrom$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAttachment\\(\\) with incorrect case\\: AddAttachment$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addReplyTo\\(\\) with incorrect case\\: AddReplyTo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:setFrom\\(\\) with incorrect case\\: SetFrom$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\TransmitProperties\\:\\:getWenoProviderId\\(\\) with incorrect case\\: getWenoProviderID$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Acl\\\\Model\\\\AclTable\\:\\:insertuserACL\\(\\) with incorrect case\\: insertUserACL$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Controller/AclController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method XSLTProcessor\\:\\:importStylesheet\\(\\) with incorrect case\\: importStyleSheet$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method XSLTProcessor\\:\\:transformToUri\\(\\) with incorrect case\\: transformToURI$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Application\\\\Model\\\\SendtoTable\\:\\:getCCDAComponents\\(\\) with incorrect case\\: getCcdaComponents$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method XSLTProcessor\\:\\:importStylesheet\\(\\) with incorrect case\\: importStyleSheet$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncountermanagerTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method XSLTProcessor\\:\\:transformToUri\\(\\) with incorrect case\\: transformToURI$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncountermanagerTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method DOMNode\\:\\:lookupNamespaceURI\\(\\) with incorrect case\\: lookupNamespaceUri$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method XSLTProcessor\\:\\:importStylesheet\\(\\) with incorrect case\\: importStyleSheet$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method XSLTProcessor\\:\\:transformToXml\\(\\) with incorrect case\\: transformToXML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Installer\\\\Controller\\\\InstallerController\\:\\:makeButtonForACLAction\\(\\) with incorrect case\\: makeButtonForAClAction$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Mpdf\\\\Mpdf\\:\\:WriteHTML\\(\\) with incorrect case\\: writeHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Mpdf\\\\Mpdf\\:\\:WriteHTML\\(\\) with incorrect case\\: writeHTML$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Mpdf\\\\Mpdf\\:\\:WriteHTML\\(\\) with incorrect case\\: writeHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Mpdf\\\\Mpdf\\:\\:WriteCell\\(\\) with incorrect case\\: writeCell$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/procedure_tools/libs/labs_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Mpdf\\\\Mpdf\\:\\:WriteHTML\\(\\) with incorrect case\\: writeHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/libs/labs_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addReplyTo\\(\\) with incorrect case\\: AddReplyTo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method ADORecordSet\\:\\:recordCount\\(\\) with incorrect case\\: RecordCount$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addReplyTo\\(\\) with incorrect case\\: AddReplyTo$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:isHTML\\(\\) with incorrect case\\: IsHTML$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:msgHTML\\(\\) with incorrect case\\: MsgHTML$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:setFrom\\(\\) with incorrect case\\: SetFrom$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Mpdf\\\\Mpdf\\:\\:WriteHTML\\(\\) with incorrect case\\: writeHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Symfony\\\\Component\\\\HttpFoundation\\\\Request\\:\\:getRequestUri\\(\\) with incorrect case\\: getRequestURI$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRouteHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method ADORecordSet\\:\\:recordCount\\(\\) with incorrect case\\: RecordCount$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Uuid/UuidRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Laminas\\\\Code\\\\Generator\\\\ClassGenerator\\:\\:setDocBlock\\(\\) with incorrect case\\: setDocblock$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/Generator.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method XSLTProcessor\\:\\:importStylesheet\\(\\) with incorrect case\\: importStyleSheet$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method XSLTProcessor\\:\\:transformToUri\\(\\) with incorrect case\\: transformToURI$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Easipro/Easipro.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addReplyTo\\(\\) with incorrect case\\: AddReplyTo$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Easipro/Easipro.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:isHTML\\(\\) with incorrect case\\: IsHTML$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Easipro/Easipro.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:msgHTML\\(\\) with incorrect case\\: MsgHTML$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Easipro/Easipro.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Easipro/Easipro.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:setFrom\\(\\) with incorrect case\\: SetFrom$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Easipro/Easipro.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Mpdf\\\\Mpdf\\:\\:WriteHTML\\(\\) with incorrect case\\: writeHtml$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Pdf/PatientPortalPDFDocumentCreator.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method OpenEMR\\\\Reminder\\\\BirthdayReminder\\:\\:isBirthdayAlertOff\\(\\) with incorrect case\\: isbirthdayAlertOff$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Reminder/BirthdayReminder.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method OpenEMR\\\\RestControllers\\\\SMART\\\\SMARTAuthorizationController\\:\\:needSMARTAuthorization\\(\\) with incorrect case\\: needSmartAuthorization$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Symfony\\\\Component\\\\HttpFoundation\\\\Request\\:\\:getRequestUri\\(\\) with incorrect case\\: getRequestURI$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method XSLTProcessor\\:\\:importStylesheet\\(\\) with incorrect case\\: importStyleSheet$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CDADocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method XSLTProcessor\\:\\:transformToUri\\(\\) with incorrect case\\: transformToURI$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CDADocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DrugSalesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DrugSalesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:insertOpenEMRRecord\\(\\) with incorrect case\\: insertOpenEmrRecord$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addAddress\\(\\) with incorrect case\\: AddAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAccessOnsiteService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:addReplyTo\\(\\) with incorrect case\\: AddReplyTo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAccessOnsiteService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:isHTML\\(\\) with incorrect case\\: IsHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAccessOnsiteService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:msgHTML\\(\\) with incorrect case\\: MsgHTML$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAccessOnsiteService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:send\\(\\) with incorrect case\\: Send$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAccessOnsiteService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:setFrom\\(\\) with incorrect case\\: SetFrom$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAccessOnsiteService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method OpenEMR\\\\Services\\\\Search\\\\FHIRSearchFieldFactory\\:\\:buildFHIRCompositeField\\(\\) with incorrect case\\: buildFhirCompositeField$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/FHIRSearchFieldFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertArrayHasKey\\(\\) with incorrect case\\: assertArrayhasKey$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/AllergyIntoleranceFhirApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertArrayHasKey\\(\\) with incorrect case\\: assertArrayhasKey$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientFhirApiTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
