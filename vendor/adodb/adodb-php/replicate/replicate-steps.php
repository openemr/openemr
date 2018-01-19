<?php

# CONFIG

if (empty($USER)) {
	$BA = "LOAN"; ## -- leave $BA as empty string to copy all BA. Otherwise enter 1 BA (no need to quote BA)
	$STAGES = ""; ## $STAGES = "STGCAT1,STGCAT2"  -- leave $STAGES as empty string to run all stages. No need to quote stgcats.

	$HOST='192.168.0.2';
	$USER='JCOLLECT_BKRM';
	$PWD='natsoft';
	$DBASE='RAPTOR';
}
# =================================== INCLUDES

include_once('../adodb.inc.php');
include_once('adodb-replicate.inc.php');

# ==================================== CONNECTION
$DB = ADONewConnection('oci8');
$ok = $DB->Connect($HOST,$USER,$PWD,$DBASE);
if (!$ok) return;


#$DB->debug=1;

$bkup = 'tmp'.date('ymd_His');


if ($BA) {
	$QTY_BA = " and qu_bacode='$BA'";
	if (1) $STP_BA = " and s_stagecat in (select stg_stagecat from kbstage where stg_bacode='$BA')"; # OLDER KBSTEP
	else $STP_BA = " and s_bacode='$BA'";  # LATEST KBSTEP format
	$STG_BA = " and stg_bacode='$BA'";
} else {
	$QTY_BA = "";
	$STP_BA = "";
	$STG_BA = "";
}

if ($STAGES) {

	$STAGES = explode(',',$STAGES);
	$STAGES = "'".implode("','",$STAGES)."'";
	$QTY_STG = " and qu_stagecat in ($STAGES)";
	$STP_STG = " and s_stagecat in ($STAGES)";
	$STG_STG = " and stg_stagecat in ($STAGES)";
} else {
	$QTY_STG = "";
	$STP_STG = "";
	$STG_STG = "";
}

echo "<pre>

/******************************************************************************
<font color=green>
 Migrate stages, steps and qtypes for the following

  business area: $BA
     and stages: $STAGES


 WARNING: DO NOT 'Ignore All Errors'.
 If any error occurs, make sure you stop and check the reason and fix it.
 Otherwise you could corrupt everything!!!

 Connected to $USER@$DBASE $HOST;
</font>
*******************************************************************************/

-- BACKUP
create table kbstage_$bkup as select * from kbstage;
create table kbstep_$bkup as select * from kbstep;
create table kbqtype_$bkup as select * from kbqtype;


-- IF CODE FAILS, REMEMBER TO RENABLE ALL TRIGGERS and following CONSTRAINT
ALTER TABLE kbstage DISABLE all triggers;
ALTER TABLE kbstep DISABLE all triggers;
ALTER TABLE kbqtype DISABLE all triggers;
ALTER TABLE jqueue DISABLE CONSTRAINT QUEUE_MUST_HAVE_TYPE;


-- NOW DELETE OLD STEPS/STAGES/QUEUES
delete from kbqtype where qu_mode in ('STAGE','STEP') $QTY_BA $QTY_STG;
delete from kbstep where (1=1) $STP_BA$STP_STG;
delete from kbstage where (1=1)$STG_BA$STG_STG;



SET DEFINE OFF; -- disable  variable handling by sqlplus
/
/* Assume kbstrategy and business areas are compatible for steps and stages to be copied */
</pre>

";


$rep = new ADODB_Replicate($DB,$DB);
$rep->execute = false;
$rep->deleteFirst = false;

				// src table name, dst table name, primary key, where condition
$rep->ReplicateData('KBSTAGE', 'KBSTAGE', array(), " where (1=1)$STG_BA$STG_STG");
$rep->ReplicateData('KBSTEP', 'KBSTEP', array(), " where (1=1)$STP_BA$STP_STG");
$rep->ReplicateData('KBQTYPE','KBQTYPE',array()," where qu_mode in ('STAGE','STEP')$QTY_BA$QTY_STG");

echo "

-- Check for QUEUES not in KBQTYPE and FIX by copying from kbqtype_$bkup
begin
for rec in (select distinct q_type from jqueue where q_type not in (select qu_code from kbqtype)) loop
	insert into kbqtype select * from kbqtype_$bkup where qu_code = rec.q_type;
	update kbqtype set qu_name=substr('MISSING.'||qu_name,1,64) where qu_code=rec.q_type;
end loop;
end;
/

commit;


ALTER TABLE kbstage ENABLE all triggers;
ALTER TABLE kbstep ENABLE all triggers;
ALTER TABLE kbqtype ENABLE all triggers;
ALTER TABLE jqueue ENABLE CONSTRAINT QUEUE_MUST_HAVE_TYPE;

/*
-- REMEMBER TO COMMIT
	commit;
	begin Juris.UpdateQCounts; end;

-- To check for bad queues after conversion, run this
	select * from kbqtype where qu_name like 'MISSING%'
*/
/
";
