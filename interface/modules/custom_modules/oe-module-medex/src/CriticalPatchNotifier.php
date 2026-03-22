<?php
/**
 * MedEx Critical Patch Notifier
 *
 * Displays critical update notifications that block functionality until updated
 * Triggered when MedEx Admin pushes a critical security patch
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx;

use OpenEMR\Core\OEGlobalsBag;

class CriticalPatchNotifier
{
    private static bool $displayed = false;

    /**
     * Check for and display critical patch notification
     * Called on every page load for admin users
     */
    public static function checkAndDisplay(): void
    {
        // Only check once per page load
        if (self::$displayed) {
            return;
        }
        self::$displayed = true;

        // Only check for admins
        if (!isset($_SESSION['authUser']) || empty($_SESSION['authUser'])) {
            return;
        }

        // Check ACL
        if (!\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin', 'super')) {
            return;
        }

        // Check for critical update
        $updateInfo = UpdateManager::checkCriticalUpdate();

        if ($updateInfo) {
            self::displayNotification($updateInfo);
        }
    }

    /**
     * Display critical update notification modal
     */
    private static function displayNotification(array $updateInfo): void
    {
        $modalId = 'medex-critical-update-modal-' . time();
        ?>
        <style>
            #<?php echo $modalId; ?> {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                z-index: 999999;
                align-items: center;
                justify-content: center;
            }
            #<?php echo $modalId; ?>.show {
                display: flex;
            }
            .medex-critical-modal-content {
                background: white;
                border-radius: 12px;
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
                max-width: 600px;
                width: 90%;
                max-height: 90vh;
                overflow-y: auto;
                animation: slideIn 0.3s ease-out;
            }
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-50px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .medex-critical-modal-header {
                background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
                color: white;
                padding: 25px;
                border-radius: 12px 12px 0 0;
                text-align: center;
            }
            .medex-critical-modal-header h2 {
                margin: 0;
                font-size: 24px;
                font-weight: 700;
            }
            .medex-critical-modal-header .icon {
                font-size: 48px;
                margin-bottom: 10px;
                animation: pulse 2s infinite;
            }
            .medex-critical-modal-body {
                padding: 30px;
            }
            .medex-critical-modal-body h3 {
                margin: 0 0 15px 0;
                color: #333;
                font-size: 18px;
            }
            .medex-critical-modal-body p {
                margin: 10px 0;
                color: #666;
                line-height: 1.6;
            }
            .medex-version-info {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
                font-family: 'Courier New', monospace;
                text-align: center;
            }
            .medex-version-info strong {
                color: #e53e3e;
                font-size: 18px;
            }
            .medex-critical-message {
                background: #fff5f5;
                border-left: 4px solid #e53e3e;
                padding: 15px;
                margin: 20px 0;
                color: #742a2a;
                font-weight: 600;
            }
            .medex-critical-buttons {
                display: flex;
                gap: 10px;
                margin-top: 25px;
            }
            .medex-critical-buttons a,
            .medex-critical-buttons button {
                flex: 1;
                padding: 15px;
                text-align: center;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                font-size: 16px;
                border: none;
                cursor: pointer;
                transition: all 0.2s;
            }
            .medex-btn-update {
                background: #e53e3e;
                color: white;
            }
            .medex-btn-update:hover {
                background: #c53030;
            }
            .medex-btn-later {
                background: #e2e8f0;
                color: #64748b;
            }
            .medex-btn-later:hover {
                background: #cbd5e1;
            }
        </style>

        <div id="<?php echo $modalId; ?>" class="show">
            <div class="medex-critical-modal-content">
                <div class="medex-critical-modal-header">
                    <div class="icon">🚨</div>
                    <h2><?php echo xlt('CRITICAL SECURITY UPDATE REQUIRED'); ?></h2>
                </div>
                <div class="medex-critical-modal-body">
                    <h3><?php echo xlt('A critical security patch has been released'); ?></h3>

                    <?php if (!empty($updateInfo['critical_message'])): ?>
                        <div class="medex-critical-message">
                            <?php echo nl2br(text($updateInfo['critical_message'])); ?>
                        </div>
                    <?php endif; ?>

                    <div class="medex-version-info">
                        <div><?php echo xlt('Current Version'); ?>: <strong><?php echo text($updateInfo['current_version']); ?></strong></div>
                        <div style="margin: 10px 0; font-size: 20px;">↓</div>
                        <div><?php echo xlt('Required Version'); ?>: <strong><?php echo text($updateInfo['latest_version']); ?></strong></div>
                    </div>

                    <p>
                        <?php echo xlt('This update addresses a critical security vulnerability and must be installed as soon as possible.'); ?>
                    </p>

                    <?php if (!empty($updateInfo['changelog'])): ?>
                        <p style="margin-top: 20px;">
                            <strong><?php echo xlt('What\'s Fixed'); ?>:</strong><br>
                            <?php echo nl2br(text($updateInfo['changelog'])); ?>
                        </p>
                    <?php endif; ?>

                    <div class="medex-critical-buttons">
                        <a href="<?php echo OEGlobalsBag::getInstance()->get('webroot'); ?>/interface/modules/custom_modules/oe-module-medex/public/update.php"
                           class="medex-btn-update">
                            <i class="fa fa-shield-alt"></i> <?php echo xlt('Install Update Now'); ?>
                        </a>
                        <button type="button" class="medex-btn-later" onclick="document.getElementById('<?php echo $modalId; ?>').style.display='none'">
                            <?php echo xlt('Remind Me Later'); ?>
                        </button>
                    </div>

                    <p style="margin-top: 20px; font-size: 12px; color: #94a3b8; text-align: center;">
                        <?php echo xlt('This notification will continue to appear until the update is installed.'); ?>
                    </p>
                </div>
            </div>
        </div>

        <script>
        (function() {
            // Prevent closing modal by clicking outside (force admin to acknowledge)
            document.getElementById('<?php echo $modalId; ?>').addEventListener('click', function(e) {
                if (e.target === this) {
                    // Optional: Shake animation to indicate it can't be closed
                    this.querySelector('.medex-critical-modal-content').style.animation = 'shake 0.5s';
                    setTimeout(() => {
                        this.querySelector('.medex-critical-modal-content').style.animation = '';
                    }, 500);
                }
            });

            // Track that notification was shown
            if (window.console) {
                console.warn('[MedEx] CRITICAL UPDATE REQUIRED - Version <?php echo text($updateInfo['latest_version']); ?>');
            }
        })();
        </script>

        <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
            20%, 40%, 60%, 80% { transform: translateX(10px); }
        }
        </style>
        <?php
    }

    /**
     * Add notification hook to OpenEMR footer
     * This ensures it appears on every admin page
     */
    public static function registerHook(): void
    {
        // Register with OpenEMR event system if available
        if (function_exists('add_action')) {
            add_action('footer_content', [self::class, 'checkAndDisplay']);
        }
    }
}
