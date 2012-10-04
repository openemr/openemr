<?php
/**
 * Logiciel : HTML2PDF - classe ParsingHTML
 *
 * Convertisseur HTML => PDF
 * Distribué sous la licence LGPL. 
 *
 * @author		Laurent MINGUET <webmaster@html2pdf.fr>
 * @version		3.31
 */

class parsingHTML
{
	var $html		= '';				// code HTML à parser
	var $num		= 0;				// numéro de table
	var $level		= 0;				// niveaux de table
	var $encoding	= '';				// encodage
	var $code		= array();			// code HTML parsé
	
	/**
	 * Constructeur
	 *
	 * @return	null
	 */
	function parsingHTML($encoding = 'ISO-8859-15')
	{
		$this->num		= 0;
		$this->level	= array($this->num);
		$this->html		= '';
		$this->code		= array();
		$this->setEncoding($encoding);
	}
	
	function setEncoding($encoding)
	{
		$this->encoding = $encoding;
	}
	
	/**
	 * Définir le code HTML à parser
	 *
	 * @param	string code html
	 * @return	null
	 */
	function setHTML($html)
	{
		$html = preg_replace('/<!--(.*)-->/isU', '', $html);
		$this->html = $html;
	}
	
	/**
	 * parser le code HTML
	 *
	 * @return	null
	 */
	function parse()
	{
		$parents = array();

		// chercher les balises HTML du code
		$tmp = array();
		$this->searchCode($tmp);
		
		// identifier les balises une à une
		$pre_in = false;
		$pre_br = array(
					'name' => 'br',
					'close' => false,
					'param' => array(
						'style' => array(),
						'num'	=> 0
					)
				);

		$balises_no_closed = array(
									'br', 'hr', 'img', 'col',
									'input', 'link', 'option',
									'circle', 'ellipse', 'path', 'rect', 'line', 'polygon', 'polyline'
							);
		$todos = array();
		foreach($tmp as $part)
		{
			// si c'est un code
			if ($part[0]=='code')
			{
				$res = $this->analiseCode($part[1]);
				
				// si le code est bien un code analisable
				if ($res)
				{
					$res['html_pos'] = $part[2];
					if (!in_array($res['name'], $balises_no_closed))
					{
						if ($res['close'])
						{
							if (count($parents)<1)
								HTML2PDF::makeError(3, __FILE__, __LINE__, $res['name'], $this->getHtmlErrorCode($res['html_pos']));
							else if ($parents[count($parents)-1]!=$res['name'])
								HTML2PDF::makeError(4, __FILE__, __LINE__, $parents, $this->getHtmlErrorCode($res['html_pos']));
							else
								unset($parents[count($parents)-1]);
						}
						else
						{
							if ($res['autoclose'])
							{
								$todos[] = $res;
								$res['params'] = array();
								$res['close'] = true;
							}
							else
								$parents[count($parents)] = $res['name'];
													
						}
						if (($res['name']=='pre' || $res['name']=='code') && !$res['autoclose'])
								$pre_in = !$res['close'];
					}
					
					$todos[] = $res;
				}
				// sinon (code non analisable) => on le transforme en texte
				else
				{
					$part[0]='txt';
				}
			}
			// sinon si c'est un texte
			if ($part[0]=='txt')
			{
				// enregistrer l'action correspondante
				if (!$pre_in)
				{
					// remplacer tous les espaces, tabulations, saufs de ligne multiples par de simples espaces
					$todos[] = array(
									'name'	=> 'write',
									'close'	=> false,
									'param' => array('txt' => $this->prepareTxt($part[1])),
								);
				}
				else
				{
					$part[1] = str_replace("\r", '', $part[1]);
					$part[1] = explode("\n", $part[1]);
					
					foreach($part[1] as $k => $txt)
					{
						$txt = str_replace("\t", '        ', $txt);
						$txt = str_replace(' ', '&nbsp;', $txt);
						if ($k>0) $todos[] = $pre_br;

						$todos[] = array(
										'name'	=> 'write',
										'close'	=> false,
										'param' => array('txt' => $this->prepareTxt($txt, false)),
									);
					}
				}
			}	
		}

		// pour chaque action identifiée, il faut nettoyer le début et la fin des textes
		// en fonction des balises qui l'entourent.
		$balises_clean = array('page', 'page_header', 'page_footer', 'form',
								'table', 'thead', 'tfoot', 'tr', 'td', 'th', 'br',
								'div', 'hr', 'p', 'ul', 'ol', 'li',
								'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
								'bookmark', 'fieldset', 'legend',
								'draw', 'circle', 'ellipse', 'path', 'rect', 'line', 'g', 'polygon', 'polyline');
		$nb = count($todos);
		for($k=0; $k<$nb; $k++)
		{
			//si c'est un texte
			if ($todos[$k]['name']=='write')
			{
				// et qu'une balise spécifique le précède => on nettoye les espaces du début du texte
				if ($k>0 && in_array($todos[$k-1]['name'], $balises_clean))
					$todos[$k]['param']['txt'] = ltrim($todos[$k]['param']['txt']);

				// et qu'une balise spécifique le suit => on nettoye les espaces de la fin du texte
				if ($k<$nb-1 && in_array($todos[$k+1]['name'], $balises_clean))
					$todos[$k]['param']['txt'] = rtrim($todos[$k]['param']['txt']);
				
				if (!strlen($todos[$k]['param']['txt']))
					unset($todos[$k]);
			}
		}
		if (count($parents)) HTML2PDF::makeError(5, __FILE__, __LINE__, $parents);

		// liste des actions sauvée
		$this->code = array_values($todos);
	}
	
