<?php

require_once("../../globals.php");
require_once ($GLOBALS['srcdir'].'/OemrAD/oemrad.globals.php');

use OpenEMR\OemrAd\Utility;

$internalNoteColumnList = array(
	array(
		"name" => "dt_control",
		"title" => "",
		"data" => array(
			"className" => 'dt-control text',
			"width" => "30px",
			"orderable" => false
		)
	),
	array(
		"name" => "active",
		"title" => xlt('Active'),
		"data" => array(
            "width" => "35px",
            "needToRender" => false,
            "orderable" => false
		)
	),
	array(
		"name" => "date_time",
		"title" => xlt('Date/Time'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "140px",
            "orderable" => false
		)
	),
	array(
		"name" => "assigned",
		"title" => xlt('Assigned'),
		"ellipsis" => true,
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "120px",
            "orderable" => false,
		)
	),
	array(
		"name" => "type",
		"title" => xlt('Type'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
		)
	)
);

$emailColumnList = array(
	array(
		"name" => "dt_control",
		"title" => "",
		"data" => array(
			"className" => 'dt-control text',
			"width" => "30px",
			"orderable" => false,
		)
	),
	array(
		"name" => "active",
		"title" => xlt('Active'),
		"data" => array(
            "width" => "35px",
            "needToRender" => false,
            "orderable" => false
		)
	),
	array(
		"name" => "date_time",
		"title" => xlt('Date/Time'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "140px",
            "orderable" => false
		)
	),
	array(
		"name" => "assignment",
		"title" => xlt('Assignment'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "120px",
            "orderable" => false,
		)
	),
	array(
		"name" => "direction",
		"title" => xlt('Direction'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "100px",
            "orderable" => false,
		)
	),
	array(
		"name" => "author",
		"title" => xlt('Author'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "120px",
            "orderable" => false,
		)
	),
	array(
		"name" => "to_from",
		"title" => xlt('To/From'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
		)
	)
);

$smsColumnList = array(
	array(
		"name" => "dt_control",
		"title" => "",
		"data" => array(
			"className" => 'dt-control text',
			"width" => "30px",
			"orderable" => false,
		)
	),
	array(
		"name" => "active",
		"title" => xlt('Active'),
		"data" => array(
            "width" => "35px",
            "needToRender" => false
		)
	),
	array(
		"name" => "date_time",
		"title" => xlt('Date/Time'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "140px"
		)
	),
	array(
		"name" => "assignment",
		"title" => xlt('Assignment'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "120px",
            "orderable" => false,
		)
	),
	array(
		"name" => "author",
		"title" => xlt('Author'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "120px",
            "orderable" => false,
		)
	),
	array(
		"name" => "to_from",
		"title" => xlt('To/From'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
		)
	)
);

$faxColumnList = array(
	array(
		"name" => "dt_control",
		"title" => "",
		"data" => array(
			"className" => 'dt-control text',
			"width" => "30px",
			"orderable" => false,
		)
	),
	array(
		"name" => "active",
		"title" => xlt('Active'),
		"data" => array(
            "width" => "35px",
            "needToRender" => false,
            "orderable" => false
		)
	),
	array(
		"name" => "date_time",
		"title" => xlt('Date/Time'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "140px",
            "orderable" => false
		)
	),
	array(
		"name" => "assignment",
		"title" => xlt('Assignment'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "120px",
            "orderable" => false,
		)
	),
	array(
		"name" => "author",
		"title" => xlt('Author'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "120px",
            "orderable" => false,
		)
	),
	array(
		"name" => "to_from",
		"title" => xlt('To/From'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
		)
	),
	array(
		"name" => "status",
		"title" => xlt('Status'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
            "visible" => false
		)
	)
);

$postalLetterColumnList = array(
	array(
		"name" => "dt_control",
		"title" => "",
		"data" => array(
			"className" => 'dt-control text',
			"width" => "30px",
			"orderable" => false,
		)
	),
	array(
		"name" => "active",
		"title" => xlt('Active'),
		"data" => array(
            "width" => "35px",
            "needToRender" => false
		)
	),
	array(
		"name" => "date_time",
		"title" => xlt('Date/Time'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "140px"
		)
	),
	array(
		"name" => "assignment",
		"title" => xlt('Assignment'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "120px",
            "orderable" => false,
		)
	),
	array(
		"name" => "author",
		"title" => xlt('Author'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "120px",
            "orderable" => false,
		)
	),
	array(
		"name" => "to_from",
		"title" => xlt('To'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
		)
	),
	array(
		"name" => "status",
		"title" => xlt('Status'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
            "visible" => false
		)
	)
);