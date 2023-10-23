<?php

namespace OpenEMR\Modules\EhiExporter;

class ExportTableDataFilterer
{
    private const SELECT_COLUMNS = [
        'users' =>
        [
            'id', 'uuid', 'username', 'authorized', 'fname', 'lname', 'suffix'
            , 'federaltaxid', 'federaldrugid', 'facility', 'facility_id', 'see_auth'
            , 'active', 'npi', 'title', 'specialty', 'billname', 'url', 'assistant'
            , 'valedictory', 'state', 'taxonomy', 'abook_type', 'default_warehouse'
            , 'irnpool','state_license_number','weno_prov_id','newcrop_user_role'
            ,'cpoe','physician_type', 'portal_user','supervisor_id','billing_facility','billing_facility_id'
        ]
    ];

    public function getSelectQueryForTable(string $tableName) {
        if (isset(self::SELECT_COLUMNS[$tableName])) {
            return self::SELECT_COLUMNS[$tableName];
        }
        // all tables not in the list should return every column
        return "*";
    }
}
