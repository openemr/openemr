<?php
interface AmcFilterIF extends RsFilterIF
{
    public function test( AmcPatient $patient, $dateBegin, $dateEnd );
}
