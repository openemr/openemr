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

	$this->assign('title','Patient Portal | Onsite Activity Views');
	$this->assign('nav','onsiteactivityviews');

	$this->display('_FormsHeader.tpl.php');
	echo "<script>var cuser='" . $this->cuser . "';</script>";
?>
<script type="text/javascript">
	$LAB.script("scripts/app/onsiteactivityviews.js").wait(function(){
		$(document).ready(function(){
			actpage.init();
		});
		setTimeout(function(){
			if (!actpage.isInitialized) actpage.init();
		},1000);
	});
</script>

<div class="container">

<h3>
	<i class="icon-th-list"></i><?php echo xlt('Onsite Patient Activities')?>
	<span id="loader" class="loader progress progress-striped active"><span class="progress-bar"></span></span>
	<div class="col-sm-3 col-md-3 pull-right">
		<form class="navbar-form" role="search">
		<div class="input-group">
			<input type="text" class="form-control" placeholder="<?php echo xla('Search'); ?>" name="srch-term" id="srch-term">
			<div class="input-group-btn">
				<button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
			</div>
		</div>
		</form>
	</div>
</h3>
	<!-- underscore template for the collection -->
	<script type="text/template" id="onsiteActivityViewCollectionTemplate">
		<table class="collection table table-condensed table-bordered table-hover">
		<thead>
			<tr>
				<th id="header_Date"><?php echo xlt('Date')?><% if (actpage.orderBy == 'Date') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_PatientId"><?php echo xlt('Patient Id')?><% if (actpage.orderBy == 'PatientId') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Fname"><?php echo xlt('First')?><% if (actpage.orderBy == 'Fname') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Mname"><?php echo xlt('Middle')?><% if (actpage.orderBy == 'Mname') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Lname"><?php echo xlt('Last')?><% if (actpage.orderBy == 'Lname') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Narrative"><?php echo xlt('Narrative')?><% if (actpage.orderBy == 'Narrative') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Activity"><?php echo xlt('Activity')?><% if (actpage.orderBy == 'Activity') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_RequireAudit"><?php echo xlt('Require Audit')?><% if (actpage.orderBy == 'RequireAudit') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_PendingAction"><?php echo xlt('Pending Action')?><% if (actpage.orderBy == 'PendingAction') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Status"><?php echo xlt('Status')?><% if (actpage.orderBy == 'Status') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
<!-- UNCOMMENT TO SHOW ADDITIONAL COLUMNS - Leave in place for future use
				<th id="header_Id">Id<% if (actpage.orderBy == 'Id') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_ActionTaken">Action Taken<% if (actpage.orderBy == 'ActionTaken') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_TableAction">Table Action<% if (actpage.orderBy == 'TableAction') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_TableArgs">Table Args<% if (actpage.orderBy == 'TableArgs') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_ActionUser">Action User<% if (actpage.orderBy == 'ActionUser') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_ActionTakenTime">Action Taken Time<% if (actpage.orderBy == 'ActionTakenTime') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Checksum">Checksum<% if (actpage.orderBy == 'Checksum') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Title">Title<% if (actpage.orderBy == 'Title') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Dob">Dob<% if (actpage.orderBy == 'Dob') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Ss">Ss<% if (actpage.orderBy == 'Ss') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Street">Street<% if (actpage.orderBy == 'Street') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_PostalCode">Postal Code<% if (actpage.orderBy == 'PostalCode') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_City">City<% if (actpage.orderBy == 'City') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_State">State<% if (actpage.orderBy == 'State') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Referrerid">Referrerid<% if (actpage.orderBy == 'Referrerid') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Providerid">Providerid<% if (actpage.orderBy == 'Providerid') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_RefProviderid">Ref Providerid<% if (actpage.orderBy == 'RefProviderid') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Pubpid">Pubpid<% if (actpage.orderBy == 'Pubpid') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_CareTeam">Care Team<% if (actpage.orderBy == 'CareTeam') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Username">Username<% if (actpage.orderBy == 'Username') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Authorized">Authorized<% if (actpage.orderBy == 'Authorized') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Ufname">Ufname<% if (actpage.orderBy == 'Ufname') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Umname">Umname<% if (actpage.orderBy == 'Umname') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Ulname">Ulname<% if (actpage.orderBy == 'Ulname') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Facility">Facility<% if (actpage.orderBy == 'Facility') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Active">Active<% if (actpage.orderBy == 'Active') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Utitle">Utitle<% if (actpage.orderBy == 'Utitle') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_PhysicianType">Physician Type<% if (actpage.orderBy == 'PhysicianType') { %> <i class='icon-arrow-<%= actpage.orderDesc ? 'up' : 'down' %>' /><% } %></th>
