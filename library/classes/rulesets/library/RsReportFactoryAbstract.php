<?php
abstract class RsReportFactoryAbstract
{
    public abstract function createReport( $className, $rowRule, $patients, $dateTarget );
}
