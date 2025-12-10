<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing;

enum Gateway: string
{
    case InHouse = 'InHouse';
    case AuthorizeNet = 'AuthorizeNet';
    case Sphere = 'Sphere';
    case Stripe = 'Stripe';
}
