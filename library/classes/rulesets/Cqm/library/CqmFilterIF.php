<?php
interface CqmFilterIF extends RsFilterIF
{
    public function test( CqmPatient $patient, $dateBegin, $dateEnd );
}
