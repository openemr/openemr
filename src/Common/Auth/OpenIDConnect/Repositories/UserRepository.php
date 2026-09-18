<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Auth\MfaUtils;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\UserEntity;
use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;

class UserRepository implements UserRepositoryInterface, IdentityProviderInterface
{
    use SystemLoggerAwareTrait;

    public function __construct(private $fhirBaseUrl)
    {
    }

    public function getUserEntityByIdentifier($identifier)
    {
        return $this->createUserEntity($identifier);
    }

    /**
     * @param $userrole
     * @param $username
     * @param $password
     * @param $email
     * @param $grantType
     * @param ClientEntityInterface $clientEntity
     * @return false|UserEntity
     * @throws OAuthServerException
     */
    public function getCustomUserEntityByUserCredentials(
        $userrole,
        $username,
        $password,
        $email,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $user = $this->createUserEntity();
        if (!empty($userrole) && !empty($username) && !empty($password)) {
            if (!$this->getAccountByPassword($user, $userrole, $username, $password, $email)) {
                return false;
            }

            return $user;
        }
        return false;
    }

    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        return false;
    }


    /**
     * Gets the user account by password.
     * @param UserEntity $user
     * @param $userrole
     * @param $username
     * @param $password
     * @param $email
     * @return bool
     * @throws OAuthServerException
     */
    protected function getAccountByPassword(UserEntity $user, $userrole, $username, $password, $email = ''): bool
    {
        if (($userrole == UuidUserAccount::USER_ROLE_USERS) && ((OEGlobalsBag::getInstance()->get('oauth_password_grant') == 1) || (OEGlobalsBag::getInstance()->get('oauth_password_grant') == 3))) {
            $auth = new AuthUtils('api');
            if ($auth->confirmPassword($username, $password)) {
                $id = $auth->getUserId();
                if (!is_int($id) && !is_string($id)) {
                    $this->logger?->error("Unable to resolve user id after password grant login");
                    return false;
                }
                UuidRegistry::createMissingUuidForRow('users', 'id', $id);
                $uuid = sqlQueryNoLog("SELECT `uuid` FROM `users` WHERE `id` = ?", [$id])['uuid'];
                if (empty($uuid)) {
                    $this->getSystemLogger()->error("Unable to map uuid for user when creating oauth password grant token");
                    return false;
                }
                $user->setIdentifier(UuidRegistry::uuidToString($uuid));

                // TOTP is the only second factor supportable via password
                // grant (U2F needs an interactive browser exchange). If the
                // user has TOTP enrolled and MFA is required, the mfa_token
                // parameter must be present and valid — omitting it must not
                // skip the check.
                $mfa = new MfaUtils($id);
                $mfaToken = $mfa->tokenFromRequest(MfaUtils::TOTP);
                $totpEnrolled = $mfa->isMfaRequired() && in_array(MfaUtils::TOTP, $mfa->getType(), true);

                if ($totpEnrolled) {
                    if (empty($mfaToken)) {
                        throw new OAuthServerException(
                            'MFA token required.',
                            13,
                            'mfa_token_required',
                            401
                        );
                    }
                    if (!$mfa->check($mfaToken, MfaUtils::TOTP)) {
                        throw new OAuthServerException(
                            $mfa->errorMessage(),
                            12,
                            'mfa_token_invalid',
                            401
                        );
                    }
                    return true;
                }

                // No TOTP enrolled. Reject an unexpected mfa_token — it
                // signals a client error (user is not configured for MFA)
                // rather than silently accepting the token.
                if (!is_null($mfaToken)) {
                    throw new OAuthServerException(
                        'MFA not supported.',
                        11,
                        'mfa_not_supported',
                        403
                    );
                }

                return true;
            }
        } elseif (($userrole == UuidUserAccount::USER_ROLE_PATIENT) && ((OEGlobalsBag::getInstance()->get('oauth_password_grant') == 2) || (OEGlobalsBag::getInstance()->get('oauth_password_grant') == 3))) {
            $auth = new AuthUtils('portal-api');
            if ($auth->confirmPassword($username, $password, $email)) {
                $id = $auth->getPatientId();
                if (!is_int($id) && !is_string($id)) {
                    $this->logger?->error("Unable to resolve patient id after password grant login");
                    return false;
                }
                UuidRegistry::createMissingUuidForRow('patient_data', 'pid', $id);
                $uuid = sqlQueryNoLog("SELECT `uuid` FROM `patient_data` WHERE `pid` = ?", [$id])['uuid'];
                if (empty($uuid)) {
                    $this->getSystemLogger()->error("Unable to map uuid for patient when creating oauth password grant token");
                    return false;
                }
                $user->setIdentifier(UuidRegistry::uuidToString($uuid));
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|null $identifier
     * @return UserEntity
     * @throws OAuthServerException if the fhir base URL is not a proper URL, see site_addr_oath if this is thrown
     */
    private function createUserEntity(?string $identifier = null): UserEntity
    {
        $user = new UserEntity();
        if (!empty($identifier)) {
            $user->setIdentifier($identifier);
        }
        $user->setFhirBaseUrl($this->fhirBaseUrl);
        return $user;
    }
}
