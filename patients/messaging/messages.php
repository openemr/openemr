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

$_SESSION ['whereto'] = 'profilepanel';
session_start();
if ( isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite']) ) {
	$pid = $_SESSION['pid'];
	$ignoreAuth = true;
	$sanitize_all_escapes = true;
	$fake_register_globals = false;
	require_once ( dirname( __FILE__ ) . "/../../interface/globals.php" );
	define('IS_DASHBOARD', false);
	define('IS_PORTAL', $_SESSION['pid']);
}
else {
	session_destroy();
	$ignoreAuth = false;
	$sanitize_all_escapes = true;
	$fake_register_globals = false;
	require_once ( dirname( __FILE__ ) . "/../../interface/globals.php" );
	if ( ! isset($_SESSION['authUserID']) ){
		$landingpage = "index.php";
		header('Location: '.$landingpage);
		exit;
	}
	define('IS_DASHBOARD', $_SESSION['authUserID']);
	define('IS_PORTAL', false);
}
require_once ("$srcdir/acl.inc");
require_once ("$srcdir/patient.inc");
require_once ("$srcdir/options.inc.php");
require_once ("$srcdir/classes/Document.class.php");
require_once ("./../lib/portal_mail.inc");

$docid = empty ( $_REQUEST ['docid'] ) ? 0 : intval ( $_REQUEST ['docid'] );
$orderid = empty ( $_REQUEST ['orderid'] ) ? 0 : intval ( $_REQUEST ['orderid'] );

