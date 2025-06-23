<?php

class parseCSV
{
    /*
     *
     * Class: parseCSV v0.4.3 beta
     * http://code.google.com/p/parsecsv-for-php/
     *
     *
     * Fully conforms to the specifications lined out on wikipedia:
     * - http://en.wikipedia.org/wiki/Comma-separated_values
     *
     * Based on the concept of Ming Hong Ng's CsvFileParser class:
     * - http://minghong.blogspot.com/2006/07/csv-parser-for-php.html
     *
     *
     *
     * Copyright (c) 2007 Jim Myhrberg (jim@zydev.info).
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in
     * all copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
     * THE SOFTWARE.
     *
     *
     *
     * Code Examples
     * ----------------
     * # general usage
     * $csv = new parseCSV('data.csv');
     * print_r($csv->data);
     * ----------------
     * # tab delimited, and encoding conversion
     * $csv = new parseCSV();
     * $csv->encoding('UTF-16', 'UTF-8');
     * $csv->delimiter = "\t";
     * $csv->parse('data.tsv');
     * print_r($csv->data);
     * ----------------
     * # auto-detect delimiter character
     * $csv = new parseCSV();
     * $csv->auto('data.csv');
     * print_r($csv->data);
     * ----------------
     * # modify data in a csv file
     * $csv = new parseCSV();
     * $csv->sort_by = 'id';
     * $csv->parse('data.csv');
     * # "4" is the value of the "id" column of the CSV row
     * $csv->data[4] = array('firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@doe.com');
     * $csv->save();
     * ----------------
     * # add row/entry to end of CSV file
     * # - only recommended when you know the extact sctructure of the file
     * $csv = new parseCSV();
     * $csv->save('data.csv', array('1986', 'Home', 'Nowhere', ''), true);
     * ----------------
     * # convert 2D array to csv data and send headers
     * # to browser to treat output as a file and download it
     * $csv = new parseCSV();
     * $csv->output (true, 'movies.csv', $array);
     * ----------------
     *
     *
     */

    /**
     * Configuration
     * - set these options with $object->var_name = 'value';
     */

    // use first line/entry as field names
    var $heading = true;

    // override field names
    var $fields = array ();

    // sort entries by this field
    var $sort_by = null;
    var $sort_reverse = false;

    // sort behavior passed to ksort/krsort functions
    // regular = SORT_REGULAR
    // numeric = SORT_NUMERIC
    // string = SORT_STRING
    var $sort_type = null;

    // delimiter (comma) and enclosure (double quote)
    var $delimiter = ',';
    var $enclosure = '"';

    // basic SQL-like conditions for row matching
    var $conditions = null;

    // number of rows to ignore from beginning of data
    var $offset = null;

    // limits the number of returned rows to specified amount
    var $limit = null;

    // number of rows to analyze when attempting to auto-detect delimiter
    var $auto_depth = 15;

    // characters to ignore when attempting to auto-detect delimiter
    var $auto_non_chars = "a-zA-Z0-9\n\r";

    // preferred delimiter characters, only used when all filtering method
    // returns multiple possible delimiters (happens very rarely)
    var $auto_preferred = ",;\t.:|";

    // character encoding options
    var $convert_encoding = false;
    var $input_encoding = 'ISO-8859-1';
    var $output_encoding = 'ISO-8859-1';

    // used by unparse(), save(), and output() functions
    var $linefeed = "\r\n";

    // only used by output() function
    var $output_delimiter = ',';
    var $output_filename = 'data.csv';

    // keep raw file data in memory after successful parsing (useful for debugging)
    var $keep_file_data = false;

    /**
     * Internal variables
     */

    // current file
    var $file;

    // loaded file contents
    var $file_data;

    // error while parsing input data
    // 0 = No errors found. Everything should be fine :)
    // 1 = Hopefully correctable syntax error was found.
    // 2 = Enclosure character (double quote by default)
    // was found in non-enclosed field. This means
    // the file is either corrupt, or does not
    // standard CSV formatting. Please validate
    // the parsed data yourself.
    var $error = 0;

    // detailed error info
    var $error_info = array ();

    // array of field values in data parsed
    var $titles = array ();

