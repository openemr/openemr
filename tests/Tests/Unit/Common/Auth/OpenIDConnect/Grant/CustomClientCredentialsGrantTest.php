<?php
/**
 * CustomClientCredentialsGrantTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Auth\OpenIDConnect\Grant;


use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\AccessTokenEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomClientCredentialsGrant;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;

class CustomClientCredentialsGrantTest extends TestCase
{
    const OAUTH_INVALID_CLIENT_MESSAGE ='Client authentication failed';
    const TEST_CLIENT_ID = 'gCz3kd1r322a8yffyNgVj-nglCBRU4yVwRsXq9ScEvo';

    /**
     * Checks to make sure that not having the client assertion type throws an exception because the client can't be
     * found
     */
    public function testMissingClientAssertionTypeThrowsInvalidClientException()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseTypeInterface::class);
        $clientRepo = $this->createMock(ClientRepositoryInterface::class);
        $clientRepo->method('getClientEntity')
            ->willReturn(null);

        $ttl = new \DateInterval('PT300S');
        $grant = new CustomClientCredentialsGrant();

        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage(self::OAUTH_INVALID_CLIENT_MESSAGE);

        $grant->setClientRepository($clientRepo);
        $grant->respondToAccessTokenRequest($request, $response, $ttl);
    }

    /**
     * Tests that we can get a valid response for the client credentials grant using the client's jwk_uri to validate
     * @throws \Exception
     */
    public function testValidResponseForClientWithJwkURI() {
        $jwkPublicSet =
            '[ "keys" : '
            . '['
                . '{"kty":"RSA",'
                . '"n": "0vx7agoebGcQSuuPiLJXZptN9nndrQmbXEps2aiAFbWhM78LhWx'
                . '4cbbfAAtVT86zwu1RK7aPFFxuhDR1L6tSoc_BJECPebWKRXjBZCiFV4n3oknjhMs'
                . 'tn64tZ_2W-5JsGY4Hc5n9yBXArwl93lqt7_RN5w6Cf0h4QyQ5v-65YGjQR0_FDW2'
                . 'QvzqY368QQMicAtaSqzs8KJZgnYb9c7d0zgdAZHzu6qMQvRL5hajrn1n91CbOpbI'
                . 'SD08qNLyrdkt-bFTWhAI4vMQFh6WeZu0fM4lFd2NcRwr3XPksINHaQ-G_xBniIqb'
                . 'w0Ls1jF44-csFCur-kEgU8awapJzKnqDKgw",'
                . '"e":"AQAB",'
                . '"alg":"RS256",'
                . '"kid":"2011-04-29"}'
            . ']'
        . ']';
        $jwkPrivateSet =
            '[ "keys" : '
            . '['
                . '{"kty":"RSA",'
                . '"n":"0vx7agoebGcQSuuPiLJXZptN9nndrQmbXEps2aiAFbWhM78LhWx4'
                . 'cbbfAAtVT86zwu1RK7aPFFxuhDR1L6tSoc_BJECPebWKRXjBZCiFV4n3oknjhMst'
                . 'n64tZ_2W-5JsGY4Hc5n9yBXArwl93lqt7_RN5w6Cf0h4QyQ5v-65YGjQR0_FDW2Q'
                . 'vzqY368QQMicAtaSqzs8KJZgnYb9c7d0zgdAZHzu6qMQvRL5hajrn1n91CbOpbIS'
                . 'D08qNLyrdkt-bFTWhAI4vMQFh6WeZu0fM4lFd2NcRwr3XPksINHaQ-G_xBniIqbw'
                . '0Ls1jF44-csFCur-kEgU8awapJzKnqDKgw",'
                . '"e":"AQAB",'
                . '"d":"X4cTteJY_gn4FYPsXB8rdXix5vwsg1FLN5E3EaG6RJoVH-HLLKD9'
                . 'M7dx5oo7GURknchnrRweUkC7hT5fJLM0WbFAKNLWY2vv7B6NqXSzUvxT0_YSfqij'
                . 'wp3RTzlBaCxWp4doFk5N2o8Gy_nHNKroADIkJ46pRUohsXywbReAdYaMwFs9tv8d'
                . '_cPVY3i07a3t8MN6TNwm0dSawm9v47UiCl3Sk5ZiG7xojPLu4sbg1U2jx4IBTNBz'
                . 'nbJSzFHK66jT8bgkuqsk0GjskDJk19Z4qwjwbsnn4j2WBii3RL-Us2lGVkY8fkFz'
                . 'me1z0HbIkfz0Y6mqnOYtqc0X4jfcKoAC8Q",'
                . '"p":"83i-7IvMGXoMXCskv73TKr8637FiO7Z27zv8oj6pbWUQyLPQBQxtPV'
                . 'nwD20R-60eTDmD2ujnMt5PoqMrm8RfmNhVWDtjjMmCMjOpSXicFHj7XOuVIYQyqV'
                . 'WlWEh6dN36GVZYk93N8Bc9vY41xy8B9RzzOGVQzXvNEvn7O0nVbfs",'
                . '"q":"3dfOR9cuYq-0S-mkFLzgItgMEfFzB2q3hWehMuG0oCuqnb3vobLyum'
                . 'qjVZQO1dIrdwgTnCdpYzBcOfW5r370AFXjiWft_NGEiovonizhKpo9VVS78TzFgx'
                . 'kIdrecRezsZ-1kYd_s1qDbxtkDEgfAITAG9LUnADun4vIcb6yelxk",'
                . '"dp":"G4sPXkc6Ya9y8oJW9_ILj4xuppu0lzi_H7VTkS8xj5SdX3coE0oim'
                . 'YwxIi2emTAue0UOa5dpgFGyBJ4c8tQ2VF402XRugKDTP8akYhFo5tAA77Qe_Nmtu'
                . 'YZc3C3m3I24G2GvR5sSDxUyAN2zq8Lfn9EUms6rY3Ob8YeiKkTiBj0",'
                . '"dq":"s9lAH9fggBsoFR8Oac2R_E2gw282rT2kGOAhvIllETE1efrA6huUU'
                . 'vMfBcMpn8lqeW6vzznYY5SSQF7pMdC_agI3nG8Ibp1BUb0JUiraRNqUfLhcQb_d9'
                . 'GF4Dh7e74WbRsobRonujTYN1xCaP6TO61jvWrX-L18txXw494Q_cgk",'
                . '"qi":"GyM_p6JrXySiz1toFgKbWV-JdI3jQ4ypu9rbMWx3rQJBfmt0FoYzg'
                . 'UIZEVFEcOqwemRN81zoDAaa-Bk0KWNGDjJHZDdDmFhW3AN7lI-puxk_mHZGJ11rx'
                . 'yR8O55XLSe3SPmRfKwZI6yU24ZxvQKFYItdldUKGzO6Ia6zTKhAVRU",'
                . '"alg":"RS256",'
                . '"kid":"2011-04-29"}'
            . ']'
        . ']';

        

        $jwt = '';
        $jwkUri = 'https://localhost:9000/some-jwk-uri';
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')
            ->willReturn([
                'client_assertion_type' => CustomClientCredentialsGrant::OAUTH_JWT_CLIENT_ASSERTION_TYPE
                ,'client_assertion' => $jwt
                ,'redirect_uri' => null
            ]);

        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier(self::TEST_CLIENT_ID);
        $clientEntity->setJwksUri($jwkUri);

        // the custom grant will make an external call to the jwkUri which we expect
        // we mock the call and have it return the jwk set here.
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($jwkSet);


        $clientRepo = $this->createMock(ClientRepositoryInterface::class);
        $clientRepo->method('getClientEntity')
            ->willReturn($clientEntity);

        $clientRepo->method('validateClient')
            ->willReturn(true);

        // setup our fake access token & our repo
        $accessToken = new AccessTokenEntity();
        $accessTokenRepo = $this->createMock(AccessTokenRepository::class);
        $accessTokenRepo->method('getNewToken')
            ->willReturn($accessToken);

        $ttl = new \DateInterval('PT300S');
        $grant = new CustomClientCredentialsGrant();
        $grant->setClientRepository($clientRepo);
        $grant->setHttpClient($httpClient);
        $grant->setAccessTokenRepository($accessTokenRepo);

        $response = $this->createMock(ResponseTypeInterface::class);

        // make sure we assert that our setAccessToken will be called as this is the final step where we know
        // the system will work fine
        $response->expects($this->once())
            ->method('setAccessToken')
            ->willReturn($accessToken);

        $grant->respondToAccessTokenRequest($request, $response, $ttl);

    }
}