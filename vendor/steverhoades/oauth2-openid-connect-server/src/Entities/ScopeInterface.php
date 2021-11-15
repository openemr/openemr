<?php
/**
 * @author Steve Rhoades <sedonami@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace OpenIDConnectServer\Entities;


interface ScopeInterface
{
    /**
     * @return string
     */
    public function getScope();
}
