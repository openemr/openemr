<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     fetch
 * Purpose:  fetch file, web or ftp data and display results
 * -------------------------------------------------------------
 */
function smarty_function_fetch($params, &$smarty)
{
	$file = $params['file'];

    if (empty($file)) {
        $smarty->_trigger_plugin_error("parameter 'file' cannot be empty");
        return;
    }

    if ($smarty->security && !preg_match('!^(http|ftp)://!i', $file)) {
        // fetching file, make sure it comes from secure directory
        foreach ($smarty->secure_dir as $curr_dir) {
            if (substr(realpath($file), 0, strlen(realpath($curr_dir))) == realpath($curr_dir)) {
                $resource_is_secure = true;
                break;
            }
        }
        if (!$resource_is_secure) {
            $smarty->_trigger_plugin_error("(secure mode) fetch '$file' is not allowed");
            return;
        }
		// fetch the file
		if($fp = @fopen($file,'r')) {
			while(!feof($fp)) {
				$content .= fgets ($fp,4096);
			}
			fclose($fp);
		} else {
            $smarty->_trigger_plugin_error("fetch cannot read file '$file'");
            return;			
		}
    } else {
		// not a local file
		if(preg_match('!^http://!i',$file)) {
			// http fetch
			if($uri_parts = parse_url($file)) {
				// set defaults
				$host = $server_name = $uri_parts['host'];
				$timeout = 30;
				$accept = "image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*";
				$agent = "Smarty Template Engine ".$smarty->_version;
				$referer = "";
				if(!empty($uri_parts['path'])) {
					$uri = $uri_parts['path'];
				} else {
					$uri = '/';
				}
				$_is_proxy = false;
				if(empty($uri_parts['port'])) {
					$port = 80;
				} else {
					$port = $uri_parts['port'];
				}
				if(empty($uri_parts['user'])) {
					$user = $uri_parts['user'];
				}				
				// loop through parameters, setup headers
				foreach($params as $param_key => $param_value) {			
					switch($param_key) {
						case "file":
						case "assign":
						case "assign_headers":
							break;
						case "user":
							if(!empty($param_value)) {
								$user = $param_value;
							}
							break;
						case "pass":
							if(!empty($param_value)) {
								$pass = $param_value;
							}
							break;
						case "accept":
							if(!empty($param_value)) {
								$accept = $param_value;
							}
							break;
						case "header":
							if(!empty($param_value)) {
								if(!preg_match('![\w\d-]+: .+!',$param_value)) {
            						$smarty->_trigger_plugin_error("invalid header format '".$param_value."'");
            						return;									
								} else {
									$extra_headers[] = $param_value;
								}
							}
							break;
						case "proxy_host":
							if(!empty($param_value)) {
								$proxy_host = $param_value;
							}
							break;
						case "proxy_port":
							if(!preg_match('!\D!', $param_value)) {
								$proxy_port = (int) $param_value;
							} else {
            					$smarty->_trigger_plugin_error("invalid value for attribute '".$param_key."'");
            					return;									
							}
							break;
						case "agent":
							if(!empty($param_value)) {
								$agent = $param_value;
							}
							break;
						case "referer":
							if(!empty($param_value)) {
								$referer = $param_value;
							}
							break;
						case "timeout":
							if(!preg_match('!\D!', $param_value)) {
								$timeout = (int) $param_value;
							} else {
            					$smarty->_trigger_plugin_error("invalid value for attribute '".$param_key."'");
            					return;									
							}
							break;
						default:
            				$smarty->_trigger_plugin_error("unrecognized attribute '".$param_key."'");
            				return;
					}			
				}
				if(!empty($proxy_host) && !empty($proxy_port)) {
					$_is_proxy = true;
					$fp = fsockopen($proxy_host,$proxy_port,$errno,$errstr,$timeout);
				} else {
					$fp = fsockopen($server_name,$port,$errno,$errstr,$timeout);
				}

				if(!$fp) {
            		$smarty->_trigger_plugin_error("unable to fetch: $errstr ($errno)");
            		return;				
				} else {
					if($_is_proxy) {
						fputs($fp, "GET $file HTTP/1.0\r\n");						
					} else {
						fputs($fp, "GET $uri HTTP/1.0\r\n");
					}
					if(!empty($host)) {
						fputs($fp, "Host: $host\r\n");
					}
					if(!empty($accept)) {
						fputs($fp, "Accept: $accept\r\n");
					}
					if(!empty($agent)) {
						fputs($fp, "User-Agent: $agent\r\n");
					}
					if(!empty($referer)) {
						fputs($fp, "Referer: $referer\r\n");
					}
					if(is_array($extra_headers)) {
						foreach($extra_headers as $curr_header) {
							fputs($fp, $curr_header."\r\n");
						}
					}
					if(!empty($user) && !empty($pass)) {
						fputs($fp, "Authorization: BASIC ".base64_encode("$user:$pass")."\r\n");						
					}
					
					fputs($fp, "\r\n");
					while(!feof($fp)) {
						$content .= fgets($fp,4096);
					}
					fclose($fp);					
					$csplit = split("\r\n\r\n",$content,2);

					$content = $csplit[1];
					
					if(!empty($params['assign_headers'])) {
						$smarty->assign($params['assign_headers'],split("\r\n",$csplit[0]));
					}
				}
			} else {
            		$smarty->_trigger_plugin_error("unable to parse URL, check syntax");
            		return;
			}
		} else {
			// ftp fetch
			if($fp = @fopen($file,'r')) {
				while(!feof($fp)) {
					$content .= fgets ($fp,4096);
				}
				fclose($fp);
			} else {
            	$smarty->_trigger_plugin_error("fetch cannot read file '$file'");
            	return;			
			}
		}
		
	}


    if (!empty($params['assign'])) {
        $smarty->assign($params['assign'],$content);
    } else {
        echo $content;
    }
}

/* vim: set expandtab: */

?>
