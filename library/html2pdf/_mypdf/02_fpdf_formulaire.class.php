<?php
/**
 * Logiciel : HTML2PDF - classe FPDF_Formulaire
 * 
 * permet la gestion de champs de formulaire dans un PDF 
 * Inspiré des sources de http://fpdf.org/fr/script/script36.php et http://fpdf.org/fr/script/script40.php
 *
 * @author		Laurent MINGUET <webmaster@html2pdf.fr>
 */

if (!defined('__CLASS_FPDF_FORMULAIRE__'))
{
	define('__CLASS_FPDF_FORMULAIRE__', true);
	
	require_once(dirname(__FILE__).'/01_fpdf_bookmark.class.php');
	
	class FPDF_Formulaire extends FPDF_BookMark
	{
		var $javascript = '';	//javascript code
		var $n_js;				//numéro de l'objet javascript
		var $n_cata;			//numéro de l'objet catalogue
		var $ur;				//
		
		function FPDF_Formulaire($orientation='P',$unit='mm',$format='A4')
		{
			$this->FPDF_BookMark($orientation,$unit,$format);
			$this->PDFVersion='1.6';
			
			$this->ur = false;
		}
		
		 function _putuserrights()
		{
			if (!$this->ur) return;
			$this->_out('/Perms<<');
			
			$this->_out('/UR3<<');
			$this->_out('/Reference[<<');
			$this->_out('/Type /SigRef');
			$this->_out('/TransformMethod /UR3');
			$this->_out('/TransformParams<<');
			$this->_out('/Type /TransformParams');
			$this->_out('/Annots[ /Create /Delete /Modify /Copy /Import /Export ]');
			$this->_out('/Document [ /FullSave ]');
			$this->_out('/Form[ /Add /FillIn /Delete /SubmitStandalone ]');
			$this->_out('/Signature[ /Modify ]');
			$this->_out('/V /2.2');
			$this->_out('>>');
			$this->_out('>>]');
			$this->_out('>>');
			$this->_out('>>');
		}
		
		function _putresources()
		{
		
			parent::_putresources();
			$this->_putjavascript();
		}
		
		function _putcatalog()
		{
			$this->n_cata = $this->n;
			
			parent::_putcatalog();
			
			if (!empty($this->javascript)) $this->_out('/Names <</JavaScript '.($this->n_js).' 0 R>>');
			$this->_putuserrights();
		}

		/*
		* Create a javascript PDF string.
		* @access protected
		* @author Johannes Güntert, Nicola Asuni
		*/
		function _putjavascript()
		{
			if (empty($this->javascript)) return;
			
			// the following two lines are used to avoid form fields duplication after saving
			if ($this->ur)
			{
				$js1 = "if(!this.getField('pdfoldsaved')) this.addField('pdfoldsaved','text',0, [0, 1, 0, 1]);";
				$js2 = "getField('pdfoldsaved').value = 'saved';";
			}
			else
			{
				$js1 = '';
				$js2 = '';	
			}
		
			$this->_newobj();
			$this->n_js = $this->n;
			$this->_out('<<');
			$this->_out('/Names [(EmbeddedJS) '.($this->n + 1).' 0 R ]');
			$this->_out('>>');
			$this->_out('endobj');
			$this->_newobj();
			$this->_out('<<');
			$this->_out('/S /JavaScript');
			$this->_out('/JS '.$this->_textstring($js1."\n".$this->javascript."\n".$js2));
			$this->_out('>>');
			$this->_out('endobj');
		}
		
		/*
		* Convert color to javascript color.
		* @param string $color color name or #RRGGBB
		* @access protected
		* @author Denis Van Nuffelen, Nicola Asuni
		*/
		function _JScolor($color)
		{
			static $aColors = array('transparent', 'black', 'white', 'red', 'green', 'blue', 'cyan', 'magenta', 'yellow', 'dkGray', 'gray', 'ltGray');
			if (substr($color,0,1) == '#')
			{
				return sprintf("['RGB',%.3F,%.3F,%.3F]", hexdec(substr($color,1,2))/255, hexdec(substr($color,3,2))/255, hexdec(substr($color,5,2))/255);
			}
			if (!in_array($color,$aColors))
			{
				$this->Error('Invalid color: '.$color);
			}
			
			return 'color.'.$color;
		}
		
		/*
		* Adds a javascript form field.
		* @param string $type field type
		* @param string $name field name
		* @param int $x horizontal position
		* @param int $y vertical position
		* @param int $w width
		* @param int $h height
		* @param array $prop array of properties. Possible values are (http://www.adobe.com/devnet/acrobat/pdfs/js_developer_guide.pdf): <ul><li>rect: Position and size of field on page.</li><li>borderStyle: Rectangle border appearance.</li><li>strokeColor: Color of bounding rectangle.</li><li>lineWidth: Width of the edge of the surrounding rectangle.</li><li>rotation: Rotation of field in 90-degree increments.</li><li>fillColor: Background color of field (gray, transparent, RGB, or CMYK).</li><li>userName: Short description of field that appears on mouse-over.</li><li>readonly: Whether the user may change the field contents.</li><li>doNotScroll: Whether text fields may scroll.</li><li>display: Whether visible or hidden on screen or in print.</li><li>textFont: Text font.</li><li>textColor: Text color.</li><li>textSize: Text size.</li><li>richText: Rich text.</li><li>richValue: Text.</li><li>comb: Text comb format.</li><li>multiline: Text multiline.</li><li>charLimit: Text limit to number of characters.</li><li>fileSelect: Text file selection format.</li><li>password: Text password format.</li><li>alignment: Text layout in text fields.</li><li>buttonAlignX: X alignment of icon on button face.</li><li>buttonAlignY: Y alignment of icon on button face.</li><li>buttonFitBounds: Relative scaling of an icon to fit inside a button face.</li><li>buttonScaleHow: Relative scaling of an icon to fit inside a button face.</li><li>buttonScaleWhen: Relative scaling of an icon to fit inside a button face.</li><li>highlight: Appearance of a button when pushed.</li><li>style: Glyph style for checkbox and radio buttons.</li><li>numItems: Number of items in a combo box or list box.</li><li>editable: Whether the user can type in a combo box.</li><li>multipleSelection: Whether multiple list box items may be selected.</li></ul>
		* @access protected
		* @author Denis Van Nuffelen, Nicola Asuni
		*/
		function _addfield($type, $name, $x, $y, $w, $h, $prop, $js_after = '')
		{
			if (!isset($prop['textSize']))		$prop['textSize']		= $this->FontSizePt;
			if (!isset($prop['strokeColor']))	$prop['strokeColor']	= 'ltGray';
			if (isset($prop['value'])) 			$prop['value']			= str_replace('"', '', $prop['value']);
			$name_field = preg_replace('/[^a-zA-Z0-9_]/isU', '_', $name);
			
			$this->SetFillColor(240);
			if ($w>0 && $h>0)
			{
				$d = 1/$this->k;
				$r = 0.1;
				$this->Rect($x+$d*0.5+$r, $y-$d*0.5+$r, $w-$d-2*$r, $h-$d-2*$r, 'F');
			}
			
			// javascript inclus			
			$this->ur = true;
			
			// the followind avoid fields duplication after saving the document
			$this->javascript .= "if(this.getField('pdfoldsaved') && this.getField('pdfoldsaved').value != 'saved') {";
			$this->javascript .= sprintf("f".$name_field."=this.addField('%s','%s',%d,[%.2F,%.2F,%.2F,%.2F]);", $name, $type, $this->PageNo()-1, $x*$this->k, ($this->h-$y)*$this->k+1, ($x+$w)*$this->k, ($this->h-$y-$h)*$this->k+1)."\n";
			$this->javascript .= 'f'.$name_field.'.textSize='.$this->FontSizePt.";\n";
			while (list($key, $val) = each($prop))
			{
				if (strcmp(substr($key, -5), 'Color') == 0)
					$val = $this->_JScolor($val);
				else
					$val = '"'.$val.'"';
				$this->javascript .= 'f'.$name_field.'.'.$key.'='.$val.";\n";
			}
			
			$this->javascript .= '}';
			$this->javascript.= "\n".$js_after;
		}
		
		function IncludeJS($script)
		{
			$this->javascript .= $script;
		}
		
		function form_InputHidden($name, $value)
		{
			$name_field = preg_replace('/[^a-zA-Z0-9_]/isU', '_', $name);
			$prop = array('value' => $value);
			$js_after = '';
			$this->_addfield('checkbox', $name, 0, 0, 0.1, 0.1, $prop, $js_after);
		}
		
		function form_InputCheckBox($name, $x, $y, $w, $checked)
		{
			$name_field = preg_replace('/[^a-zA-Z0-9_]/isU', '_', $name);
			
			$prop = array();
			$prop['value'] = ($checked ? 'Yes' : 'Off');
			$js_after = '';
			$this->_addfield('checkbox', $name, $x, $y, $w, $w, $prop, $js_after);
		}
		
		function form_InputRadio($name, $x, $y, $w)
		{
			$name_field = preg_replace('/[^a-zA-Z0-9_]/isU', '_', $name);
			
			$prop = array();
			$js_after = '';
			$this->_addfield('radiobutton', $name, $x, $y, $w, $w, $prop, $js_after);
		}
		
		function form_InputText($name, $x, $y, $w, $h, $prop)
		{
			$name_field = preg_replace('/[^a-zA-Z0-9_]/isU', '_', $name);
			
			$js_after = '';
			$this->_addfield('text', $name, $x, $y, $w, $h, $prop, $js_after);
		}
		
		function form_InputButton($name, $x, $y, $w, $h, $caption, $action, $prop)
		{
			$name_field = preg_replace('/[^a-zA-Z0-9_]/isU', '_', $name);
			
			if (!isset($prop['borderStyle']))	$prop['borderStyle']	= 'beveled';
			if (!isset($prop['fillColor']))		$prop['fillColor']		= 'ltGray';
			if (!isset($prop['strokeColor']))	$prop['strokeColor']	= 'black';

			$js_after = 'f'.$name_field.".buttonSetCaption('".addslashes($caption)."');\n";
			$js_after.= 'f'.$name_field.".setAction('MouseUp','".addslashes($action)."');\n";
			$js_after.= 'f'.$name_field.".highlight='push';\n";
			$js_after.= 'f'.$name_field.".print=false;\n";
			$this->_addfield('button', $name, $x, $y, $w, $h, $prop, $js_after);
		}

		function form_Select($name, $x, $y, $w, $h, $values, $multiligne, $prop)
		{
			$name_field = preg_replace('/[^a-zA-Z0-9_]/isU', '_', $name);
			
			$type = ($multiligne ? 'listbox' : 'combobox');				
			$s = ''; foreach ($values as $value) { $s .= ($s ? ',' : '')."'".addslashes($value)."'"; }
			$js_after = 'f'.$name_field.'.setItems(['.$s."]);\n";
			$this->_addfield($type, $name, $x, $y, $w, $h, $prop, $js_after);
		}
	}
}
