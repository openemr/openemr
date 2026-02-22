<?php

namespace OpenEMR\Rest;

use OpenApi\Annotations as OA;

class ExampleController
{
    /**
     * @OA\Get(
     *     path="/hello",
     *     summary="Returns a greeting",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Hello, world!")
     *         )
     *     )
     * )
     */
    public function hello()
    {
        return ['message' => 'Hello, world!'];
    }
}
