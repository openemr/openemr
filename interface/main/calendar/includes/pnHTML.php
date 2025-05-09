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
 * Set object to keep generated HTML.
 *
 * After calling SetOutputMode() with this value, all future calls to
 * pnHTML methods will store their HTML in the objecr rather than
 * returning it to the calling process.
 *
 * $const _PNH_KEEPOUTPUT Keep the output from method calls
 */

define('_PNH_KEEPOUTPUT', 0);

/**
 * Set object to return generated HTML to caller.
 *
 * After calling SetOutputMode() with this value, all future calls to
 * pnHTML methods will return their HTML directly to the calling process
 * rather than storing it within the object.
 *
 * $const _PNH_RETURNOUTPUT Return the output from method calls
 */
define('_PNH_RETURNOUTPUT', 1);

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
 * This class is designed to make generating HTML output in PostNuke
 * very simple, and also allows for much greater control of output by
 * the site administrator.
 *
 *
 * <B>Example</B>
 * <pre>
 * // Information array
 * $colors = array(array('id' => 1,
 *                       'name' => 'Red',
 *                       'encoding' => 'ff0000'),
 *                 array('id' => 2,
 *                       'name' => 'Blue',
 *                       'encoding' => '00ff00'),
 *                 array('id' => 3,
 *                       'name' => 'Green',
 *                       'encoding' => '0000ff'));
 *
 * // Create the HTML object and start it
 * $myhtml = new pnHTML();
 * $myhtml->Start();
 *
 * // Add form to select a color
 * $myhtml->Text('&lt;P&gt;&lt;P&gt;');
 * $myhtml->FormStart('colorchosen.php');
 * $myhtml->Text('Select a color: ');
 * $myhtml->FormList('chosen', $colorinfo);
 * $myhtml->FormSubmit('That\'s the color I want');
 * $myhtml->FormEnd();
 *
 *
 * // End the HTML object and print it
 * $myhtml->End();
 * $myhtml->PrintPage();
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
     * @var string $header
     */
    var $header;

    /**
     * The pending HTML output
     *
     * @access private
     * @var string $output
     */
    var $output;

    /**
     * Return output?
     *
     * @access private
     * @var integer $return
     */
    var $return;

    /**
     * Parse text for output?
     *
     * @access private
     * @var integer $parse
     */
    var $parse;

    /**
     * Current tab index value
     *
     * @access private
     * @var integer $tabindex
     */
    var $tabindex;

    /**
     * File upload mode
     *
     * @access private
     * @since 1.13 - 2002/01/23
     * @var integer $fileupload
     */
    var $fileupload;

    /*==============================================================================*
     |                             Methods: Base                                    |
     *==============================================================================*/


    /**
     * pnHTML constructor.
     */
    function __construct()
    {
        $this->header = array ();
        $this->output = '';
        $this->return = _PNH_KEEPOUTPUT;
        $this->parse = _PNH_PARSEINPUT;
        $this->tabindex = 0;
        $this->fileupload = 0;
        return true;
    }

    /**
     * Return the current state of the output stream
     *
     * @access public
     * @since 1.13 - 2002/01/23
     * @return integer Current output state
     * @see SetOutputMode()
     */
    function GetOutputMode()
    {
        // The ONLY time this should be accessed directly
        return $this->return;
    }

    /**
     * Set state of the output stream
     *
     * @access public
     * @since 1.14 - 2002/01/29
     * @param int $st Output state to set to
     * @return integer Previous state
     * @see GetOutputMode()
     */
    function SetOutputMode($st)
    {
        $pre = $this->GetOutputMode();
        switch ($st) {
            default:
            case _PNH_KEEPOUTPUT:
                // The ONLY time this should be accessed directly
                $this->return = _PNH_KEEPOUTPUT;
                break;
            case _PNH_RETURNOUTPUT:
                // The ONLY time this should be accessed directly
                $this->return = _PNH_RETURNOUTPUT;
                break;
        }

        return $pre;
    }

    /**
     * Retrive the current input state
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
     * Return the HTML output from the buffer.
     *
     * Note that this function does not clear out the object's buffer.
     *
     * @access public
     * @since 1.15 - 2002/01/30
     * @return string An HTML string
     */
    function GetOutput()
    {
        return implode("\n", $this->header) . "\n" . $this->output;
    }

    /**
     * Print the HTML currently held in the object.
     *
     * Note that this function does not clear out the object's buffer.
     *
     * @access public
     */
    function PrintPage()
    {
        // Headers set by the system
        foreach ($this->header as $headerline) {
            header($headerline);
        }

        // Other headers
        // Removed as per patch #264 bvdbos
        // header('Content-length: ' . strlen($this->output));

        print $this->output;
    }

    /*==============================================================================*
     |                             Methods: Misc                                    |
     *==============================================================================*/

    /**
     * Put the appropriate HTML tags in place to create a valid start to HTML output.
     *
     * @access public
     * @return string An HTML string if <code>ReturnHTML()</code> has been called,
     * otherwise null
     * @see EndPage()
     */
    function StartPage()
    {
        ob_start();
        include 'header.php';
        print '<table class="w-100 border-0" cellpadding="0" cellspacing="0"><tr><td class="text-left align-top">';

        $output = ob_get_contents();
        @ob_end_clean();

        if ($this->GetOutputMode() == _PNH_RETURNOUTPUT) {
            return $output;
        } else {
            $this->output .= $output;
        }
    }

    /**
     * Put the appropriate HTML tags in place to create a valid end to HTML output.
     *
     * @access public
     * @return string An HTML string if <code>ReturnHTML()</code> has been called,
     * otherwise null
     * @see StartPage()
     */
    function EndPage()
    {
        global $index;
        if (pnVarCleanFromInput('module')) {
            $index = 0;
        } else {
            $index = 1;
        }

        ob_start();
        print '</td></tr></table>';
        include 'footer.php';
        $output = ob_get_contents();
        @ob_end_clean();

        if ($this->GetOutputMode() == _PNH_RETURNOUTPUT) {
            return $output;
        } else {
            $this->output .= $output;
        }
    }


    /*==============================================================================*
     |                             Methods: Text                                    |
     *==============================================================================*/

    /**
     * Add free-form text to the object's buffer
     *
     * @access public
     * @param string $text The text string to add
     * @return string An HTML string if <code>ReturnHTML()</code> has been called,
     * otherwise null
     */
    function Text($text)
    {
        if ($this->GetInputMode() == _PNH_PARSEINPUT) {
            $text = pnVarPrepForDisplay($text);
        }

        if ($this->GetOutputMode() == _PNH_RETURNOUTPUT) {
            return $text;
        } else {
            $this->output .= $text;
        }
    }


    /**
     * Add line break
     *
     * @access public
     * @param integer $numbreaks number of linebreaks to add
     * @return string An HTML string if <code>ReturnHTML()</code> has been called,
     * otherwise null
     */
    function Linebreak($numbreaks = 1)
    {
        $out = '';
        for ($i = 0; $i < $numbreaks; $i++) {
            $out .= '<br />';
        }

        if ($this->GetOutputMode() == _PNH_RETURNOUTPUT) {
            return $out;
        } else {
            $this->output .= $out;
        }
    }


    /*==============================================================================*
     |                             Methods: Forms                                   |
     *==============================================================================*/

    /**
     * Add HTML tags to start a form.
     *
     * @access public
     * @param string $action the URL that this form should go to on submission
     * @return string An HTML string if <code>ReturnHTML()</code> has been called,
     * otherwise null
     */
    function FormStart($action)
    {
        $output = '<form'
            . ' action="' . pnVarPrepForDisplay($action) . '"'
            . ' method="post"'
            . ' enctype="' . ((empty($this->fileupload)) ? 'application/x-www-form-urlencoded' : 'multipart/form-data') . '"'
            . '>'
        ;
        if ($this->GetOutputMode() == _PNH_RETURNOUTPUT) {
            return $output;
        } else {
            $this->output .= $output;
        }
    }

    /**
     * Add HTML tags to end a form.
     *
     * @access public
     * @return string An HTML string if <code>ReturnHTML()</code> has been called,
     * otherwise null
     */
    function FormEnd()
    {
        $output = '</form>';

        if ($this->GetOutputMode() == _PNH_RETURNOUTPUT) {
            return $output;
        } else {
            $this->output .= $output;
        }
    }

    /**
     * Add HTML tags for a submission button as part of a form.
     *
     * @access public
     * @param string $label (optional) the name of the submission button.  This
     * defaults to <code>'Submit'</code>
     * @param string $accesskey (optional) accesskey to active this button
     * @return string An HTML string if <code>ReturnHTML()</code> has been called,
     * otherwise null
     */
    function FormSubmit($label = 'Submit', $accesskey = '')
    {
        $this->tabindex++;
        $output = '<input class="btn btn-primary"'
            . ' type="submit"'
            . ' value="' . pnVarPrepForDisplay($label) . '"'
            . ' align="middle"'
            . ((empty($accesskey)) ? '' : ' accesskey="' . pnVarPrepForDisplay($accesskey) . '"')
            . ' tabindex="' . $this->tabindex . '"'
            . ' />'
        ;
        if ($this->GetOutputMode() == _PNH_RETURNOUTPUT) {
            return $output;
        } else {
            $this->output .= $output;
        }
    }


    /**
     * Add HTML tags for a hidden field as part of a form.
     *
     * @access public
     * @param mixed $fieldname the name of the hidden field.  can also be an array.
     * @param string $value the value of the hidden field
     * @return string An HTML string if <code>ReturnHTML()</code> has been called,
     * otherwise null
     */
    function FormHidden($fieldname, $value = '')
    {
        if (empty($fieldname)) {
            return;
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

        if ($this->GetOutputMode() == _PNH_RETURNOUTPUT) {
            return $output;
        } else {
            $this->output .= $output;
        }
    }

    /**
     * Add HTML tags for a select field as part of a form.
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
     * @return string An HTML string if <code>ReturnHTML()</code> has been called,
     * otherwise null
     */
    function FormSelectMultiple($fieldname, $data, $multiple = 0, $size = 1, $selected = '', $accesskey = '', $disable = false, $readonly = false)
    {
        if (empty($fieldname)) {
            return;
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
        if ($this->GetOutputMode() == _PNH_RETURNOUTPUT) {
            return $output;
        } else {
            $this->output .= $output;
        }
    }
}
