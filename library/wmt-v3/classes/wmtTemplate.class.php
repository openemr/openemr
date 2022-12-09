<?php
/** **************************************************************************
 *	TEMPLATE.CLASS.PHP
 *
 *	Copyright (c)2016 - Medical Technology Services <MDTechSvcs.com>
 *
 *	This program is free software: you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation, 
 *  either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY
 *	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *  PARTICULAR PURPOSE. DISTRIBUTOR IS NOT LIABLE TO USER FOR ANY DAMAGES, INCLUDING 
 *  COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, 
 *  CONNECTED WITH OR RESULTING FROM THIS AGREEMENT OR USE OF THIS SOFTWARE.
 *
 *	See the GNU General Public License <http://www.gnu.org/licenses/> for more details.
 *
 *  @package wmt
 *  @subpackage template
 *  @version 1.0.0
 *  @category Email Base Class
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

require_once($GLOBALS['srcdir']."/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\ShortenLink;

/**
 * Provides standardized processing for document templates.
 *
 * @package wmt
 * @subpackage template
 */
class Template {
	public $html_body;
	public $html_tags;
	public $html_merged;
	public $text_body;
	public $text_tags;
	public $text_merged;
	
	/**
	 * Constructor for the 'template' class which generates all types 
	 * of documents used for email, PDF, and simple text.
	 *
	 * @return object instance of form class
	 * 
	 */
	public function __construct($id = false) {
		if ($id === false) return;
		
		// get table fields (verify table exists)
		$fields = sqlListFields("templates");
		if (is_array($fields) === false) 
			throw new \Exception("wmtTemplate::__construct - missing templates database table");

		// retrieve record
		$data = sqlQueryNoLog("SELECT * FROM `templates` WHERE `id` = ?", array($id));
		if ($data === false) $data = array();
		
		// store content
		foreach ($fields AS $key) {
			$this->$key = (array_key_exists($key, $data)) ? $data[$key] : '';
		}
		
		// find tags within content
		$count = preg_match_all("/\[([^\]]*)\]/", $this->html_body, $html_tags);
		$this->html_tags = ($count) ? $html_tags[1] : array(); // array[1] contains match content
		
		$count = preg_match_all("/\[([^\]]*)\]/", $this->text_body, $text_tags);
		$this->text_tags = ($count) ? $text_tags[1] : array();
		
		return;
	}

	/**
	 * Inserts template object content into the database.
	 *
	 */
	public function store() {
		$insert = true;
		if($this->id) $insert = false;

		// build sql insert
		$sql = '';
		$binds = array();
		$fields = sqlListFields('templates'); // need only sup rec fields
		
		// find tags within content
		$this->html_body = str_replace('<p>&nbsp;</p>','<br/><br/>',$this->html_body);
		$count = preg_match_all("/\[([^\]]*)\]/", $this->html_body, $html_tags);
		$tag_array = ($count) ? $html_tags[1] : array();
		$this->html_tags = implode('|', $tag_array);
		
		$count = preg_match_all("/\[([^\]]*)\]/", $this->text_body, $text_tags);
		$tag_array = ($count) ? $text_tags[1] : array();
		$this->text_tags = implode('|', $tag_array);
		
		foreach ($this as $key => $value) {
			if ($key == 'id') continue;
			if ($value == 'YYYY-MM-DD' || $value == "_blank") $value = "";
				
			// both object and database
			if (array_search($key, $fields) !== false) {
				$sql .= ($sql)? ", `$key` = ? " : "`$key` = ? ";
				$binds[] = ($value == 'null')? "" : $value;
			}
		}

		// store as appropriate
		if ($insert) { // do insert
			$this->id = sqlInsert("REPLACE `templates` SET $sql",$binds);
		} else { // do update
			$binds[] = $this->id;
			sqlStatement("UPDATE `templates` SET $sql WHERE `id` = ?",$binds);
		}
				
		return $this->id;
	}


	/**
	 * Inserts template object content into the database.
	 *
	 */
	public function delete() {
		// delete if appropriate
		if (! empty($this->id) ) { // do delete
			sqlStatement("DELETE FROM `templates` WHERE `id` = ?", array($this->id));
		}
				
		return;
	}