	/**
	 * preparer le texte une seule fois pour gagner du temps. vient de o_WRITE
	 *
	 * @param	string texte
	 * @return	string texte
	 */
	function prepareTxt($txt, $spaces = true)
	{
		if ($spaces) $txt = preg_replace('/\s+/is', ' ', $txt);
		$txt = str_replace('&euro;', '€', $txt);
		$txt = html_entity_decode($txt, ENT_QUOTES, $this->encoding);
		return $txt;
	}
	
	/**
	 * parser le code HTML
	 *
	 * @param	&array	tableau de retour des données
	 * @return	null
	 */
	function searchCode(&$tmp)
	{
		// séparer les balises du texte
		$tmp = array();
		$reg = '/(<[^>]+>)|([^<]+)+/isU';

		// pour chaque élément trouvé :
		$str = '';
		$offset = 0;
		while(preg_match($reg, $this->html, $parse, PREG_OFFSET_CAPTURE, $offset))
		{
			// si une balise a été détectée
			if ($parse[1][0])
			{
				// sauvegarde du texte précédent si il existe
				if ($str!=='')	$tmp[] = array('txt',$str);
	
				// sauvegarde de la balise
				$tmp[] = array('code',trim($parse[1][0]), $offset);
				
				// initialisation du texte suivant
				$str = '';
			}
			else
			{
				// ajout du texte à la fin de celui qui est déjà détecté
				$str.= $parse[2][0];
			}
			// Update offset to the end of the match
			$offset = $parse[0][1] + strlen($parse[0][0]);
			unset($parse);
		}
		// si un texte est présent à la fin, on l'enregistre
		if ($str!='') $tmp[] = array('txt',$str);
		unset($str);
	}
	
