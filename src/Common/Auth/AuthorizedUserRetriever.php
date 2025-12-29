<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth;

use OpenEMR\Common\Database\Repository\User\UserRepository;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\Traits\SingletonTrait;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @todo Avoid passing request, get request from RequestStack once it will be at container
 *
 * Usage:
 *   $userId = AuthorizedUserRetriever::getInstance()->getAuthorizedUserIdFromRequest($request);
 *   $user = AuthorizedUserRetriever::getInstance()->getAuthorizedUserFromRequest($request);
 *
 * @phpstan-import-type TUser from UserRepository
 */
class AuthorizedUserRetriever
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            UserRepository::getInstance(),
        );
    }

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getAuthorizedUserIdFromRequest(HttpRestRequest $request): int
    {
        $authUserID = $request->getSession()->get("authUserID");
        Assert::notNull(
            $authUserID,
            sprintf(
                'User should be logged in before calling %s::%s',
                self::class,
                __METHOD__,
            )
        );

        return (int) $authUserID;
    }

    /**
     * @phpstan-return TUser
     * @throws InvalidArgumentException
     */
    public function getAuthorizedUserFromRequest(HttpRestRequest $request): array
    {
        return $this->userRepository->find(
            $this->getAuthorizedUserIdFromRequest($request),
        );
    }
}
