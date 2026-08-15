<?php

/**
 * Allows enabled modules to contribute external authentication buttons to the
 * OpenEMR staff login page.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Events\Core;

use Symfony\Contracts\EventDispatcher\Event;

class ExternalAuthenticationProviderEvent extends Event
{
    public const EVENT_NAME = 'auth.external.providers';

    /** @var array<string, array{id: string, label: string, loginUrl: string}> */
    private array $providers = [];

    public function addProvider(string $id, string $label, string $loginUrl): void
    {
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]{0,63}$/', $id)) {
            throw new \InvalidArgumentException('External authentication provider ID is invalid.');
        }
        if (trim($label) === '' || trim($loginUrl) === '') {
            throw new \InvalidArgumentException('External authentication provider label and login URL are required.');
        }
        if (isset($this->providers[$id])) {
            throw new \InvalidArgumentException('External authentication provider ID is already registered.');
        }

        $this->providers[$id] = ['id' => $id, 'label' => $label, 'loginUrl' => $loginUrl];
    }

    /** @return array<int, array{id: string, label: string, loginUrl: string}> */
    public function getProviders(): array
    {
        return array_values($this->providers);
    }
}
