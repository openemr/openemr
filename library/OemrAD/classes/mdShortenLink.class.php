<?php

namespace OpenEMR\OemrAd;

class ShortenLink {

	public static function getConfigVars() {
		$returnList = new \stdClass();
		$returnList->access_token = isset($GLOBALS['shortenlink_access_token']) ? $GLOBALS['shortenlink_access_token'] : "";
		$returnList->service_type = isset($GLOBALS['shortenlink_service']) ? $GLOBALS['shortenlink_service'] : "";
		$returnList->shlink_domain = isset($GLOBALS['shlink_domain']) ? $GLOBALS['shlink_domain'] : "";
		$returnList->shortenlink_username = isset($GLOBALS['shortenlink_username']) ? $GLOBALS['shortenlink_username'] : "";
		$returnList->shortenlink_password = isset($GLOBALS['shortenlink_password']) ? $GLOBALS['shortenlink_password'] : "";

		return $returnList;
	}

	/*Generate ShortenLink*/
	public static function generateShortenLink($html_tags, &$elements) {
		$configList = self::getConfigVars();
		$tags_list = array('zm_short_patient_url', 'zm_short_provider_url');

		// do html substitutions
		foreach ($tags_list as $key => $tList) {
			if (in_array($tList, $html_tags)) {
				$value = (array_key_exists($tList, $elements)) ? $elements[$tList] : '';
				if(!empty($value)) {
					if($configList->service_type == "bitly") {
						$link = self::handleShortenLink($value);
					} else if($configList->service_type == "tinyurl") {
						$link = self::handleTinyShortenLink($value);
					} else if($configList->service_type == "shlink") {
						$link = self::handleShlink($value);
					} else if($configList->service_type == "yourls") {
						$link = self::handleYourls($value);
					}
					$elements[$tList] = $link;
				}
			}
		}
	}

	/*Handle Tiny ShortenLink*/
	public static function handleTinyShortenLink($link) {
		if(isset($link) && !empty($link)) {
			$tLink = file_get_contents('http://tinyurl.com/api-create.php?url='.$link);
			if(isset($tLink) && !empty($tLink)) {
				$link = $tLink;
			}
		}
		return $link;
	}

	/*Handle ShortenLink*/
	public static function handleShortenLink($link) {
		$configList = self::getConfigVars();
		if(isset($configList->access_token) && !empty($configList->access_token)) {
			if(isset($link) && !empty($link)) {
				$responce = self::getShortenLink(array(
					'long_url' => $link,
					'domain' => 'bit.ly'
				));

				if(isset($responce) && isset($responce['id']) && isset($responce['link'])) {
					return $responce['link'];
				}
			}
		}

		return $link;
	}

	/*Get Shorten Link*/
	public static function getShortenLink($data = array()) {
		$configList = self::getConfigVars();
		$api_url = "https://api-ssl.bitly.com/v4/shorten";

		// Define header values
		$headers = [
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: Bearer '.$configList->access_token
		];
		
		// Set up client connection
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		
		// Send data
		$result = curl_exec($ch);
		$errCode = curl_errno($ch);
		$errText = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// Handle result
		return self::handle($result, $httpCode);

	}

	/* Handle CURL response from servers. */
	public static function handle($result, $httpCode) {
		// Check for non-OK statuses
		return json_decode($result, true);
	}

	public static function get_domain($url){
	  $pieces = parse_url($url);
	  $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
	  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
	    return $regs['domain'];
	  }
	  return false;
	}

	/*Handle Shlink*/
	public static function handleShlink($link) {
		$configList = self::getConfigVars();
		if(isset($configList->access_token) && !empty($configList->access_token) && !empty($configList->shlink_domain)) {
			if(isset($link) && !empty($link)) {
				$responce = self::getShortenShlink(array(
					'longUrl' => $link,
					'domain' => self::get_domain($configList->shlink_domain),
					'forwardQuery' => true,
					'findIfExists' => true
				));

				if(isset($responce) && isset($responce['shortCode']) && isset($responce['shortUrl'])) {
					return $responce['shortUrl'];
				}
			}
		}

		return $link;
	}

	/*Get Shorten Link*/
	public static function getShortenShlink($data = array()) {
		$configList = self::getConfigVars();
		$api_url = $configList->shlink_domain."/rest/v1/short-urls";

		// Define header values
		$headers = [
			'Content-Type: application/json',
			'Accept: application/json',
			'X-Api-Key: '.$configList->access_token
		];
		
		// Set up client connection
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		
		// Send data
		$result = curl_exec($ch);
		$errCode = curl_errno($ch);
		$errText = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// Handle result
		return self::handle($result, $httpCode);
	}

	/*Handle Yourls*/
	public static function handleYourls($link) {
		$configList = self::getConfigVars();
		if(!empty($configList->shortenlink_username) && !empty($configList->shortenlink_password) && !empty($configList->shlink_domain)) {
			if(isset($link) && !empty($link)) {
				$responce = self::getShortenYourls(array(
					'username' => $configList->shortenlink_username,
					'password' => $configList->shortenlink_password,
					'url' => $link,
					'format' => 'json',
					'action' => "shorturl"
				));

				if(isset($responce) && isset($responce['shorturl']) && !empty($responce['shorturl'])) {
					return $responce['shorturl'];
				}
			}
		}

		return $link;
	}

	/*Get Shorten Link*/
	public static function getShortenYourls($data = array()) {
		$configList = self::getConfigVars();
		$api_url = $configList->shlink_domain."/yourls-api.php";
		
		// Set up client connection
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		
		// Send data
		$result = curl_exec($ch);
		$errCode = curl_errno($ch);
		$errText = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// Handle result
		return self::handle($result, $httpCode);
	}
}