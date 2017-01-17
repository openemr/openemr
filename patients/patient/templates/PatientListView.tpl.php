<?php
/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */

	//require_once ("./../verify_session.php");
	$this->assign('title','Patient Portal | PatientData');
	$this->assign('nav','patientdata');

	echo "<script>var recid='" . $this->recid . "';var webRoot='" . $GLOBALS['web_root'] . "';var cpid='" . $this->cpid . "';var cuser='" .$this->cuser . "';</script>";
	$_SESSION['whereto'] = 'profilepanel';

	$this->display('_modalFormHeader.tpl.php');
?>

<script type="text/javascript">
	$LAB.script("scripts/app/patientdata.js").wait(function(){
		$(document).ready(function(){
			page.init();
		});
		// hack for browsers or slow sessions which may respond inconsistently with document.ready-second chance at init
		setTimeout(function(){
			if (!page.isInitialized) page.init();
		},1000);
	});
</script>

<div class="container-fluid">

<script type="text/template" id="patientCollectionTemplate"></script>
	<!-- Could add/manage table list here -->
	<script type="text/template" id="patientModelTemplate"> <!-- -->
		<div id='profileHelp' class='well' style='display:none;width: 650px; margin: 0 auto;'>
			<p><?php echo xlt('Any changes here will be reviewed by provider staff before commiting to chart. The following apply'); ?>:<br>
