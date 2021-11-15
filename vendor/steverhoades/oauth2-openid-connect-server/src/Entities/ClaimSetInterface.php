<?php
/**
 * @author Steve Rhoades <sedonami@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace OpenIDConnectServer\Entities;


interface ClaimSetInterface
{
    /**
     * @return array
     */
    public function getClaims();
}
