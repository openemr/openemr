<?php

/**
 * ESign object consists of all the essential parts.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace ESign;

class ESign
{
    public function __construct(private readonly ConfigurationIF $_configuration, private readonly SignableIF $_signable, private readonly ButtonIF $_button, private readonly LogIF $_log)
    {
    }

    /**
     * Check if the signable object is locked from futher editing
     *
     * @return boolean
     */
    public function isLocked(): bool
    {
        return $this->_signable->isLocked();
    }

    public function isButtonViewable()
    {
        return $this->_button->isViewable();
    }

    /**
     * Check if the log is viewable
     * @param  string  $mode  Currently supports "default" and "report"
     * @return boolean
     */
    public function isLogViewable($mode = "default"): bool
    {
        $viewable = false;
        if (count($this->_signable->getSignatures()) > 0 && empty($GLOBALS['esign_report_hide_all_sig'])) {
            // If we have signatures, always show the log.
            $viewable = true;
        } else {
            // If in report mode then hide the log if $_GLOBALS['esign_report_hide_empty_sig'] is true and there are no signatures
            if (($mode == "report") && ($GLOBALS['esign_report_hide_empty_sig'])) {
                $viewable = false;
            } else {
                // defer if viewable to the log object
                $viewable = $this->_log->isViewable();
            }
        }

        return $viewable;
    }

    /**
     * Check if is signed
     *
     * @return boolean
     */
    public function isSigned(): bool
    {
        return (count($this->_signable->getSignatures()) > 0);
    }

    public function renderLog(): void
    {
        $this->_log->render($this->_signable);
    }

    public function buttonHtml()
    {
        return $this->_button->getHtml();
    }

    public function getSignatures()
    {
        return $this->_signable->getSignatures();
    }
}
