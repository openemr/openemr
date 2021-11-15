<?php

/*
 * This file is part of the Panther project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Symfony\Component\Panther;

/**
 * @internal
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
trait ExceptionThrower
{
    private function createNotSupportedException(string $method): \InvalidArgumentException
    {
        return new \InvalidArgumentException(\sprintf('The "%s" method is not supported when using WebDriver.', $method));
    }
}
