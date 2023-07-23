<?php

/**
 * PortalCredentialsUpdatedEvent is intended to be used and dispatched when a patient's portal credentials have been
 * updated.  For now it only fires from the admin page.  Future updates would be to add this to the patient change
 * password page so that event listeners can connect to that as well.  For security reasons we do NOT pass the patient
 * password as part of this event.  Only the fact that their credentials are about to change and that their credentials
 * have changed.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) Discover and Change, Inc <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Patient\Summary;

class PortalCredentialsUpdatedEvent
{
    const EVENT_UPDATE_PRE = 'patient.portal-credentials.update.pre';
    const EVENT_UPDATE_POST  = 'patient.portal-credentials.update.post';

    /**
     * @var int
     */
    private $pid;

    // TODO: do we want to expose the patient password credentials to module listeners?

    /**
     * @var string The username for the patient
     */
    private $username;

    /**
     * @var string The username the patient will use to login
     */
    private $loginUsername;

    public function __construct(int $pid)
    {
        $this->pid = $pid;
    }

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     * @return PortalCredentialsUpdatedEvent
     */
    public function setPid(int $pid): PortalCredentialsUpdatedEvent
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return PortalCredentialsUpdatedEvent
     */
    public function setUsername(string $username): PortalCredentialsUpdatedEvent
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getLoginUsername(): string
    {
        return $this->loginUsername;
    }

    /**
     * @param string $loginUsername
     * @return PortalCredentialsUpdatedEvent
     */
    public function setLoginUsername(string $loginUsername): PortalCredentialsUpdatedEvent
    {
        $this->loginUsername = $loginUsername;
        return $this;
    }
}
