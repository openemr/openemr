<?php
/*
 * Project:     Smarty: the PHP compiling template engine
 * File:        Smarty.class.php
 * Author:      Monte Ohrt <monte@ispi.net>
 *              Andrei Zmievski <andrei@php.net>
 *
 * Version:     2.3.1
 * Copyright:   2001,2002 ispi of Lincoln, Inc.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-general-subscribe@lists.php.net
 *
 * You may contact the authors of Smarty by e-mail at:
 * monte@ispi.net
 * andrei@php.net
 *
 * Or, write to:
 * Monte Ohrt
 * Director of Technology, ispi
 * 237 S. 70th suite 220
 * Lincoln, NE 68510
 *
 * The latest version of Smarty can be obtained from:
 * http://www.phpinsider.com/
 *
 */

// set SMARTY_DIR to absolute path to Smarty library files.
// if not defined, include_path will be used.

define('DIR_SEP', DIRECTORY_SEPARATOR);

if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', dirname(__FILE__) . DIR_SEP);
}

define('SMARTY_PHP_PASSTHRU',   0);
define('SMARTY_PHP_QUOTE',      1);
define('SMARTY_PHP_REMOVE',     2);
define('SMARTY_PHP_ALLOW',      3);

class Smarty
{

/**************************************************************************/
/* BEGIN SMARTY CONFIGURATION SECTION                                     */
/* Set the following config variables to your liking.                     */
/**************************************************************************/

    // public vars
    var $template_dir    =  'templates';       // name of directory for templates
    var $compile_dir     =  'templates_c';     // name of directory for compiled templates
    var $config_dir      =  'configs';         // directory where config files are located
    var $plugins_dir     =  array('plugins');  // plugin directories

    var $debugging       =  false;             // enable debugging console true/false
    var $debug_tpl       =  '';                // path to debug console template
                                               // (this gets set in the constructor)
    var $debugging_ctrl  =  'NONE';            // Possible values:
                                               // NONE - no debug control allowed
                                               // URL - enable debugging when keyword
                                               //       SMARTY_DEBUG is found in $QUERY_STRING

    var $global_assign   =  array( 'HTTP_SERVER_VARS' => array( 'SCRIPT_NAME' )
                                 );     // variables from the GLOBALS array
                                        // that are implicitly assigned
                                        // to all templates
    var $undefined       =  null;       // undefined variables in $global_assign will be
                                        // created with this value
    var $autoload_filters = array();    // indicates which filters will be auto-loaded

    var $compile_check   =  true;       // whether to check for compiling step or not:
                                        // This is generally set to false once the
                                        // application is entered into production and
                                        // initially compiled. Leave set to true
                                        // during development. true/false default true.
//force compile - pennfirm
    var $force_compile   =  true;      // force templates to compile every time,
                                        // overrides cache settings. default false.

    var $caching         =  0;     		// enable caching. can be one of 0/1/2.
										// 0 = no caching
										// 1 = use class cache_lifetime value
										// 2 = use cache_lifetime in cache file
										// default = 0.
    var $cache_dir       =  'cache';    // name of directory for template cache files
    var $cache_lifetime  =  3600;       // number of seconds cached content will persist.
										// 0 = always regenerate cache,
                                        // -1 = never expires. default is one hour (3600)
    var $cache_handler_func   = null;   // function used for cached content. this is
                                        // an alternative to using the built-in file
                                        // based caching.
    var $cache_modified_check = false;  // respect If-Modified-Since headers on cached content


    var $default_template_handler_func = ''; // function to handle missing templates

    var $php_handling    =  SMARTY_PHP_PASSTHRU;
                                        // how smarty handles php tags in the templates
                                        // possible values:
                                        // SMARTY_PHP_PASSTHRU -> echo tags as is
                                        // SMARTY_PHP_QUOTE    -> escape tags as entities
                                        // SMARTY_PHP_REMOVE   -> remove php tags
                                        // SMARTY_PHP_ALLOW    -> execute php tags
                                        // default: SMARTY_PHP_PASSTHRU


    var $security       =   false;      // enable template security (default false)
    var $secure_dir     =   array('templates'); // array of directories considered secure
    var $security_settings  = array(
                                    'PHP_HANDLING'    => false,
                                    'IF_FUNCS'        => array('array', 'list',
                                                               'isset', 'empty',
                                                               'count', 'sizeof',
                                                               'in_array', 'is_array'),
                                    'INCLUDE_ANY'     => false,
                                    'PHP_TAGS'        => false,
                                    'MODIFIER_FUNCS'  => array('count')
                                   );
    var $trusted_dir        = array();  // directories where trusted templates & php scripts
                                        // reside ($security is disabled during their
                                        // inclusion/execution).

    var $left_delimiter  =  '{';        // template tag delimiters.
    var $right_delimiter =  '}';

    var $compiler_class        =   'Smarty_Compiler'; // the compiler class used by
                                                      // Smarty to compile templates

    var $request_vars_order    = "EGPCS";   // the order in which request variables are
                                            // registered, similar to variables_order
                                            // in php.ini

    var $compile_id            = null;      // persistent compile identifier
	var $use_sub_dirs          = true;		// use sub dirs for cache and compiled files?
											// sub directories are more efficient, but
											// you can set this to false if your PHP environment
											// does not allow the creation of them.
	var $default_modifiers		= array();
											// modifiers to implicitly append to every var
											// example: array('escape:"htmlall"');

/**************************************************************************/
/* END SMARTY CONFIGURATION SECTION                                       */
/* There should be no need to touch anything below this line.             */
/**************************************************************************/

    // internal vars
    var $_error_msg            = false;      // error messages. true/false
    var $_tpl_vars             = array();    // where assigned template vars are kept
    var $_smarty_vars          = null;       // stores run-time $smarty.* vars
    var $_sections             = array();    // keeps track of sections
    var $_foreach              = array();    // keeps track of foreach blocks
    var $_tag_stack            = array();    // keeps track of tag hierarchy
    var $_conf_obj             = null;       // configuration object
    var $_config               = array();    // loaded configuration settings
    var $_smarty_md5           = 'f8d698aea36fcbead2b9d5359ffca76f'; // md5 checksum of the string 'Smarty'
    var $_version              = '2.3.1';    // Smarty version number
    var $_extract              = false;      // flag for custom functions
    var $_inclusion_depth      = 0;          // current template inclusion depth
    var $_compile_id           = null;       // for different compiled templates
    var $_smarty_debug_id      = 'SMARTY_DEBUG'; // text in URL to enable debug mode
    var $_smarty_debug_info    = array();    // debugging information for debug console
    var $_cache_info           = array();    // info that makes up a cache file
    var $_plugins              = array(      // table keeping track of plugins
                                       'modifier'      => array(),
                                       'function'      => array(),
                                       'block'         => array(),
                                       'compiler'      => array(),
                                       'prefilter'     => array(),
                                       'postfilter'    => array(),
                                       'outputfilter'  => array(),
                                       'resource'      => array(),
                                       'insert'        => array());


/*======================================================================*\
    Function: Smarty
    Purpose:  Constructor
\*======================================================================*/
    function Smarty()
    {
        foreach ($this->global_assign as $key => $var_name) {
            if (is_array($var_name)) {
                foreach ($var_name as $var) {
                    if (isset($GLOBALS[$key][$var])) {
                        $this->assign($var, $GLOBALS[$key][$var]);
                    } else {
                        $this->assign($var, $this->undefined);
                    }
                }
            } else {
                if (isset($GLOBALS[$var_name])) {
                    $this->assign($var_name, $GLOBALS[$var_name]);
                } else {
                    $this->assign($var_name, $this->undefined);
                }
            }
        }

		if(empty($this->debug_tpl)) {
			// set path to debug template from SMARTY_DIR
			$this->debug_tpl = 'file:'.SMARTY_DIR.'debug.tpl';
		}
    }


/*======================================================================*\
    Function:   assign()
    Purpose:    assigns values to template variables
\*======================================================================*/
    function assign($tpl_var, $value = NULL)
    {
        if (is_array($tpl_var)){
            foreach ($tpl_var as $key => $val) {
                if ($key != '' && isset($val)) {
                    $this->_tpl_vars[$key] = $val;
                }
            }
        } else {
            if ($tpl_var != '' && isset($value))
                $this->_tpl_vars[$tpl_var] = $value;
        }
        $this->_extract = true;
    }

/*======================================================================*\
    Function:   assign_by_ref()
    Purpose:    assigns values to template variables by reference
\*======================================================================*/
    function assign_by_ref($tpl_var, &$value)
    {
        if ($tpl_var != '' && isset($value))
            $this->_tpl_vars[$tpl_var] = &$value;
        $this->_extract = true;
    }

/*======================================================================*\
    Function: append
    Purpose:  appends values to template variables
\*======================================================================*/
    function append($tpl_var, $value = NULL)
    {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $key => $val) {
                if ($key != '') {
					if(!@is_array($this->_tpl_vars[$key])) {
						settype($this->_tpl_vars[$key],'array');
					}
                    $this->_tpl_vars[$key][] = $val;
                }
            }
        } else {
            if ($tpl_var != '' && isset($value)) {
				if(!@is_array($this->_tpl_vars[$tpl_var])) {
					settype($this->_tpl_vars[$tpl_var],'array');
				}
                $this->_tpl_vars[$tpl_var][] = $value;
            }
        }
        $this->_extract = true;
    }

