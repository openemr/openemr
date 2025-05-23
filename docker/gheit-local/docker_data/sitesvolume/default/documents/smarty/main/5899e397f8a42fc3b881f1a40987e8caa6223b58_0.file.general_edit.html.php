<?php
/* Smarty version 4.5.5, created on 2025-05-22 11:35:37
  from '/var/www/localhost/htdocs/openemr/templates/x12_partners/general_edit.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_682f0c09a7ed39_80815868',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '5899e397f8a42fc3b881f1a40987e8caa6223b58' => 
    array (
      0 => '/var/www/localhost/htdocs/openemr/templates/x12_partners/general_edit.html',
      1 => 1747825421,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_682f0c09a7ed39_80815868 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/var/www/localhost/htdocs/openemr/library/smarty/plugins/function.xlt.php','function'=>'smarty_function_xlt',),1=>array('file'=>'/var/www/localhost/htdocs/openemr/vendor/smarty/smarty/libs/plugins/function.html_options.php','function'=>'smarty_function_html_options',),));
?>
<form name="x12_partner" method="post" action="<?php echo $_smarty_tpl->tpl_vars['FORM_ACTION']->value;?>
" class='form-horizontal' onsubmit="return top.restoreSession()">
    <div class="form-row my-sm-2">
        <label for="name" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Partner Name'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="name" name="name" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_name());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_submitter_id" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Submitter Name (3rd Party Submitter Only)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <select id="x12_submitter_id" name="x12_submitter_id" class="form-control">
                <?php echo smarty_function_html_options(array('options'=>$_smarty_tpl->tpl_vars['partner']->value->get_x12_submitter_array(),'selected'=>$_smarty_tpl->tpl_vars['partner']->value->get_x12_submitter_id()),$_smarty_tpl);?>

            </select>    
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="id_number" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'ID Number (ETIN)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="id_number" name="id_number" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_id_number());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_isa01" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'User logon Required Indicator (ISA01~ use 00 or 03)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="x12_isa01" name="x12_isa01" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_isa01());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_isa02" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'User Logon (If 03 above, else leave spaces) (ISA02)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="x12_isa02" name="x12_isa02" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_isa02());?>
" onKeyDown="PreventIt(event)" maxlength="10">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_isa03" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'User password required Indicator (ISA03~ use 00 or 01)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="x12_isa03" name="x12_isa03" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_isa03());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_isa04" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'User Password (ISA04~ if 01 above, else leave spaces)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="x12_isa04" name="x12_isa04" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_isa04());?>
" onKeyDown="PreventIt(event)" maxlength="10">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_isa05" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Sender ID Qualifier (ISA05)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <select id="x12_isa05" name="x12_isa05" class="form-control">
                <?php echo smarty_function_html_options(array('options'=>$_smarty_tpl->tpl_vars['partner']->value->get_idqual_array(),'selected'=>$_smarty_tpl->tpl_vars['partner']->value->get_x12_isa05()),$_smarty_tpl);?>

            </select>
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_sender_id" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Sender ID (ISA06)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="x12_sender_id" name="x12_sender_id" class="form-control"  value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_sender_id());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_isa07" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Receiver ID Qualifier (ISA07)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <select id="x12_isa07" name="x12_isa07" class="form-control">
                <?php echo smarty_function_html_options(array('options'=>$_smarty_tpl->tpl_vars['partner']->value->get_idqual_array(),'selected'=>$_smarty_tpl->tpl_vars['partner']->value->get_x12_isa07()),$_smarty_tpl);?>

            </select>
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_receiver_id" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Receiver ID (ISA08)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="x12_receiver_id" name="x12_receiver_id" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_receiver_id());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_isa14" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Acknowledgment Requested (ISA14)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <select id="x12_isa14" name="x12_isa14" class="form-control">
                <?php echo smarty_function_html_options(array('options'=>$_smarty_tpl->tpl_vars['partner']->value->get_x12_isa14_array(),'selected'=>$_smarty_tpl->tpl_vars['partner']->value->get_x12_isa14()),$_smarty_tpl);?>

            </select>
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_isa15" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Usage Indicator (ISA15)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <select id="x12_isa15" name="x12_isa15" class="form-control">
                <?php echo smarty_function_html_options(array('options'=>$_smarty_tpl->tpl_vars['partner']->value->get_x12_isa15_array(),'selected'=>$_smarty_tpl->tpl_vars['partner']->value->get_x12_isa15()),$_smarty_tpl);?>

            </select>
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_gs02" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Application Sender Code (GS02)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="x12_gs02" name="x12_gs02" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_gs02());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_dtp03" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Eligibility Service Date (270 DTP03)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <select id="x12_dtp03" name="x12_dtp03" class="form-control">
                <?php echo smarty_function_html_options(array('options'=>$_smarty_tpl->tpl_vars['partner']->value->get_x12_dtp03_type_array(),'selected'=>$_smarty_tpl->tpl_vars['partner']->value->get_x12_dtp03()),$_smarty_tpl);?>

            </select>
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="12_per06" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Submitter EDI Access Number (PER06)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="12_per06" name="x12_per06" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_per06());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_version" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Version'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <select id="x12_version" name="x12_version" class="form-control">
                <?php echo smarty_function_html_options(array('options'=>$_smarty_tpl->tpl_vars['partner']->value->get_x12_version_array(),'selected'=>$_smarty_tpl->tpl_vars['partner']->value->get_x12_version()),$_smarty_tpl);?>

            </select>
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="processing_format" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Processing format'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <select id="processing_format" name="processing_format" class="form-control">
                <?php echo smarty_function_html_options(array('options'=>$_smarty_tpl->tpl_vars['partner']->value->get_processing_format_array(),'selected'=>$_smarty_tpl->tpl_vars['partner']->value->get_processing_format()),$_smarty_tpl);?>

            </select>
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_gs03" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Application Receiver Code (GS03 - If blank ISA08 will be used)'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="15" id="x12_gs03" name="x12_gs03" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_gs03());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_sftp_login" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'SFTP or Eligibility Login Credentials'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="45" id="x12_sftp_login" name="x12_sftp_login" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_sftp_login());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_sftp_pass" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'SFTP or Eligibility Pass Credentials'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="password" maxlength="45" id="x12_sftp_pass" name="x12_sftp_pass" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_sftp_pass());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_sftp_host" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'SFTP Host'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="45" id="x12_sftp_host" name="x12_sftp_host" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_sftp_host());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_sftp_port" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'SFTP Port'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="5" id="x12_sftp_port" name="x12_sftp_port" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_sftp_port());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_sftp_local_dir" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'SFTP Local Directory'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="400" id="x12_sftp_local_dir" name="x12_sftp_local_dir" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_sftp_local_dir());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_sftp_remote_dir" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'SFTP Remote Directory'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="400" id="x12_sftp_remote_dir" name="x12_sftp_remote_dir" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_sftp_remote_dir());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>

    <div class="form-row my-sm-2">
        <label for="x12_client_id" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Client ID'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="400" id="x12_client_id" name="x12_client_id" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_client_id());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>

    <div class="form-row my-sm-2">
        <label for="x12_client_secret" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Client Secret'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="400" id="x12_client_secret" name="x12_client_secret" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_client_secret());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>

    <div class="form-row my-sm-2">
        <label for="x12_token_endpoint" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Token Endpoint'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="400" id="x12_token_endpoint" name="x12_token_endpoint" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_token_endpoint());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>

    <div class="form-row my-sm-2">
        <label for="x12_eligibility_endpoint" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Eligibility Endpoint'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="400" id="x12_eligibility_endpoint" name="x12_eligibility_endpoint" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_eligibility_endpoint());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>

    <div class="form-row my-sm-2">
        <label for="x12_claim_status_endpoint" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Claim Status Endpoint'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="400" id="x12_claim_status_endpoint" name="x12_claim_status_endpoint" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_claim_status_endpoint());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>

    <div class="form-row my-sm-2">
        <label for="x12_attachment_endpoint" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Attachment Endpoint'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="400" id="x12_attachment_endpoint" name="x12_attachment_endpoint" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->get_x12_attachment_endpoint());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>

    <div class="btn-group offset-sm-2">
        <a href="javascript:add_x12();" class="btn btn-secondary btn-save" onclick="top.restoreSession()">
            <?php echo smarty_function_xlt(array('t'=>'Save'),$_smarty_tpl);?>

        </a>
        <a href="controller.php?practice_settings&x12_partner&action=list"  class="btn btn-link btn-cancel" onclick="top.restoreSession()">
            <?php echo smarty_function_xlt(array('t'=>'Cancel'),$_smarty_tpl);?>

        </a>
    </div>
    <input type="hidden" name="id" value="<?php echo attr($_smarty_tpl->tpl_vars['partner']->value->id);?>
" />
    <input type="hidden" name="process" value="<?php echo attr($_smarty_tpl->tpl_vars['PROCESS']->value);?>
" />
    <input type="hidden" name="sub" value="no" />
</form>


<?php echo '<script'; ?>
>
function add_x12()
{
if (document.x12_partner.name.value.length>0)
{
top.restoreSession();
document.x12_partner.submit();
}
else
{
document.x12_partner.name.style.backgroundColor="red";
document.x12_partner.name.focus();
}
}

 function Waittoredirect(delaymsec) {
 var st = new Date();
 var et = null;
 do {
 et = new Date();
 } while ((et - st) < delaymsec);

   }
<?php echo '</script'; ?>
>

<?php }
}
