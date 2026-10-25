<?php

namespace GeneralDermatology\App;

class SxsList
{
    public function getSxs(): array
    {
        return [
            'growth',
            'pain',
            'itching',
            'bleeding',
            'change in color',
            'change in shape',
            'spreading'
        ];
    }
}