/*======================================================================*\
    Function: append_by_ref
    Purpose:  appends values to template variables by reference
\*======================================================================*/
    function append_by_ref($tpl_var, &$value)
    {
        if ($tpl_var != '' && isset($value)) {
			if(!@is_array($this->_tpl_vars[$tpl_var])) {
				settype($this->_tpl_vars[$tpl_var],'array');
			}
            $this->_tpl_vars[$tpl_var][] = &$value;
        }
        $this->_extract = true;
    }


/*======================================================================*\
    Function:   clear_assign()
    Purpose:    clear the given assigned template variable.
\*======================================================================*/
    function clear_assign($tpl_var)
    {
        if (is_array($tpl_var))
            foreach ($tpl_var as $curr_var)
                unset($this->_tpl_vars[$curr_var]);
        else
            unset($this->_tpl_vars[$tpl_var]);
    }


/*======================================================================*\
    Function: register_function
    Purpose:  Registers custom function to be used in templates
\*======================================================================*/
    function register_function($function, $function_impl)
    {
        $this->_plugins['function'][$function] =
            array($function_impl, null, null, false);
    }

/*======================================================================*\
    Function: unregister_function
    Purpose:  Unregisters custom function
\*======================================================================*/
    function unregister_function($function)
    {
        unset($this->_plugins['function'][$function]);
    }

/*======================================================================*\
    Function: register_block
    Purpose:  Registers block function to be used in templates
\*======================================================================*/
    function register_block($block, $block_impl)
    {
        $this->_plugins['block'][$block] =
            array($block_impl, null, null, false);
    }

/*======================================================================*\
    Function: unregister_block
    Purpose:  Unregisters block function
\*======================================================================*/
    function unregister_block($block)
    {
        unset($this->_plugins['block'][$block]);
    }

/*======================================================================*\
    Function: register_compiler_function
    Purpose:  Registers compiler function
\*======================================================================*/
    function register_compiler_function($function, $function_impl)
    {
        $this->_plugins['compiler'][$function] =
            array($function_impl, null, null, false);
    }

/*======================================================================*\
    Function: unregister_compiler_function
    Purpose:  Unregisters compiler function
\*======================================================================*/
    function unregister_compiler_function($function)
    {
        unset($this->_plugins['compiler'][$function]);
    }

/*======================================================================*\
    Function: register_modifier
    Purpose:  Registers modifier to be used in templates
\*======================================================================*/
    function register_modifier($modifier, $modifier_impl)
    {
        $this->_plugins['modifier'][$modifier] =
            array($modifier_impl, null, null, false);
    }

/*======================================================================*\
    Function: unregister_modifier
    Purpose:  Unregisters modifier
\*======================================================================*/
    function unregister_modifier($modifier)
    {
        unset($this->_plugins['modifier'][$modifier]);
    }

/*======================================================================*\
    Function: register_resource
    Purpose:  Registers a resource to fetch a template
\*======================================================================*/
    function register_resource($type, $functions)
    {
        $this->_plugins['resource'][$type] =
            array((array)$functions, false);
    }

/*======================================================================*\
    Function: unregister_resource
    Purpose:  Unregisters a resource
\*======================================================================*/
    function unregister_resource($type)
    {
        unset($this->_plugins['resource'][$type]);
    }

/*======================================================================*\
    Function: register_prefilter
    Purpose:  Registers a prefilter function to apply
              to a template before compiling
\*======================================================================*/
    function register_prefilter($function)
    {
        $this->_plugins['prefilter'][$function]
            = array($function, null, null, false);
    }

/*======================================================================*\
    Function: unregister_prefilter
    Purpose:  Unregisters a prefilter function
\*======================================================================*/
    function unregister_prefilter($function)
    {
        unset($this->_plugins['prefilter'][$function]);
    }

/*======================================================================*\
    Function: register_postfilter
    Purpose:  Registers a postfilter function to apply
              to a compiled template after compilation
\*======================================================================*/
    function register_postfilter($function)
    {
        $this->_plugins['postfilter'][$function]
            = array($function, null, null, false);
    }

/*======================================================================*\
    Function: unregister_postfilter
    Purpose:  Unregisters a postfilter function
\*======================================================================*/
    function unregister_postfilter($function)
    {
        unset($this->_plugins['postfilter'][$function]);
    }

/*======================================================================*\
    Function: register_outputfilter
    Purpose:  Registers an output filter function to apply
              to a template output
\*======================================================================*/
    function register_outputfilter($function)
    {
        $this->_plugins['outputfilter'][$function]
            = array($function, null, null, false);
    }

/*======================================================================*\
    Function: unregister_outputfilter
    Purpose:  Unregisters an outputfilter function
\*======================================================================*/
    function unregister_outputfilter($function)
    {
        unset($this->_plugins['outputfilter'][$function]);
    }

/*======================================================================*\
    Function:   load_filter()
    Purpose:    load a filter of specified type and name
\*======================================================================*/
    function load_filter($type, $name)
    {
        switch ($type) {
            case 'output':
                $this->_load_plugins(array(array($type . 'filter', $name, null, null, false)));
                break;

            case 'pre':
            case 'post':
                if (!isset($this->_plugins[$type . 'filter'][$name]))
                    $this->_plugins[$type . 'filter'][$name] = false;
                break;
        }
    }

/*======================================================================*\
    Function:   clear_cache()
    Purpose:    clear cached content for the given template and cache id
\*======================================================================*/
    function clear_cache($tpl_file = null, $cache_id = null, $compile_id = null, $exp_time = null)
    {

        if (!isset($compile_id))
            $compile_id = $this->compile_id;

        if (isset($cache_id))
            $auto_id = (isset($compile_id)) ? $cache_id . '|' . $compile_id : $cache_id;
        elseif(isset($compile_id))
			$auto_id = $compile_id;
		else
            $auto_id = null;

        if (!empty($this->cache_handler_func)) {
            $funcname = $this->cache_handler_func;
            return $funcname('clear', $this, $dummy, $tpl_file, $cache_id, $compile_id);
        } else {
            return $this->_rm_auto($this->cache_dir, $tpl_file, $auto_id, $exp_time);
        }

    }


/*======================================================================*\
    Function:   clear_all_cache()
    Purpose:    clear the entire contents of cache (all templates)
\*======================================================================*/
    function clear_all_cache($exp_time = null)
    {
        if (!empty($this->cache_handler_func)) {
            $funcname = $this->cache_handler_func;
            return $funcname('clear', $this, $dummy);
        } else {
            return $this->_rm_auto($this->cache_dir,null,null,$exp_time);
        }
    }


