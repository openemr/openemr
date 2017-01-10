<?php
/* 
 * This template is not currently used
 * The support script and controller are however
 * Leave in place - eventually will use for log review gui
 * */
	$this->assign('title','Patient Portal | OnsitePortalActivities');
	$this->assign('nav','onsiteportalactivities');

	$this->display('_Header.tpl.php');
?>

<script type="text/javascript">
	$LAB.script("scripts/app/onsiteportalactivities.js").wait(function(){
		$(document).ready(function(){
			page.init();
		});

		setTimeout(function(){
			if (!page.isInitialized) page.init();
		},1000);
	});
</script>

<div class="container">

<h1>
	<i class="icon-th-list"></i> OnsitePortalActivities
	<span id="loader" class="loader progress progress-striped active"><span class="progress-bar"></span></span>
			<div class="col-sm-3 col-md-3 pull-right">
		<form class="navbar-form" role="search">
		<div class="input-group">
			<input type="text" class="form-control" placeholder="Search" name="srch-term" id="srch-term">
			<div class="input-group-btn">
				<button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
			</div>
		</div>
		</form>
		</div>
</h1>
<!--
	<!-- underscore template for the collection -->
	<script type="text/template" id="onsitePortalActivityCollectionTemplate">
		<table class="collection table table-bordered table-hover">
		<thead>
			<tr>
				<th id="header_Id">Id<% if (page.orderBy == 'Id') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Date">Date<% if (page.orderBy == 'Date') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_PatientId">Patient Id<% if (page.orderBy == 'PatientId') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Activity">Activity<% if (page.orderBy == 'Activity') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_RequireAudit">Require Audit<% if (page.orderBy == 'RequireAudit') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_PendingAction">Pending Action<% if (page.orderBy == 'PendingAction') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_ActionTaken">Action Taken<% if (page.orderBy == 'ActionTaken') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Status">Status<% if (page.orderBy == 'Status') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
<!-- UNCOMMENT TO SHOW ADDITIONAL COLUMNS
				<th id="header_Narrative">Narrative<% if (page.orderBy == 'Narrative') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_TableAction">Table Action<% if (page.orderBy == 'TableAction') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_TableArgs">Table Args<% if (page.orderBy == 'TableArgs') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_ActionUser">Action User<% if (page.orderBy == 'ActionUser') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_ActionTakenTime">Action Taken Time<% if (page.orderBy == 'ActionTakenTime') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
				<th id="header_Checksum">Checksum<% if (page.orderBy == 'Checksum') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
-->
			</tr>
		</thead>
		<tbody>
		<% items.each(function(item) { %>
			<tr id="<%= _.escape(item.get('id')) %>">
				<td><%= _.escape(item.get('id') || '') %></td>
				<td><%if (item.get('date')) { %><%= _date(app.parseDate(item.get('date'))).format('MMM D, YYYY h:mm A') %><% } else { %>NULL<% } %></td>
				<td><%= _.escape(item.get('patientId') || '') %></td>
				<td><%= _.escape(item.get('activity') || '') %></td>
				<td><%= _.escape(item.get('requireAudit') || '') %></td>
				<td><%= _.escape(item.get('pendingAction') || '') %></td>
				<td><%= _.escape(item.get('actionTaken') || '') %></td>
				<td><%= _.escape(item.get('status') || '') %></td>
<!-- UNCOMMENT TO SHOW ADDITIONAL COLUMNS
				<td><%= _.escape(item.get('narrative') || '') %></td>
				<td><%= _.escape(item.get('tableAction') || '') %></td>
				<td><%= _.escape(item.get('tableArgs') || '') %></td>
				<td><%= _.escape(item.get('actionUser') || '') %></td>
				<td><%if (item.get('actionTakenTime')) { %><%= _date(app.parseDate(item.get('actionTakenTime'))).format('MMM D, YYYY h:mm A') %><% } else { %>NULL<% } %></td>
				<td><%= _.escape(item.get('checksum') || '') %></td>
-->
			</tr>
		<% }); %>
		</tbody>
		</table>

		<%=  view.getPaginationHtml(page) %>
	</script>
