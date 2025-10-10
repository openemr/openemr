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
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserRepository implements UserRepositoryInterface, IdentityProviderInterface
{
    use SystemLoggerAwareTrait;

    public function __construct(private OEGlobalsBag $globalsBag, private SessionInterface $session)
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
        if (($userrole == UuidUserAccount::USER_ROLE_USERS) && (($GLOBALS['oauth_password_grant'] == 1) || ($GLOBALS['oauth_password_grant'] == 3))) {
            $auth = new AuthUtils('api');
            if ($auth->confirmPassword($username, $password)) {
                $id = $auth->getUserId();
                UuidRegistry::createMissingUuidsForTables(['users']);
                $uuid = sqlQueryNoLog("SELECT `uuid` FROM `users` WHERE `id` = ?", [$id])['uuid'];
                if (empty($uuid)) {
                    $this->getSystemLogger()->errorLogCaller("OpenEMR Error: unable to map uuid for user when creating oauth password grant token");
                    return false;
                }
                $user->setIdentifier(UuidRegistry::uuidToString($uuid));

                // If an mfa_token was provided, then will force TOTP MFA (U2F impossible to support via password grant)
                //  (note that this is only forced if mfa_token is provided)
                $mfa = new MfaUtils($id);
                $mfaToken = $mfa->tokenFromRequest(MfaUtils::TOTP);
                if (!is_null($mfaToken)) {
                    if (!$mfa->isMfaRequired() || !in_array(MfaUtils::TOTP, $mfa->getType())) {
                        // A mfa_token was provided, however the user is not configured for totp
                        throw new OAuthServerException(
                            'MFA not supported.',
                            11,
                            'mfa_not_supported',
                            403
                        );
                    } else {
                        //Check the validity of the totp token, if applicable
                        if (!empty($mfaToken) && $mfa->check($mfaToken, MfaUtils::TOTP)) {
                            return true;
                        } else {
                            throw new OAuthServerException(
                                $mfa->errorMessage(),
                                12,
                                'mfa_token_invalid',
                                401
                            );
                        }
                    }
                }

                return true;
            }
        } elseif (($userrole == UuidUserAccount::USER_ROLE_PATIENT) && (($GLOBALS['oauth_password_grant'] == 2) || ($GLOBALS['oauth_password_grant'] == 3))) {
            $auth = new AuthUtils('portal-api');
            if ($auth->confirmPassword($username, $password, $email)) {
                $id = $auth->getPatientId();
                UuidRegistry::createMissingUuidsForTables(['patient_data']);
                $uuid = sqlQueryNoLog("SELECT `uuid` FROM `patient_data` WHERE `pid` = ?", [$id])['uuid'];
                if (empty($uuid)) {
                    $this->getSystemLogger()->errorLogCaller("OpenEMR Error: unable to map uuid for patient when creating oauth password grant token");
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
        $user->setFhirBaseUrl($this->globalsBag->get('site_addr_oath') . $this->globalsBag->get('web_root') . '/apis/' . $this->session->get('site_id') . '/fhir/');
        return $user;
    }
}
