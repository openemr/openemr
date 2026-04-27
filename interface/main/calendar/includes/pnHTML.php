<?php

// File: $Id$
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Jim McDonald
// Purpose of file: HTML helpers
// ----------------------------------------------------------------------

/**
 * Set incoming text to be copied verbatim to the output buffer
 *
 * $const _PNH_VERBATIMINPUT Do not parse incoming text
 */

define('_PNH_VERBATIMINPUT', 0);

/**
 * Set incoming text to be parsed for display before putting in the output buffer
 *
 * $const _PNH_PARSEINPUT Parse incoming text
 */
define('_PNH_PARSEINPUT', 1);

/**
 * HTML creation and display functions
 *
 * Every generator method returns an HTML string. Callers assemble the final
 * page by concatenating the returned strings. Use GetOutput() or PrintPage()
 * to attach the accumulated headers and emit or return the complete page.
 *
 * <B>Example</B>
 * <pre>
 * $myhtml = new pnHTML();
 * $body  = $myhtml->generateStartPage();
 * $body .= $myhtml->generateFormStart('colorchosen.php');
 * $body .= $myhtml->generateText('Select a color: ');
 * $body .= $myhtml->generateFormSelectMultiple('chosen', $colorinfo);
 * $body .= $myhtml->generateFormSubmit('That\'s the color I want');
 * $body .= $myhtml->generateFormEnd();
 * $body .= $myhtml->generateEndPage();
 * $myhtml->PrintPage($body);
 * </pre>
 *
 * @package PostNuke
 * @author Jim McDonald
 * @author Patrick Kellum
 * @link http://www.postnuke.com/ The Official PostNuke website
 * @copyright (C) 2001, 2002 by the Post-Nuke Development Team
 * @version $Revision$
 * @todo need to add text sanitizer
 */
class pnHTML
{
    /*==============================================================================*
     |                               Properties                                     |
     *==============================================================================*/

    /**
     * Specific headers which must be printed prior to the main body of HTML
     *
     * @access private
     * @var array $header
     */
    public $header;

    /**
     * Parse text for output?
     *
     * @access private
     * @var integer $parse
     */
    public $parse;

    /**
     * Current tab index value
     *
     * @access private
     * @var integer $tabindex
     */
    public $tabindex;

    /**
     * File upload mode
     *
     * @access private
     * @since 1.13 - 2002/01/23
     * @var integer $fileupload
     */
    public $fileupload;

    /*==============================================================================*
     |                             Methods: Base                                    |
     *==============================================================================*/


    /**
     * pnHTML constructor.
     */
    function __construct()
    {
        $this->header =  [];
        $this->parse = _PNH_PARSEINPUT;
        $this->tabindex = 0;
        $this->fileupload = 0;
    }

    /**
     * Retrieve the current input state
     *
     * @access public
     * @since 1.13 - 2002/01/23
     * @return integer Current input state
     * @see SetInputMode()
     */
    function GetInputMode()
    {
        // The ONLY time this should be accessed directly
        return $this->parse;
    }

    /**
     * Set state of the input stream
     *
     * @access public
     * @since 1.14 - 2002/01/29
     * @param int $st Input state to set to
     * @return integer Previous state
     * @see GetInputMode()
     */
    function SetInputMode($st)
    {
        $pre = $this->GetInputMode();
        switch ($st) {
            case _PNH_VERBATIMINPUT:
                // The ONLY time this should be accessed directly
                $this->parse = _PNH_VERBATIMINPUT;
                break;
            default:
            case _PNH_PARSEINPUT:
                // The ONLY time this should be accessed directly
                $this->parse = _PNH_PARSEINPUT;
                break;
        }

        return $pre;
    }


    /*==============================================================================*
     |                            Methods: Output                                   |
     *==============================================================================*/

    /**
     * Return the full HTML output — accumulated headers plus the supplied body.
     *
     * @access public
     * @since 1.15 - 2002/01/30
     * @param string $body The assembled HTML body (from generate*() calls)
     * @return string An HTML string
     */
    function GetOutput(string $body): string
    {
        return implode("\n", $this->header) . "\n" . $body;
    }

    /**
     * Send the accumulated HTTP headers and print the supplied HTML body.
     *
     * @access public
     * @param string $body The assembled HTML body (from generate*() calls)
     * @return void
     */
    function PrintPage(string $body): void
    {
        // Headers set by the system
        foreach ($this->header as $headerline) {
            header($headerline);
        }

        print $body;
    }

    /*==============================================================================*
     |                             Methods: Misc                                    |
     *==============================================================================*/

    /**
     * Generate the appropriate HTML tags for a valid start to HTML output.
     *
     * @access public
     * @return string The HTML string
     * @see generateEndPage()
     */
    function generateStartPage(): string
    {
        ob_start();
        print '<table class="w-100 border-0" cellpadding="0" cellspacing="0"><tr><td class="text-left align-top">';

        $output = ob_get_contents();
        @ob_end_clean();

        return $output;
    }

    /**
     * Generate the appropriate HTML tags for a valid end to HTML output.
     *
     * @access public
     * @return string The HTML string
     * @see generateStartPage()
     */
    function generateEndPage(): string
    {
        global $index;
        $index = pnVarCleanFromInput('module') ? 0 : 1;

        ob_start();
        print '</td></tr></table>';
        $output = ob_get_contents();
        @ob_end_clean();

        return $output;
    }


    /*==============================================================================*
     |                             Methods: Text                                    |
     *==============================================================================*/

    /**
     * Generate free-form text, parsed according to the current input mode.
     *
     * @access public
     * @param string $text The text string to add
     * @return string The processed text
     */
    function generateText($text): string
    {
        if ($this->GetInputMode() == _PNH_PARSEINPUT) {
            $text = pnVarPrepForDisplay($text);
        }

        return $text;
    }


