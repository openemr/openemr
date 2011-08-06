<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
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