/*======================================================================*\
    Function:   is_cached()
    Purpose:    test to see if valid cache exists for this template
\*======================================================================*/
    function is_cached($tpl_file, $cache_id = null, $compile_id = null)
    {
        if (!$this->caching)
            return false;

        if (!isset($compile_id))
            $compile_id = $this->compile_id;

        return $this->_read_cache_file($tpl_file, $cache_id, $compile_id, $results);
    }


/*======================================================================*\
    Function:   clear_all_assign()
    Purpose:    clear all the assigned template variables.
\*======================================================================*/
    function clear_all_assign()
    {
        $this->_tpl_vars = array();
    }

/*======================================================================*\
    Function:   clear_compiled_tpl()
    Purpose:    clears compiled version of specified template resource,
                or all compiled template files if one is not specified.
                This function is for advanced use only, not normally needed.
\*======================================================================*/
    function clear_compiled_tpl($tpl_file = null, $compile_id = null, $exp_time = null)
    {
        if (!isset($compile_id))
            $compile_id = $this->compile_id;
        return $this->_rm_auto($this->compile_dir, $tpl_file, $compile_id, $exp_time);
    }

 /*======================================================================*\
    Function:   template_exists()
    Purpose:    Checks whether requested template exists.
\*======================================================================*/
    function template_exists($tpl_file)
    {
        return $this->_fetch_template_info($tpl_file, $source, $timestamp, true, true);
    }

/*======================================================================*\
    Function: get_template_vars
    Purpose:  Returns an array containing template variables
\*======================================================================*/
    function &get_template_vars()
    {
        return $this->_tpl_vars;
    }


/*======================================================================*\
    Function: trigger_error
    Purpose:  trigger Smarty error
\*======================================================================*/
    function trigger_error($error_msg, $error_type = E_USER_WARNING)
    {
        trigger_error("Smarty error: $error_msg", $error_type);
    }


/*======================================================================*\
    Function:   display()
    Purpose:    executes & displays the template results
\*======================================================================*/
    function display($tpl_file, $cache_id = null, $compile_id = null)
    {
        $this->fetch($tpl_file, $cache_id, $compile_id, true);
    }

/*======================================================================*\
    Function:   fetch()
    Purpose:    executes & returns or displays the template results
\*======================================================================*/
    function fetch($_smarty_tpl_file, $_smarty_cache_id = null, $_smarty_compile_id = null, $_smarty_display = false)
    {
        $_smarty_old_error_level = $this->debugging ? error_reporting() : error_reporting(error_reporting() & ~E_NOTICE);

        if (!$this->debugging && $this->debugging_ctrl == 'URL'
               && strstr($GLOBALS['HTTP_SERVER_VARS']['QUERY_STRING'], $this->_smarty_debug_id)) {
            $this->debugging = true;
        }

        if ($this->debugging) {
            // capture time for debugging info
            $debug_start_time = $this->_get_microtime();
            $this->_smarty_debug_info[] = array('type'      => 'template',
                                                'filename'  => $_smarty_tpl_file,
                                                'depth'     => 0);
            $included_tpls_idx = count($this->_smarty_debug_info) - 1;
        }

        if (!isset($_smarty_compile_id))
            $_smarty_compile_id = $this->compile_id;

        $this->_compile_id = $_smarty_compile_id;

        $this->_inclusion_depth = 0;

        if ($this->caching) {
            if ($this->_read_cache_file($_smarty_tpl_file, $_smarty_cache_id, $_smarty_compile_id, $_smarty_results)) {
                if (@count($this->_cache_info['insert_tags'])) {
                    $this->_load_plugins($this->_cache_info['insert_tags']);
                    $_smarty_results = $this->_process_cached_inserts($_smarty_results);
                }
                if ($_smarty_display) {
                    if ($this->debugging)
                    {
                        // capture time for debugging info
                        $this->_smarty_debug_info[$included_tpls_idx]['exec_time'] = $this->_get_microtime() - $debug_start_time;

                        $_smarty_results .= $this->_generate_debug_output();
                    }
                    if ($this->cache_modified_check) {
                        $last_modified_date = substr($GLOBALS['HTTP_SERVER_VARS']['HTTP_IF_MODIFIED_SINCE'], 0, strpos($GLOBALS['HTTP_SERVER_VARS']['HTTP_IF_MODIFIED_SINCE'], 'GMT') + 3);
                        $gmt_mtime = gmdate('D, d M Y H:i:s', $this->_cache_info['timestamp']).' GMT';
                        if (@count($this->_cache_info['insert_tags']) == 0
                            && $gmt_mtime == $last_modified_date) {
                            header("HTTP/1.1 304 Not Modified");
                        } else {
                            header("Last-Modified: ".$gmt_mtime);
                    		echo $_smarty_results;
                        }
                    } else {
                    		echo $_smarty_results;
					}
                    error_reporting($_smarty_old_error_level);
                    return true;
                } else {
                    error_reporting($_smarty_old_error_level);
                    return $_smarty_results;
                }
            } else {
                $this->_cache_info = array();
                $this->_cache_info['template'][] = $_smarty_tpl_file;
            }
        }

        extract($this->_tpl_vars);

        /* Initialize config array. */
        $this->_config = array(array('vars'  => array(),
                                     'files' => array()));

        if (count($this->autoload_filters))
            $this->_autoload_filters();

        $_smarty_compile_path = $this->_get_compile_path($_smarty_tpl_file);

        // if we just need to display the results, don't perform output
        // buffering - for speed
        if ($_smarty_display && !$this->caching && count($this->_plugins['outputfilter']) == 0) {
            if ($this->_process_template($_smarty_tpl_file, $_smarty_compile_path))
            {
                include($_smarty_compile_path);
            }
        } else {
            ob_start();
            if ($this->_process_template($_smarty_tpl_file, $_smarty_compile_path))
            {
                include($_smarty_compile_path);
            }
            $_smarty_results = ob_get_contents();
            ob_end_clean();

            foreach ((array)$this->_plugins['outputfilter'] as $output_filter) {
                $_smarty_results = $output_filter[0]($_smarty_results, $this);
            }
        }

        if ($this->caching) {
            $this->_write_cache_file($_smarty_tpl_file, $_smarty_cache_id, $_smarty_compile_id, $_smarty_results);
            $_smarty_results = $this->_process_cached_inserts($_smarty_results);
        }

        if ($_smarty_display) {
            if (isset($_smarty_results)) { echo $_smarty_results; }
            if ($this->debugging) {
                // capture time for debugging info
                $this->_smarty_debug_info[$included_tpls_idx]['exec_time'] = ($this->_get_microtime() - $debug_start_time);

                echo $this->_generate_debug_output();
            }
            error_reporting($_smarty_old_error_level);
            return;
        } else {
            error_reporting($_smarty_old_error_level);
            if (isset($_smarty_results)) { return $_smarty_results; }
        }
    }


/*======================================================================*\
    Function: _assign_smarty_interface
    Purpose:  assign $smarty interface variable
\*======================================================================*/
    function _assign_smarty_interface()
    {
        if ($this->_smarty_vars !== null)
            return;

        $globals_map = array('g'  => 'HTTP_GET_VARS',
                             'p'  => 'HTTP_POST_VARS',
                             'c'  => 'HTTP_COOKIE_VARS',
                             's'  => 'HTTP_SERVER_VARS',
                             'e'  => 'HTTP_ENV_VARS');

        $smarty  = array('request'  => array());

        foreach (preg_split('!!', strtolower($this->request_vars_order)) as $c) {
            if (isset($globals_map[$c])) {
                $smarty['request'] = array_merge($smarty['request'], $GLOBALS[$globals_map[$c]]);
            }
        }
        $smarty['request'] = @array_merge($smarty['request'], $GLOBALS['HTTP_SESSION_VARS']);

        $this->_smarty_vars = $smarty;
    }


/*======================================================================*\
    Function:   _generate_debug_output()
    Purpose:    generate debug output
\*======================================================================*/

