<?php

namespace Stripe;

/**
 * Class OrderReturn
 *
 * @package Stripe
 */
class OrderReturn extends ApiResource
{
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

    /**
     * @param array|string $id The ID of the order return to retrieve, or an
     *     options array containing an `id` field.
     * @param array|string|null $opts
     *
     * @return Order
     */
    public static function retrieve($id, $opts = null)
    {
        return self::_retrieve($id, $opts);
    }

    /**
     * @param array|null $params
     * @param array|string|null $opts
     *
     * @return Collection of OrderReturns
     */
    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }
}
