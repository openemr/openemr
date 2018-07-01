<?php


namespace Doctrine\CouchDB\HTTP;


/**
 * Streams the multipart data from the source to the target and thus makes the
 * transfer with lesser memory footprint.
 *
 * Class MultipartParserAndSender
 * @package Doctrine\CouchDB\HTTP
 */
class MultipartParserAndSender
{
    /**
     * @var StreamClient
     */
    protected $sourceClient;

    /**
     * @var SocketClient
     */
    protected $targetClient;

    /**
     * @var resource
     */
    protected $sourceConnection;

    /**
     * @var resource
     */
    protected $targetConnection;

    /**
     * @param StreamClient $source
     * @param SocketClient $target
     */
    public function __construct(
        AbstractHTTPClient $source,
        AbstractHTTPClient $target
    ) {
        $sourceOptions = $source->getOptions();
        $this->sourceClient = new StreamClient(
            $sourceOptions['host'],
            $sourceOptions['port'],
            $sourceOptions['username'],
            $sourceOptions['password'],
            $sourceOptions['ip'],
            $sourceOptions['ssl'],
            $sourceOptions['path']
        );

        $targetOptions = $target->getOptions();
        $this->targetClient = new SocketClient(
            $targetOptions['host'],
            $targetOptions['port'],
            $targetOptions['username'],
            $targetOptions['password'],
            $targetOptions['ip'],
            $targetOptions['ssl'],
            $targetOptions['path']
        );
    }

    /**
     * Perform request to the source, parse the multipart response,
     * stream the documents with attachments to the target and return
     * the responses along with docs that did not have any attachments.
     *
     * @param string $sourceMethod
     * @param string $sourcePath
     * @param string $targetPath
     * @param string $sourceData
     * @param array $sourceHeaders
     * @return array|ErrorResponse|string
     * @throws HTTPException
     * @throws \Exception
     */
    public function request(
        $sourceMethod,
        $sourcePath,
        $targetPath,
        $sourceData = null,
        array $sourceHeaders = array()
    ) {
        $this->sourceConnection = $this->sourceClient->getConnection(
            $sourceMethod,
            $sourcePath,
            $sourceData,
            $sourceHeaders
        );
        $sourceResponseHeaders = $this->sourceClient->getStreamHeaders();
        $body = '';

        if (empty($sourceResponseHeaders['status'])) {
            try{
                // Close the connection resource.
                fclose($this->sourceConnection);
            } catch (\Exception $e) {

            }
            throw HTTPException::readFailure(
                $this->sourceClient->getOptions()['ip'],
                $this->sourceClient->getOptions()['port'],
                'Received an empty response or not status code',
                0
            );


        } elseif ($sourceResponseHeaders['status'] != 200) {
            while (!feof($this->sourceConnection)) {
                $body .= fgets($this->sourceConnection);
            }
            try{
                fclose($this->sourceConnection);
            } catch (\Exception $e) {

            }
            return new ErrorResponse(
                $sourceResponseHeaders['status'],
                $sourceResponseHeaders,
                $body
            );

        } else {
            try {
                // Body is an array containing:
                // 1) Array of json string documents that don't have
                //  attachments. These should be posted using the Bulk API.
                // 2) Responses of posting docs with attachments.
                $body = $this->parseAndSend($targetPath);
                try{
                    fclose($this->sourceConnection);
                } catch (\Exception $e) {

                }
                return $body;
            } catch(\Exception $e) {
                throw $e;
            }
        }
    }


    /**
     * Read and return next line from the connection pointer.
     * $maxLength parameter can be used to set the maximum length
     * to be read.
     *
     * @param int $maxLength
     * @return string
     */
    protected function getNextLineFromSourceConnection($maxLength = null)
    {
        if ($maxLength !== null) {
            return fgets($this->sourceConnection, $maxLength);
        } else {
            return fgets($this->sourceConnection);
        }
    }

