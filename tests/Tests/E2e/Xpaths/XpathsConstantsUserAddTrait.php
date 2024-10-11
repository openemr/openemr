<?php

/**
 * XpathsConstantsUserAddTrait class
 *
 *
 * TODO when no longer supporting PHP 8.1
 * THIS class is needed since unable to add constants directly to the UserAddTrait in PHP 8.1
 *
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Xpaths;

class XpathsConstantsUserAddTrait
{
    public const NEW_USER_IFRAME_USERADD_TRAIT = "//*[@id='modalframe']";

    public const ADD_USER_BUTTON_USERADD_TRAIT = "/html//a[text()='Add User']";

    public const NEW_USER_BUTTON_USERADD_TRAIT = "//form[@id='new_user']";

    public const CREATE_USER_BUTTON_USERADD_TRAIT = "//a[@id='form_save']";
}