    // two dimentional array of CSV data
    var $data = array ();

    /**
     * Constructor
     *
     * @param
     *          input CSV file or string
     * @return nothing
     */
    function __construct($input = null, $offset = null, $limit = null, $conditions = null)
    {
        if ($offset !== null) {
            $this->offset = $offset;
        }

        if ($limit !== null) {
            $this->limit = $limit;
        }

        if (count($conditions) > 0) {
            $this->conditions = $conditions;
        }

        if (! empty($input)) {
            $this->parse($input);
        }
    }

    // ==============================================
    // ----- [ Main Functions ] ---------------------
    // ==============================================

    /**
     * Parse CSV file or string
     *
     * @param
     *          input CSV file or string
     * @return nothing
     */
    function parse($input = null, $offset = null, $limit = null, $conditions = null)
    {
        if ($input === null) {
            $input = $this->file;
        }

        if (! empty($input)) {
            if ($offset !== null) {
                $this->offset = $offset;
            }

            if ($limit !== null) {
                $this->limit = $limit;
            }

            if (count($conditions) > 0) {
                $this->conditions = $conditions;
            }

            if (is_readable($input)) {
                $this->data = $this->parse_file($input);
            } else {
                $this->file_data = &$input;
                $this->data = $this->parse_string();
            }

            if ($this->data === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Save changes, or new file and/or data
     *
     * @param
     *          file file to save to
     * @param
     *          data 2D array with data
     * @param
     *          append append current data to end of target CSV if exists
     * @param
     *          fields field names
     * @return true or false
     */
    function save($file = null, $data = array(), $append = false, $fields = array())
    {
        if (empty($file)) {
            $file = &$this->file;
        }

        $mode = ($append) ? 'at' : 'wt';
        $is_php = (preg_match('/\.php$/i', $file)) ? true : false;
        return $this->_wfile($file, $this->unparse($data, $fields, $append, $is_php), $mode);
    }

    /**
     * Generate CSV based string for output
     *
     * @param
     *          filename if specified, headers and data will be output directly to browser as a downloable file
     * @param
     *          data 2D array with data
     * @param
     *          fields field names
     * @param
     *          delimiter delimiter used to separate data
     * @return CSV data using delimiter of choice, or default
     */
    function output($filename = null, $data = array(), $fields = array(), $delimiter = null)
    {
        if (empty($filename)) {
            $filename = $this->output_filename;
        }

        if ($delimiter === null) {
            $delimiter = $this->output_delimiter;
        }

        $data = $this->unparse($data, $fields, null, null, $delimiter);
        if ($filename !== null) {
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo $data;
        }

        return $data;
    }

    /**
     * Convert character encoding
     *
     * @param
     *          input input character encoding, uses default if left blank
     * @param
     *          output output character encoding, uses default if left blank
     * @return nothing
     */
    function encoding($input = null, $output = null)
    {
        $this->convert_encoding = true;
        if ($input !== null) {
            $this->input_encoding = $input;
        }

        if ($output !== null) {
            $this->output_encoding = $output;
        }
    }

    /**
     * Auto-Detect Delimiter: Find delimiter by analyzing a specific number of
     * rows to determine most probable delimiter character
     *
     * @param
     *          file local CSV file
     * @param
     *          parse true/false parse file directly
     * @param
     *          search_depth number of rows to analyze
     * @param
     *          preferred preferred delimiter characters
     * @param
     *          enclosure enclosure character, default is double quote (").
     * @return delimiter character
     */
    function auto($file = null, $parse = true, $search_depth = null, $preferred = null, $enclosure = null)
    {
        if ($file === null) {
            $file = $this->file;
        }

        if (empty($search_depth)) {
            $search_depth = $this->auto_depth;
        }

        if ($enclosure === null) {
            $enclosure = $this->enclosure;
        }

        if ($preferred === null) {
            $preferred = $this->auto_preferred;
        }

        if (empty($this->file_data)) {
            if ($this->_check_data($file)) {
                $data = &$this->file_data;
            } else {
                return false;
            }
        } else {
            $data = &$this->file_data;
        }

        $chars = array ();
        $strlen = strlen($data);
        $enclosed = false;
        $n = 1;
        $to_end = true;

        // walk specific depth finding posssible delimiter characters
        for ($i = 0; $i < $strlen; $i++) {
            $ch = $data [$i];
            $nch = (isset($data [$i + 1])) ? $data [$i + 1] : false;
            $pch = (isset($data [$i - 1])) ? $data [$i - 1] : false;

            // open and closing quotes
            if ($ch == $enclosure) {
                if (! $enclosed || $nch != $enclosure) {
                    $enclosed = ($enclosed) ? false : true;
                } elseif ($enclosed) {
                    $i++;
                }

                // end of row
            } elseif (($ch == "\n" && $pch != "\r" || $ch == "\r") && ! $enclosed) {
                if ($n >= $search_depth) {
                    $strlen = 0;
                    $to_end = false;
                } else {
                    $n++;
                }

                // count character
            } elseif (! $enclosed) {
                if (! preg_match('/[' . preg_quote($this->auto_non_chars, '/') . ']/i', $ch)) {
                    if (! isset($chars [$ch] [$n])) {
                        $chars [$ch] [$n] = 1;
                    } else {
                        $chars [$ch] [$n]++;
                    }
                }
            }
        }

        // filtering
        $depth = ($to_end) ? $n - 1 : $n;
        $filtered = array ();
        foreach ($chars as $char => $value) {
            if ($match = $this->_check_count($char, $value, $depth, $preferred)) {
                $filtered [$match] = $char;
            }
        }

        // capture most probable delimiter
        ksort($filtered);
        $this->delimiter = reset($filtered);

        // parse data
        if ($parse) {
            $this->data = $this->parse_string();
        }

        return $this->delimiter;
    }

    // ==============================================
    // ----- [ Core Functions ] ---------------------
    // ==============================================

    /**
     * Read file to string and call parse_string()
     *
     * @param
     *          file local CSV file
     * @return 2D array with CSV data, or false on failure
     */
    function parse_file($file = null)
    {
        if ($file === null) {
            $file = $this->file;
        }

        if (empty($this->file_data)) {
            $this->load_data($file);
        }

        return (! empty($this->file_data)) ? $this->parse_string() : false;
    }

    /**
     * Parse CSV strings to arrays
     *
     * @param
     *          data CSV string
     * @return 2D array with CSV data, or false on failure
     */
    function parse_string($data = null)
    {
        if (empty($data)) {
            if ($this->_check_data()) {
                $data = &$this->file_data;
            } else {
                return false;
            }
        }

        $white_spaces = str_replace($this->delimiter, '', " \t\x0B\0");

        $rows = array ();
        $row = array ();
        $row_count = 0;
        $current = '';
        $head = (! empty($this->fields)) ? $this->fields : array ();
        $col = 0;
        $enclosed = false;
        $was_enclosed = false;
        $strlen = strlen($data);

        // walk through each character
        for ($i = 0; $i < $strlen; $i++) {
            $ch = $data [$i];
            $nch = (isset($data [$i + 1])) ? $data [$i + 1] : false;
            $pch = (isset($data [$i - 1])) ? $data [$i - 1] : false;

            // open/close quotes, and inline quotes
            if ($ch == $this->enclosure) {
                if (! $enclosed) {
                    if (ltrim($current, $white_spaces) == '') {
                        $enclosed = true;
                        $was_enclosed = true;
                    } else {
                        $this->error = 2;
                        $error_row = count($rows) + 1;
                        $error_col = $col + 1;
                        if (! isset($this->error_info [$error_row . '-' . $error_col])) {
                            $this->error_info [$error_row . '-' . $error_col] = array (
                                    'type' => 2,
                                    'info' => 'Syntax error found on row ' . $error_row . '. Non-enclosed fields can not contain double-quotes.',
                                    'row' => $error_row,
                                    'field' => $error_col,
                                    'field_name' => (! empty($head [$col])) ? $head [$col] : null
                            );
                        }

                        $current .= $ch;
                    }
                } elseif ($nch == $this->enclosure) {
                    $current .= $ch;
                    $i++;
                } elseif ($nch != $this->delimiter && $nch != "\r" && $nch != "\n") {
                    for ($x = ($i + 1); isset($data [$x]) && ltrim($data [$x], $white_spaces) == ''; $x++) {
                    }

                    if ($data [$x] == $this->delimiter) {
                        $enclosed = false;
                        $i = $x;
                    } else {
                        if ($this->error < 1) {
                            $this->error = 1;
                        }

                        $error_row = count($rows) + 1;
                        $error_col = $col + 1;
                        if (! isset($this->error_info [$error_row . '-' . $error_col])) {
                            $this->error_info [$error_row . '-' . $error_col] = array (
                                    'type' => 1,
                                    'info' => 'Syntax error found on row ' . (count($rows) + 1) . '. ' . 'A single double-quote was found within an enclosed string. ' . 'Enclosed double-quotes must be escaped with a second double-quote.',
                                    'row' => count($rows) + 1,
                                    'field' => $col + 1,
                                    'field_name' => (! empty($head [$col])) ? $head [$col] : null
                            );
                        }

                        $current .= $ch;
                        $enclosed = false;
                    }
                } else {
                    $enclosed = false;
                }

                // end of field/row
            } elseif (($ch == $this->delimiter || $ch == "\n" || $ch == "\r") && ! $enclosed) {
                $key = (! empty($head [$col])) ? $head [$col] : $col;
                $row [$key] = ($was_enclosed) ? $current : trim($current);
                $current = '';
                $was_enclosed = false;
                $col++;

                // end of row
                if ($ch == "\n" || $ch == "\r") {
                    if ($this->_validate_offset($row_count) && $this->_validate_row_conditions($row, $this->conditions)) {
                        if ($this->heading && empty($head)) {
                            $head = $row;
                        } elseif (empty($this->fields) || (! empty($this->fields) && (($this->heading && $row_count > 0) || ! $this->heading))) {
                            if (! empty($this->sort_by) && ! empty($row [$this->sort_by])) {
                                if (isset($rows [$row [$this->sort_by]])) {
                                    $rows [$row [$this->sort_by] . '_0'] = &$rows [$row [$this->sort_by]];
                                    unset($rows [$row [$this->sort_by]]);
                                    for ($sn = 1; isset($rows [$row [$this->sort_by] . '_' . $sn]); $sn++) {
                                    }

                                    $rows [$row [$this->sort_by] . '_' . $sn] = $row;
                                } else {
                                    $rows [$row [$this->sort_by]] = $row;
                                }
                            } else {
                                $rows [] = $row;
                            }
                        }
                    }

                    $row = array ();
                    $col = 0;
                    $row_count++;
                    if ($this->sort_by === null && $this->limit !== null && count($rows) == $this->limit) {
                        $i = $strlen;
                    }

                    if ($ch == "\r" && $nch == "\n") {
                        $i++;
                    }
                }

                // append character to current field
            } else {
                $current .= $ch;
            }
        }

        $this->titles = $head;
        if (! empty($this->sort_by)) {
            $sort_type = SORT_REGULAR;
            if ($this->sort_type == 'numeric') {
                $sort_type = SORT_NUMERIC;
            } elseif ($this->sort_type == 'string') {
                $sort_type = SORT_STRING;
            }

            ($this->sort_reverse) ? krsort($rows, $sort_type) : ksort($rows, $sort_type);
            if ($this->offset !== null || $this->limit !== null) {
                $rows = array_slice($rows, ($this->offset === null ? 0 : $this->offset), $this->limit, true);
            }
        }

        if (! $this->keep_file_data) {
            $this->file_data = null;
        }

        return $rows;
    }

    /**
     * Create CSV data from array
     *
     * @param
     *          data 2D array with data
     * @param
     *          fields field names
     * @param
     *          append if true, field names will not be output
     * @param
     *          is_php if a php die() call should be put on the first
     *          line of the file, this is later ignored when read.
     * @param
     *          delimiter field delimiter to use
     * @return CSV data (text string)
     */
    function unparse($data = array(), $fields = array(), $append = false, $is_php = false, $delimiter = null)
    {
        if (! is_array($data) || empty($data)) {
            $data = &$this->data;
        }

        if (! is_array($fields) || empty($fields)) {
            $fields = &$this->titles;
        }

        if ($delimiter === null) {
            $delimiter = $this->delimiter;
        }

        $string = ($is_php) ? "<?php header('Status: 403'); die(' '); ?>" . $this->linefeed : '';
        $entry = array ();

        // create heading
        if ($this->heading && ! $append && ! empty($fields)) {
            foreach ($fields as $key => $value) {
                $entry [] = $this->_enclose_value($value);
            }

            $string .= implode($delimiter, $entry) . $this->linefeed;
            $entry = array ();
        }

        // create data
        foreach ($data as $key => $row) {
            foreach ($row as $field => $value) {
                $entry [] = $this->_enclose_value($value);
            }

            $string .= implode($delimiter, $entry) . $this->linefeed;
            $entry = array ();
        }

        return $string;
    }

    /**
     * Load local file or string
     *
     * @param
     *          input local CSV file
     * @return true or false
     */
    function load_data($input = null)
    {
        $data = null;
        $file = null;
        if ($input === null) {
            $file = $this->file;
        } elseif (file_exists($input)) {
            $file = $input;
        } else {
            $data = $input;
        }

        if (! empty($data) || $data = $this->_rfile($file)) {
            if ($this->file != $file) {
                $this->file = $file;
            }

            if (preg_match('/\.php$/i', $file) && preg_match('/<\?.*?\?>(.*)/ims', $data, $strip)) {
                $data = ltrim($strip [1]);
            }

            if ($this->convert_encoding) {
                $data = iconv($this->input_encoding, $this->output_encoding, $data);
            }

            if (substr($data, - 1) != "\n") {
                $data .= "\n";
            }

            $this->file_data = &$data;
            return true;
        }

        return false;
    }

    // ==============================================
    // ----- [ Internal Functions ] -----------------
    // ==============================================

    /**
     * Validate a row against specified conditions
     *
     * @param
     *          row array with values from a row
     * @param
     *          conditions specified conditions that the row must match
     * @return true of false
     */
    function _validate_row_conditions($row = array(), $conditions = null)
    {
        if (! empty($row)) {
            if (! empty($conditions)) {
                $conditions = (strpos($conditions, ' OR ') !== false) ? explode(' OR ', $conditions) : array (
                        $conditions
                );
                $or = '';
                foreach ($conditions as $key => $value) {
                    if (strpos($value, ' AND ') !== false) {
                        $value = explode(' AND ', $value);
                        $and = '';
                        foreach ($value as $k => $v) {
                            $and .= $this->_validate_row_condition($row, $v);
                        }

                        $or .= (strpos($and, '0') !== false) ? '0' : '1';
                    } else {
                        $or .= $this->_validate_row_condition($row, $value);
                    }
                }

                return (strpos($or, '1') !== false) ? true : false;
            }

            return true;
        }

        return false;
    }

    /**
     * Validate a row against a single condition
     *
     * @param
     *          row array with values from a row
     * @param
     *          condition specified condition that the row must match
     * @return true of false
     */
    function _validate_row_condition($row, $condition)
    {
        $operators = array (
                '=',
                'equals',
                'is',
                '!=',
                'is not',
                '<',
                'is less than',
                '>',
                'is greater than',
                '<=',
                'is less than or equals',
                '>=',
                'is greater than or equals',
                'contains',
                'does not contain'
        );
        $operators_regex = array ();
        foreach ($operators as $value) {
            $operators_regex [] = preg_quote($value, '/');
        }

        $operators_regex = implode('|', $operators_regex);
        if (preg_match('/^(.+) (' . $operators_regex . ') (.+)$/i', trim($condition), $capture)) {
            $field = $capture [1];
            $op = $capture [2];
            $value = $capture [3];
            if (preg_match('/^([\'\"]{1})(.*)([\'\"]{1})$/i', $value, $capture)) {
                if ($capture [1] == $capture [3]) {
                    $value = $capture [2];
                    $value = str_replace("\\n", "\n", $value);
                    $value = str_replace("\\r", "\r", $value);
                    $value = str_replace("\\t", "\t", $value);
                    $value = stripslashes($value);
                }
            }

            if (array_key_exists($field, $row)) {
                if (($op == '=' || $op == 'equals' || $op == 'is') && $row [$field] == $value) {
                    return '1';
                } elseif (($op == '!=' || $op == 'is not') && $row [$field] != $value) {
                    return '1';
                } elseif (($op == '<' || $op == 'is less than') && $row [$field] < $value) {
                    return '1';
                } elseif (($op == '>' || $op == 'is greater than') && $row [$field] > $value) {
                    return '1';
                } elseif (($op == '<=' || $op == 'is less than or equals') && $row [$field] <= $value) {
                    return '1';
                } elseif (($op == '>=' || $op == 'is greater than or equals') && $row [$field] >= $value) {
                    return '1';
                } elseif ($op == 'contains' && preg_match('/' . preg_quote($value, '/') . '/i', $row [$field])) {
                    return '1';
                } elseif ($op == 'does not contain' && ! preg_match('/' . preg_quote($value, '/') . '/i', $row [$field])) {
                    return '1';
                } else {
                    return '0';
                }
            }
        }

        return '1';
    }

    /**
     * Validates if the row is within the offset or not if sorting is disabled
     *
     * @param
     *          current_row the current row number being processed
     * @return true of false
     */
    function _validate_offset($current_row)
    {
        if ($this->sort_by === null && $this->offset !== null && $current_row < $this->offset) {
            return false;
        }

        return true;
    }

    /**
     * Enclose values if needed
     * - only used by unparse()
     *
     * @param
     *          value string to process
     * @return Processed value
     */
    function _enclose_value($value = null)
    {
        if ($value !== null && $value != '') {
            $delimiter = preg_quote($this->delimiter, '/');
            $enclosure = preg_quote($this->enclosure, '/');
            if (preg_match("/" . $delimiter . "|" . $enclosure . "|\n|\r/i", $value) || ($value [0] == ' ' || substr($value, - 1) == ' ')) {
                $value = str_replace($this->enclosure, $this->enclosure . $this->enclosure, $value);
                $value = $this->enclosure . $value . $this->enclosure;
            }
        }

        return $value;
    }

    /**
     * Check file data
     *
     * @param
     *          file local filename
     * @return true or false
     */
    function _check_data($file = null)
    {
        if (empty($this->file_data)) {
            if ($file === null) {
                $file = $this->file;
            }

            return $this->load_data($file);
        }

        return true;
    }

    /**
     * Check if passed info might be delimiter
     * - only used by find_delimiter()
     *
     * @return special string used for delimiter selection, or false
     */
    function _check_count($char, $array, $depth, $preferred)
    {
        if ($depth == count($array)) {
            $first = null;
            $equal = null;
            $almost = false;
            foreach ($array as $key => $value) {
                if ($first == null) {
                    $first = $value;
                } elseif ($value == $first && $equal !== false) {
                    $equal = true;
                } elseif ($value == $first + 1 && $equal !== false) {
                    $equal = true;
                    $almost = true;
                } else {
                    $equal = false;
                }
            }

            if ($equal) {
                $match = ($almost) ? 2 : 1;
                $pref = strpos($preferred, $char);
                $pref = ($pref !== false) ? str_pad($pref, 3, '0', STR_PAD_LEFT) : '999';
                return $pref . $match . '.' . (99999 - str_pad($first, 5, '0', STR_PAD_LEFT));
            } else {
                return false;
            }
        }
    }

    /**
     * Read local file
     *
     * @param
     *          file local filename
     * @return Data from file, or false on failure
     */
    function _rfile($file = null)
    {
        if (is_readable($file)) {
            if (! ($fh = fopen($file, 'r'))) {
                return false;
            }

            $data = fread($fh, filesize($file));
            fclose($fh);
            return $data;
        }

        return false;
    }

    /**
     * Write to local file
     *
     * @param
     *          file local filename
     * @param
     *          string data to write to file
     * @param
     *          mode fopen() mode
     * @param
     *          lock flock() mode
     * @return true or false
     */
    function _wfile($file, $string = '', $mode = 'wb', $lock = 2)
    {
        if ($fp = fopen($file, $mode)) {
            flock($fp, $lock);
            $re = fwrite($fp, $string);
            $re2 = fclose($fp);
            if ($re != false && $re2 != false) {
                return true;
            }
        }

        return false;
    }
}