    /**
     * Parses multipart data. Returns an array having:
     * 1) Array of json docs(which are strings) that don't have attachments.
     * These should be posted using the Bulk API.
     * 2) Responses of posting docs with attachments.
     *
     * @param $targetPath
     * @return array
     * @throws \Exception
     * @throws \HTTPException
     */
    protected function parseAndSend($targetPath)
    {
        // Main response boundary of the multipart stream.
        $mainBoundary = trim($this->getNextLineFromSourceConnection());

        // Docs that don't have attachment.
        // These should be posted using Bulk upload.
        $docStack = array();

        // Responses from posting docs that have attachments.
        $responses = array();

        while (!feof($this->sourceConnection)) {

            $line = ltrim($this->getNextLineFromSourceConnection());
            if ($line == '') {
                continue;

            } elseif (strpos($line, 'Content-Type') !== false) {


                list($header, $value) = explode(':', $line);
                $header = trim($header);
                $value = trim($value);
                $boundary = '';

                if (strpos($value, ';') !== false) {
                    list($type, $info) = explode(';', $value, 2);
                    $info = trim($info);

                    // Get the boundary for the current doc.
                    if (strpos($info, 'boundary') !== false) {
                        $boundary = $info;

                    } elseif (strpos($info, 'error') !== false) {

                        // Missing revs at the source. Continue till the end
                        // of this document.
                        while (strpos($this->getNextLineFromSourceConnection(), $mainBoundary) === false) ;
                        continue;

                    } else {

                        throw new \Exception('Unknown parameter with Content-Type.');
                    }

                }
                // Doc with attachments.
                if (strpos($value, 'multipart/related') !== false) {

                    if ($boundary == '') {
                        throw new \Exception('Boundary not set for multipart/related data.');
                    }


                    $boundary = explode('=', $boundary, 2)[1];

                    try {
                        $responses[] = $this->sendStream(
                            'PUT',
                            $targetPath,
                            $mainBoundary,
                            array('Content-Type' => 'multipart/related; boundary=' . $boundary));
                    } catch (\Exception $e) {
                        $responses[] = $e;
                    }


                } elseif ($value == 'application/json') {
                    // JSON doc without attachment.
                    $jsonDoc = '';

                    while(trim(($jsonDoc = $this->getNextLineFromSourceConnection())) == '');
                    array_push($docStack, trim($jsonDoc));

                    // Continue till the end of this document.
                    while (strpos($this->getNextLineFromSourceConnection(), $mainBoundary) === false) ;

                } else {
                    throw new \UnexpectedValueException('This value is not supported.');
                }
            } else {
                throw new \Exception('The first line is not the Content-Type.');
            }
        }
        return array($docStack, $responses);
    }