$result = getMails(IS_DASHBOARD ? IS_DASHBOARD:IS_PORTAL, 'inbox', '', '');
$theresult = array ();
foreach ( $result as $iter ) {
	$theresult[] = $iter;
}
$dashuser = Array();
if(IS_DASHBOARD){
	$dashuser = getUserIDInfo(IS_DASHBOARD);
}
function getAuthPortalUsers(){
	$resultpd = $resultusers= $resultpatients = array ();
	if( IS_DASHBOARD ){ // admin can mail anyone
		$resultusers[] = ['userid'=>'99998','username'=>'-- Auth Users --'];
		$authusers = sqlStatement( "SELECT users.id as userid, CONCAT(users.fname,' ',users.lname) as username  FROM users WHERE authorized = 1" );
		while( $row = sqlFetchArray($authusers) ){
			$resultusers[] = $row;
		}
		$resultpatients[] = ['userid'=>'99999','username'=>'-- Portal Patients --'];
		$authpatients = sqlStatement( "SELECT patient_data.pid as userid, Concat_Ws(' ', patient_data.fname, patient_data.lname) as username FROM patient_data WHERE allow_patient_portal = 'YES'" );
		while( $row = sqlFetchArray($authpatients) ){
			$resultpatients[] = $row;
		}
		$resultpd[] = array_merge($resultusers,$resultpatients);
		return $resultpd[0];
	}
	else{
		$resultpd = array ();
		$authusers = sqlStatement( "SELECT users.id as userid, CONCAT(users.fname,' ',users.lname) as username  FROM users WHERE authorized = 1" );
		while( $row = sqlFetchArray($authusers) ){
			$resultpd[] = $row;
		}
	}
	return $resultpd;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title>Secure Messaging</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="description" content="Mail Application" />
<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/font-awesome-4-6-3/css/font-awesome.min.css" type="text/css" rel="stylesheet">
<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-3-3-4/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<?php if ($_SESSION['language_direction'] == 'rtl') { ?>
    <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-rtl-3-3-4/dist/css/bootstrap-rtl.min.css" rel="stylesheet" type="text/css" />
<?php } ?>
<script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-11-3/index.js"></script>
<script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-3-3-4/dist/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/summernote-0-8-2/dist/summernote.css" />
<script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/summernote-0-8-2/dist/summernote.js"></script>
<script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/angular-1-5-8/angular.min.js"></script>
<script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/angular-summernote-0-8-1/dist/angular-summernote.js"></script>
<script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/angular-sanitize-1-5-8/angular-sanitize.min.js"></script>
<script src='<?php echo $GLOBALS['assets_static_relative']; ?>/checklist-model-0-10-0/checklist-model.js'></script>
<!--<script type='text/javascript' src="./appmail.js.php"></script>  -->

</head>
<body ><!-- style='background:#f1f2f7;' -->
<script>
(function() {
	var app = angular.module("emrMessageApp",['ngSanitize','summernote',"checklist-model"]);
	app.controller('inboxCtrl', ['$scope', '$filter','$http', function ($scope, $filter,$http) {
		$scope.date = new Date;
		$scope.sortingOrder = 'id';
		$scope.pageSizes = [5,10,20,50,100];
		$scope.reverse = false;
		$scope.filteredItems = [];
		$scope.groupedItems = [];
		$scope.itemsPerPage = 10;
		$scope.pagedItems = [];
		$scope.compose = [];
		$scope.selrecip = [];
		$scope.currentPage = 0;
		$scope.sentItems = [];
		$scope.allItems = [];
		$scope.deletedItems = [];
		$scope.inboxItems = [];
		$scope.inboxItems = <?php echo json_encode($theresult);?>;
	    $scope.userproper = "<?php echo $_SESSION['ptName'] ? $_SESSION['ptName'] : ($dashuser['fname'] . ' ' . $dashuser['lname']) ;?>";
	    $scope.isPortal = "<?php echo IS_PORTAL;?>" ;
	    $scope.isDashboard = "<?php echo IS_DASHBOARD ? IS_DASHBOARD : 0;?>" ;
	    $scope.cMode = "$scope.isPortal ? 'portal' : 'dashboard'";
	    $scope.cUserId = $scope.isPortal ? $scope.isPortal : $scope.isDashboard;
	    $scope.username = "<?php echo $_SESSION['portal_username'] ? $_SESSION['portal_username'] : $_SESSION['authUser'];?>";
	    $scope.authrecips = <?php echo json_encode(getAuthPortalUsers());?>;
	    
	    $scope.init = function () {
	       $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
	       $scope.getSentMessages();
	       $scope.getAllMessages();
	       $scope.getDeletedMessages();
	       $scope.isInboxSelected();
	       $scope.search();
	       $scope.isInit = true;
	       $('#main').show();
	    }

	    var searchMatch = function (haystack, needle) {
	        if (!needle) {
	          return true;
	        }
	        return haystack.toLowerCase().indexOf(needle.toLowerCase()) !== -1;
	    };
	    
	    // filter the items
	    $scope.search = function () {
	        $scope.filteredItems = $filter('filter')($scope.items, function (item) {
	          for(var attr in item) {
	            if (searchMatch(item[attr], $scope.query))
	              return true;
	          }
	          return false;
	        });
	        $scope.currentPage = 0;
	        // now group by pages
	        $scope.groupToPages();
	    };
	    
	    // calculate page in place
	    $scope.groupToPages = function () {
	        $scope.selected = null;
	        $scope.pagedItems = [];
	        for (var i = 0; i < $scope.filteredItems.length; i++) {
	          if (i % $scope.itemsPerPage === 0) {
	            $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [ $scope.filteredItems[i] ];
	          } else {
	            $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.filteredItems[i]);
	          }
	        }
	    };
	    
	    $scope.range = function (start, end) {
	        var ret = [];
	        if (!end) {
	          end = start;
	          start = 0;
	        }
	        for (var i = start; i < end; i++) {
	          ret.push(i);
	        }
	        return ret;
	    };
	    
	    $scope.prevPage = function () {
	        if ($scope.currentPage > 0) {
	            $scope.currentPage--;
	        }
	        return false;
	    };
	    
	    $scope.nextPage = function () {
	        if ($scope.currentPage < $scope.pagedItems.length - 1) {
	            $scope.currentPage++;
	        }
	        return false;
	    };
	    
	    $scope.setPage = function () {
	        $scope.currentPage = this.n;
	    };
	    
	    $scope.deleteItem = function (idx) {
	    	if( !confirm('Confirm to Delete Selected?') ) return false;	    	
	        var itemToDelete = $scope.pagedItems[$scope.currentPage][idx];
	        var idxInItems = $scope.items.indexOf(itemToDelete);
	        $scope.deleteMessage(itemToDelete.id);
	        $scope.items.splice(idxInItems,1);
	        $scope.search();
	        return false;
	    };
	    
	    $scope.batchDelete = function ( i ) {
	    	if( !confirm('Confirm to delete all selected?') ) return false;
	    	angular.forEach(i, function(o, key) {
		    	if(o.hasOwnProperty('deleted')){
	       	 		var itemToDelete = $scope.items[i.indexOf(o)];
	       	 		var idxInItems = $scope.items.indexOf(itemToDelete);
	       	 		$scope.deleteMessage(itemToDelete.id);
	        		$scope.items.splice(idxInItems,1);
		    	}
	    	})
	        location.reload(true);
	    };
	    
	    $scope.deleteMessage = function (id){ /* @todo add array for mass delete */
        	$http.post('handle_note.php', $.param( {'task':'delete','noteid':id} ))
        	.success(function(data, status, headers, config) {
        		return true;
        	}).error(function(data, status, headers, config) {
        		alert('Failed Status: '+ data);
        	});
    	};	    
	    $scope.isMessageSelected = function () {
	        if (typeof $scope.selected!=="undefined" && $scope.selected!==null) {
	            return true;
	        }
	        else {
	            return false;
	        }
	    };
	    
	    $scope.isSentSelected = function () {
	    	$scope.isSent = true; $scope.isTrash = $scope.isAll = $scope.isInbox = false;
	    	$scope.items = [];
	    	$scope.items = $scope.sentItems;
	    	$scope.search();
	    	return true;
	    }
	    
	    $scope.isTrashSelected = function () {
	    	$scope.isTrash = true; $scope.isSent = $scope.isAll = $scope.isInbox = false;
	    	$scope.items = [];
	    	$scope.items = $scope.deletedItems;
	    	$scope.search();
	    	return true;
	    }
	    
	    $scope.isInboxSelected = function () { 
	    	$scope.isInbox = true; $scope.isTrash = $scope.isAll = $scope.isSent = false;
	    	$scope.items = $scope.inboxItems;
	    	$scope.search();
	    	return true;
	    }
	    
	    $scope.isAllSelected = function () { 
	    	$scope.isAll = true; $scope.isTrash = $scope.isSent = $scope.isInbox = false;
	    	$scope.items = $scope.allItems;
	    	$scope.search();
	    	return true;
	    }
	    
	    $scope.readMessage = function (idx) {
	        $scope.selected = $scope.items[idx];
	        if( $scope.items[idx].message_status == 'New'){ // mark mail read else ignore
	        	$http.post('handle_note.php', $.param({'task':'setread','noteid':$scope.items[idx].id}))
	        	.success(function(data, status, headers, config) {
	        		$scope.items[idx].message_status = 'Read';
	        	}).error(function(data, status, headers, config) {
	        		alert('Failed Status: '+ data);
	        	});
	        } // status update
	    };
    
	    $scope.readAll = function () {
	        for (var i in $scope.items) {
	            $scope.items[i].message_status = 'Read';
	        }
	    };
	    
	    $scope.closeMessage = function () {
	        $scope.selected = null;
	    };
	    
	    $scope.renderMessageBody = function(html)
	    {
	        return html;
	    };
	    
	    $scope.getInbox = function () {
	        $http.post('handle_note.php', $.param({'task':'getinbox','owner':$scope.cUserId}))
	        .success(function(data, status, headers, config) {
	        	if(data){
	        	  $scope.inboxItems = angular.copy(data);
	        	  //$scope.search();
	        	}
	        	else alert('whoops get inbox: '+ data);
	        }).error(function(data, status, headers, config) {
	        	alert('Failed Status: '+data);
	        });
	    };
	    
	    $scope.getAllMessages = function () {
	        $http.post('handle_note.php', $.param({'task':'getall','owner':$scope.cUserId}))
	        .success(function(data, status, headers, config) {
	        	if(data){
	        	  $scope.allItems = angular.copy(data);
	        	  //$scope.search();
	        	}
	        	else alert('whoops : '+ data);
	        }).error(function(data, status, headers, config) {
	        	alert('Failed Status: '+data);
	        });
	    };
	    
	    $scope.getDeletedMessages = function () {
	        $http.post('handle_note.php', $.param({'task':'getdeleted','owner':$scope.cUserId}))
	        .success(function(data, status, headers, config) {
	        	if(data){
	        	  $scope.deletedItems = [];
	        	  $scope.deletedItems = angular.copy(data);
	        	  //$scope.search();
	        	}
	        	else alert('whoops : '+ data);
	        }).error(function(data, status, headers, config) {
	        	alert('Failed Status: '+data);
	        });
	    };
	    
	    $scope.getSentMessages = function () {
	        $http.post('handle_note.php', $.param({'task':'getsent','owner':$scope.cUserId}))
	        .success(function(data, status, headers, config) {
	        	$scope.sentItems = [];
	            $scope.sentItems = angular.copy(data);
	            //$scope.search();
	        }).error(function(data, status, headers, config) {
	        	alert('Failed Status: '+data);
	        });
	    }
	    
	    $scope.submitForm = function(compose){
		 	var subject = compose.title;
	    	$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
		 	// re-enable title for submit
		 	$("#title").prop( "disabled", false )
		 	$("#selSendto").prop( "disabled", false )
		 	compose.inputBody = $("#inputBody").summernote('code');
		 	compose.owner = $scope.cUserId
			compose.sender_id=$scope.cUserId
			compose.sender_name=$scope.userproper
			if( $scope.selrecip == compose.owner ){
				if(!confirm("You are sending a message to yourself! Continue?"))
					return false;
			}
		 	if( compose.task == 'add' ){
				compose.recipient_id=$scope.selrecip;
				compose.recipient_name=$("#selSendto option:selected").text();
		 	}
	        return true; // okay to submit - add some validation
	    }
	   
	    $('#modalCompose').on('show.bs.modal', function(e) {
	    	 	$('#inputBody').summernote({focus:true,height:'225px'});
	        	var mode = $(e.relatedTarget).attr('data-mode');
	         if(mode == 'reply'){
		         $('#modalCompose .modal-header .modal-title').html("Compose Reply Message");
	        	 $scope.compose.task = mode;
		        //get data attributes of the clicked element (selected recipient) for replies only
		        var chain = $(e.relatedTarget).attr('data-noteid');
	        	var recipId = $(e.relatedTarget).attr('data-whoto');
		        var title = $(e.relatedTarget).attr('data-mtitle');
		        var uname = $(e.relatedTarget).attr('data-username');
	        	$(e.currentTarget).find('select[id="selSendto"]').val(recipId)
	        	$(e.currentTarget).find('select[id="selSendto"]').prop( "disabled", true );
	        	$(e.currentTarget).find('input[name="title"]').val(title);
	        	$(e.currentTarget).find('input[name="title"]').prop( "disabled", true );
	        	$scope.compose.title = title;
	        	$scope.selrecip = recipId;
	        	$scope.selrecip.username = uname;
	        	$scope.compose.recipient_name = uname;
	        	$scope.compose.recipient_id = recipId;
	        	$("#selSendto").val(recipId);
	        	$scope.compose.noteid = chain;
	        	$(e.currentTarget).find('input[name="noteid"]').val(chain)
	        }
	        else{
	        	//$scope.compose.noteid = chain;
	        	$('#modalCompose .modal-header .modal-title').html("Compose New Message");
	        	$scope.compose.task = 'add';
	        	$(e.currentTarget).find('select[id="selSendto"]').prop( "disabled", false );
	        	$(e.currentTarget).find('input[name="title"]').prop( "disabled", false );
	        }
	    }); // on modal - do the prelim to save
	    
	    $('#modalCompose').on('hidden.bs.modal', function(e){
	    	// cleanup
	    });
	    // initialize application
	    if(!$scope.isInit)
	    		$scope.init();
	}])  /* end inbox functions */
	
	.controller('messageCtrl', ['$scope', function ($scope) {
	    $scope.message = function(idx) {
	        return items(idx);
	    };
	}]);   // end messageCtrl

})(); // application end
</script>
	<ng ng-app="emrMessageApp">
	<div class="container" id='main' style="display:none">
		<div class='header logo'>
		<h2><img style='width:25%;height:auto;' class='logo' src='<?php echo $GLOBALS['images_static_relative']; ?>/logo-full-con.png'/>  <?php echo xlt('Patient Messaging'); ?></h2>
		</div>
		<div class="row" ng-controller="inboxCtrl">
			<aside class="col-md-1" style='padding:0 0;margin:0 0;text-align:left;'>
				<ul class="nav nav-pills nav-stacked" style='padding:0 0;margin:0 0;text-align:left;'>
					<li data-toggle="pill" class="active bg-info"><a href="javascript:;" ng-click="isInboxSelected()"><span class="badge pull-right">{{inboxItems.length}}</span><?php echo xlt('Inbox'); ?></a></li>
					<li data-toggle="pill" class="bg-info"><a href="javascript:;" ng-click="isSentSelected()"><span class="badge pull-right">{{sentItems.length}}</span><?php echo xlt('Sent'); ?></a></li>
					<li data-toggle="pill" class="bg-info"><a href="javascript:;" ng-click="isAllSelected()"><span class="badge pull-right">{{allItems.length}}</span><?php echo xlt('All'); ?></a></li>
					<li data-toggle="pill" class="bg-info"><a href="#"><span class="badge pull-right">0</span><?php echo xlt('Drafts'); ?></a></li>
					<li data-toggle="pill" class="bg-info"><a href="javascript:;" ng-click="isTrashSelected()"><span class="badge pull-right">{{deletedItems.length}}</span><?php echo xlt('Trash'); ?></a></li>
					<li class="pill bg-danger"><a href="./../patient/provider" ng-show="!isPortal"><?php echo xlt('Exit Mail'); ?></a></li>
					<li data-toggle="pill" class="bg-danger"><a href="javascript:;" onclick='window.location.replace("./../home.php")' ng-show="isPortal"><?php echo xlt('Exitl'); ?></a></li>
				</ul>
			</aside>
			<div class="col-md-11">
				<!--inbox toolbar-->
				<div class="row" ng-show="!isMessageSelected()">
					<div class="col-xs-12">
						<a class="btn btn-default btn-lg" data-toggle="tooltip"
							title="Refresh" id="refreshInbox" href="javascript:;"
							onclick='window.location.replace("./messages.php")'> <span
							class="fa fa-refresh fa-lg"></span>
						</a>
						<button class="btn btn-default btn-lg" title="New Note"
							data-mode="add" data-toggle="modal" data-target="#modalCompose">
							<span class="fa fa-edit fa-lg"></span>
						</button>
						<div class="btn-group btn-group pull-right">
							<button type="button" class="btn btn-primary dropdown-toggle"
								data-toggle="dropdown">
								<?php echo xlt('Actions'); ?> <span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu">
								<li><a href="javascript:;" ng-click="readAll()"><?php echo xlt('Mark all as read'); ?></a></li>
								<li class="divider"></li>
								<li><a href="" data-mode="add" data-toggle="modal" data-target="#modalCompose"><?php echo xlt('Compose new'); ?></a></li>
								<li ng-show='!isTrash'><a href="javascript:;" ng-click="batchDelete(items)"><i class="fa fa-trash-o"></i> <?php echo xlt('Send Selected to Trash'); ?></a></li>
								<li><a href="javascript:;" onclick='window.location.replace("./../home.php")'	ng-show="isPortal" class="text-muted"><?php echo xlt('Return Home'); ?></a></li>
								<li><a href="./../patient/provider" ng-show="!isPortal"	class="text-muted"><?php echo xlt('Return Home'); ?></a></li>
							</ul>
						</div>
					</div><!--/col-->
					<div class="col-xs-12 spacer5"></div>
				</div><!--/row-->
				<!--/inbox toolbar-->
				<div class="panel panel-default inbox" id="inboxPanel">
					<!--message list-->
					<div class="table-responsive" ng-show="!isMessageSelected()">
						<table class="table table-striped table-hover refresh-container pull-down">
							<thead class="bg-info hidden-xs"></thead>
							<tbody>
								<tr ng-repeat="item in pagedItems[currentPage]"><!--  | orderBy:sortingOrder:reverse -->
									<td>
									<span class="col-sm-1" style="max-width:5px;"><input type="checkbox" checklist-model="item.deleted" value={{item.deleted}}></span></span>
									<span class="col-sm-1" style="max-width:8px;"><span	ng-class="{strong: !item.read}">{{item.id}}</span></span>
									<span class="col-sm-1" ng-click="readMessage($index)"><span	ng-class="{strong: !item.read}">{{item.message_status}}</span></span>
									<span class="col-sm-2" ng-click="readMessage($index)"><span	ng-class="{strong: !item.read}">{{item.date | date:'yyyy-MM-dd hh:mm'}}</span></span>
									<span class="col-sm-3" ng-click="readMessage($index)"><span ng-class="{strong: !item.read}">{{item.sender_name}} to {{item.recipient_name}}</span></span>
									<span class="col-sm-1" ng-click="readMessage($index)"><span ng-class="{strong: !item.read}">{{item.title}}</span></span>
									<span class="col-sm-4" ng-click="readMessage($index)"><span ng-class="{strong: !item.read}" ng-bind-html='(renderMessageBody(item.body)| limitTo:40)'></span></span>
									<!-- <span class="col-sm-1" ng-click="readMessage($index)"><span ng-class="{strong: !item.read}">{{item.mail_chain}}</span></span> -->
									<!-- <span class="col-sm-1 " ng-click="readMessage($index)"><span
										ng-show="item.attachment"
										class="glyphicon glyphicon-paperclip pull-right"></span> <span ng-show="item.priority==1"
										class="pull-right glyphicon glyphicon-warning-sign text-danger"></span></span> -->
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<!--message detail-->
					<div class="container-fluid" ng-show="isMessageSelected()">
						<div class="row" ng-controller="messageCtrl">
							<!--  <div class="col-xs-12">
								<h4 title="subject">
									<button type="button" class="btn btn-danger btn-sm pull-right" ng-click="closeMessage()"><?php //echo xlt('Back'); ?></button>
									<a href="javascript:;" ng-click="groupToPages()"><?php //echo xlt('This Conversation'); ?></a> &gt; {{selected.title}}
								</h4>
							</div>-->
							<div class="col-md-9">
								<blockquote class="bg-warning">
								<a href="javascript:;" ng-click="groupToPages()"><?php echo xlt('Conversation'); ?></a>
									<span><?php //echo xlt('Seleted Message id'); ?><!-- :{{selected.id}} --> <?php echo xlt('from'); ?> </span> <strong>{{selected.sender_name}}</strong>
									<?php echo xlt('regarding'); ?> {{selected.title}} <?php echo xlt('on'); ?> &lt;{{selected.date | date:'yyyy-MM-dd hh:mm'}}&gt;
								</blockquote>
							</div>
							<div class="col-md-3">
								<div class="btn-group btn-group pull-right">
									<button ng-show="selected.sender_id != cUserId" class="btn btn-primary" title="<?php echo xla('Reply to this message'); ?>" data-toggle="modal" data-mode="reply" data-noteid={{selected.id}}
										data-whoto={{selected.sender_id}} data-mtitle={{selected.title}}	data-username={{selected.sender_name}} data-target="#modalCompose">
										<i class="fa fa-reply"></i> <?php echo xlt('Reply'); ?></button>
									<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown" title="More options"><i class="fa fa-angle-down"></i></button>
									<ul class="dropdown-menu pull-right">
										<li><a href="javascript:;"><i class="fa fa-reply"></i> <?php echo xlt('Reply'); ?></a></li>
										<li><a href="javascript:;"><i class="fa fa-mail-forward"></i><?php echo xlt('Forward'); ?></a></li>
										<li><a href="javascript:;"><i class="fa fa-print"></i> <?php echo xlt('Print'); ?></a></li>
										<li class="divider"></li>
										<li ng-show='!isTrash'><a href="javascript:;" ng-click="batchDelete(items)"><i class="fa fa-trash-o"></i> <?php echo xlt('Send to Trash'); ?></a></li>
									</ul>
								</div>
								<div class="spacer5 pull-right"></div>
								<button ng-show='!isTrash' class="btn btn-md btn-primary pull-right" ng-click="deleteItem(items.indexOf(selected))" title="<?php echo xla('Delete this message'); ?>" data-toggle="tooltip">
									<i class="fa fa-trash-o fa-1x"></i>
								</button>
							</div>
							<div class="table-responsive col-sm-12 col-md-12">
									<table	class="table table-striped refresh-container pull-down">
										<thead><?php //echo xlt('Associated Notes.');?></thead>
										<tbody>
										<tr class="animate-repeat" ng-repeat="item in pagedItems[currentPage]"><!--  | filter: {selected.id}  | filter: {mail_chain: selected.id}orderBy:sortingOrder:reverse -->
											<td>
												<span class="col-sm-1" style="max-width: 5px;"><input type="checkbox" checklist-model="item.deleted" value={{item.deleted}}></span></span>
												<span class="col-sm-1" style="max-width: 8px;"><span ng-class="{strong: !item.read}">{{item.id}}</span></span>
												<span class="col-sm-2" ng-click="readMessage($index)"><span>{{item.date | date:'yyyy-MM-dd hh:mm'}}</span></span>
												<span class="col-sm-1" ng-click="readMessage($index)"><span>{{item.message_status}}</span></span>
												<span class="col-sm-3" ng-click="readMessage($index)"><span>{{item.sender_name}} to {{item.recipient_name}}</span></span>
												<span class="col-sm-1" ng-click="readMessage($index)"><span>{{item.title}}</span></span>
												<!-- <span class="col-sm-1" style="max-width:8px;">{{item.mail_chain}}</span> -->
												<div class='col-sm-10 well' ng-show="selected.id == item.id" style='margin:5px 5px;padding:5px 5px;border-color:black;'>
														<span ng-bind-html=renderMessageBody(selected.body)></span>
												</div>
											</td>
										</tr>
										</tbody>
									</table>
							   </div><!--/message body-->
						</div><!--/row-->
					</div>
				</div><!--/inbox panel-->
				<div class="well well-s text-right">
					<em>Inbox last updated: <span id="lastUpdated">{{date |
							date:'MM-dd-yyyy HH:mm:ss'}}</span></em>
				</div>
				<!--paging-->
				<div class="pull-right">
					<span class="text-muted"><b>{{(itemsPerPage * currentPage) + 1}}</b>~<b>{{(itemsPerPage
							* currentPage) + pagedItems[currentPage].length}}</b> of <b>{{items.length}}</b></span>
					<div class="btn-group btn-group">
						<button type="button" class="btn btn-default btn-lg" ng-class="{disabled: currentPage == 0}" ng-click="prevPage()">
							<span class="glyphicon glyphicon-chevron-left"></span>
						</button>
						<button type="button" class="btn btn-default btn-lg" ng-class="{disabled: currentPage == pagedItems.length - 1}" ng-click="nextPage()">
							<span class="glyphicon glyphicon-chevron-right"></span>
						</button>
					</div>
				</div>
				<hr>
			</div>
			<!-- /.modal compose message -->
			<div class="modal fade" id="modalCompose" ng-controller="inboxCtrl">
				<div class="modal-dialog  modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
							<h4 class="modal-title"><?php echo xlt('Compose Message'); ?></h4>
						</div>
						<div class="modal-body ">
						<div class="row container-fluid">
						<label ng-show='selected.mail_chain'><?php echo xlt('Refer to Message') . ' # ';?>{{selected.id}}</label>
						<div class="well well-sm col-12-md" ng-show='selected.mail_chain' style='background:ghostwhite;border-color:black;' class='well well-lg row' ng-bind-html=renderMessageBody(selected.body)></div>
							<fieldset>
							<form role="form" class="form-horizontal" ng-submit="submitForm(compose)" name="fcompose" id="fcompose" method="post" action="./handle_note.php">
								<div class="form-group">
									<label class="col-sm-1 col-md-1" for="selSendto"><?php echo xlt('To'); ?></label>
									<div class="col-sm-3 col-md-3">
										<select class="form-control" id="selSendto" ng-model="selrecip" ng-options="recip.userid as recip.username for recip in authrecips track by recip.userid"></select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-1 col-md-1" for="title"><?php echo xlt('Subject'); ?></label>
									<div class="col-sm-6 col-md-6">
									<input type='text' list='listid' name='title' id='title' class="form-control" ng-model='compose.title'>
									<datalist id='listid'>
										<option><?php echo xlt('Unassigned'); ?></option>
										<option label='<?php echo xlt('Insurance'); ?>' value='<?php echo xlt('Insurance'); ?>'/>
										<option label='<?php echo xlt('Prior Auth'); ?>' value='<?php echo xlt('Prior Auth'); ?>'/>
										<option label='<?php echo xlt('Bill/Collect'); ?>' value='<?php echo xlt('Bill/Collect'); ?>'/>
										<option label='<?php echo xlt('Referral'); ?>' value='<?php echo xlt('Referral'); ?>'/>
										<option label='<?php echo xlt('Medication'); ?>' value='<?php echo xlt('Medication'); ?>'/>
									</datalist>
									</div>
								 </div>
								</fieldset>
								<!-- <fieldset><legend><?php //echo xlt('Message') . ' # '; ?>{{selected.id}}</legend> -->
								<div class="row container-fluid">
									<div class="col-sm-12 col-md-12"  id="inputBody" ng-model="compose.inputBody"></div>
								</div></fieldset>
								<input type='hidden' name='noteid' id='noteid' ng-value="compose.noteid" value={{selected.id}} />
								<input type='hidden' name='owner' ng-value='compose.owner' />
								<input type='hidden' name='recipient_id' ng-value='compose.recipient_id' />
								<input type='hidden' name='recipient_name' ng-value='compose.recipient_name' />
								<input type='hidden' name='sender_id' ng-value='compose.sender_id' />
								<input type='hidden' name='sender_name' ng-value='compose.sender_name' />
								<input type='hidden' name='task' ng-value='compose.task' />
								<input type='hidden' name='inputBody' ng-value='compose.inputBody' />
						   <div class='modal-footer'>
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo xlt('Cancel'); ?></button>
							<button type="submit" id="submit" name="submit" class="btn btn-primary pull-right" value="messages.php"><?php echo xlt('Send'); ?> <i class="fa fa-arrow-circle-right fa-lg"></i></button>
						 </div>
					   </form>
					 </div>
					 </div>
				  </div><!-- /.modal-content -->
				</div><!-- /.modal-dialog -->
			  </div><!-- /.modal compose message -->
			</div><!--/row ng-controller-->
		  </div><!--/container-->
	</ng>

</body>
</html>