	/**
	 * Determine the current version for a template name and return
	 * an object for that template.
	 * 
	 * @param string $name unique name for the template
	 * @return object wmtTemplate
	 * 
	 */
	public static function Lookup($name,$lang='English',$fail=TRUE) {
		if (!$name)
			throw new \Exception('wmtTemplate::Lookup - no name provided for template lookup');

		$name = strtolower(preg_replace('/^[^\w]+$/', '', $name)); // only a-z,0-9,_
		$result = sqlQueryNoLog("SELECT `id` FROM `templates` WHERE `name` LIKE ? AND `language` LIKE ?", array($name, $lang));

		// Fallback to English if not found
		if ($lang != 'English' && (!$result || !$result['id']) ) {
			$result = sqlQueryNoLog("SELECT `id` FROM `templates` WHERE `name` LIKE ? AND `language` LIKE ?", array($name, 'English'));
		}
		
		// Verify that we found a template
		if (!$result || !$result['id']) {
            if(!$fail) return FALSE;		   
			throw new \Exception('wmtTemplate::Lookup - no matching record in template table ('. $name .')');
		}
		
		return new self($result['id']);
	}
		
	/**
	 * Generate a merge template document based on a stored template
	 * using the data provided.
	 * 
	 * @param array $elements data elements to be inserted
	 * @param int $id record identifier for the template
	 * @return string merged template content
	 */
	public function Merge($elements, $raw = FALSE, $preventImageReplace = true) {
		if (is_array($elements) === false)
			throw new \Exception('wmtTemplate::Merge - no element data provided for merge');

		if (!$this->id)
			throw new \Exception('wmtTemplate::Merge - no template available for merge');
		
		ShortenLink::generateShortenLink($this->html_tags, $elements);

		// inject standard style information
		if(!$raw) {
	   	   $content = <<<EOF
<style>
	table {
		font-size: 75%;
		font-weight:bold;
		padding: 1px 3px;
		width: 100%;
		border: 0px solid white;
		border-collapse: collapse;
	}
	small {
		font-size: 85%;
		font-weight: normal;
	}
	p {
		margin: 0;
		padding: 0;
	}
	table.border {
		border: 1px solid black;
	}
	td.border {
		border: 0.1px solid black;
	}
	.shadow {
		background-color: #cccccc;
	}
</style>
EOF;
		}
		
		// get html content
		$content .= $this->html_body;

		// do html substitutions
		if (is_array($this->html_tags)) {
			foreach ($this->html_tags AS $tag) {
				$value = (array_key_exists($tag, $elements)) ? $elements[$tag] : '';
				$content = str_ireplace('['.$tag.']', $value, $content);
			}
		}
		

		if($preventImageReplace === false) {
			// fix image path
			preg_match_all("/src=[\"'](.*\/).*\.[?:png|jpg|jpeg|gif|png|svg]/i", $content, $matches);
			foreach ($matches[1] AS $path) {
				$content = str_ireplace($path, $GLOBALS['webroot'].'/images/', $content);
			}
		}

		// store merged html document
		$this->html_merged = $content;
		
		// get text content
		$content = $this->text_body;

		// do html substitutions
		if (is_array($this->text_tags)) {
			foreach ($this->text_tags AS $tag) {
				$value = (array_key_exists($tag, $elements)) ? $elements[$tag] : '';

				if($tag == "all_future_appointments") {
					$value = str_replace("<br/>", "\n", $value);
					$value = strip_tags(html_entity_decode($value));
				}

				$content = str_ireplace('['.$tag.']', $value, $content);
			}
		}
		
		// store merged html document
		$this->text_merged = $content;

	}

	/* Merge Text */
	public function MergeText($elements, $html_str = '') {
		if (is_array($elements) === false)
			throw new \Exception('wmtTemplate::Merge - no element data provided for merge');
		

		// find tags within content
		$count = preg_match_all("/\[([^\]]*)\]/", $html_str, $html_tag);
		$html_tags = ($count) ? $html_tag[1] : array(); // array[1] contains match content

		// get html content
		$content = $html_str;

		// do html substitutions
		if (is_array($html_tags)) {
			foreach ($html_tags AS $tag) {
				$value = (array_key_exists($tag, $elements)) ? $elements[$tag] : '';

				if($tag == "all_future_appointments") {
					$value = str_replace("<br/>", "\n", $value);
					$value = strip_tags(html_entity_decode($value));
				}

				$content = str_ireplace('['.$tag.']', $value, $content);
			}
		}
		
		return $content;
	}
	
}
