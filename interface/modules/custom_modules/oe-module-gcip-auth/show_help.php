<?php

/**
 * GCIP module help content.
 *
 * Included by ModuleManagerListener::help_requested() via output buffering.
 * The captured HTML is returned as JSON to the Module Manager UI.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

?>
<div class="container mt-3">
    <h4><?php echo xlt('GCIP Authentication Module'); ?></h4>
    <p><?php echo xlt('This module enables Single Sign-On (SSO) via Google Cloud Identity Platform (GCIP) using OpenID Connect.'); ?></p>

    <h5><?php echo xlt('Setup Steps'); ?></h5>
    <ol>
        <li><?php echo xlt('Create a Firebase/GCIP project in the Google Cloud Console'); ?></li>
        <li><?php echo xlt('Enable Identity Platform and configure your identity providers'); ?></li>
        <li><?php echo xlt('Install and enable this module'); ?></li>
        <li><?php echo xlt('Open the module configuration (cog icon) and enter your Firebase project credentials'); ?></li>
        <li><?php echo xlt('Enable OIDC in OpenEMR global settings'); ?></li>
    </ol>

    <h5><?php echo xlt('Configuration Fields'); ?></h5>
    <dl>
        <dt><?php echo xlt('Firebase Project ID'); ?></dt>
        <dd><?php echo xlt('Your Google Cloud/Firebase project identifier'); ?></dd>

        <dt><?php echo xlt('Firebase API Key'); ?></dt>
        <dd><?php echo xlt('Client-side API key from the Firebase console'); ?></dd>

        <dt><?php echo xlt('Firebase Auth Domain'); ?></dt>
        <dd><?php echo xlt('Typically your-project.firebaseapp.com'); ?></dd>

        <dt><?php echo xlt('OIDC Issuer URL'); ?></dt>
        <dd><?php echo xlt('The expected issuer claim — typically https://securetoken.google.com/your-project'); ?></dd>

        <dt><?php echo xlt('Expected Audience'); ?></dt>
        <dd><?php echo xlt('The expected audience claim — typically the Firebase project ID'); ?></dd>

        <dt><?php echo xlt('Allowed Tenant ID'); ?></dt>
        <dd><?php echo xlt('GCIP tenant ID for this deployment. Leave empty if you are not using Firebase tenants. When set, tokens whose firebase.tenant claim does not match are rejected.'); ?></dd>
    </dl>

    <h5><?php echo xlt('Documentation'); ?></h5>
    <p><?php echo xlt('For detailed setup instructions, see the docs/ directory in the module folder:'); ?></p>
    <ul>
        <li><code>docs/firebase_account_setup.md</code> — <?php echo xlt('Firebase project setup guide'); ?></li>
        <li><code>docs/troubleshooting.md</code> — <?php echo xlt('Common issues and solutions'); ?></li>
        <li><code>docs/migration.md</code> — <?php echo xlt('Migration guide'); ?></li>
    </ul>
</div>
