<?php

use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\Test\TestCase;

class ApiResponseTest extends TestCase
{

    public function testMultipart()
    {

        $goodMultipartMixedResponse =
            "Content-Type: multipart/mixed; boundary=Boundary_1245_945802293_1394135045248\n" .
            "\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\r\n" .
            "\r\n" .
            "{\"response\" : [{\"status\" : 200}, {\"status\" : 200}]\n" .
            "}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"foo\": \"bar\"}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"baz\" : \"qux\"}\n" .
            "--Boundary_1245_945802293_1394135045248--\n";

        $r = new ApiResponse(null, ApiResponse::createResponse($goodMultipartMixedResponse, 207));
        $parts = $r->multipart();

        $this->assertEquals(2, count($parts));
        $this->assertEquals('bar', $parts[0]->json()->foo);
        $this->assertEquals('qux', $parts[1]->json()->baz);


    }

    public function testMultipartWithErrorPart()
    {

        $multipartMixedResponseWithErrorPart =
            "Content-Type: multipart/mixed; boundary=Boundary_1245_945802293_1394135045248\n" .
            "\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"response\" : [{\"status\" : 200}, {\"status\" : 404}]\n" .
            "}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"foo\" : \"bar\"}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"message\" : \"object not found\"}\n" .
            "--Boundary_1245_945802293_1394135045248--\n";

        $r = new ApiResponse(null, ApiResponse::createResponse($multipartMixedResponseWithErrorPart, 207));
        $parts = $r->multipart();

        $this->assertEquals(2, count($parts));
        $this->assertEquals('bar', $parts[0]->json()->foo);
        $this->assertEquals('object not found', $parts[1]->error());

    }

    public function testMultipartCorruptedResponse()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('JSON Error: Syntax error, malformed JSON');

        $badMultipartMixedResponse =
            "Content-Type: multipart/mixed; boundary=Boundary_1245_945802293_1394135045248\n" .
            "\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "THIS IS JUNK AND CANNOT BE PARSED AS JSON\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"foo\" : \"bar\"}\n" .
            "--Boundary_1245_945802293_1394135045248--\n";

        $r3 = new ApiResponse(null, ApiResponse::createResponse($badMultipartMixedResponse, 207));
        $r3->multipart();

    }

    public function testMultipartOnNotAMultipartResponse()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Response is not multipart');

        $r3 = new ApiResponse(null, ApiResponse::createResponse("Content-Type: text/plain\n\nWhatever", 207));
        $r3->multipart();

    }

    public function testMultipartWitoutBoundary()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Boundary not found');

        $response =
            "Content-Type: multipart/mixed\n" .
            "\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\r\n" .
            "\r\n" .
            "{\"response\" : [ {\"status\" : 200} ]}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"foo\" : \"bar\"}\n" .
            "--Boundary_1245_945802293_1394135045248--\n";

        $r3 = new ApiResponse(null, ApiResponse::createResponse($response, 207));
        $r3->multipart();

    }

    public function testGetJson()
    {

        $r = new ApiResponse(null, ApiResponse::createResponse("content-type: application/json\n\n{\"foo\":\"bar\"}", 200));

        $this->assertEquals('{"foo":"bar"}', $r->text());
        $this->assertEquals('bar', $r->json()->foo);

        $asArray = $r->jsonArray();
        $this->assertEquals('bar', $asArray['foo']);

    }

    public function testGetJsonWithNotJSON()
    {

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Response is not JSON');

        $r = new ApiResponse(null, ApiResponse::createResponse("content-type: application/not-a-json\n\nfoo", 200));
        $r->json();

    }

    public function testGetJsonWithCorruptedJSON()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('JSON Error: Syntax error, malformed JSON');

        $r = new ApiResponse(null, ApiResponse::createResponse("content-type: application/json\n\n{\"foo\";\"bar\"}", 200));
        $r->json();

    }

    public function testGetJsonWithEmptyJSON()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('JSON Error: Result is empty after parsing');

        $r = new ApiResponse(null, ApiResponse::createResponse("content-type: application/json\n\nnull", 200));
        $r->json();

    }

}