<?php
/**
 * Stripe Item bag
 */

namespace Omnipay\Stripe;

use Omnipay\Common\ItemBag;
use Omnipay\Common\ItemInterface;

/**
 * Class StripeItemBag
 *
 * @package Omnipay\Stripe
 */
class StripeItemBag extends ItemBag
{
    /**
     * Add an item to the bag
     *
     * @see Item
     *
     * @param ItemInterface|array $item An existing item, or associative array of item parameters
     */
    public function add($item)
    {
        if ($item instanceof ItemInterface) {
            $this->items[] = $item;
        } else {
            $this->items[] = new StripeItem($item);
        }
    }
}
