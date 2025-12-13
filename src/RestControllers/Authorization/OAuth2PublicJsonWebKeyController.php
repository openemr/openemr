<?php

namespace OpenEMR\RestControllers\Authorization;

use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Http\HttpRestRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OAuth2PublicJsonWebKeyController
{
    public function __construct(private readonly string $publicKeyPath)
    {
        // Constructor can be used for dependency injection if needed
    }

    /**
     * @param HttpRestRequest $request
     * @return Response
     * @throws OAuthServerException
     */
    public function getJsonWebKeyResponse(HttpRestRequest $request): Response
    {
        $public = file_get_contents($this->publicKeyPath);
        if ($public === false) {
            throw OAuthServerException::serverError("Failed to read public key file");
        }
        $keyPublic = openssl_pkey_get_details(openssl_pkey_get_public($public));
        if ($keyPublic === false) {
            throw OAuthServerException::serverError("Failed to parse public key");
        }
        $key_info = [
            'kty' => 'RSA',
            'n' => $this->base64url_encode($keyPublic['rsa']['n']),
            'e' => $this->base64url_encode($keyPublic['rsa']['e']),
        ];
        $key_info['use'] = 'sig';

        $jsonData = ['keys' => [$key_info]];

        $request->getSession()->invalidate();
        return new JsonResponse($jsonData);
    }


    public function base64url_encode($input): string
    {
        return rtrim(strtr(base64_encode((string) $input), '+/', '-_'), '=');
    }
}
