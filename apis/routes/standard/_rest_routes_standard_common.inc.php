<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 Igor Mukhin <igor.mukhin@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="api_standard_setting_put_request",
 *     description="Setting PATCH Request",
 *
 *     @OA\Property(
 *         property="setting_key",
 *         description="The setting key.",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="setting_value",
 *         description="The setting value.",
 *         type="null|bool|int|string|array"
 *     ),
 *     required={
 *         "setting_key",
 *         "setting_value"
 *     },
 *     example={
 *         "setting_key": "default_search_code_type",
 *         "setting_value": "ICD9"
 *     }
 * )
 *
 * @OA\Response(
 *     response="api_standard_setting_response",
 *     description="Setting List Response",
 *     @OA\MediaType(
 *         mediaType="application/json",
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="validationErrors",
 *                 description="Validation errors.",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                 ),
 *             ),
 *             @OA\Property(
 *                 property="internalErrors",
 *                 description="Internal errors.",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                 ),
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 description="Returned data.",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                 ),
 *
 *                 @OA\Items(
 *                     @OA\Property(
 *                         property="setting_section",
 *                         description="Section.",
 *                         type="string",
 *                     ),
 *                     @OA\Property(
 *                         property="setting_key",
 *                         description="Setting key.",
 *                         type="string",
 *                     ),
 *                     @OA\Property(
 *                         property="setting_name",
 *                         description="Setting name.",
 *                         type="string",
 *                     ),
 *                     @OA\Property(
 *                         property="setting_description",
 *                         description="Setting description.",
 *                         type="string",
 *                     ),
 *                     @OA\Property(
 *                         property="setting_default_value",
 *                         description="Default value.",
 *                         type="string",
 *                     ),
 *                     @OA\Property(
 *                         property="setting_is_default_value",
 *                         description="Is default value set?",
 *                         type="boolean",
 *                     ),
 *                     @OA\Property(
 *                         property="setting_value",
 *                         description="Current setting value.",
 *                         type="string",
 *                     ),
 *                     @OA\Property(
 *                         property="setting_value_options",
 *                         description="Possible value options.",
 *                         type="array",
 *                     ),
 *                 ),
 *             ),
 *             example={
 *                 "validationErrors": {},
 *                 "internalErrors": {},
 *                 "data": {
 *                     "setting_section": "",
 *                 }
 *             }
 *         )
 *     )
 * )
 */
return [];
