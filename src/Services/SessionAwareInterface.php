<?php

/*
 * SessionAwareInterface.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

interface SessionAwareInterface
{
    /**
     * @param SessionInterface $session The session that will be used in the object
     * @return void
     */
    public function setSession(SessionInterface $session): void;

    /**
     * @return SessionInterface|null Returns the session if its been populated, null otherwise
     */
    public function getSession(): ?SessionInterface;
}