    /**
     * Generate a run of HTML line breaks.
     *
     * @access public
     * @param integer $numbreaks number of linebreaks to add
     * @return string The HTML string
     */
    function generateLinebreak($numbreaks = 1): string
    {
        $out = '';
        for ($i = 0; $i < $numbreaks; $i++) {
            $out .= '<br />';
        }

        return $out;
    }


    /*==============================================================================*
     |                             Methods: Forms                                   |
     *==============================================================================*/

    /**
     * Generate HTML tags to start a form.
     *
     * @access public
     * @param string $action the URL that this form should go to on submission
     * @return string The HTML string
     */
    function generateFormStart($action): string
    {
        return '<form'
            . ' action="' . pnVarPrepForDisplay($action) . '"'
            . ' method="post"'
            . ' enctype="' . ((empty($this->fileupload)) ? 'application/x-www-form-urlencoded' : 'multipart/form-data') . '"'
            . '>'
        ;
    }

    /**
     * Generate HTML tags to end a form.
     *
     * @access public
     * @return string The HTML string
     */
    function generateFormEnd(): string
    {
        return '</form>';
    }

    /**
     * Generate HTML tags for a submission button as part of a form.
     *
     * @access public
     * @param string $label (optional) the name of the submission button.  This
     * defaults to <code>'Submit'</code>
     * @param string $accesskey (optional) accesskey to active this button
     * @return string The HTML string
     */
    function generateFormSubmit($label = 'Submit', $accesskey = ''): string
    {
        $this->tabindex++;
        return '<input class="btn btn-primary"'
            . ' type="submit"'
            . ' value="' . pnVarPrepForDisplay($label) . '"'
            . ' align="middle"'
            . ((empty($accesskey)) ? '' : ' accesskey="' . pnVarPrepForDisplay($accesskey) . '"')
            . ' tabindex="' . $this->tabindex . '"'
            . ' />'
        ;
    }


    /**
     * Generate HTML tags for a hidden field as part of a form.
     *
     * @access public
     * @param mixed $fieldname the name of the hidden field.  can also be an array.
     * @param string $value the value of the hidden field
     * @return string The HTML string (empty when $fieldname is empty)
     */
    function generateFormHidden($fieldname, $value = ''): string
    {
        if (empty($fieldname)) {
            return '';
        }

        if (is_array($fieldname)) {
            $output = '';
            foreach ($fieldname as $n => $v) {
                $output .= '<input'
                    . ' type="hidden"'
                    . ' name="' . pnVarPrepForDisplay($n) . '"'
                    . ' id="' . pnVarPrepForDisplay($n) . '"'
                    . ' value="' . pnVarPrepForDisplay($v) . '"'
                    . '/>'
                ;
            }
        } else {
            $output = '<input'
                . ' type="hidden"'
                . ' name="' . pnVarPrepForDisplay($fieldname) . '"'
                . ' id="' . pnVarPrepForDisplay($fieldname) . '"'
                . ' value="' . pnVarPrepForDisplay($value) . '"'
                . ' />'
            ;
        }

        return $output;
    }

    /**
     * Generate HTML tags for a select field as part of a form.
     *
     * @access public
     * @since 1.13 - 2002/01/23
     * @param string $fieldname the name of the select field
     * @param array $data an array containing the data for the list.  Each array
     * entry is itself an array, containing the values for <code>'id'</code>
     * (the value returned if the entry is selected), <code>'name'</code>
     * (the string displayed for this entry) and <code>'selected'</code>
     * (optional, <code>1</code> if this option is selected)
     * @param integer $multiple (optional) <code>1</code> if the user is allowed to
     * make multiple selections
     * @param integer $size (optional) the number of entries that are visible in the
     * select at any one time.  Note that if the number
     * of actual items is less than this value then the select box will
     * shrink automatically to the correct size
     * @param string $selected (optional) selected value of <code>id</code>
     * @param string $accesskey (optional) accesskey to active this item
     * @return string The HTML string (empty when $fieldname is empty)
     */
    function generateFormSelectMultiple($fieldname, $data, $multiple = 0, $size = 1, $selected = '', $accesskey = '', $disable = false, $readonly = false): string
    {
        if (empty($fieldname)) {
            return '';
        }

        $disable_text = "";
        if ($disable) {
            $disable_text = " disabled ";
        }

        if ($readonly) {
            $disable_text = " disabled  ";
        }

        $this->tabindex++;

        // Set up selected if required
        if (!empty($selected)) {
            for ($i = 0; !empty($data[$i]); $i++) {
                if ($data[$i]['id'] == $selected) {
                    $data[$i]['selected'] = 1;
                }
            }
        }

        $c = count($data);
        if ($c < $size) {
            $size = $c;
        }

        $output = '<select class="form-control"'
            . ' name="' . pnVarPrepForDisplay($fieldname) . '"'
            . ' id="' . pnVarPrepForDisplay($fieldname) . '"'
            . ' size="' . pnVarPrepForDisplay($size) . '"'
            . (($multiple == 1) ? ' multiple="multiple"' : '')
            . ((empty($accesskey)) ? '' : ' accesskey="' . pnVarPrepForDisplay($accesskey) . '"')
            . ' tabindex="' . $this->tabindex . '"'
            . ' ' . $disable_text
            . '>'
        ;
        foreach ($data as $datum) {
            $output .= '<option'
                . ' value="' . pnVarPrepForDisplay($datum['id']) . '"'
                . ((empty($datum['selected'])) ? '' : ' selected="selected"')
                . '>'
                . pnVarPrepForDisplay($datum['name'])
                . '</option>'
            ;
        }

        $output .= '</select>';
        return $output;
    }
}
