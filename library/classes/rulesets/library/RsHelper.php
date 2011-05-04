<?php
class RsHelper
{
    public static function formatClinicalRules( array $results )
    {
        $formattedResults = array();
        foreach ( $results as $result ) {
            if ( $result instanceof RsResultIF ) {
                $formattedResults []= $result->format();
            } else {
                throw new Exception( "Result must be an instance of RsResultIF" );
            }
        }

        return $formattedResults;
    }
}
