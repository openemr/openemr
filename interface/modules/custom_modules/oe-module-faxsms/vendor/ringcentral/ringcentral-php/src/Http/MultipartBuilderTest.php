<?php

use GuzzleHttp\Psr7\Stream;
use RingCentral\SDK\Http\MultipartBuilder;
use RingCentral\SDK\Test\TestCase;

class MultipartBuilderTest extends TestCase
{

    private $fname;

    public function setup(): void
    {

        $this->fname = tempnam('/tmp', 'tfile') . '.txt';

        if (file_exists($this->fname)) {
            unlink($this->fname);
        }

        file_put_contents($this->fname, 'file');

    }

    public function tearDown(): void
    {

        if (file_exists($this->fname)) {
            unlink($this->fname);
        }

    }


    public function testContentPlainText()
    {

        $expected =
            "--boundary\r\n" .
            "Content-Type: application/json\r\n" .
            "Content-Disposition: form-data; name=\"json\"; filename=\"request.json\"\r\n" .
            "Content-Length: 51\r\n" .
            "\r\n" .
            "{\"to\":{\"phoneNumber\":\"foo\"},\"faxResolution\":\"High\"}\r\n" .
            "--boundary\r\n" .
            "Content-Type: application/octet-stream\r\n" .
            "Content-Disposition: form-data; name=\"plain.txt\"; filename=\"plain.txt\"\r\n" .
            "Content-Length: 10\r\n" .
            "\r\n" .
            "plain text\r\n" .
            "--boundary--\r\n";

        $builder = new MultipartBuilder();

        $builder->setBody(['to' => ['phoneNumber' => 'foo'], 'faxResolution' => 'High'])
                ->setBoundary('boundary')
                ->add('plain text', 'plain.txt');

        $request = $builder->request('/fax');

        $this->assertEquals($expected, (string)$request->getBody());

    }

    public function testContentPlainTextWithNoName()
    {

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File name was not provided and cannot be auto-discovered');

        $builder = new MultipartBuilder();

        $builder->setBody(['to' => ['phoneNumber' => 'foo'], 'faxResolution' => 'High'])
                ->setBoundary('boundary')
                ->add('plain text');

    }

    public function testContentStream()
    {

        $fileName = basename($this->fname);

        $expected =
            "--boundary\r\n" .
            "Content-Type: application/json\r\n" .
            "Content-Disposition: form-data; name=\"json\"; filename=\"request.json\"\r\n" .
            "Content-Length: 51\r\n" .
            "\r\n" .
            "{\"to\":{\"phoneNumber\":\"foo\"},\"faxResolution\":\"High\"}\r\n" .
            "--boundary\r\n" .
            "Content-Disposition: form-data; name=\"streamed.txt\"; filename=\"streamed.txt\"\r\n" .
            "Content-Length: 8\r\n" .
            "Content-Type: text/plain\r\n" . // <----- DETECTED AUTOMATICALLY FROM FILE NAME
            "\r\n" .
            "streamed\r\n" .
            "--boundary\r\n" .
            "Content-Disposition: form-data; name=\"" . $fileName . "\"; filename=\"" . $fileName . "\"\r\n" .
            "Content-Length: 4\r\n" .
            "Content-Type: text/plain\r\n" . // <----- DETECTED AUTOMATICALLY FROM FILE NAME
            "\r\n" .
            "file\r\n" .
            "--boundary--\r\n";

        $builder = new MultipartBuilder();

        $builder->setBody(['to' => ['phoneNumber' => 'foo'], 'faxResolution' => 'High'])
                ->setBoundary('boundary')
                ->add(\GuzzleHttp\Psr7\Utils::streamFor('streamed'), 'streamed.txt')
                ->add(new Stream(fopen($this->fname, 'r')));

        $this->assertEquals($expected, (string)$builder->request('/fax')->getBody());

    }

    public function testStreamWithNoName()
    {

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File name was not provided and cannot be auto-discovered');

        $builder = new MultipartBuilder();

        $builder->setBody(['to' => ['phoneNumber' => 'foo'], 'faxResolution' => 'High'])
                ->setBoundary('boundary')
                ->add(\GuzzleHttp\Psr7\Utils::streamFor('streamed'));

    }

    public function testStreamWithUnknownExtension()
    {

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Content-Type header was not provided and cannot be auto-discovered');

        $builder = new MultipartBuilder();

        $builder->setBody(['to' => ['phoneNumber' => 'foo'], 'faxResolution' => 'High'])
                ->setBoundary('boundary')
                ->add(\GuzzleHttp\Psr7\Utils::streamFor('streamed'), 'streamed');

    }

    public function testContentResource()
    {

        $fileName = basename($this->fname);

        $expected =
            "--boundary\r\n" .
            "Content-Type: application/json\r\n" .
            "Content-Disposition: form-data; name=\"json\"; filename=\"request.json\"\r\n" .
            "Content-Length: 51\r\n" .
            "\r\n" .
            "{\"to\":{\"phoneNumber\":\"foo\"},\"faxResolution\":\"High\"}\r\n" .
            "--boundary\r\n" .
            "Content-Disposition: form-data; name=\"" . $fileName . "\"; filename=\"" . $fileName . "\"\r\n" .
            "Content-Length: 4\r\n" .
            "Content-Type: text/plain\r\n" . // <----- DETECTED AUTOMATICALLY FROM FILE NAME
            "\r\n" .
            "file\r\n" .
            "--boundary--\r\n";

        $builder = new MultipartBuilder();

        $builder->setBody(['to' => ['phoneNumber' => 'foo'], 'faxResolution' => 'High'])
                ->setBoundary('boundary')
                ->add(fopen($this->fname, 'r'));

        $this->assertEquals($expected, (string)$builder->request('/fax')->getBody());

    }

    public function testHeaders()
    {

        $expected =
            "--boundary\r\n" .
            "Content-Type: application/json\r\n" .
            "Content-Disposition: form-data; name=\"json\"; filename=\"request.json\"\r\n" .
            "Content-Length: 51\r\n" .
            "\r\n" .
            "{\"to\":{\"phoneNumber\":\"foo\"},\"faxResolution\":\"High\"}\r\n" .
            "--boundary\r\n" .
            "Content-Type: text/custom\r\n" . // <----- CUSTOM HEADER
            "Content-Disposition: form-data; name=\"plain.txt\"; filename=\"plain.txt\"\r\n" .
            "Content-Length: 10\r\n" .
            "\r\n" .
            "plain text\r\n" .
            "--boundary--\r\n";

        $builder = new MultipartBuilder();

        $builder->setBody(['to' => ['phoneNumber' => 'foo'], 'faxResolution' => 'High'])
                ->setBoundary('boundary')
                ->add('plain text', 'plain.txt', ['Content-Type' => 'text/custom']);

        $request = $builder->request('/fax');

        $this->assertEquals($expected, (string)$request->getBody());
        $this->assertEquals('multipart/form-data; boundary=boundary', $request->getHeaderLine('Content-Type'));
        $this->assertEquals('boundary', $builder->boundary());
        $this->assertEquals(1, count($builder->contents()));

    }

    public function testBody()
    {

        $builder = new MultipartBuilder();
        $body = ['to' => ['phoneNumber' => 'foo'], 'faxResolution' => 'High'];

        $builder->setBody($body);

        $this->assertEquals($body, $builder->body());

    }

}