<?php

/**
 * UserRestController - REST API for user related operations
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\PractitionerService;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;

class UserRestController
{
    /**
     * @var UserService $userService
     */
    private $userService;

    /**
     * White list of practitioner search fields
     */
    private const WHITELISTED_FIELDS = array(
        "id",
        "title",
        "fname",
        "lname",
        "mname",
        "federaltaxid",
        "federaldrugid",
        "upin",
        "facility_id",
        "facility",
        "npi",
        "email",
        "specialty",
        "billname",
        "url",
        "assistant",
        "organization",
        "valedictory",
        "street",
        "streetb",
        "city",
        "state",
        "zip",
        "phone",
        "fax",
        "phonew1",
        "phonecell",
        "notes",
        "state_license_number",
        "username"
    );

    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * Fetches a single user resource by id.
     * @param $uuid- The user uuid identifier in string format.
     */
    public function getOne($uuid)
    {
        $processingResult = new ProcessingResult();
        $user = $this->userService->getUserByUUID($uuid);
        if (!empty($user)) {
            $processingResult->setData([$user]);
        }

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns user resources which match an optional search criteria.
     */
    public function getAll($search = array())
    {
        $validKeys = array_combine(self::WHITELISTED_FIELDS, self::WHITELISTED_FIELDS);
        $validSearchFields = array_intersect_key($search, $validKeys);
        $result = $this->userService->search($validSearchFields, true);
        return RestControllerHelper::handleProcessingResult($result, 200, true);
    }
}