-->
			</tr>
		</thead>
		<tbody>
		<% items.each(function(item) { %>
			<tr id="<%= _.escape(item.get('id')) %>">
				<td><%if (item.get('date')) { %><%= _date(app.parseDate(item.get('date'))).format('MMM D, YYYY h:mm A') %><% } else { %>NULL<% } %></td>
				<td><%= _.escape(item.get('patientId') || '') %></td>
				<td><%= _.escape(item.get('fname') || '') %></td>
				<td><%= _.escape(item.get('mname') || '') %></td>
				<td><%= _.escape(item.get('lname') || '') %></td>
				<td><%= _.escape(item.get('narrative') || '') %></td>
				<td><%= _.escape(item.get('activity') || '') %></td>
				<td><%= _.escape(item.get('requireAudit') || '') %></td>
				<td><%= _.escape(item.get('pendingAction') || '') %></td>
				<td><%= _.escape(item.get('status') || '') %></td>
<!-- UNCOMMENT TO SHOW ADDITIONAL COLUMNS - Leave in place for future use
				<td><%= _.escape(item.get('id') || '') %></td>
				<td><%= _.escape(item.get('actionTaken') || '') %></td>
				<td><%= _.escape(item.get('tableAction') || '') %></td>
				<td><%= _.escape(item.get('tableArgs') || '') %></td>
				<td><%= _.escape(item.get('actionUser') || '') %></td>
				<td><%if (item.get('actionTakenTime')) { %><%= _date(app.parseDate(item.get('actionTakenTime'))).format('MMM D, YYYY h:mm A') %><% } else { %>NULL<% } %></td>
				<td><%= _.escape(item.get('checksum') || '') %></td>
				<td><%= _.escape(item.get('title') || '') %></td>
				<td><%if (item.get('dob')) { %><%= _date(app.parseDate(item.get('dob'))).format('MMM D, YYYY') %><% } else { %>NULL<% } %></td>
				<td><%= _.escape(item.get('ss') || '') %></td>
				<td><%= _.escape(item.get('street') || '') %></td>
				<td><%= _.escape(item.get('postalCode') || '') %></td>
				<td><%= _.escape(item.get('city') || '') %></td>
				<td><%= _.escape(item.get('state') || '') %></td>
				<td><%= _.escape(item.get('referrerid') || '') %></td>
				<td><%= _.escape(item.get('providerid') || '') %></td>
				<td><%= _.escape(item.get('refProviderid') || '') %></td>
				<td><%= _.escape(item.get('pubpid') || '') %></td>
				<td><%= _.escape(item.get('careTeam') || '') %></td>
				<td><%= _.escape(item.get('username') || '') %></td>
				<td><%= _.escape(item.get('authorized') || '') %></td>
				<td><%= _.escape(item.get('ufname') || '') %></td>
				<td><%= _.escape(item.get('umname') || '') %></td>
				<td><%= _.escape(item.get('ulname') || '') %></td>
				<td><%= _.escape(item.get('facility') || '') %></td>
				<td><%= _.escape(item.get('active') || '') %></td>
				<td><%= _.escape(item.get('utitle') || '') %></td>
				<td><%= _.escape(item.get('physicianType') || '') %></td>
-->
			</tr>
		<% }); %>
		</tbody>
		</table>
		<%=  view.getPaginationHtml(page) %>
	</script>
	<!-- underscore template for the model -->
	<script type="text/template" id="onsiteActivityViewModelTemplate"></script>
	<div id="collectionAlert"></div>
	<div id="onsiteActivityViewCollectionContainer" class="collectionContainer"></div>
	<p id="returnButtonContainer" class="buttonContainer">
		<button id="returnHome" class="btn btn-primary"><?php echo xlt('Home'); ?></button>
	</p>
</div> <!-- /container -->
<?php
	$this->display('_Footer.tpl.php');
?>
