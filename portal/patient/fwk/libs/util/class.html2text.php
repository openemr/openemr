<?php

/**
 * @deprecated use html2text.php convert_html_to_text function instead
 */

require_once 'html2text.php';
class html2text
{
    private $html;
    public function __construct($html)
    {
        $this->html = $html;
    }

    /**
     *
     * @deprecated use html2text.php convert_html_to_text function instead
     */
    public function get_text()
    {
        return convert_html_to_text($this->html);
    }
}
