<?php

namespace Stripe;

/**
 * Class OrderReturn
 *
 * @property string $id
 * @property string $object
 * @property int $amount
 * @property int $created
 * @property string $currency
 * @property OrderItem[] $items
 * @property bool $livemode
 * @property string $order
 * @property string $refund
 *
 * @package Stripe
 */
class OrderReturn extends ApiResource
{

    const OBJECT_NAME = "order_return";

    use ApiOperations\All;
    use ApiOperations\Retrieve;

    /**
     * This is a special case because the order returns endpoint has an
     *    underscore in it. The parent `className` function strips underscores.
     *
     * @return string The name of the class.
     */
    public static function className()
    {
        return 'order_return';
    }
}
