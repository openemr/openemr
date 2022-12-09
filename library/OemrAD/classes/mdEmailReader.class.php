<?php

namespace OpenEMR\OemrAd;

class EmailReader {

	/*Constructor*/
	public function __construct() {
	}

	public static function _fix($str) {
	    if (preg_match("/=\?.{0,}\?[Bb]\?/", $str))
	    {
	        $str = preg_split("/=\?.{0,}\?[Bb]\?/", $str);
	        while (list($key, $value) = each($str))
	        {
	            if (preg_match("/\?=/", $value))
	            {
	                $arrTemp = preg_split("/\?=/", $value);
	                $arrTemp[0] = base64_decode($arrTemp[0]);
	                $str[$key] = join("", $arrTemp);
	            }
	        }
	        $str = join("", $str);
	    }

	    if (preg_match("/=\?.{0,}\?Q\?/", $str))
	    {
	        $str = quoted_printable_decode($str);
	        $str = preg_replace("/=\?.{0,}\?[Qq]\?/", "", $str);
	        $str = preg_replace("/\?=/", "", $str);
	    }
	    return trim($str);
	}

	public static function _fetchHeader($header, $id){
	    if (!is_object($header))
	    {
	        return;
	    }
	    $mail = new \stdClass();
	    $mail->id = $id;
	    $mail->mbox = "";
	    $mail->timestamp = (isset($header->udate)) ? ($header->udate) : ('');
	    $mail->date = date("d/m/Y H:i:s", (isset($header->udate)) ? ($header->udate) : (''));
	    $mail->from = self::_fix(isset($header->fromaddress) ? ($header->fromaddress) : (''));
	    $mail->to = self::_fix(isset($header->toaddress) ? ($header->toaddress) : (''));
	    $mail->reply_to = self::_fix(isset($header->reply_toaddress) ? ($header->reply_toaddress) : (''));
	    $mail->subject = self::_fix(isset($header->subject) ? ($header->subject) : (''));
	    $mail->content = array ();
	    $mail->attachments = array ();
	    $mail->deleted = false;
	    return $mail;
	}

	public static function _fetchHeader1($header, $mail){
		if(!empty($header) && !empty($mail)) {
			$mail->to_list = array();
			$mail->cc_list = array(); 

			if(isset($header->to) && is_array($header->to)) {
				foreach ($header->to as $tk => $toEmail) {
					$mail->to_list[] = $toEmail->mailbox . "@" . $toEmail->host;
				}
			}

			if(isset($header->cc) && is_array($header->cc)) {
				foreach ($header->cc as $cck => $ccEmail) {
					$mail->cc_list[] = $ccEmail->mailbox . "@" . $ccEmail->host;
				}
			}
		}

		return $mail;
	}

	public static function fetchType($structure){
	        $primary_mime_type = array ("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");
	        if ((isset($structure->subtype)) && ($structure->subtype) && (isset($structure->type)))
	        {
	            return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype;
	        }
	        return "TEXT/PLAIN";
	}

	public static function fetchParameter($parameters, $key){
	    foreach ($parameters as $parameter)
	    {
	        if (strcmp($key, $parameter->attribute) == 0)
	        {
	            return $parameter->value;
	        }
	    }
	    return null;
	}

	public static function fetchPartsStructureRoot($mail, $structure) {
	    $parts = array ();
	    if ((isset($structure->parts)) && (is_array($structure->parts)) && (count($structure->parts) > 0))
	    {
	        foreach ($structure->parts as $key => $data)
	        {
	            self::fetchPartsStructure($mail, $data, ($key + 1), $parts);
	        }
	    }
	    return $parts;
	}

	public static function fetchPartsStructure($mail, $structure, $prefix, &$parts) {
	    if ((isset($structure->parts)) && (is_array($structure->parts)) && (count($structure->parts) > 0))
	    {
	        foreach ($structure->parts as $key => $data)
	        {
	            self::fetchPartsStructure($mail, $data, $prefix . "." . ($key + 1), $parts);
	        }
	    }

	    $part = new \stdClass;
	    $part->no = $prefix;
	    $part->data = $structure;

	    $parts[] = $part;
	}

	public static function msgdecode($message, $coding) {
	    switch ($coding)
	    {
	        case 2:
	            $message = imap_binary($message);
	            break;
	        case 3:
	            $message = imap_base64($message);
	            break;
	        case 4:
	            $message = imap_qprint($message);
	            break;
	        case 5:
	            break;
	        default:
	            break;
	    }
	    return $message;
	}

	public static function _fetch($connection, $emailIdent, $mail, $structure) {
		if ((!isset($structure->parts)) || (!is_array($structure->parts)))
        {
            $body = imap_body($connection, $emailIdent);
            $content = new \stdClass();
            $content->type = 'content';
            $content->mime = self::fetchType($structure);
            $content->charset = self::fetchParameter($structure->parameters, 'charset');
            $content->data = self::msgdecode($body, $structure->type);
            $content->size = strlen($content->data);

            $mail->content[] = $content;
        } else {
            $parts = self::fetchPartsStructureRoot($mail, $structure);
            foreach ($parts as $part)
            {
                $content = new \stdClass();
                $content->type = null;
                $content->data = null;
                $content->mime = self::fetchType($part->data);
                if ((isset($part->data->disposition))
                   && ((strcmp('attachment', $part->data->disposition) == 0)
                   || (strcmp('inline', $part->data->disposition) == 0)))
                {
                    $content->type = $part->data->disposition;
                    $content->name = null;
                    if (isset($part->data->dparameters))
                    {
                        $content->name = self::fetchParameter($part->data->dparameters, 'filename');
                    }
                    if (is_null($content->name))
                    {
                        if (isset($part->data->parameters))
                        {
                            $content->name = self::fetchParameter($part->data->parameters, 'name');
                        }
                    }
                    $mail->attachments[] = $content;
                } else if ($part->data->type == 0)
                {
                    $content->type = 'content';
                    $content->charset = null;
                    if (isset($part->data->parameters))
                    {
                        $content->charset = self::fetchParameter($part->data->parameters, 'charset');
                    }
                    $mail->content[] = $content;
                }
                $body = imap_fetchbody($connection, $emailIdent, $part->no);
                if (isset($part->data->encoding))
                {
                    $content->data = self::msgdecode($body, $part->data->encoding);
                }
                else
                {
                    $content->data = $body;
                }
                $content->size = strlen($content->data);
            }
        }

        return $mail;
	}

	public static function geMessagesContent($mail) {
		$content = "";
		if(!empty($mail)) {
			if(isset($mail->content) && is_array($mail->content)) {
				$i=0;
				$typeList = array("TEXT/PLAIN", "TEXT/HTML");
				
				while (($i < 2) && empty($content)) {
					foreach ($mail->content as $k => $mailItem) {
						if($mailItem->mime == $typeList[$i] && $mailItem->type == "content") {
							if(isset($mailItem->data) && !empty($mailItem->data)) {
								$content = $mailItem->data;
							}
						}
					}
					$i++;
				}
			}
		}

		return htmlentities($content);
	}
}