-->
	<!-- underscore template for the model -->
	<script type="text/template" id="onsitePortalActivityModelTemplate">
		<form class="form-inline" onsubmit="return false;">
			<fieldset>
				<div class="form-group inline" id="idInputContainer">
					<label class="control-label" for="id">Id</label>
					<div class="controls inline-inputs">
						<span class="form-control uneditable-input" id="id"><%= _.escape(item.get('id') || '') %></span>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="dateInputContainer">
					<label class="control-label" for="date">Date</label>
					<div class="controls inline-inputs">
						<div class="input-append date date-picker" data-date-format="yyyy-mm-dd">
							<input id="date" type="text" value="<%= _date(app.parseDate(item.get('date'))).format('YYYY-MM-DD') %>" />
							<span class="add-on"><i class="icon-calendar"></i></span>
						</div>
						<div class="input-append bootstrap-timepicker-component">
							<input id="date-time" type="text" class="timepicker-default  form-control-small" value="<%= _date(app.parseDate(item.get('date'))).format('h:mm A') %>" />
							<span class="add-on"><i class="icon-time"></i></span>
						</div>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="patientIdInputContainer">
					<label class="control-label" for="patientId">Patient Id</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="patientId" placeholder="Patient Id" value="<%= _.escape(item.get('patientId') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="activityInputContainer">
					<label class="control-label" for="activity">Activity</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="activity" placeholder="Activity" value="<%= _.escape(item.get('activity') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="requireAuditInputContainer">
					<label class="control-label" for="requireAudit">Require Audit</label>
					<div class="controls inline-inputs">
						<div class="radio-inline">
							<label class="radio-inline"><input id="requireAudit0" name="requireAudit" type="radio" value=0<% if (item.get('requireAudit')==0) { %> checked="checked"<% } %>>No</label>
							<label class="radio-inline"><input id="requireAudit1" name="requireAudit" type="radio" value=1<% if (item.get('requireAudit')==1) { %> checked="checked"<% } %>>Yes</label>
						</div>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="pendingActionInputContainer">
					<label class="control-label" for="pendingAction">Pending Action</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="pendingAction" placeholder="Pending Action" value="<%= _.escape(item.get('pendingAction') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="actionTakenInputContainer">
					<label class="control-label" for="actionTaken">Action Taken</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="actionTaken" placeholder="Action Taken" value="<%= _.escape(item.get('actionTaken') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="statusInputContainer">
					<label class="control-label" for="status">Status</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="status" placeholder="Status" value="<%= _.escape(item.get('status') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="narrativeInputContainer">
					<label class="control-label" for="narrative">Narrative</label>
					<div class="controls inline-inputs">
						<textarea class="form-control" id="narrative" rows="3"><%= _.escape(item.get('narrative') || '') %></textarea>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="tableActionInputContainer">
					<label class="control-label" for="tableAction">Table Action</label>
					<div class="controls inline-inputs">
						<textarea class="form-control" id="tableAction" rows="3"><%= _.escape(item.get('tableAction') || '') %></textarea>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="tableArgsInputContainer">
					<label class="control-label" for="tableArgs">Table Args</label>
					<div class="controls inline-inputs">
						<textarea class="form-control" id="tableArgs" rows="3"><%= _.escape(item.get('tableArgs') || '') %></textarea>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="actionUserInputContainer">
					<label class="control-label" for="actionUser">Action User</label>
					<div class="controls inline-inputs">
						<input type="text" class="form-control" id="actionUser" placeholder="Action User" value="<%= _.escape(item.get('actionUser') || '') %>">
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="actionTakenTimeInputContainer">
					<label class="control-label" for="actionTakenTime">Action Taken Time</label>
					<div class="controls inline-inputs">
						<div class="input-append date date-picker" data-date-format="yyyy-mm-dd">
							<input id="actionTakenTime" type="text" value="<%= _date(app.parseDate(item.get('actionTakenTime'))).format('YYYY-MM-DD') %>" />
							<span class="add-on"><i class="icon-calendar"></i></span>
						</div>
						<div class="input-append bootstrap-timepicker-component">
							<input id="actionTakenTime-time" type="text" class="timepicker-default  form-control-small" value="<%= _date(app.parseDate(item.get('actionTakenTime'))).format('h:mm A') %>" />
							<span class="add-on"><i class="icon-time"></i></span>
						</div>
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="form-group inline" id="checksumInputContainer">
					<label class="control-label" for="checksum">Checksum</label>
					<div class="controls inline-inputs">
						<textarea class="form-control" id="checksum" rows="3"><%= _.escape(item.get('checksum') || '') %></textarea>
						<span class="help-inline"></span>
					</div>
				</div>
			</fieldset>
		</form>

		<!-- delete button is is a separate form to prevent enter key from triggering a delete -->
		<form id="deleteOnsitePortalActivityButtonContainer" class="form-inline" onsubmit="return false;">
			<fieldset>
				<div class="form-group">
					<label class="control-label"></label>
					<div class="controls">
						<button id="deleteOnsitePortalActivityButton" class="btn btn-mini btn-danger"><i class="icon-trash icon-white"></i> Delete OnsitePortalActivity</button>
						<span id="confirmDeleteOnsitePortalActivityContainer" class="hide">
							<button id="cancelDeleteOnsitePortalActivityButton" class="btn btn-mini">Cancel</button>
							<button id="confirmDeleteOnsitePortalActivityButton" class="btn btn-mini btn-danger">Confirm</button>
						</span>
					</div>
				</div>
			</fieldset>
		</form>
	</script>

	<!-- modal edit dialog -->
<div class="modal fade" id="onsitePortalActivityDetailDialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><a class="close" data-dismiss="modal">×</a>
                <h3><i class="icon-edit"></i> Edit OnsitePortalActivity
					<span id="modelLoader" class="loader progress progress-striped active"><span class="bar"></span></span>
				</h3>
            </div>
            <div class="modal-body">
                <div id="modelAlert"></div>
                <div id="onsitePortalActivityModelContainer"></div>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal">Cancel</button>
                <button id="saveOnsitePortalActivityButton" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

	<div id="collectionAlert"></div>
	<div id="onsitePortalActivityCollectionContainer" class="collectionContainer"></div>
	<p id="newButtonContainer" class="buttonContainer">
		<button id="newOnsitePortalActivityButton" class="btn btn-primary">Add OnsitePortalActivity</button>
	</p>

</div> <!-- /container -->

<?php
	$this->display('_Footer.tpl.php');
?>