    /**
     * Reads multipart data from sourceConnection and streams it to the
     * targetConnection.Returns the body of the request or the status code in
     * case there is no body.
     *
     * @param $method
     * @param $path
     * @param $streamEnd
     * @param array $requestHeaders
     * @return mixed|string
     * @throws \Exception
     * @throws \HTTPException
     */
    protected function sendStream(
        $method,
        $path,
        $streamEnd,
        $requestHeaders = array()
    ) {
        $dataStream = $this->sourceConnection;


        // Read the json doc. Use _attachments field to find the total
        // Content-Length and create the request header with initial doc data.
        // At present CouchDB can't handle chunked data and needs
        // Content-Length header.
        $str = '';
        $jsonFlag = 0;
        $attachmentCount = 0;
        $totalAttachmentLength = 0;
        $streamLine = $this->getNextLineFromSourceConnection();
        while (
            $jsonFlag == 0 ||
            ($jsonFlag == 1 &&
                trim($streamLine) == ''
            )
        ) {
            $str .= $streamLine;
            if (strpos($streamLine, 'Content-Type: application/json') !== false) {
                $jsonFlag = 1;
            }
            $streamLine = $this->getNextLineFromSourceConnection();
        }
        $docBoundaryLength = strlen(explode('=', $requestHeaders['Content-Type'], 2)[1]) + 2;
        $json = json_decode($streamLine, true);
        foreach ($json['_attachments'] as $docName => $metaData) {
            // Quotes and a "/r/n"
            $totalAttachmentLength += strlen('Content-Disposition: attachment; filename=') + strlen($docName) + 4;
            $totalAttachmentLength += strlen('Content-Type: ') + strlen($metaData['content_type']) + 2;
            $totalAttachmentLength +=  strlen('Content-Length: ');
            if (isset($metaData['encoding'])) {
                $totalAttachmentLength += $metaData['encoded_length'] + strlen($metaData['encoded_length']) + 2;
                $totalAttachmentLength += strlen('Content-Encoding: ') + strlen($metaData['encoding']) + 2;
            } else {
                $totalAttachmentLength += $metaData['length'] + strlen($metaData['length']) + 2;
            }
            $totalAttachmentLength += 2;
            $attachmentCount++;
        }

        // Add Content-Length to the headers.
        $requestHeaders['Content-Length'] = strlen($str) + strlen($streamLine)
            + $totalAttachmentLength + $attachmentCount * (2 + $docBoundaryLength) + $docBoundaryLength + 2;


        if ($this->targetConnection == null) {
            $this->targetConnection = $this->targetClient->getConnection(
                $method,
                $path,
                null,
                $requestHeaders
            );
        }
        // Write the initial body data.
        fwrite($this->targetConnection, $str);

        // Write the rest of the data including attachments line by line or in
        // chunks.
        while(!feof($dataStream) &&
            ($streamEnd === null ||
                strpos($streamLine, $streamEnd) ===
                false
            )
        ) {
            $totalSent = 0;
            $length = strlen($streamLine);
            while($totalSent != $length) {
                $sent = fwrite($this->targetConnection, substr($streamLine,$totalSent));
                if ($sent === false) {
                    throw new \HTTPException('Stream write error.');
                } else {
                    $totalSent += $sent;
                }
            }
            // Use maxLength while reading the data as there may be no newlines
            // in the binary and compressed attachments, or the lines may be
            // very long.
            $streamLine = $this->getNextLineFromSourceConnection(100000);
        }

        // Read response headers
        $rawHeaders = '';
        $headers = array(
            'connection' => ($this->targetClient->getOptions()['keep-alive'] ? 'Keep-Alive' : 'Close'),
        );


        // Remove leading newlines, should not occur at all, actually.
        while ((($line = fgets($this->targetConnection)) !== false) &&
            (($lineContent = rtrim($line)) === ''));

        // Throw exception, if connection has been aborted by the server, and
        // leave handling to the user for now.
        if ($line === false) {
            // sendStream can't be called in recursion as the source stream can be
            // read only once.
            $error = error_get_last();
            throw HTTPException::connectionFailure(
                $this->targetClient->getOptions()['ip'],
                $this->targetClient->getOptions()['port'],
                $error['message'],
                0
            );
        }

        do {
            // Also store raw headers for later logging
            $rawHeaders .= $lineContent . "\n";
            // Extract header values
            if (preg_match('(^HTTP/(?P<version>\d+\.\d+)\s+(?P<status>\d+))S', $lineContent, $match)) {
                $headers['version'] = $match['version'];
                $headers['status']  = (int) $match['status'];
            } else {
                list($key, $value) = explode(':', $lineContent, 2);
                $headers[strtolower($key)] = ltrim($value);
            }
        }  while ((($line = fgets($this->targetConnection)) !== false) &&
            (($lineContent = rtrim($line)) !== ''));


        // Read response body
        $body = '';

        // HTTP 1.1 supports chunked transfer encoding, if the according
        // header is not set, just read the specified amount of bytes.
        $bytesToRead = (int) (isset( $headers['content-length']) ? $headers['content-length'] : 0);
        // Read body only as specified by chunk sizes, everything else
        // are just footnotes, which are not relevant for us.
        while ($bytesToRead > 0) {
            $body .= $read = fgets($this->targetConnection, $bytesToRead + 1);
            $bytesToRead -= strlen($read);
        }

        // Reset the connection if the server asks for it.
        if ($headers['connection'] !== 'Keep-Alive') {
            fclose($this->targetConnection);
            $this->targetConnection = null;
        }
        // Handle some response state as special cases
        switch ($headers['status']) {
            case 301:
            case 302:
            case 303:
            case 307:
                // Temporary redirect.
                // sendStream can't be called in recursion as the source stream can be
                // read only once.
                throw HTTPException::fromResponse($path, new Response($headers['status'], $headers, $body));
        }
        return ($body != '' ? json_decode($body, true) : array("status" => $headers['status'])) ;
    }


}