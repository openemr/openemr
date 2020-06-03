<?php

class C_Hl7 extends Controller
{

    function __construct($template_mod = "general")
    {
        parent::__construct();
        $this->template_mod = $template_mod;
        $this->assign("STYLE", $GLOBALS['style']);
    }

    function default_action()
    {
        return $this->fetch($GLOBALS['template_dir'] . "hl7/" . $this->template_mod . "_parse.html");
    }
    function default_action_process()
    {
        $msg = '';
        if ($_POST['process'] == "true") {
            $msg = $_POST['hl7data'];
        }

        $hp = new Parser_HL7v2($msg);
        $err = $hp->parse();
        //print_r($hp);
        if (!empty($err)) {
            $this->assign("hl7_message_err", nl2br("Error:<br />" . $err));
        }

        $this->assign("hl7_array", $hp->composite_array());
        return;
    }
}


//sample HL7 message used for testing
/*$msg = <<<EOF
MSH|^~\&|ADT1|CUH|LABADT|CUH|198808181127|SECURITY|ADT^A01|MSG00001|P|2.3|
EVN|A01|198808181122||
PID|||PATID1234^5^M11||RYAN^HENRY^P||19610615|M||C|1200 N ELM STREET^^GREENSBORO^NC^27401-1020|GL|(919)379-1212|(919)271-3434 ||S||PATID12345001^2^M10|123456789|987654^NC|
NK1|JOHNSON^JOAN^K|WIFE||||||NK^NEXT OF KIN
PV1|1|I|2000^2053^01||||004777^FISHER^BEN^J.|||SUR||||ADM|A0|
EOF;
$hp = new Parser_HL7v2($msg);
print_r($hp->MSH);
echo "<br /><br />";
print_r($hp->EVN);*/
