<?php

/**
 * UserEditRenderEvent class is fired from the interface/usergroup/user_admin.php and interface/usergroup/usergroup_admin_add.php
 * pages inside OpenEMR and allows event listeners to render content before or after the form fields for the user.  Content
 * will be contained inside a div.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\User;

use OpenEMR\Events\Core\TemplatePageEvent;

class UserEditRenderEvent extends TemplatePageEvent
{
    const EVENT_USER_EDIT_RENDER_BEFORE = 'user.edit.render.before';


    const EVENT_USER_EDIT_RENDER_AFTER = 'user.edit.render.after';

    private $userId;

    /**
     * UserEditRenderEvent constructor.
     * @param string $pageName
     * @param int|null $userId The userid that is being edited, null if this is a brand new user
     * @param array $context
     */
    public function __construct(string $pageName, ?int $userId = null, $context = array())
    {
        parent::__construct($pageName, $context);
        $this->setUserId($userId);
    }

    /**
     * @return int|null
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int|null $userId
     * @return UserEditRenderEvent
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }
}
