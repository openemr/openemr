<?php

function pnSecAuthAction ($a,$b,$c,$d) {
//	echo "forcing auth to true";
	return true;
}

/**
 * generate an authorisation key
 * <br>
 * The authorisation key is used to confirm that actions requested by a
 * particular user have followed the correct path.  Any stage that an
 * action could be made (e.g. a form or a 'delete' button) this function
 * must be called and the resultant string passed to the client as either
 * a GET or POST variable.  When the action then takes place it first calls
 * <code>pnSecConfirmAuthKey()</code> to ensure that the operation has
 * indeed been manually requested by the user and that the key is valid
 *
 * @public
 * @param modname the module this authorisation key is for (optional)
 * @returns string
 * @return an encrypted key for use in authorisation of operations
 */
function pnSecGenAuthKey($modname='')
{

    if (empty($modname)) {
        $modname = pnVarCleanFromInput('module');
    }

// Date gives extra security but leave it out for now
//    $key = pnSessionGetVar('rand') . $modname . date ('YmdGi');
    $key = pnSessionGetVar('rand') . strtolower($modname);
    // Encrypt key
    $authid = md5($key);
    // Return encrypted key
    return $authid;
}


/**
 * confirm an authorisation key is valid
 * <br>
 * See description of <code>pnSecGenAuthKey</code> for information on
 * this function
 * @public
 * @returns bool
 * @return true if the key is valid, false if it is not
 */
function pnSecConfirmAuthKey($preview = false)
{
    list($module, $authid) = pnVarCleanFromInput('module', 'authid');

    // Regenerate static part of key
    $partkey = pnSessionGetVar('rand') . strtolower($module);
	if ((md5($partkey)) == $authid) {
        // Match - generate new random number for next key and leave happy
		if (!$preview) {
        srand((double)microtime()*1000000);
        pnSessionSetVar('rand', rand());
		}
        return true;
    }

    // Not found, assume invalid
    return false;
}







?>
