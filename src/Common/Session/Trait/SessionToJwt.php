<?php

namespace OpenEMR\Common\Session\Trait;

trait SessionToJwt
{
    public function allFilterForJWT(): array
    {
        $all = $this->all();
        unset($all['csrf_private_key']);
        return $all;
    }

    public function applyDataFromJWT(array $jwtData): void
    {
        $jwtData = array_filter($jwtData, static fn($key): bool => in_array($key, ['iat', 'exp', 'nbf', 'jti']) === false, ARRAY_FILTER_USE_KEY);

        foreach ($jwtData as $key => $value) {
            $this->set($key, $value);
        }
    }
}