function _generate_debug_output() {
    // we must force compile the debug template in case the environment
    // changed between separate applications.
	$_ldelim_orig = $this->left_delimiter;
	$_rdelim_orig = $this->right_delimiter;

	$this->left_delimiter = '{';
	$this->right_delimiter = '}';

    $_force_compile_orig = $this->force_compile;
    $this->force_compile = true;
	$_compile_id_orig = $this->_compile_id;
	$this->_compile_id = null;

    $compile_path = $this->_get_compile_path($this->debug_tpl);
    if ($this->_process_template($this->debug_tpl, $compile_path))
    {
    	ob_start();
        include($compile_path);
    	$results = ob_get_contents();
    	ob_end_clean();
    }
    $this->force_compile = $_force_compile_orig;
	$this->_compile_id = $_compile_id_orig;

	$this->left_delimiter = $_ldelim_orig;
	$this->right_delimiter = $_rdelim_orig;

    return $results;
}

/*======================================================================*\
    Function:   _is_trusted()
    Purpose:    determines if a resource is trusted or not
\*======================================================================*/
    function _is_trusted($resource_type, $resource_name)
    {
        $_smarty_trusted = false;
        if ($resource_type == 'file') {
            if (!empty($this->trusted_dir)) {
                // see if template file is within a trusted directory. If so,
                // disable security during the execution of the template.

                if (!empty($this->trusted_dir)) {
                    foreach ((array)$this->trusted_dir as $curr_dir) {
                        if (!empty($curr_dir) && is_readable ($curr_dir)) {
                            if (substr(realpath($resource_name),0, strlen(realpath($curr_dir))) == realpath($curr_dir)) {
                                $_smarty_trusted = true;
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            // resource is not on local file system
            $resource_func = $this->_plugins['resource'][$resource_type][0][3];
            $_smarty_trusted = $resource_func($resource_name, $this);
        }

        return $_smarty_trusted;
    }

/*======================================================================*\
    Function:   _is_secure()
    Purpose:    determines if a resource is secure or not.
\*======================================================================*/
    function _is_secure($resource_type, $resource_name)
    {
        if (!$this->security || $this->security_settings['INCLUDE_ANY']) {
            return true;
        }

        $_smarty_secure = false;
        if ($resource_type == 'file') {
            if (!empty($this->secure_dir)) {
                foreach ((array)$this->secure_dir as $curr_dir) {
                    if ( !empty($curr_dir) && is_readable ($curr_dir)) {
                        if (substr(realpath($resource_name),0, strlen(realpath($curr_dir))) == realpath($curr_dir)) {
                            $_smarty_secure = true;
                            break;
                        }
                    }
                }
            }
        } else {
            // resource is not on local file system
            $resource_func = $this->_plugins['resource'][$resource_type][0][2];
            $_smarty_secure = $resource_func($resource_name, $_smarty_secure, $this);
        }

        return $_smarty_secure;
    }


/*======================================================================*\
    Function:   _get_php_resource
    Purpose:    Retrieves PHP script resource
\*======================================================================*/
    function _get_php_resource($resource, &$resource_type, &$php_resource)
    {
        $this->_parse_file_path($this->trusted_dir, $resource, $resource_type, $resource_name);

        /*
         * Find out if the resource exists.
         */

        if ($resource_type == 'file') {
            $readable = false;
			if(@is_file($resource_name)) {
				$readable = true;
			} else {
				// test for file in include_path
				if($this->_get_include_path($resource_name,$_include_path)) {
					$readable = true;
				}
			}
        } else if ($resource_type != 'file') {
            $readable = true;
            $resource_func = $this->_plugins['resource'][$resource_type][0][0];
            $readable = $resource_func($resource_name, $template_source, $this);
        }

        /*
         * Set the error function, depending on which class calls us.
         */
        if (method_exists($this, '_syntax_error')) {
            $error_func = '_syntax_error';
        } else {
            $error_func = 'trigger_error';
        }

        if ($readable) {
            if ($this->security) {
                if (!$this->_is_trusted($resource_type, $resource_name)) {
                    $this->$error_func("(secure mode) '$resource_type:$resource_name' is not trusted");
                    return false;
                }
            }
        } else {
            $this->$error_func("'$resource_type: $resource_name' is not readable");
            return false;
        }

        if ($resource_type == 'file') {
            $php_resource = $resource_name;
        } else {
            $php_resource = $template_source;
        }

        return true;
    }


/*======================================================================*\
    Function:   _process_template()
    Purpose:
\*======================================================================*/
    function _process_template($tpl_file, $compile_path)
    {
        // test if template needs to be compiled
        if (!$this->force_compile && file_exists($compile_path)) {
            if (!$this->compile_check) {
                // no need to check if the template needs recompiled
                return true;
            } else {
                // get template source and timestamp
                if (!$this->_fetch_template_info($tpl_file, $template_source,
                                                 $template_timestamp)) {
                    return false;
                }
                if ($template_timestamp <= filemtime($compile_path)) {
                    // template not expired, no recompile
                    return true;
                } else {
                    // compile template
                    $this->_compile_template($tpl_file, $template_source, $template_compiled);
                    $this->_write_compiled_template($compile_path, $template_compiled, $template_timestamp);
                    return true;
                }
            }
        } else {
            // compiled template does not exist, or forced compile
            if (!$this->_fetch_template_info($tpl_file, $template_source,
                                             $template_timestamp)) {
                return false;
            }
            $this->_compile_template($tpl_file, $template_source, $template_compiled);
            $this->_write_compiled_template($compile_path, $template_compiled, $template_timestamp);
            return true;
        }
    }

/*======================================================================*\
    Function:   _get_compile_path
    Purpose:    Get the compile path for this template file
\*======================================================================*/
    function _get_compile_path($tpl_file)
    {
        return $this->_get_auto_filename($this->compile_dir, $tpl_file,
                                         $this->_compile_id);
    }

/*======================================================================*\
    Function:   _write_compiled_template
    Purpose:
\*======================================================================*/
    function _write_compiled_template($compile_path, $template_compiled, $template_timestamp)
    {
        // we save everything into $compile_dir
        $this->_write_file($compile_path, $template_compiled, true);
        touch($compile_path, $template_timestamp);
        return true;
    }

/*======================================================================*\
    Function:   _parse_file_path
    Purpose:    parse out the type and name from the template resource
\*======================================================================*/
    function _parse_file_path($file_base_path, $file_path, &$resource_type, &$resource_name)
    {
        // split tpl_path by the first colon
        $_file_path_parts = explode(':', $file_path, 2);

        if (count($_file_path_parts) == 1) {
            // no resource type, treat as type "file"
            $resource_type = 'file';
            $resource_name = $_file_path_parts[0];
        } else {
            $resource_type = $_file_path_parts[0];
            $resource_name = $_file_path_parts[1];
            if ($resource_type != 'file') {
                $this->_load_resource_plugin($resource_type);
            }
        }

        if ($resource_type == 'file') {
            if (!preg_match("/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/", $resource_name)) {
                // relative pathname to $file_base_path
                // use the first directory where the file is found
                foreach ((array)$file_base_path as $_curr_path) {
					$_fullpath = $_curr_path . DIR_SEP . $resource_name;
                    if (@is_file($_fullpath)) {
                        $resource_name = $_fullpath;
                        return true;
                    }
                	// didn't find the file, try include_path
					if($this->_get_include_path($_fullpath, $_include_path)) {
						$resource_name = $_include_path;
						return true;
					}
                }
                return false;
            }
        }

        // resource type != file
        return true;
    }


/*======================================================================*\
    Function:   _fetch_template_info()
    Purpose:    fetch the template info. Gets timestamp, and source
                if get_source is true
\*======================================================================*/
    function _fetch_template_info($tpl_path, &$template_source, &$template_timestamp, $get_source = true, $quiet = false)
    {
        $_return = false;
        if ($this->_parse_file_path($this->template_dir, $tpl_path, $resource_type, $resource_name)) {
            switch ($resource_type) {
                case 'file':
                    if ($get_source) {
                        $template_source = $this->_read_file($resource_name);
                    }
                    $template_timestamp = filemtime($resource_name);
                    $_return = true;
                    break;

                default:
                    // call resource functions to fetch the template source and timestamp
                    if ($get_source) {
                        $resource_func = $this->_plugins['resource'][$resource_type][0][0];
                        $_source_return = $resource_func($resource_name, $template_source, $this);
                    } else {
                        $_source_return = true;
                    }
                    $resource_func = $this->_plugins['resource'][$resource_type][0][1];
                    $_timestamp_return = $resource_func($resource_name, $template_timestamp, $this);
                    $_return = $_source_return && $_timestamp_return;
                    break;
            }
        }

        if (!$_return) {
            // see if we can get a template with the default template handler
            if (!empty($this->default_template_handler_func)) {
                if (!function_exists($this->default_template_handler_func)) {
                    $this->trigger_error("default template handler function \"$this->default_template_handler_func\" doesn't exist.");
                    $_return = false;
                }
                $funcname = $this->default_template_handler_func;
                $_return = $funcname($resource_type, $resource_name, $template_source, $template_timestamp, $this);
            }
        }

        if (!$_return) {
            if (!$quiet) {
                $this->trigger_error("unable to read template resource: \"$tpl_path\"");
			}
        } else if ($_return && $this->security && !$this->_is_secure($resource_type, $resource_name)) {
            if (!$quiet)
                $this->trigger_error("(secure mode) accessing \"$tpl_path\" is not allowed");
            $template_source = null;
            $template_timestamp = null;
            return false;
        }

        return $_return;
    }


/*======================================================================*\
    Function:   _compile_template()
    Purpose:    called to compile the templates
\*======================================================================*/
    function _compile_template($tpl_file, $template_source, &$template_compiled)
    {
        require_once SMARTY_DIR.$this->compiler_class . '.class.php';

        $smarty_compiler = new $this->compiler_class;

        $smarty_compiler->template_dir      = $this->template_dir;
        $smarty_compiler->compile_dir       = $this->compile_dir;
        $smarty_compiler->plugins_dir       = $this->plugins_dir;
        $smarty_compiler->config_dir        = $this->config_dir;
        $smarty_compiler->force_compile     = $this->force_compile;
        $smarty_compiler->caching           = $this->caching;
        $smarty_compiler->php_handling      = $this->php_handling;
        $smarty_compiler->left_delimiter    = $this->left_delimiter;
        $smarty_compiler->right_delimiter   = $this->right_delimiter;
        $smarty_compiler->_version          = $this->_version;
        $smarty_compiler->security          = $this->security;
        $smarty_compiler->secure_dir        = $this->secure_dir;
        $smarty_compiler->security_settings = $this->security_settings;
        $smarty_compiler->trusted_dir       = $this->trusted_dir;
        $smarty_compiler->_plugins          = &$this->_plugins;
        $smarty_compiler->_tpl_vars         = &$this->_tpl_vars;
        $smarty_compiler->default_modifiers = $this->default_modifiers;

        if ($smarty_compiler->_compile_file($tpl_file, $template_source, $template_compiled))
            return true;
        else {
            $this->trigger_error($smarty_compiler->_error_msg);
            return false;
        }
    }

/*======================================================================*\
    Function:   _smarty_include()
    Purpose:    called for included templates
\*======================================================================*/
    function _smarty_include($_smarty_include_tpl_file, $_smarty_include_vars)
    {
        if ($this->debugging) {
            $debug_start_time = $this->_get_microtime();
            $this->_smarty_debug_info[] = array('type'      => 'template',
                                                'filename'  => $_smarty_include_tpl_file,
                                                'depth'     => ++$this->_inclusion_depth);
            $included_tpls_idx = count($this->_smarty_debug_info) - 1;
        }

        $this->_tpl_vars = array_merge($this->_tpl_vars, $_smarty_include_vars);
        extract($this->_tpl_vars);

        array_unshift($this->_config, $this->_config[0]);
        $_smarty_compile_path = $this->_get_compile_path($_smarty_include_tpl_file);

        if ($this->_process_template($_smarty_include_tpl_file, $_smarty_compile_path)) {
            include($_smarty_compile_path);
        }

        array_shift($this->_config);
        $this->_inclusion_depth--;

        if ($this->debugging) {
            // capture time for debugging info
            $this->_smarty_debug_info[$included_tpls_idx]['exec_time'] = $this->_get_microtime() - $debug_start_time;
        }

        if ($this->caching) {
            $this->_cache_info['template'][] = $_smarty_include_tpl_file;
        }
    }

/*======================================================================*\
    Function:   _smarty_include_php()
    Purpose:    called for included templates
\*======================================================================*/
    function _smarty_include_php($_smarty_include_php_file, $_smarty_assign, $_smarty_once)
    {
        $this->_get_php_resource($_smarty_include_php_file, $_smarty_resource_type,
                                 $_smarty_php_resource);

        if (!empty($_smarty_assign)) {
            ob_start();
            if ($_smarty_resource_type == 'file') {
				if($_smarty_once) {
                	include_once($_smarty_php_resource);
				} else {
                	include($_smarty_php_resource);
				}
            } else {
                eval($_smarty_php_resource);
            }
            $this->assign($_smarty_assign, ob_get_contents());
            ob_end_clean();
        } else {
            if ($_smarty_resource_type == 'file') {
				if($_smarty_once) {
                	include_once($_smarty_php_resource);
				} else {
                	include($_smarty_php_resource);
				}
            } else {
                eval($_smarty_php_resource);
            }
        }
    }

/*======================================================================*\
    Function: _config_load
    Purpose:  load configuration values
\*======================================================================*/
    function _config_load($file, $section, $scope)
    {
		if(@is_dir($this->config_dir)) {
			$_config_dir = $this->config_dir;
		} else {
			// config_dir not found, try include_path
			$this->_get_include_path($this->config_dir,$_config_dir);
		}

        if ($this->_conf_obj === null) {
            /* Prepare the configuration object. */
            if (!class_exists('Config_File'))
                require_once SMARTY_DIR.'Config_File.class.php';
            $this->_conf_obj = new Config_File($_config_dir);
            $this->_conf_obj->read_hidden = false;
        } else {
            $this->_conf_obj->set_path($_config_dir);
        }

        if ($this->debugging) {
            $debug_start_time = $this->_get_microtime();
        }

        if ($this->caching) {
            $this->_cache_info['config'][] = $file;
        }

        if (!isset($this->_config[0]['files'][$file])) {
            $this->_config[0]['vars'] = array_merge($this->_config[0]['vars'], $this->_conf_obj->get($file));
            $this->_config[0]['files'][$file] = true;
        }
        if ($scope == 'parent') {
            if (count($this->_config) > 0 && !isset($this->_config[1]['files'][$file])) {
                $this->_config[1]['vars'] = array_merge($this->_config[1]['vars'], $this->_conf_obj->get($file));
                $this->_config[1]['files'][$file] = true;
            }
        } else if ($scope == 'global')
            for ($i = 1, $for_max = count($this->_config); $i < $for_max; $i++) {
                if (!isset($this->_config[$i]['files'][$file])) {
                    $this->_config[$i]['vars'] = array_merge($this->_config[$i]['vars'], $this->_conf_obj->get($file));
                    $this->_config[$i]['files'][$file] = true;
                }
            }

        if (!empty($section)) {
            $this->_config[0]['vars'] = array_merge($this->_config[0]['vars'], $this->_conf_obj->get($file, $section));
            if ($scope == 'parent') {
                if (count($this->_config) > 0)
                    $this->_config[1]['vars'] = array_merge($this->_config[1]['vars'], $this->_conf_obj->get($file, $section));
            } else if ($scope == 'global')
                for ($i = 1, $for_max = count($this->_config); $i < $for_max; $i++)
                    $this->_config[$i]['vars'] = array_merge($this->_config[$i]['vars'], $this->_conf_obj->get($file, $section));
        }

        if ($this->debugging) {
            $debug_start_time = $this->_get_microtime();
            $this->_smarty_debug_info[] = array('type'      => 'config',
                                                'filename'  => $file.' ['.$section.'] '.$scope,
                                                'depth'     => $this->_inclusion_depth,
                                                'exec_time' => $this->_get_microtime() - $debug_start_time);
        }
    }


/*======================================================================*\
    Function: _process_cached_inserts
    Purpose:  Replace cached inserts with the actual results
\*======================================================================*/
    function _process_cached_inserts($results)
    {
        preg_match_all('!'.$this->_smarty_md5.'{insert_cache (.*)}'.$this->_smarty_md5.'!Uis',
                       $results, $match);
        list($cached_inserts, $insert_args) = $match;

        for ($i = 0, $for_max = count($cached_inserts); $i < $for_max; $i++) {
            if ($this->debugging) {
                $debug_start_time = $this->_get_microtime();
            }

            $args = unserialize($insert_args[$i]);

            $name = $args['name'];
            unset($args['name']);

            if (isset($args['script'])) {
                if (!$this->_get_php_resource($this->_dequote($args['script']), $resource_type, $php_resource)) {
                    return false;
                }

                if ($resource_type == 'file') {
                    include_once($php_resource);
                } else {
                    eval($php_resource);
                }
                unset($args['script']);
            }

            $function_name = $this->_plugins['insert'][$name][0];
            $replace = $function_name($args, $this);

            $results = str_replace($cached_inserts[$i], $replace, $results);
            if ($this->debugging) {
                $this->_smarty_debug_info[] = array('type'      => 'insert',
                                                    'filename'  => 'insert_'.$name,
                                                    'depth'     => $this->_inclusion_depth,
                                                    'exec_time' => $this->_get_microtime() - $debug_start_time);
            }
        }

        return $results;
    }


/*======================================================================*\
    Function: _run_insert_handler
    Purpose:  Handle insert tags
\*======================================================================*/
function _run_insert_handler($args)
{
    if ($this->debugging) {
        $debug_start_time = $this->_get_microtime();
    }

    if ($this->caching) {
        $arg_string = serialize($args);
        $name = $args['name'];
        if (!isset($this->_cache_info['insert_tags'][$name])) {
            $this->_cache_info['insert_tags'][$name] = array('insert',
                                                             $name,
                                                             $this->_plugins['insert'][$name][1],
                                                             $this->_plugins['insert'][$name][2],
                                                             !empty($args['script']) ? true : false);
        }
        return $this->_smarty_md5."{insert_cache $arg_string}".$this->_smarty_md5;
    } else {
        if (isset($args['script'])) {
            if (!$this->_get_php_resource($this->_dequote($args['script']), $resource_type, $php_resource)) {
                return false;
            }

            if ($resource_type == 'file') {
                include_once($php_resource);
            } else {
                eval($php_resource);
            }
            unset($args['script']);
        }

        $function_name = $this->_plugins['insert'][$args['name']][0];
        $content = $function_name($args, $this);
        if ($this->debugging) {
            $this->_smarty_debug_info[] = array('type'      => 'insert',
                                                'filename'  => 'insert_'.$args['name'],
                                                'depth'     => $this->_inclusion_depth,
                                                'exec_time' => $this->_get_microtime() - $debug_start_time);
        }

        if (!empty($args["assign"])) {
            $this->assign($args["assign"], $content);
        } else {
            return $content;
        }
    }
}


/*======================================================================*\
    Function: _run_mod_handler
    Purpose:  Handle modifiers
\*======================================================================*/
    function _run_mod_handler()
    {
        $args = func_get_args();
        list($modifier_name, $map_array) = array_splice($args, 0, 2);
        list($func_name, $tpl_file, $tpl_line) =
            $this->_plugins['modifier'][$modifier_name];
        $var = $args[0];

        if ($map_array && is_array($var)) {
            foreach ($var as $key => $val) {
                $args[0] = $val;
                $var[$key] = call_user_func_array($func_name, $args);
            }
            return $var;
        } else {
            return call_user_func_array($func_name, $args);
        }
    }


/*======================================================================*\
    Function: _dequote
    Purpose:  Remove starting and ending quotes from the string
\*======================================================================*/
    function _dequote($string)
    {
        if (($string{0} == "'" || $string{0} == '"') &&
            $string{strlen($string)-1} == $string{0})
            return substr($string, 1, -1);
        else
            return $string;
    }


/*======================================================================*\
    Function:   _read_file()
    Purpose:    read in a file from line $start for $lines.
                read the entire file if $start and $lines are null.
\*======================================================================*/
    function _read_file($filename, $start=null, $lines=null)
    {
        if (!($fd = @fopen($filename, 'r'))) {
            return false;
        }
        flock($fd, LOCK_SH);
        if ($start == null && $lines == null) {
            // read the entire file
            $contents = fread($fd, filesize($filename));
        } else {
            if ( $start > 1 ) {
                // skip the first lines before $start
                for ($loop=1; $loop < $start; $loop++) {
                    fgets($fd, 65536);
                }
            }
            if ( $lines == null ) {
                // read the rest of the file
                while (!feof($fd)) {
                    $contents .= fgets($fd, 65536);
                }
            } else {
                // read up to $lines lines
                for ($loop=0; $loop < $lines; $loop++) {
                    $contents .= fgets($fd, 65536);
                    if (feof($fd)) {
                        break;
                    }
                }
            }
        }
        fclose($fd);
        return $contents;
    }

/*======================================================================*\
    Function:   _write_file()
    Purpose:    write out a file
\*======================================================================*/
    function _write_file($filename, $contents, $create_dirs = false)
    {
        if ($create_dirs)
            $this->_create_dir_structure(dirname($filename));

        touch($filename, 'w'); // php bug
        if (!($fd = @fopen($filename, 'w'))) {
            $this->trigger_error("problem writing '$filename.'");
            return false;
        }

        // flock doesn't seem to work on several windows platforms (98, NT4, NT5, ?),
        // so we'll not use it at all in windows.

        if ( strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || (flock($fd, LOCK_EX)) ) {
            fwrite( $fd, $contents );
            fclose($fd);
            chmod($filename, 0644);
        }

        return true;
    }

/*======================================================================*\
    Function: _get_auto_filename
    Purpose:  get a concrete filename for automagically created content
\*======================================================================*/
    function _get_auto_filename($auto_base, $auto_source = null, $auto_id = null)
    {
		static $_dir_sep = null;
		static $_dir_sep_enc = null;

		if(!isset($_dir_sep)) {
			$_dir_sep_enc = urlencode(DIR_SEP);
			if($this->use_sub_dirs) {
				$_dir_sep = DIR_SEP;
			} else {
				$_dir_sep = '^';
			}
		}

		if(@is_dir($auto_base)) {
        	$res = $auto_base . DIR_SEP;
		} else {
			// auto_base not found, try include_path
			$this->_get_include_path($auto_base,$_include_path);
			$res = $_include_path . DIR_SEP;
		}

		if(isset($auto_id)) {
			// make auto_id safe for directory names
			$auto_id = str_replace('%7C','|',(urlencode($auto_id)));
			// split into separate directories
			$auto_id = str_replace('|', $_dir_sep, $auto_id);
        	$res .= $auto_id . $_dir_sep;
		}

		if(isset($auto_source)) {
			// make source name safe for filename
			if($this->use_sub_dirs) {
				$_filename = urlencode(basename($auto_source));
				$_crc32 = crc32($auto_source) . $_dir_sep;
				// prepend %% to avoid name conflicts with
				// with $auto_id names
				$_crc32 = '%%' . substr($_crc32,0,3) . $_dir_sep . '%%' . $_crc32;
				$res .= $_crc32 . $_filename . '.php';
			} else {
        		$res .= str_replace($_dir_sep_enc,'^',urlencode($auto_source));
			}
		}

        return $res;
    }

/*======================================================================*\
    Function: _rm_auto
    Purpose: delete an automagically created file by name and id
\*======================================================================*/
    function _rm_auto($auto_base, $auto_source = null, $auto_id = null, $exp_time = null)
    {
        if (!@is_dir($auto_base))
          return false;

		if(!isset($auto_id) && !isset($auto_source)) {
			$res = $this->_rmdir($auto_base, 0, $exp_time);
		} else {
        	$tname = $this->_get_auto_filename($auto_base, $auto_source, $auto_id);

			if(isset($auto_source)) {
				$res = $this->_unlink($tname);
			} elseif ($this->use_sub_dirs) {
				$res = $this->_rmdir($tname, 1, $exp_time);
			} else {
				// remove matching file names
				$handle = opendir($auto_base);
        		while ($filename = readdir($handle)) {
					if($filename == '.' || $filename == '..') {
						continue;
					} elseif (substr($auto_base . DIR_SEP . $filename,0,strlen($tname)) == $tname) {
						$this->_unlink($auto_base . DIR_SEP . $filename, $exp_time);
					}
				}
			}
		}

        return $res;
    }

/*======================================================================*\
    Function: _rmdir
    Purpose: delete a dir recursively (level=0 -> keep root)
    WARNING: no security whatsoever!!
\*======================================================================*/
    function _rmdir($dirname, $level = 1, $exp_time = null)
    {

       if($handle = @opendir($dirname)) {

        	while ($entry = readdir($handle)) {
            	if ($entry != '.' && $entry != '..') {
                	if (@is_dir($dirname . DIR_SEP . $entry)) {
                    	$this->_rmdir($dirname . DIR_SEP . $entry, $level + 1, $exp_time);
                	}
                	else {
                    	$this->_unlink($dirname . DIR_SEP . $entry, $exp_time);
                	}
            	}
        	}

        	closedir($handle);

        	if ($level)
            	@rmdir($dirname);

			return true;

		} else {
       	 	return false;
		}
    }

/*======================================================================*\
    Function: _unlink
    Purpose: unlink a file, possibly using expiration time
\*======================================================================*/
    function _unlink($resource, $exp_time = null)
    {
		if(isset($exp_time)) {
			if(time() - filemtime($resource) >= $exp_time) {
				@unlink($resource);
			}
		} else {
			@unlink($resource);
		}
    }

/*======================================================================*\
    Function: _create_dir_structure
    Purpose:  create full directory structure
\*======================================================================*/
    function _create_dir_structure($dir)
    {
        if (!@file_exists($dir)) {
            $_dir_parts = preg_split('!\\'.DIR_SEP.'+!', $dir, -1, PREG_SPLIT_NO_EMPTY);
            $_new_dir = ($dir{0} == DIR_SEP) ? DIR_SEP : '';

			// do not attempt to test or make directories outside of open_basedir
			$_open_basedir_ini = ini_get('open_basedir');
			if(!empty($_open_basedir_ini)) {
				$_use_open_basedir = true;
            	$_open_basedir_sep = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? ';' : ':';
            	$_open_basedirs = explode($_open_basedir_sep, $_open_basedir_ini);
			} else {
				$_use_open_basedir = false;
			}

            foreach ($_dir_parts as $_dir_part) {
                $_new_dir .= $_dir_part;

                if ($_use_open_basedir) {
                    $_make_new_dir = false;
                    foreach ($_open_basedirs as $_open_basedir) {
                        if (substr($_new_dir.'/', 0, strlen($_open_basedir)) == $_open_basedir) {
                            $_make_new_dir = true;
                            break;
                        }
                    }
                } else {
                	$_make_new_dir = true;
				}

                if ($_make_new_dir && !@file_exists($_new_dir) && !@mkdir($_new_dir, 0771)) {
                    $this->trigger_error("problem creating directory \"$dir\"");
                    return false;
                }
                $_new_dir .= DIR_SEP;
            }
        }
    }

/*======================================================================*\
    Function:   _write_cache_file
    Purpose:    Prepend the cache information to the cache file
                and write it
\*======================================================================*/
    function _write_cache_file($tpl_file, $cache_id, $compile_id, $results)
    {
        // put timestamp in cache header
        $this->_cache_info['timestamp'] = time();
        if ($this->cache_lifetime > -1){
            // expiration set
            $this->_cache_info['expires'] = $this->_cache_info['timestamp'] + $this->cache_lifetime;
        } else {
            // cache will never expire
            $this->_cache_info['expires'] = -1;
        }

        // prepend the cache header info into cache file
        $results = serialize($this->_cache_info)."\n".$results;

        if (!empty($this->cache_handler_func)) {
            // use cache_handler function
            $funcname = $this->cache_handler_func;
            return $funcname('write', $this, $results, $tpl_file, $cache_id, $compile_id);
        } else {
            // use local cache file
            if (isset($cache_id))
            	$auto_id = (isset($compile_id)) ? $cache_id . '|' . $compile_id : $cache_id;
            elseif(isset($compile_id))
				$auto_id = $compile_id;
			else
                $auto_id = null;

            $cache_file = $this->_get_auto_filename($this->cache_dir, $tpl_file, $auto_id);
            $this->_write_file($cache_file, $results, true);
            return true;
        }
    }

/*======================================================================*\
    Function:   _read_cache_file
    Purpose:    read a cache file, determine if it needs to be
                regenerated or not
\*======================================================================*/
    function _read_cache_file($tpl_file, $cache_id, $compile_id, &$results)
    {
        static  $content_cache = array();

        if ($this->force_compile) {
            // force compile enabled, always regenerate
            return false;
        }

        if (isset($content_cache["$tpl_file,$cache_id,$compile_id"])) {
            list($results, $this->_cache_info) = $content_cache["$tpl_file,$cache_id,$compile_id"];
            return true;
        }

        if (!empty($this->cache_handler_func)) {
            // use cache_handler function
            $funcname = $this->cache_handler_func;
            $funcname('read', $this, $results, $tpl_file, $cache_id, $compile_id);
        } else {
            // use local cache file
            if (isset($cache_id))
            	$auto_id = (isset($compile_id)) ? $cache_id . '|' . $compile_id : $cache_id;
            elseif(isset($compile_id))
				$auto_id = $compile_id;
			else
                $auto_id = null;

            $cache_file = $this->_get_auto_filename($this->cache_dir, $tpl_file, $auto_id);
            $results = $this->_read_file($cache_file);
        }

        if (empty($results)) {
            // nothing to parse (error?), regenerate cache
            return false;
        }

        $cache_split = explode("\n", $results, 2);
        $cache_header = $cache_split[0];

        $this->_cache_info = unserialize($cache_header);

        if ($this->caching == 2 && isset ($this->_cache_info['expires'])){
            // caching by expiration time
            if ($this->_cache_info['expires'] > -1 && (time() > $this->_cache_info['expires'])) {
            // cache expired, regenerate
            return false;
            }
        } else {
            // caching by lifetime
            if ($this->cache_lifetime > -1 && (time() - $this->_cache_info['timestamp'] > $this->cache_lifetime)) {
            // cache expired, regenerate
            return false;
            }
        }

        if ($this->compile_check) {
            foreach ($this->_cache_info['template'] as $template_dep) {
                $this->_fetch_template_info($template_dep, $template_source, $template_timestamp, false);
                if ($this->_cache_info['timestamp'] < $template_timestamp) {
                    // template file has changed, regenerate cache
                    return false;
                }
            }

            if (isset($this->_cache_info['config'])) {
                foreach ($this->_cache_info['config'] as $config_dep) {
                    if ($this->_cache_info['timestamp'] < filemtime($this->config_dir.DIR_SEP.$config_dep)) {
                        // config file has changed, regenerate cache
                        return false;
                    }
                }
            }
        }

        $results = $cache_split[1];
        $content_cache["$tpl_file,$cache_id,$compile_id"] = array($results, $this->_cache_info);

        return true;
    }

/*======================================================================*\
    Function:  _get_plugin_filepath
    Purpose:   get filepath of requested plugin
\*======================================================================*/
    function _get_plugin_filepath($type, $name)
    {
        $_plugin_filename = "$type.$name.php";

        foreach ((array)$this->plugins_dir as $_plugin_dir) {

            $_plugin_filepath = $_plugin_dir . DIR_SEP . $_plugin_filename;

			// see if path is relative
            if (!preg_match("/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/", $_plugin_dir)) {
				$_relative_paths[] = $_plugin_dir;
                // relative path, see if it is in the SMARTY_DIR
            	if (@is_readable(SMARTY_DIR . $_plugin_filepath)) {
                	return SMARTY_DIR . $_plugin_filepath;
            	}
			}
			// try relative to cwd (or absolute)
            if (@is_readable($_plugin_filepath)) {
                return $_plugin_filepath;
            }
        }

		// still not found, try PHP include_path
		if(isset($_relative_paths)) {
        	foreach ((array)$_relative_paths as $_plugin_dir) {

            	$_plugin_filepath = $_plugin_dir . DIR_SEP . $_plugin_filename;

        		if ($this->_get_include_path($_plugin_filepath, $_include_filepath)) {
            		return $_include_filepath;
        		}
        	}
		}


        return false;
    }

/*======================================================================*\
    Function:  _load_plugins
    Purpose:   Load requested plugins
\*======================================================================*/
    function _load_plugins($plugins)
    {

        foreach ($plugins as $plugin_info) {
            list($type, $name, $tpl_file, $tpl_line, $delayed_loading) = $plugin_info;
            $plugin = &$this->_plugins[$type][$name];

            /*
             * We do not load plugin more than once for each instance of Smarty.
             * The following code checks for that. The plugin can also be
             * registered dynamically at runtime, in which case template file
             * and line number will be unknown, so we fill them in.
             *
             * The final element of the info array is a flag that indicates
             * whether the dynamically registered plugin function has been
             * checked for existence yet or not.
             */
            if (isset($plugin)) {
                if (!$plugin[3]) {
                    if (!function_exists($plugin[0])) {
                        $this->_trigger_plugin_error("$type '$name' is not implemented", $tpl_file, $tpl_line);
                    } else {
                        $plugin[1] = $tpl_file;
                        $plugin[2] = $tpl_line;
                        $plugin[3] = true;
                    }
                }
                continue;
            } else if ($type == 'insert') {
                /*
                 * For backwards compatibility, we check for insert functions in
                 * the symbol table before trying to load them as a plugin.
                 */
                $plugin_func = 'insert_' . $name;
                if (function_exists($plugin_func)) {
                    $plugin = array($plugin_func, $tpl_file, $tpl_line, true);
                    continue;
                }
            }

            $plugin_file = $this->_get_plugin_filepath($type, $name);

            if ($found = ($plugin_file != false)) {
                $message = "could not load plugin file '$type.$name.php'\n";
            }

            /*
             * If plugin file is found, it -must- provide the properly named
             * plugin function. In case it doesn't, simply output the error and
             * do not fall back on any other method.
             */
            if ($found) {
                include_once $plugin_file;

                $plugin_func = 'smarty_' . $type . '_' . $name;
                if (!function_exists($plugin_func)) {
                    $this->_trigger_plugin_error("plugin function $plugin_func() not found in $plugin_file", $tpl_file, $tpl_line);
                    continue;
                }
            }
            /*
             * In case of insert plugins, their code may be loaded later via
             * 'script' attribute.
             */
            else if ($type == 'insert' && $delayed_loading) {
                $plugin_func = 'smarty_' . $type . '_' . $name;
                $found = true;
            }

            /*
             * Plugin specific processing and error checking.
             */
            if (!$found) {
                if ($type == 'modifier') {
                    /*
                     * In case modifier falls back on using PHP functions
                     * directly, we only allow those specified in the security
                     * context.
                     */
                    if ($this->security && !in_array($name, $this->security_settings['MODIFIER_FUNCS'])) {
                        $message = "(secure mode) modifier '$name' is not allowed";
                    } else {
                        if (!function_exists($name)) {
                            $message = "modifier '$name' is not implemented";
                        } else {
                            $plugin_func = $name;
                            $found = true;
                        }
                    }
                } else if ($type == 'function') {
                    /*
                     * This is a catch-all situation.
                     */
                    $message = "unknown tag - '$name'";
                }
            }

            if ($found) {
                $this->_plugins[$type][$name] = array($plugin_func, $tpl_file, $tpl_line, true);
            } else {
                // output error
                $this->_trigger_plugin_error($message, $tpl_file, $tpl_line);
            }
        }
    }

/*======================================================================*\
    Function:   _load_resource_plugin
    Purpose:
\*======================================================================*/
    function _load_resource_plugin($type)
    {
        /*
         * Resource plugins are not quite like the other ones, so they are
         * handled differently. The first element of plugin info is the array of
         * functions provided by the plugin, the second one indicates whether
         * all of them exist or not.
         */

        $plugin = &$this->_plugins['resource'][$type];
        if (isset($plugin)) {
            if (!$plugin[1] && count($plugin[0])) {
                $plugin[1] = true;
                foreach ($plugin[0] as $plugin_func) {
                    if (!function_exists($plugin_func)) {
                        $plugin[1] = false;
                        break;
                    }
                }
            }

            if (!$plugin[1]) {
                $this->_trigger_plugin_error("resource '$type' is not implemented");
            }

            return;
        }

        $plugin_file = $this->_get_plugin_filepath('resource', $type);
        $found = ($plugin_file != false);

        if ($found) {            /*
             * If the plugin file is found, it -must- provide the properly named
             * plugin functions.
             */
            include_once $plugin_file;

            /*
             * Locate functions that we require the plugin to provide.
             */
            $resource_ops = array('source', 'timestamp', 'secure', 'trusted');
            $resource_funcs = array();
            foreach ($resource_ops as $op) {
                $plugin_func = 'smarty_resource_' . $type . '_' . $op;
                if (!function_exists($plugin_func)) {
                    $this->_trigger_plugin_error("plugin function $plugin_func() not found in $plugin_file");
                    return;
                } else {
                    $resource_funcs[] = $plugin_func;
                }
            }

            $this->_plugins['resource'][$type] = array($resource_funcs, true);
        }
    }

/*======================================================================*\
    Function:   _autoload_filters()
    Purpose:    automatically load a set of filters
\*======================================================================*/
    function _autoload_filters()
    {
        foreach ($this->autoload_filters as $filter_type => $filters) {
            foreach ($filters as $filter) {
                $this->load_filter($filter_type, $filter);
            }
        }
    }

/*======================================================================*\
    Function:   quote_replace
    Purpose:    Quote subpattern references
\*======================================================================*/
    function quote_replace($string)
    {
        return preg_replace('![\\$]\d!', '\\\\\\0', $string);
    }


/*======================================================================*\
    Function: _trigger_plugin_error
    Purpose:  trigger Smarty plugin error
\*======================================================================*/
    function _trigger_plugin_error($error_msg, $tpl_file = null, $tpl_line = null, $error_type = E_USER_ERROR)
    {
        if (isset($tpl_line) && isset($tpl_file)) {
            trigger_error("Smarty plugin error: [in " . $tpl_file . " line " .
                          $tpl_line . "]: $error_msg", $error_type);
        } else {
            trigger_error("Smarty plugin error: $error_msg", $error_type);
        }
    }

/*======================================================================*\
    Function:   _get_microtime
    Purpose:    Get seconds and microseconds
\*======================================================================*/
    function _get_microtime()
    {
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = (double)($mtime[1]) + (double)($mtime[0]);
        return ($mtime);
    }

/*======================================================================*\
    Function:   _get_include_path
    Purpose:    Get path to file from include_path
\*======================================================================*/
    function _get_include_path($file_path,&$new_file_path)
    {
		static $_path_array = null;

		if(!isset($_path_array)) {
			$_ini_include_path = ini_get('include_path');

			if(strstr($_ini_include_path,';')) {
				// windows pathnames
				$_path_array = explode(';',$_ini_include_path);
			} else {
				$_path_array = explode(':',$_ini_include_path);
			}
		}
        foreach ($_path_array as $_include_path) {
            if (@file_exists($_include_path . DIR_SEP . $file_path)) {
               	$new_file_path = $_include_path . DIR_SEP . $file_path;
				return true;
            }
        }
		return false;
	}

}

/* vim: set expandtab: */

?>
