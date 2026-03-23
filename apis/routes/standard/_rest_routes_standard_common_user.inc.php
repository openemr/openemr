<?php

/**
 * Common User API OpenAPI definitions
 *
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 Igor Mukhin <igor.mukhin@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenApi\Annotations as OA;

/**
 * @OA\Parameter(parameter="api_standard_user_title", name="title", in="query", description="The title for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_fname", name="fname", in="query", description="The first name for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_lname", name="lname", in="query", description="The last name for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_mname", name="mname", in="query", description="The middle name for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_username", name="username", in="query", description="The username for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_email", name="email", in="query", description="The email for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_federaltaxid", name="federaltaxid", in="query", description="The federal tax id for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_federaldrugid", name="federaldrugid", in="query", description="The federal drug id for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_upin", name="upin", in="query", description="The upin for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_facility_id", name="facility_id", in="query", description="The facility id for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_facility", name="facility", in="query", description="The facility for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_npi", name="npi", in="query", description="The npi for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_specialty", name="specialty", in="query", description="The specialty for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_billname", name="billname", in="query", description="The billname for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_url", name="url", in="query", description="The url for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_assistant", name="assistant", in="query", description="The assistant for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_organization", name="organization", in="query", description="The organization for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_valedictory", name="valedictory", in="query", description="The valedictory for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_street", name="street", in="query", description="The street for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_streetb", name="streetb", in="query", description="The street (line 2) for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_city", name="city", in="query", description="The city for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_state", name="state", in="query", description="The state for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_zip", name="zip", in="query", description="The zip for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_phone", name="phone", in="query", description="The phone for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_fax", name="fax", in="query", description="The fax for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_phonew1", name="phonew1", in="query", description="The phonew1 for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_phonecell", name="phonecell", in="query", description="The phonecell for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_notes", name="notes", in="query", description="The notes for the user.", required=false, @OA\Schema(type="string"))
 * @OA\Parameter(parameter="api_standard_user_state_license_number2", name="state_license_number2", in="query", description="The state license number for the user.", required=false, @OA\Schema(type="string"))
 *
 * @OA\Schema(
 *     schema="api_standard_user_common",
 *     type="object",
 *
 *     @OA\Property(property="title", description="The title for the user.", type="string"),
 *     @OA\Property(property="fname", description="The first name for the user.", type="string"),
 *     @OA\Property(property="lname", description="The last name for the user.", type="string"),
 *     @OA\Property(property="mname", description="The middle name for the user.", type="string"),
 *     @OA\Property(property="username", description="The username for the user.", type="string"),
 *     @OA\Property(property="email", description="The email for the user.", type="string"),
 *
 *     @OA\Property(property="federaltaxid", description="The federal tax id for the user.", type="string"),
 *     @OA\Property(property="federaldrugid", description="The federal drug id for the user.", type="string"),
 *     @OA\Property(property="upin", description="The upin for the user.", type="string"),
 *     @OA\Property(property="facility_id", description="The facility id for the user.", type="string"),
 *     @OA\Property(property="facility", description="The facility for the user.", type="string"),
 *     @OA\Property(property="npi", description="The npi for the user.", type="string"),
 *     @OA\Property(property="specialty", description="The specialty for the user.", type="string"),
 *     @OA\Property(property="billname", description="The billname for the user.", type="string"),
 *     @OA\Property(property="url", description="The url for the user.", type="string"),
 *     @OA\Property(property="assistant", description="The assistant for the user.", type="string"),
 *     @OA\Property(property="organization", description="The organization for the user.", type="string"),
 *     @OA\Property(property="valedictory", description="The valedictory for the user.", type="string"),
 *
 *     @OA\Property(property="street", description="The street for the user.", type="string"),
 *     @OA\Property(property="streetb", description="The street (line 2) for the user.", type="string"),
 *     @OA\Property(property="city", description="The city for the user.", type="string"),
 *     @OA\Property(property="state", description="The state for the user.", type="string"),
 *     @OA\Property(property="zip", description="The zip for the user.", type="string"),
 *     @OA\Property(property="phone", description="The phone for the user.", type="string"),
 *     @OA\Property(property="fax", description="The fax for the user.", type="string"),
 *     @OA\Property(property="phonew1", description="The phonew1 for the user.", type="string"),
 *     @OA\Property(property="phonecell", description="The phonecell for the user.", type="string"),
 *
 *     @OA\Property(property="notes", description="The notes for the user.", type="string"),
 *     @OA\Property(property="state_license_number2", description="The state license number for the user.", type="string"),
 *
 *     example={
 *         "username": "admin"
 *     }
 * )
 *
 * @todo Define allowed fields for user creation/patching and move fields from api_standard_user_common_response_data to api_standard_user_common
 *
 * @OA\Schema(
 *     schema="api_standard_user_common_response_data",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/api_standard_user_common"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="id", description="User ID.", type="integer"),
 *             @OA\Property(property="uuid", description="UUID.", type="string"),
 *
 *             @OA\Property(property="authorized", description="Authorized flag.", type="integer"),
 *             @OA\Property(property="active", description="Active flag.", type="integer"),
 *             @OA\Property(property="see_auth", description="See auth flag.", type="integer"),
 *             @OA\Property(property="portal_user", description="Portal user flag.", type="integer"),
 *
 *             @OA\Property(property="suffix", description="Suffix.", type="string"),
 *
 *             @OA\Property(property="taxonomy", description="Taxonomy code.", type="string"),
 *             @OA\Property(property="physician_type", description="Physician type.", type="string"),
 *
 *             @OA\Property(property="email_direct", description="Direct email.", type="string"),
 *             @OA\Property(property="google_signin_email", description="Google sign-in email.", type="string"),
 *
 *             @OA\Property(property="phonew2", description="Work phone 2.", type="string"),
 *
 *             @OA\Property(property="country_code", description="Country code.", type="string"),
 *
 *             @OA\Property(property="street2", description="Street (secondary address).", type="string"),
 *             @OA\Property(property="streetb2", description="Street line 2 (secondary address).", type="string"),
 *             @OA\Property(property="city2", description="City (secondary address).", type="string"),
 *             @OA\Property(property="state2", description="State (secondary address).", type="string"),
 *             @OA\Property(property="zip2", description="ZIP code (secondary address).", type="string"),
 *             @OA\Property(property="country_code2", description="Country code (secondary address).", type="string"),
 *
 *             @OA\Property(property="billing_facility", description="Billing facility name.", type="string"),
 *             @OA\Property(property="billing_facility_id", description="Billing facility ID.", type="integer"),
 *
 *             @OA\Property(property="cal_ui", description="Calendar UI preference.", type="integer"),
 *             @OA\Property(property="calendar", description="Calendar enabled flag.", type="integer"),
 *             @OA\Property(property="main_menu_role", description="Main menu role.", type="string"),
 *             @OA\Property(property="patient_menu_role", description="Patient menu role.", type="string"),
 *             @OA\Property(property="abook_type", description="Address book type.", type="string"),
 *             @OA\Property(property="default_warehouse", description="Default warehouse.", type="string"),
 *             @OA\Property(property="irnpool", description="Invoice reference number pool.", type="string"),
 *
 *             @OA\Property(property="weno_prov_id", description="Weno provider ID.", type="string"),
 *             @OA\Property(property="newcrop_user_role", description="NewCrop user role.", type="string"),
 *             @OA\Property(property="cpoe", description="CPOE flag.", type="integer"),
 *
 *             @OA\Property(property="info", description="Info.", type="string"),
 *             @OA\Property(property="source", description="Source.", type="string"),
 *             @OA\Property(property="supervisor_id", description="Supervisor ID.", type="integer"),
 *             @OA\Property(property="state_license_number", description="State license number.", type="string"),
 *
 *             @OA\Property(property="date_created", description="Date created.", type="string"),
 *             @OA\Property(property="last_updated", description="Last updated.", type="string")
 *         )
 *     },
 *     example={
 *         "id": 1,
 *         "uuid": "90cde167-7b9b-4ed1-bd55-533925cb2605",
 *         "username": "admin"
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="api_standard_user_post_patch_response_data",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/api_standard_user_common_response_data"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="password", description="The password for the user (if it was passed).", type="string")
 *         )
 *     },
 *     example={
 *         "id": 1,
 *         "uuid": "90cde167-7b9b-4ed1-bd55-533925cb2605",
 *         "username": "admin",
 *         "password": "55006c9b20ba100a"
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="api_standard_user_post_request",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/api_standard_user_common"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="password", description="The password for the user (generated if not passed).", type="string"),
 *             @OA\Property(property="acl_group_ids", description="ACL groups IDs to add user to.", type="array", @OA\Items(type="integer"))
 *         )
 *     },
 *     required={"fname", "lname", "username"},
 *     example={
 *         "fname": "Foo",
 *         "lname": "Bar",
 *         "username": "foobar",
 *         "acl_group_ids": {11}
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="api_standard_user_patch_request",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/api_standard_user_common"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="password", description="The password for the user to update.", type="string")
 *         )
 *     },
 *     example={
 *         "username": "new_username",
 *         "password": "newPassword123"
 *     }
 * )
 *
 * @OA\Response(
 *     response="api_standard_user_get_one_response",
 *     description="Get one user response",
 *     @OA\MediaType(
 *         mediaType="application/json",
 *         @OA\Schema(
 *             @OA\Property(property="validationErrors", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="internalErrors", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="data", ref="#/components/schemas/api_standard_user_common_response_data"),
 *             example={
 *                 "validationErrors": {},
 *                 "internalErrors": {},
 *                 "data": {
 *                     "id": 1,
 *                     "uuid": "90cde167-7b9b-4ed1-bd55-533925cb2605",
 *                     "username": "admin"
 *                 }
 *             }
 *         )
 *     )
 * )
 *
 * @OA\Response(
 *     response="api_standard_user_get_all_response",
 *     description="Get all user response",
 *     @OA\MediaType(
 *         mediaType="application/json",
 *         @OA\Schema(
 *             @OA\Property(property="validationErrors", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="internalErrors", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/api_standard_user_common_response_data")),
 *             example={
 *                 "validationErrors": {},
 *                 "internalErrors": {},
 *                 "data": {{
 *                     "id": 1,
 *                     "uuid": "90cde167-7b9b-4ed1-bd55-533925cb2605",
 *                     "username": "admin"
 *                 }}
 *             }
 *         )
 *     )
 * )
 *
 * @OA\Response(
 *     response="api_standard_user_post_patch_response",
 *     description="Created/updated user response",
 *     @OA\MediaType(
 *         mediaType="application/json",
 *         @OA\Schema(
 *             @OA\Property(property="validationErrors", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="internalErrors", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="data", ref="#/components/schemas/api_standard_user_post_patch_response_data"),
 *             example={
 *                 "validationErrors": {},
 *                 "internalErrors": {},
 *                 "data": {
 *                     "id": 1,
 *                     "uuid": "90cde167-7b9b-4ed1-bd55-533925cb2605",
 *                     "username": "test",
 *                     "password": "55006c9b20ba100a"
 *                 }
 *             }
 *         )
 *     )
 * )
 */
return [];
