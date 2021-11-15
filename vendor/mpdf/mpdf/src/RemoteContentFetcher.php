<?php

namespace Mpdf;

use Mpdf\Utils\Arrays;
use Psr\Log\LoggerInterface;
use Mpdf\Log\Context as LogContext;

class RemoteContentFetcher implements \Psr\Log\LoggerAwareInterface
{

	/**
	 * @var \Mpdf\Mpdf
	 */
	private $mpdf;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private $logger;

	public function __construct(Mpdf $mpdf, LoggerInterface $logger)
	{
		$this->mpdf = $mpdf;
		$this->logger = $logger;
	}

	public function getFileContentsByCurl($url)
	{
		$this->logger->debug(sprintf('Fetching (cURL) content of remote URL "%s"', $url), ['context' => LogContext::REMOTE_CONTENT]);

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_USERAGENT, $this->mpdf->curlUserAgent);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->mpdf->curlTimeout);

		if ($this->mpdf->curlFollowLocation) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		}

		if ($this->mpdf->curlAllowUnsafeSslRequests) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		if (is_file($this->mpdf->curlCaCertificate)) {
			curl_setopt($ch, CURLOPT_CAINFO, $this->mpdf->curlCaCertificate);
		}

		if ($this->mpdf->curlProxy) {
			curl_setopt($ch, CURLOPT_PROXY, $this->mpdf->curlProxy);
			if ($this->mpdf->curlProxyAuth) {
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->mpdf->curlProxyAuth);
			}
		}

		$data = curl_exec($ch);

		if (curl_error($ch)) {
			$message = sprintf('cURL error: "%s"', curl_error($ch));
			$this->logger->error($message, ['context' => LogContext::REMOTE_CONTENT]);

			if ($this->mpdf->debug) {
				throw new \Mpdf\MpdfException($message);
			}
		}

		$info = curl_getinfo($ch);
		if (isset($info['http_code']) && $info['http_code'] !== 200) {
			$message = sprintf('HTTP error: %d', $info['http_code']);
			$this->logger->error($message, ['context' => LogContext::REMOTE_CONTENT]);

			if ($this->mpdf->debug) {
				throw new \Mpdf\MpdfException($message);
			}
		}

		curl_close($ch);

		return $data;
	}

	public function getFileContentsBySocket($url)
	{
		$this->logger->debug(sprintf('Fetching (socket) content of remote URL "%s"', $url), ['context' => LogContext::REMOTE_CONTENT]);
		// mPDF 5.7.3

		$timeout = 1;
		$p = parse_url($url);

		$file = Arrays::get($p, 'path', '');
		$scheme = Arrays::get($p, 'scheme', '');
		$port = Arrays::get($p, 'port', 80);
		$prefix = '';

		if ($scheme === 'https') {
			$prefix = 'ssl://';
			$port = Arrays::get($p, 'port', 443);
		}

		$query = Arrays::get($p, 'query', null);
		if ($query) {
			$file .= '?' . $query;
		}

		if (!($fh = @fsockopen($prefix . $p['host'], $port, $errno, $errstr, $timeout))) {
			$this->logger->error(sprintf('Socket error "%s": "%s"', $errno, $errstr), ['context' => LogContext::REMOTE_CONTENT]);
			return false;
		}

		$getstring = 'GET ' . $file . " HTTP/1.0 \r\n" .
			'Host: ' . $p['host'] . " \r\n" .
			"Connection: close\r\n\r\n";

		fwrite($fh, $getstring);

		// Get rid of HTTP header
		$s = fgets($fh, 1024);
		if (!$s) {
			return false;
		}

		while (!feof($fh)) {
			$s = fgets($fh, 1024);
			if ($s === "\r\n") {
				break;
			}
		}

		$data = '';

		while (!feof($fh)) {
			$data .= fgets($fh, 1024);
		}

		fclose($fh);

		return $data;
	}

	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
}
