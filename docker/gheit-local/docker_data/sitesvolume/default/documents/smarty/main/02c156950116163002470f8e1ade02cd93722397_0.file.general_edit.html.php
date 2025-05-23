<?php
/* Smarty version 4.5.5, created on 2025-05-22 11:34:53
  from '/var/www/localhost/htdocs/openemr/templates/insurance_companies/general_edit.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_682f0bdd4e78d8_16351841',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '02c156950116163002470f8e1ade02cd93722397' => 
    array (
      0 => '/var/www/localhost/htdocs/openemr/templates/insurance_companies/general_edit.html',
      1 => 1747825421,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_682f0bdd4e78d8_16351841 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/var/www/localhost/htdocs/openemr/library/smarty/plugins/function.xlt.php','function'=>'smarty_function_xlt',),1=>array('file'=>'/var/www/localhost/htdocs/openemr/vendor/smarty/smarty/libs/plugins/function.html_options.php','function'=>'smarty_function_html_options',),));
?>

<form name="insurancecompany" method="post" action="<?php echo $_smarty_tpl->tpl_vars['FORM_ACTION']->value;?>
" class='form-horizontal' onsubmit="return top.restoreSession()">
    <!-- it is important that the hidden form_id field be listed first, when it is called it populates any old information attached with the id, this allows for partial edits
    if it were called last, the settings from the form would be overwritten with the old information-->
    <input type="hidden" name="form_id" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->id);?>
">
    <?php if ($_smarty_tpl->tpl_vars['insurancecompany']->value->get_inactive() == 1) {?>
    <div class="form-row my-sm-2">
        <label for="inactive" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Reactivate'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="checkbox" id="inactive" name="inactive" class="checkbox" value="0" />
        </div>
    </div>
    <?php }?>
    <?php if ($_smarty_tpl->tpl_vars['insurancecompany']->value->get_inactive() == 0) {?>
    <div class="form-row my-sm-2">
        <label for="inactive" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Deactivate'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="checkbox" id="inactive" name="inactive" class="checkbox" value="1" />
        </div>
    </div>
    <?php }?>
    <div class="form-row my-sm-2">
        <label for="name" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Name'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="name" name="name" class="form-control" aria-describedby="nameHelpBox" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->get_name());?>
" onKeyDown="PreventIt(event)">
            <span id="nameHelpBox" class="help-block">(<?php echo smarty_function_xlt(array('t'=>'Required'),$_smarty_tpl);?>
)</span>
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="attn" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Attn'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="attn" name="attn" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->get_attn());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="address_line1" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Address'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="address_line1" name="address_line1" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->address->line1);?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="address_line2" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Address'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="address_line2" name="address_line2" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->address->line2);?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="city" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'City'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="city" name="city" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->address->city);?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="state" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'State'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="2" id="state" name="state" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->address->state);?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="zip" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Zip Code'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="zip" name="zip" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->address->zip);?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="phone" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Phone'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="phone" name="phone" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->get_phone());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="fax" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Fax'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="fax" name="fax" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->get_fax());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="cms_id" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Payer ID'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="cms_id" name="cms_id" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->get_cms_id());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <?php if ($_smarty_tpl->tpl_vars['SUPPORT_ENCOUNTER_CLAIMS']->value) {?>
        <div class="form-row my-sm-2">
            <label for="alt_cms_id" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Payer ID For Encounter Claims'),$_smarty_tpl);?>
</label>
            <div class="col-sm-8">
                <input type="text" id="alt_cms_id" name="alt_cms_id" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->get_alt_cms_id());?>
" onKeyDown="PreventIt(event)">
            </div>
        </div>
    <?php }?>
    <?php if ($_smarty_tpl->tpl_vars['SUPPORT_ELIGIBILITY_REQUESTS']->value) {?>
    <div class="form-row my-sm-2">
        <label for="eligibility_id" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Payer Id For Eligibility'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="eligibility_id" name="eligibility_id" class="form-control" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->get_eligibility_id());?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <?php }?>
    <div class="form-row my-sm-2">
        <label for="ins_type_code" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Payer Type'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <select id="ins_type_code" name="ins_type_code" class="form-control">
                <?php echo smarty_function_html_options(array('options'=>$_smarty_tpl->tpl_vars['insurancecompany']->value->ins_type_code_array,'selected'=>$_smarty_tpl->tpl_vars['insurancecompany']->value->get_ins_type_code()),$_smarty_tpl);?>

            </select>
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="x12_default_partner_id" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Default X12 Partner'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <select id="x12_default_partner_id" name="x12_default_partner_id" class="form-control">
                <?php echo smarty_function_html_options(array('options'=>$_smarty_tpl->tpl_vars['x12_partners']->value,'selected'=>$_smarty_tpl->tpl_vars['insurancecompany']->value->get_x12_default_partner_id()),$_smarty_tpl);?>

            </select>
        </div>
    </div>
    <div class="form-row my-sm-2">
        <label for="cqm_sop" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'CQM Source of Payment'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <select id="cqm_sop" name="cqm_sop" class="form-control">
                <?php echo smarty_function_html_options(array('options'=>$_smarty_tpl->tpl_vars['insurancecompany']->value->cqm_sop_array,'selected'=>$_smarty_tpl->tpl_vars['insurancecompany']->value->get_cqm_sop()),$_smarty_tpl);?>

            </select>
        </div>
    </div>
    <?php if ($_smarty_tpl->tpl_vars['SUPPORT_ELIGIBILITY_REQUESTS']->value) {?>
    <div class="form-row my-sm-2">
        <label for="x12_default_eligibility_id" class="col-form-label col-sm-2"><?php echo smarty_function_xlt(array('t'=>'Default Eligibility X12 Partner'),$_smarty_tpl);?>
</label>
        <div class="col-sm-8">
            <select id="x12_default_eligibility_id" name="x12_default_eligibility_id" class="form-control">
                <?php echo smarty_function_html_options(array('options'=>$_smarty_tpl->tpl_vars['x12_partners']->value,'selected'=>$_smarty_tpl->tpl_vars['insurancecompany']->value->get_x12_default_eligibility_id()),$_smarty_tpl);?>

            </select>
        </div>
    </div>
    <?php }?>
    <div class="btn-group offset-sm-2">
        <a href="javascript:submit_insurancecompany();" class="btn btn-secondary btn-save" onclick="top.restoreSession()">
            <?php echo smarty_function_xlt(array('t'=>'Save'),$_smarty_tpl);?>

        </a>
        <a href="controller.php?practice_settings&insurance_company&action=list" class="btn btn-link btn-cancel" onclick="top.restoreSession()">
            <?php echo smarty_function_xlt(array('t'=>'Cancel'),$_smarty_tpl);?>

        </a>
    </div>
    <input type="hidden" name="id" value="<?php echo attr($_smarty_tpl->tpl_vars['insurancecompany']->value->id);?>
" />
    <input type="hidden" name="process" value="<?php echo attr($_smarty_tpl->tpl_vars['PROCESS']->value);?>
" />
</form>

<?php echo '<script'; ?>
>
    function submit_insurancecompany() {
        if(document.insurancecompany.name.value.length>0) {
            top.restoreSession();
            document.insurancecompany.submit();
            //Z&H Removed redirection
        } else{
            document.insurancecompany.name.style.backgroundColor="red";
            document.insurancecompany.name.focus();
        }
    }

    function jsWaitForDelay(delay) {
        var startTime = new Date();
        var endTime = null;
        do {
            endTime = new Date();
        } while ((endTime - startTime) < delay);
    }
<?php echo '</script'; ?>
>
<?php }
}
