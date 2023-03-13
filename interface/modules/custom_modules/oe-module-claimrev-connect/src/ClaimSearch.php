<?php
namespace OpenEMR\Modules\ClaimRevConnector;
use OpenEMR\Modules\ClaimRevConnector\ClaimRevApi;
use OpenEMR\Common\Crypto\CryptoGen;

class ClaimSearch 
{
    public static function Search($search)
    {
        $token = ClaimRevApi::GetAccessToken();
        $data = ClaimRevApi::searchClaims($search,$token);

        return $data;
    }
}

?>