	/**
	 * analyse une balise HTML
	 *
	 * @param	string	code HTML à identifier
	 * @return	array	action correspondante
	 */
	function analiseCode($code)
	{
		// nom de la balise et ouverture ou fermeture
		$balise = '<([\/]{0,1})([_a-z0-9]+)([\/>\s]+)';
		if (!preg_match('/'.$balise.'/isU', $code, $match))
			return null;

		$close	= ($match[1]=='/' ? true : false);
		$autoclose = preg_match('/\/>$/isU', $code);
		$name	= strtolower($match[2]);
		
		// paramètres obligatoires en fonction du nom de la balise
		$param	= array();
		$param['style'] = '';
		if ($name=='img')	{ $param['alt'] = '';	$param['src'] = ''; }
		if ($name=='a')		{ $param['href'] = '';	}
		
		// lecture des paramétres du type nom=valeur
		$prop = '([a-zA-Z0-9_]+)=([^"\'\s>]+)';
		preg_match_all('/'.$prop.'/is', $code, $match);
		for($k=0; $k<count($match[0]); $k++)
			$param[trim(strtolower($match[1][$k]))] = trim($match[2][$k]);

		// lecture des paramétres du type nom="valeur"
		$prop = '([a-zA-Z0-9_]+)=["]([^"]*)["]';
		preg_match_all('/'.$prop.'/is', $code, $match);
		for($k=0; $k<count($match[0]); $k++)
			$param[trim(strtolower($match[1][$k]))] = trim($match[2][$k]);

		// lecture des paramétres du type nom='valeur'
		$prop = "([a-zA-Z0-9_]+)=[']([^']*)[']";
		preg_match_all('/'.$prop.'/is', $code, $match);
		for($k=0; $k<count($match[0]); $k++)
			$param[trim(strtolower($match[1][$k]))] = trim($match[2][$k]);
	
		// mise en conformité en style de chaque paramètre
		$color	= "#000000";
		$border	= null;
		foreach($param as $key => $val)
		{
			$key = strtolower($key);
			switch($key)
			{
				case 'width':
					unset($param[$key]);
					$param['style'] .= 'width: '.$val.'px; ';
					break;

				case 'align':
					if ($name==='img')
					{
						unset($param[$key]);
						$param['style'] .= 'float: '.$val.'; ';
					} 
					elseif ($name!=='table')
					{
						unset($param[$key]);
						$param['style'] .= 'text-align: '.$val.'; ';
					}
					break;
					
				case 'valign':
					unset($param[$key]);
					$param['style'] .= 'vertical-align: '.$val.'; ';
					break;
					
				case 'height':
					unset($param[$key]);
					$param['style'] .= 'height: '.$val.'px; ';
					break;

				case 'bgcolor':
					unset($param[$key]);
					$param['style'] .= 'background: '.$val.'; ';
					break;

				case 'bordercolor':
					unset($param[$key]);
					$color = $val;
					break;

				case 'border':
					unset($param[$key]);
					if (preg_match('/^[0-9]+$/isU', $val)) $val = $val.'px';
					$border = $val;
					break;
				
				case 'cellpadding':
				case 'cellspacing':
					if (preg_match('/^([0-9]+)$/isU', $val)) $param[$key] = $val.'px';
					break;
					
				case 'colspan':
				case 'rowspan':
					$val = preg_replace('/[^0-9]/isU', '', $val);
					if (!$val) $val = 1;
					$param[$key] = $val;
					break;
			}
		}
		if ($border!==null)
		{
			if ($border)	$border = 'border: solid '.$border.' '.$color;
			else			$border = 'border: none';
			
			$param['style'] .= $border.'; ';
			$param['border'] = $border;
		}
		
		// lecture des styles - décomposition
		$styles = explode(';', $param['style']);
		$param['style'] = array();
		foreach($styles as $style)
		{
			$tmp = explode(':', $style);
			if (count($tmp)>1)
			{
				$cod = $tmp[0]; unset($tmp[0]); $tmp = implode(':', $tmp); 
				$param['style'][trim(strtolower($cod))] = preg_replace('/[\s]+/isU', ' ', trim($tmp));
			}
		}
		
		// détermination du niveau de table pour les ouverture, avec ajout d'un level
		if (in_array($name, array('ul', 'ol', 'table')) && !$close)
		{
			$this->num++;
			$this->level[count($this->level)] = $this->num;
		} 
		
		// attribution du niveau de table où se trouve l'élément
		if (!isset($param['num'])) $param['num'] = $this->level[count($this->level)-1];

		// pour les fins de table : suppression d'un level
		if (in_array($name, array('ul', 'ol', 'table')) && $close)
		{
			unset($this->level[count($this->level)-1]);
		} 

		if (isset($param['value']))	$param['value']	= $this->prepareTxt($param['value']);
		if (isset($param['alt']))	$param['alt']	= $this->prepareTxt($param['alt']);
		if (isset($param['title']))	$param['title']	= $this->prepareTxt($param['title']);
		if (isset($param['class']))	$param['class']	= $this->prepareTxt($param['class']);
		
		// retour de l'action identifiée
		return array('name' => $name, 'close' => $close ? 1 : 0, 'autoclose' => $autoclose, 'param' => $param);
	}
	
	// récupérer un niveau complet d'HTML entre une ouverture de balise et la fermeture correspondante
	function getLevel($k)
	{
		// si le code n'existe pas : fin
		if (!isset($this->code[$k])) return array();
		
		// quelle balise faudra-t-il détecter
		$detect = $this->code[$k]['name'];
		
		$level = 0;			// niveau de profondeur
		$end = false;		// etat de fin de recherche
		$code = array();	// code extrait
		
		// tant que c'est pas fini, on boucle
		while (!$end)
		{
			// action courante
			$row = $this->code[$k];
			
			// si write => on ajoute le texte
			if ($row['name']=='write')
			{
				$code[] = $row;
			}
			// sinon, c'est une balise html
			else
			{
				$not = false; // indicateur de non prise en compte de la balise courante
				
				// si c'est la balise que l'on cherche
				if ($row['name']==$detect)
				{
					if ($level==0) { $not = true; }					// si on est à la premiere balise : on l'ignore
					$level+= ($row['close'] ? -1 : 1);				// modification du niveau en cours en fonction de l'ouvertre / fermeture
					if ($level==0) { $not = true; $end = true; }	// si on est au niveau 0 : on a fini
				}
				
				// si on doit prendre en compte la balise courante
				if (!$not)
				{
					if (isset($row['style']['text-align'])) unset($row['style']['text-align']);
					$code[] = $row;
				}
			}
			
			// on continue tant qu'il y a du code à analyser...
			if (isset($this->code[$k+1]))
				$k++;
			else
				$end = true;
		}
		
		// retourne le code extrait
		return $code;
	}
	
	function getHtmlErrorCode($pos)
	{
		return substr($this->html, $pos-30, 70);
	}
}