<?php echo xlt('Change any item available and when ready click Send for review. The changes will be flagged and staff notified to review changes before commiting them to chart. During the time period before changes are reviewed the Revised button will show Pending and profile data is still available for changes. When accessing profile in pending state all previous edits will appear in Blue and current chart values in Red. You may revert any edit to chart value by clicking that red item (or vica versa) but remember that when you click Send for Review then items that populate the field items are the ones that are sent. Revert Edits button changes everything back to chart values and you may make changes from there. So to recap: Items in BLUE are patient edits with items in RED being original values before any edits.'); ?>
</p>
			<button class="btn btn-primary btn-xs" type="button"  id='dismissHelp'><?php echo xlt('Dismiss'); ?></button>
		</div>
		<form class="form-inline" onsubmit="return false;">
			<fieldset>
				<!-- <div class="form-group inline" id="idInputContainer">
					<label class="control-label" for="id">Id</label>
					<div class="controls inline-inputs">
						<span class="form-control uneditable-input" id="id"><%= _.escape(item.get('id') || '') %></span>
						<span class="help-inline"></span>
					</div>
				</div> -->
			<div class="form-group inline" id="titleInputContainer">
				<label class="control-label" for="title"><?php echo xlt('Title'); ?></label>
				<div class="controls inline-inputs">
					<select class="form-control" id="title" value="<%= _.escape(item.get('title') || '') %>">
						<option value=''><?php echo xlt('Unassigned'); ?></option>
						<option value="Mr."><?php echo xlt('Mr.'); ?></option>
						<option value="Mrs."><?php echo xlt('Mrs.'); ?></option>
						<option value="Ms."><?php echo xlt('Ms.'); ?></option>
						<option value="Dr."><?php echo xlt('Dr.'); ?></option>
					</select>
					<span class="help-inline"></span>
				</div>
			</div>

			<!-- <div class="form-group inline" id="financialInputContainer">
					<label class="control-label" for="financial">Financial</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="financial" placeholder="Financial" value="<%= _.escape(item.get('financial') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div> -->
				<div class="form-group inline" id="fnameInputContainer">
					<label class="control-label" for="fname"><?php echo xlt('First')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="fname" placeholder="<?php echo xla('Fname'); ?>" value="<%= _.escape(item.get('fname') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="mnameInputContainer">
					<label class="control-label" for="mname"><?php echo xlt('Middle')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="mname" placeholder="<?php echo xla('Mname'); ?>" value="<%= _.escape(item.get('mname') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="lnameInputContainer">
					<label class="control-label" for="lname"><?php echo xlt('Last')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="lname" placeholder="<?php echo xla('Lname'); ?>" value="<%= _.escape(item.get('lname') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>

				<div class="form-group inline" id="dobInputContainer">
					<label class="control-label" for="dob"><?php echo xlt('Birth Date')?></label>
					<div class="controls inline-inputs">
						<div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
							<input id="dob" type="text" class="form-control" value="<%= item.get('dob') %>" />
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						</div>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="sexInputContainer">
					<label class="control-label" for="sex"><?php echo xlt('Sex')?></label>
					<div class="controls inline-inputs">
						<select class="form-control" id="sex"  value="<%= _.escape(item.get('sex') || '') %>">
							<option value=''><?php echo xlt('Unassigned'); ?></option>
							<option value='Female'><?php echo xlt('Female'); ?></option>
							<option value='Male'><?php echo xlt('Male'); ?></option>
						</select>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="streetInputContainer">
					<label class="control-label" for="street"><?php echo xlt('Street')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="street" placeholder="<?php echo xla('Street'); ?>" value="<%= _.escape(item.get('street') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
			<div class="form-group inline" id="cityInputContainer">
					<label class="control-label" for="city"><?php echo xlt('City')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="city" placeholder="<?php echo xla('City'); ?>" value="<%= _.escape(item.get('city') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
			<div class="form-group inline" id="stateInputContainer">
				<label class="control-label" for="state"><?php echo xlt('State')?></label>
				<div class="controls inline-inputs">
					<select class="form-control" id="state" value="<%= _.escape(item.get('state') || '') %>">
						<option value='AK'>Alaska</option>
						<option value='AZ'>Arizona</option>
						<option value='AR'>Arkansas</option>
						<option value='CA'>California</option>
						<option value='CO'>Colorado</option>
						<option value='CT'>Connecticut</option>
						<option value='DE'>Delaware</option>
						<option value='DC'>District of Columbia</option>
						<option value='FL'>Florida</option>
						<option value='GA'>Georgia</option>
						<option value='HI'>Hawaii</option>
						<option value='ID'>Idaho</option>
						<option value='IL'>Illinois</option>
						<option value='IN'>Indiana</option>
						<option value='IA'>Iowa</option>
						<option value='KS'>Kansas</option>
						<option value='KY'>Kentucky</option>
						<option value='LA'>Louisiana</option>
						<option value='ME'>Maine</option>
						<option value='MD'>Maryland</option>
						<option value='MA'>Massachusetts</option>
						<option value='MI'>Michigan</option>
						<option value='MN'>Minnesota</option>
						<option value='MS'>Mississippi</option>
						<option value='MO'>Missouri</option>
						<option value='MT'>Montana</option>
						<option value='NE'>Nebraska</option>
						<option value='NV'>Nevada</option>
						<option value='NH'>New Hampshire</option>
						<option value='NJ'>New Jersey</option>
						<option value='NM'>New Mexico</option>
						<option value='NY'>New York</option>
						<option value='NC'>North Carolina</option>
						<option value='ND'>North Dakota</option>
						<option value='OH'>Ohio</option>
						<option value='OK'>Oklahoma</option>
						<option value='OR'>Oregon</option>
						<option value='PA'>Pennsylvania</option>
						<option value='RI'>Rhode Island</option>
						<option value='SC'>South Carolina</option>
						<option value='SD'>South Dakota</option>
						<option value='TN'>Tennessee</option>
						<option value='TX'>Texas</option>
						<option value='UT'>Utah</option>
						<option value='VT'>Vermont</option>
						<option value='VA'>Virginia</option>
						<option value='WA'>Washington</option>
						<option value='WV'>West Virginia</option>
						<option value='WI'>Wisconsin</option>
						<option value='WY'>Wyoming</option>
					</select>
					<span class="help-inline"></span>
				</div>
			</div>
			<div class="form-group inline" id="countryCodeInputContainer">
					<label class="control-label" for="countryCode"><?php echo xlt('Country Code')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="countryCode" placeholder="<?php echo xla('Country Code'); ?>" value="<%= _.escape(item.get('countryCode') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="postalCodeInputContainer">
					<label class="control-label" for="postalCode"><?php echo xlt('Postal Code')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="postalCode" placeholder="<?php echo xla('Postal Code'); ?>" value="<%= _.escape(item.get('postalCode') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="countyInputContainer">
					<label class="control-label" for="county"><?php echo xlt('County')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="county" placeholder="<?php echo xla('County'); ?>" value="<%= _.escape(item.get('county') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>

				<div class="form-group inline" id="phoneHomeInputContainer">
					<label class="control-label" for="phoneHome"><?php echo xlt('Home Phone')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="phoneHome" placeholder="<?php echo xla('Phone Home'); ?>" value="<%= _.escape(item.get('phoneHome') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="phoneBizInputContainer">
					<label class="control-label" for="phoneBiz"><?php echo xlt('Business Phone')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="phoneBiz" placeholder="<?php echo xla('Phone Biz'); ?>" value="<%= _.escape(item.get('phoneBiz') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="phoneContactInputContainer">
					<label class="control-label" for="phoneContact"><?php echo xlt('Phone Contact')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="phoneContact" placeholder="<?php echo xla('Phone Contact'); ?>" value="<%= _.escape(item.get('phoneContact') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="phoneCellInputContainer">
					<label class="control-label" for="phoneCell"><?php echo xlt('Phone Cell')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="phoneCell" placeholder="<?php echo xla('Phone Cell'); ?>" value="<%= _.escape(item.get('phoneCell') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<!--<div class="form-group inline" id="pharmacyIdInputContainer">
					<label class="control-label" for="pharmacyId">Pharmacy Id')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="pharmacyId" placeholder="Pharmacy Id" value="<%= _.escape(item.get('pharmacyId') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>-->
			<div class="form-group inline" id="statusInputContainer">
				<label class="control-label" for="status"><?php echo xlt('Marital Status')?></label>
				<div class="controls inline-inputs">
					<select class="form-control" id="status"
						placeholder="<?php echo xla('Marital Status'); ?>" value="<%= _.escape(item.get('status') || '') %>">
						<option value=''><?php echo xlt('Unassigned'); ?></option>
						<option value='married'><?php echo xlt('Married'); ?></option>
						<option value='single'><?php echo xlt('Single'); ?></option>
						<option value='divorced'><?php echo xlt('Divorced'); ?></option>
						<option value='widowed'><?php echo xlt('Widowed'); ?></option>
						<option value='separated'><?php echo xlt('Separated'); ?></option>
						<option value='domestic partner'><?php echo xlt('Domestic Partner'); ?></option>
					</select> <span class="help-inline"></span>
				</div>
			</div>
			<div class="form-group inline" id="contactRelationshipInputContainer">
					<label class="control-label" for="contactRelationship"><?php echo xlt('Contact Relationship')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="contactRelationship" placeholder="<?php echo xla('Contact Relationship'); ?>" value="<%= _.escape(item.get('contactRelationship') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
			<!--	<div class="form-group inline" id="dateInputContainer">
					<label class="control-label" for="date">Date</label>
					<div class="controls inline-inputs">
						<div class="input-group date date-time-picker" data-date-format="yyyy-mm-dd hh:mm A">
							<input disabled id="date" type="text" class="form-control" value="<%= item.get('date') %>" />
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						</div>
						<span class="help-inline"></span>
					</div>
				</div>-->

				<div class="form-group inline" id="provideridInputContainer">
					<label class="control-label" for="providerid"><?php echo xlt('Provider')?></label>
					<div class="controls inline-inputs">
						<select class="form-control" id="providerid"  value="<%= _.escape(item.get('providerid') || '') %>"></select>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="refProvideridInputContainer">
					<label class="control-label" for="refProviderid"><?php echo xlt('Referral Provider')?></label>
					<div class="controls inline-inputs">
						<select  disabled class="form-control" id="refProviderid"  value="<%= _.escape(item.get('refProviderid') || '') %>"></select>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="emailInputContainer">
					<label class="control-label" for="email"><?php echo xlt('Email')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="email" placeholder="<?php echo xla('Email'); ?>" value="<%= _.escape(item.get('email') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="emailDirectInputContainer">
					<label class="control-label" for="emailDirect"><?php echo xlt('Email Direct')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="emailDirect" placeholder="<?php echo xla('Direct Email'); ?>" value="<%= _.escape(item.get('emailDirect') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="languageInputContainer">
					<label class="control-label" for="language"><?php echo xlt('Language')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="language" placeholder="<?php echo xla('Language'); ?>" value="<%= _.escape(item.get('language') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
			<div class="form-group inline" id="raceInputContainer">
				<label class="control-label" for="race"><?php echo xlt('Race')?></label>
				<div class="controls inline-inputs">
					<select class="form-control" id="race" placeholder="<?php echo xla('Race'); ?>" value="<%= _.escape(item.get('race') || '') %>">
						<option value=''><?php echo xlt('Unassigned'); ?></option>
						<option value='declne_to_specfy'><?php echo xlt('Declined To Specify'); ?></option>
						<option value='amer_ind_or_alaska_native'><?php echo xlt('American Indian or Alaska Native'); ?></option>
						<option value='Asian'><?php echo xlt('Asian'); ?></option>
						<option value='black_or_afri_amer'><?php echo xlt('Black or African American'); ?></option>
						<option value='native_hawai_or_pac_island'><?php echo xlt('Native Hawaiian or Other Pacific Islander'); ?></option>
						<option value='white'><?php echo xlt('White'); ?></option>
					</select>
					<span class="help-inline"></span>
				</div>
			</div>
			<div class="form-group inline" id="ethnicityInputContainer">
					<label class="control-label" for="ethnicity"><?php echo xlt('Ethnicity')?></label>
					<div class="controls inline-inputs">
						<select class="form-control" id="ethnicity" placeholder="<?php echo xla('Ethnicity'); ?>" value="<%= _.escape(item.get('ethnicity') || '') %>">
							<option value=''><?php echo xlt('Unassigned'); ?></option>
							<option value='declne_to_specfy'><?php echo xlt('Declined To Specify'); ?></option>
							<option value='hisp_or_latin'><?php echo xlt('Hispanic or Latino'); ?></option>
							<option value='not_hisp_or_latin'><?php echo xlt('Not Hispanic or Latino'); ?></option>
						</select>
						<span class="help-inline"></span>
					</div>
				</div>
			<div class="form-group inline" id="religionInputContainer">
				<label class="control-label" for="religion"><?php echo xlt('Religion')?></label>
				<div class="controls inline-inputs">
					<select class="form-control" id="religion" placeholder="<?php echo xla('Religion'); ?>"	value="<%= _.escape(item.get('religion') || '') %>">
						<option value=''><?php echo xlt('Unassigned'); ?></option>
						<option value='adventist'>Adventist</option>
						<option value='african_religions'>African Religions</option>
						<option value='afro-caribbean_religions'>Afro-Caribbean Religions</option>
						<option value='agnosticism'>Agnosticism</option>
						<option value='anglican'>Anglican</option>
						<option value='animism'>Animism</option>
						<option value='assembly_of_god'>Assembly of God</option>
						<option value='atheism'>Atheism</option>
						<option value='babi_bahai_faiths'>Babi &amp; Baha`I faiths</option>
						<option value='baptist'>Baptist</option>
						<option value='bon'>Bon</option>
						<option value='brethren'>Brethren</option>
						<option value='cao_dai'>Cao Dai</option>
						<option value='celticism'>Celticism</option>
						<option value='christiannoncatholicnonspecifc'>Christian
							(non-Catholic, non-specific)</option>
						<option value='christian_scientist'>Christian Scientist</option>
						<option value='church_of_christ'>Church of Christ</option>
						<option value='church_of_god'>Church of God</option>
						<option value='confucianism'>Confucianism</option>
						<option value='congregational'>Congregational</option>
						<option value='cyberculture_religions'>Cyberculture Religions</option>
						<option value='disciples_of_christ'>Disciples of Christ</option>
						<option value='divination'>Divination</option>
						<option value='eastern_orthodox'>Eastern Orthodox</option>
						<option value='episcopalian'>Episcopalian</option>
						<option value='evangelical_covenant'>Evangelical Covenant</option>
						<option value='fourth_way'>Fourth Way</option>
						<option value='free_daism'>Free Daism</option>
						<option value='friends'>Friends</option>
						<option value='full_gospel'>Full Gospel</option>
						<option value='gnosis'>Gnosis</option>
						<option value='hinduism'>Hinduism</option>
						<option value='humanism'>Humanism</option>
						<option value='independent'>Independent</option>
						<option value='islam'>Islam</option>
						<option value='jainism'>Jainism</option>
						<option value='jehovahs_witnesses'>Jehovah`s Witnesses</option>
						<option value='judaism'>Judaism</option>
						<option value='latter_day_saints'>Latter Day Saints</option>
						<option value='lutheran'>Lutheran</option>
						<option value='mahayana'>Mahayana</option>
						<option value='meditation'>Meditation</option>
						<option value='messianic_judaism'>Messianic Judaism</option>
						<option value='methodist'>Methodist</option>
						<option value='mitraism'>Mitraism</option>
						<option value='native_american'>Native American</option>
						<option value='nazarene'>Nazarene</option>
						<option value='new_age'>New Age</option>
						<option value='non-roman_catholic'>non-Roman Catholic</option>
						<option value='occult'>Occult</option>
						<option value='orthodox'>Orthodox</option>
						<option value='paganism'>Paganism</option>
						<option value='pentecostal'>Pentecostal</option>
						<option value='presbyterian'>Presbyterian</option>
						<option value='process_the'>Process, The</option>
						<option value='protestant'>Protestant</option>
						<option value='protestant_no_denomination'>Protestant, No
							Denomination</option>
						<option value='reformed'>Reformed</option>
						<option value='reformed_presbyterian'>Reformed/Presbyterian</option>
						<option value='roman_catholic_church'>Roman Catholic Church</option>
						<option value='salvation_army'>Salvation Army</option>
						<option value='satanism'>Satanism</option>
						<option value='scientology'>Scientology</option>
						<option value='shamanism'>Shamanism</option>
						<option value='shiite_islam'>Shiite (Islam)</option>
						<option value='shinto'>Shinto</option>
						<option value='sikism'>Sikism</option>
						<option value='spiritualism'>Spiritualism</option>
						<option value='sunni_islam'>Sunni (Islam)</option>
						<option value='taoism'>Taoism</option>
						<option value='theravada'>Theravada</option>
						<option value='unitarian_universalist'>Unitarian Universalist</option>
						<option value='unitarian-universalism'>Unitarian-Universalism</option>
						<option value='united_church_of_christ'>United Church of Christ</option>
						<option value='universal_life_church'>Universal Life Church</option>
						<option value='vajrayana_tibetan'>Vajrayana (Tibetan)</option>
						<option value='veda'>Veda</option>
						<option value='voodoo'>Voodoo</option>
						<option value='wicca'>Wicca</option>
						<option value='yaohushua'>Yaohushua</option>
						<option value='zen_buddhism'>Zen Buddhism</option>
						<option value='zoroastrianism'>Zoroastrianism</option>
					</select>
					 <span class="help-inline"></span>
				</div>
			</div>
			<div class="form-group inline" id="familySizeInputContainer">
					<label class="control-label" for="familySize"><?php echo xlt('Family Size')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="familySize" placeholder="<?php echo xla('Family Size'); ?>" value="<%= _.escape(item.get('familySize') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>

				<div class="form-group inline" id="hipaaMailInputContainer">
					<label class="control-label" for="hipaaMail"><?php echo xlt('Allow Hipaa Mail')?></label>
					<div class="controls inline-inputs">
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaMail0" name="hipaaMail" type="radio" value="NO"<% if (item.get('hipaaMail')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaMail1" name="hipaaMail" type="radio" value="YES"<% if (item.get('hipaaMail')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaMail2" name="hipaaMail" type="radio" value=""<% if (item.get('hipaaMail')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="hipaaVoiceInputContainer">
					<label class="control-label" for="hipaaVoice"><?php echo xlt('Allow Hipaa Voice')?></label>
					<div class="controls inline-inputs">
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaVoice0" name="hipaaVoice" type="radio" value="NO"<% if (item.get('hipaaVoice')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaVoice1" name="hipaaVoice" type="radio" value="YES"<% if (item.get('hipaaVoice')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaVoice2" name="hipaaVoice" type="radio" value=""<% if (item.get('hipaaVoice')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="hipaaNoticeInputContainer">
					<label class="control-label" for="hipaaNotice"><?php echo xlt('Allow Hipaa Notice')?></label>
					<div class="controls inline-inputs">
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaNotice0" name="hipaaNotice" type="radio" value="NO"<% if (item.get('hipaaNotice')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaNotice1" name="hipaaNotice" type="radio" value="YES"<% if (item.get('hipaaNotice')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaNotice2" name="hipaaNotice" type="radio" value=""<% if (item.get('hipaaNotice')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="hipaaMessageInputContainer">
					<label class="control-label" for="hipaaMessage"><?php echo xlt('Hipaa Message')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="hipaaMessage" placeholder="<?php echo xla('Hipaa Message'); ?>" value="<%= _.escape(item.get('hipaaMessage') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="hipaaAllowsmsInputContainer">
					<label class="control-label" for="hipaaAllowsms"><?php echo xlt('Allow Hipaa SMS')?></label>
					<div class="controls inline-inputs">
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaAllowsms0" name="hipaaAllowsms" type="radio" value="NO"<% if (item.get('hipaaAllowsms')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaAllowsms1" name="hipaaAllowsms" type="radio" value="YES"<% if (item.get('hipaaAllowsms')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaAllowsms2" name="hipaaAllowsms" type="radio" value=""<% if (item.get('hipaaAllowsms')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="hipaaAllowemailInputContainer">
					<label class="control-label" for="hipaaAllowemail"><?php echo xlt('Allow Hipaa Email')?></label>
					<div class="controls inline-inputs">
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaAllowemail0" name="hipaaAllowemail" type="radio" value="NO"<% if (item.get('hipaaAllowemail')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaAllowemail1" name="hipaaAllowemail" type="radio" value="YES"<% if (item.get('hipaaAllowemail')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="hipaaAllowemail2" name="hipaaAllowemail" type="radio" value=""<% if (item.get('hipaaAllowemail')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="referralSourceInputContainer">
					<label class="control-label" for="referralSource"><?php echo xlt('Referral Source')?></label>
					<div class="controls inline-inputs">
						<select class="form-control" id="referralSource"  value="<%= _.escape(item.get('referralSource') || '') %>">
<option value=''><?php echo xlt('Unassigned'); ?></option>
<option value='Patient'><?php echo xlt('Patient'); ?></option>
<option value='Employee'><?php echo xlt('Employee'); ?></option>
<option value='Walk-In'><?php echo xlt('Walk-In'); ?></option>
<option value='Newspaper'><?php echo xlt('Newspaper'); ?></option>
<option value='Radio'><?php echo xlt('Radio'); ?></option>
<option value='T.V.'><?php echo xlt('T.V.'); ?></option>
<option value='Direct Mail'><?php echo xlt('Direct Mail'); ?></option>
<option value='Coupon'><?php echo xlt('Coupon'); ?></option>
<option value='Referral Card'><?php echo xlt('Referral Card'); ?></option>
<option value='Other'><?php echo xlt('Other'); ?></option></select>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="regdateInputContainer">
					<label class="control-label" for="regdate"><?php echo xlt('Registration Date')?></label>
					<div class="controls inline-inputs">
						<div class="input-group date  date-picker" data-date-format="yyyy-mm-dd">
							<input disabled id="regdate" type="text" class="form-control" value="<%= item.get('regdate') %>" />
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						</div>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="mothersnameInputContainer">
					<label class="control-label" for="mothersname"><?php echo xlt('Mothers Name')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="mothersname" placeholder="<?php echo xla('Mothersname'); ?>" value="<%= _.escape(item.get('mothersname') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="guardiansnameInputContainer">
					<label class="control-label" for="guardiansname"><?php echo xlt('Guardians Name')?></label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="guardiansname" placeholder="<?php echo xla('Guardiansname'); ?>" value="<%= _.escape(item.get('guardiansname') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="allowImmRegUseInputContainer">
					<label class="control-label" for="allowImmRegUse"><?php echo xlt('Allow Imm Reg Use')?></label>
					<div class="controls inline-inputs">
							<label class="btn btn-default btn-gradient btn-sm"><input id="allowImmRegUse0" name="allowImmRegUse" type="radio" value="NO"<% if (item.get('allowImmRegUse')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="allowImmRegUse1" name="allowImmRegUse" type="radio" value="YES"<% if (item.get('allowImmRegUse')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="allowImmRegUse2" name="allowImmRegUse" type="radio" value=""<% if (item.get('allowImmRegUse')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="allowImmInfoShareInputContainer">
					<label class="control-label" for="allowImmInfoShare"><?php echo xlt('Allow Imm Info Share')?></label>
					<div class="controls inline-inputs">
							<label class="btn btn-default btn-gradient btn-sm"><input id="allowImmInfoShare0" name="allowImmInfoShare" type="radio" value="NO"<% if (item.get('allowImmInfoShare')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="allowImmInfoShare1" name="allowImmInfoShare" type="radio" value="YES"<% if (item.get('allowImmInfoShare')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="allowImmInfoShare2" name="allowImmInfoShare" type="radio" value=""<% if (item.get('allowImmInfoShare')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="allowHealthInfoExInputContainer">
					<label class="control-label" for="allowHealthInfoEx"><?php echo xlt('Allow Health Info Ex')?></label>
					<div class="controls inline-inputs">
							<label class="btn btn-default btn-gradient btn-sm"><input id="allowHealthInfoEx0" name="allowHealthInfoEx" type="radio" value="NO"<% if (item.get('allowHealthInfoEx')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="allowHealthInfoEx1" name="allowHealthInfoEx" type="radio" value="YES"<% if (item.get('allowHealthInfoEx')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="allowHealthInfoEx2" name="allowHealthInfoEx" type="radio" value=""<% if (item.get('allowHealthInfoEx')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="careTeamInputContainer">
					<label class="control-label" for="careTeam"><?php echo xlt('Care Team')?></label>
					<div class="controls inline-inputs">
						<select class="form-control" id="careTeam" placeholder="<?php echo xla('Care Team'); ?>" value="<%= _.escape(item.get('careTeam') || '') %>"></select>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="noteInputContainer">
					<label class="control-label" style="color:green" for="note"><?php echo xlt('Message to Reviewer')?></label>
					<div class="controls inline-inputs">
						<textarea class="form-control" id="note" rows="1" style='min-width:180px'><%= _.escape("To Admin: ") %></textarea>
						<span class="help-inline"></span>
					</div>
				</div>
			</fieldset>
		</form>
</script>

	<div id="collectionAlert"></div>
	<div id="modelAlert"></div>
	<div id="patientCollectionContainer" class="collectionContainer"></div><!--  -->
	<div id="patientModelContainer" class="modelContainer"></div>

</div> <!-- /container -->
<?php //$this->display('_Footer.tpl.php');?>

				<!-- <div class="form-group inline" id="ethnoracialInputContainer">
					<label class="control-label" for="ethnoracial">Ethnoracial</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="ethnoracial" placeholder="Ethnoracial" value="<%= _.escape(item.get('ethnoracial') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div> -->
				<!-- <div class="form-group inline" id="interpretterInputContainer">
					<label class="control-label" for="interpretter">Interpretter</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="interpretter" placeholder="Interpretter" value="<%= _.escape(item.get('interpretter') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="migrantseasonalInputContainer">
					<label class="control-label" for="migrantseasonal">Migrantseasonal</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="migrantseasonal" placeholder="Migrantseasonal" value="<%= _.escape(item.get('migrantseasonal') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div> -->
				<!-- <div class="form-group inline" id="industryInputContainer">
					<label class="control-label" for="industry">Industry</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="industry" placeholder="Industry" value="<%= _.escape(item.get('industry') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="occupationInputContainer">
					<label class="control-label" for="occupation">Occupation</label>
					<div class="controls inline-inputs">
						<textarea class="form-control" id="occupation" rows="1" style='min-width:90px'><%= _.escape(item.get('occupation') || '') %></textarea>
						<span class="help-inline"></span>
					</div>
				</div> -->
			<!--<div class="form-group inline" id="referrerInputContainer">
					<label class="control-label" for="referrer">Referrer</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="referrer" placeholder="Referrer" value="<%= _.escape(item.get('referrer') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="referreridInputContainer">
					<label class="control-label" for="referrerid">Referrerid</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="referrerid" placeholder="Referrerid" value="<%= _.escape(item.get('referrerid') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>-->
								<!-- <div class="form-group inline" id="monthlyIncomeInputContainer">
					<label class="control-label" for="monthlyIncome">Monthly Income</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="monthlyIncome" placeholder="Monthly Income" value="<%= _.escape(item.get('monthlyIncome') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="billingNoteInputContainer">
					<label class="control-label" for="billingNote">Billing Note</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="billingNote" placeholder="Billing Note" value="<%= _.escape(item.get('billingNote') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="homelessInputContainer">
					<label class="control-label" for="homeless">Homeless</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="homeless" placeholder="Homeless" value="<%= _.escape(item.get('homeless') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="financialReviewInputContainer">
					<label class="control-label" for="financialReview">Financial Review</label>
					<div class="controls inline-inputs">
						<div class="input-group date date-time-picker" data-date-format="yyyy-mm-dd hh:mm A">
							<input id="financialReview" type="text" class="form-control" value="<%= _date(app.parseDate(item.get('financialReview'))).format('YYYY-MM-DD hh:mm A') %>" />
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						</div>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="pubpidInputContainer">
					<label class="control-label" for="pubpid">Pubpid</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="pubpid" placeholder="Pubpid" value="<%= _.escape(item.get('pubpid') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="pidInputContainer">
					<label class="control-label" for="pid">Pid</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="pid" placeholder="Pid" value="<%= _.escape(item.get('pid') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="genericname1InputContainer">
					<label class="control-label" for="genericname1">Genericname1</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="genericname1" placeholder="Genericname1" value="<%= _.escape(item.get('genericname1') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="genericval1InputContainer">
					<label class="control-label" for="genericval1">Genericval1</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="genericval1" placeholder="Genericval1" value="<%= _.escape(item.get('genericval1') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="genericname2InputContainer">
					<label class="control-label" for="genericname2">Genericname2</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="genericname2" placeholder="Genericname2" value="<%= _.escape(item.get('genericname2') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="genericval2InputContainer">
					<label class="control-label" for="genericval2">Genericval2</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="genericval2" placeholder="Genericval2" value="<%= _.escape(item.get('genericval2') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div> -->
				<!-- <div class="form-group inline" id="squadInputContainer">
					<label class="control-label" for="squad">Squad</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="squad" placeholder="Squad" value="<%= _.escape(item.get('squad') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="fitnessInputContainer">
					<label class="control-label" for="fitness">Fitness</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="fitness" placeholder="Fitness" value="<%= _.escape(item.get('fitness') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div> -->
								<!-- <div class="form-group inline" id="allowPatientPortalInputContainer">
					<label class="control-label" for="allowPatientPortal">Allow Patient Portal</label>
					<div class="controls inline-inputs">
							<label class="btn btn-default btn-gradient btn-sm"><input id="allowPatientPortal0" name="allowPatientPortal" type="radio" value="NO"<% if (item.get('allowPatientPortal')=="NO") { %> checked="checked"<% } %>>NO</label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="allowPatientPortal1" name="allowPatientPortal" type="radio" value="YES"<% if (item.get('allowPatientPortal')=="YES") { %> checked="checked"<% } %>>YES</label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="allowPatientPortal2" name="allowPatientPortal" type="radio" value=""<% if (item.get('allowPatientPortal')=="") { %> checked="checked"<% } %>>Unassigned</label>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="deceasedDateInputContainer">
					<label class="control-label" for="deceasedDate">Deceased Date</label>
					<div class="controls inline-inputs">
						<div class="input-group date date-picker" data-date-format="yyyy-mm-dd hh:mm A">
							<input id="deceasedDate" type="text" class="form-control" value="<%= _date(app.parseDate(item.get('deceasedDate'))).format('YYYY-MM-DD hh:mm A') %>" />
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						</div>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="deceasedReasonInputContainer">
					<label class="control-label" for="deceasedReason">Deceased Reason</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="deceasedReason" placeholder="Deceased Reason" value="<%= _.escape(item.get('deceasedReason') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="soapImportStatusInputContainer">
					<label class="control-label" for="soapImportStatus">Soap Import Status</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="soapImportStatus" placeholder="Soap Import Status" value="<%= _.escape(item.get('soapImportStatus') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="cmsportalLoginInputContainer">
					<label class="control-label" for="cmsportalLogin">Cmsportal Login</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="cmsportalLogin" placeholder="Cmsportal Login" value="<%= _.escape(item.get('cmsportalLogin') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div> -->
						<!--		<div class="form-group inline" id="contrastartInputContainer">
					<label class="control-label" for="contrastart">Contrastart</label>
					<div class="controls inline-inputs">
						<div class="input-group date date-picker" data-date-format="yyyy-mm-dd hh:mm A">
							<input id="contrastart" type="text" class="form-control" value="<%= _date(app.parseDate(item.get('contrastart'))).format('YYYY-MM-DD hh:mm A') %>" />
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						</div>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="completedAdInputContainer">
					<label class="control-label" for="completedAd">Completed Ad</label>
					<div class="controls inline-inputs">
							<label class="btn btn-default btn-gradient btn-sm"><input id="completedAd0" name="completedAd" type="radio" value="NO"<% if (item.get('completedAd')=="NO") { %> checked="checked"<% } %>>NO</label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="completedAd1" name="completedAd" type="radio" value="YES"<% if (item.get('completedAd')=="YES") { %> checked="checked"<% } %>>YES</label>
							<label class="btn btn-default btn-gradient btn-sm"><input id="completedAd2" name="completedAd" type="radio" value=""<% if (item.get('completedAd')=="") { %> checked="checked"<% } %>>Unassigned</label>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="adReviewedInputContainer">
					<label class="control-label" for="adReviewed">Ad Reviewed</label>
					<div class="controls inline-inputs">
						<div class="input-group date date-picker" data-date-format="yyyy-mm-dd hh:mm A">
							<input id="adReviewed" type="text" class="form-control" value="<%= _date(app.parseDate(item.get('adReviewed'))).format('YYYY-MM-DD hh:mm A') %>" />
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						</div>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="vfcInputContainer">
					<label class="control-label" for="vfc">Vfc</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="vfc" placeholder="Vfc" value="<%= _.escape(item.get('vfc') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div> -->
								<!-- <div class="form-group inline" id="usertext1InputContainer">
					<label class="control-label" for="usertext1">Usertext1</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="usertext1" placeholder="Usertext1" value="<%= _.escape(item.get('usertext1') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="usertext2InputContainer">
					<label class="control-label" for="usertext2">Usertext2</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="usertext2" placeholder="Usertext2" value="<%= _.escape(item.get('usertext2') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="usertext3InputContainer">
					<label class="control-label" for="usertext3">Usertext3</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="usertext3" placeholder="Usertext3" value="<%= _.escape(item.get('usertext3') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="usertext4InputContainer">
					<label class="control-label" for="usertext4">Usertext4</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="usertext4" placeholder="Usertext4" value="<%= _.escape(item.get('usertext4') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="usertext5InputContainer">
					<label class="control-label" for="usertext5">Usertext5</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="usertext5" placeholder="Usertext5" value="<%= _.escape(item.get('usertext5') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="usertext6InputContainer">
					<label class="control-label" for="usertext6">Usertext6</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="usertext6" placeholder="Usertext6" value="<%= _.escape(item.get('usertext6') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="usertext7InputContainer">
					<label class="control-label" for="usertext7">Usertext7</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="usertext7" placeholder="Usertext7" value="<%= _.escape(item.get('usertext7') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="usertext8InputContainer">
					<label class="control-label" for="usertext8">Usertext8</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="usertext8" placeholder="Usertext8" value="<%= _.escape(item.get('usertext8') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="userlist1InputContainer">
					<label class="control-label" for="userlist1">Userlist1</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="userlist1" placeholder="Userlist1" value="<%= _.escape(item.get('userlist1') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="userlist2InputContainer">
					<label class="control-label" for="userlist2">Userlist2</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="userlist2" placeholder="Userlist2" value="<%= _.escape(item.get('userlist2') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="userlist3InputContainer">
					<label class="control-label" for="userlist3">Userlist3</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="userlist3" placeholder="Userlist3" value="<%= _.escape(item.get('userlist3') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="userlist4InputContainer">
					<label class="control-label" for="userlist4">Userlist4</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="userlist4" placeholder="Userlist4" value="<%= _.escape(item.get('userlist4') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="userlist5InputContainer">
					<label class="control-label" for="userlist5">Userlist5</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="userlist5" placeholder="Userlist5" value="<%= _.escape(item.get('userlist5') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="userlist6InputContainer">
					<label class="control-label" for="userlist6">Userlist6</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="userlist6" placeholder="Userlist6" value="<%= _.escape(item.get('userlist6') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="userlist7InputContainer">
					<label class="control-label" for="userlist7">Userlist7</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="userlist7" placeholder="Userlist7" value="<%= _.escape(item.get('userlist7') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="pricelevelInputContainer">
					<label class="control-label" for="pricelevel">Pricelevel</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="pricelevel" placeholder="Pricelevel" value="<%= _.escape(item.get('pricelevel') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>-->
