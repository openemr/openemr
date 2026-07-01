<?php

declare(strict_types=1);

namespace OpenEMR\Config;

interface ManagerInterface
{
    /**
     * @template T
     * @param Key<T> $key
     * @return T
     */
    public function getCurrentValue(Key $key): mixed;

    /**
     * @template T
     * @param Key<T> $key
     * @param T $newValue
     */
    public function setValue(Key $key, mixed $newValue): void;

    // Ideally, there's a bulk setValues as well, but you can't use enums as
    // keys so the ergonomics get weird fast. It's only really needed during
    // initial install so...meh. The current UI's implementation would benefit
    // too, but that's more an indication that the impl should change.
}
