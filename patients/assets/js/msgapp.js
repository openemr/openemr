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
/**
 * sjpadgett@gmail.com June 2016 .. may rewrite with  php javascript
 */
var app = angular.module("emrMessageApp",['ngSanitize']);
   
app.controller('inboxCtrl', ['$scope', '$filter','$http', function ($scope, $filter,$http) {
 	$scope.date = new Date;
    $scope.sortingOrder = 'id';
    $scope.pageSizes = [5,10,20,50,100];
    $scope.reverse = false;
    $scope.filteredItems = [];
    $scope.groupedItems = [];
    $scope.itemsPerPage = 5;
    $scope.pagedItems = [];
    $scope.currentPage = 0;
    $scope.sentItems = [];
    $scope.allItems = [];
    $scope.inboxItems = [];
    
    $scope.init = function () {
       $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
       $scope.inboxItems = messages;
       $scope.getSentMessages();
       $scope.getAllMessages();
       $scope.isInboxSelected();
       $scope.search();
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
        var itemToDelete = $scope.pagedItems[$scope.currentPage][idx];
        var idxInItems = $scope.items.indexOf(itemToDelete);
        $scope.items.splice(idxInItems,1);
        $scope.search();
        
        return false;
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
    	$scope.items = $scope.sentItems;
    	$scope.search();
    	return true;
    }
    $scope.isInboxSelected = function () { 
    	$scope.items = $scope.inboxItems;
    	$scope.search();
    	return true;
    }
    $scope.isAllSelected = function () { 
    	$scope.items = $scope.allItems;
    	$scope.search();
    	return true;
    }
    $scope.readMessage = function (idx) {
        $scope.selected = $scope.items[idx];
        if( $scope.items[idx].message_status == 'New'){// mark pnote read
        	$http.post('handle_note.php', $.param({'task':'setread','noteid':$scope.items[idx].id}))
        	.success(function(data, status, headers, config) {
        		if(data == 'done')
        			$scope.items[idx].message_status = 'Read';
        		else alert('whoops : '+ data);
        	}).error(function(data, status, headers, config) {
        		alert('Failed Status: '+ data);
        	});
        }
    };
    
    $scope.readAll = function () {
        for (var i in $scope.items) {
            $scope.items[i].read = true;
        }
    };
    
    $scope.closeMessage = function () {
        $scope.selected = null;
    };
    
    $scope.renderMessageBody = function(html)
    {
        return html;
    };
    
    $scope.getAllMessages = function () {
        $http.post('handle_note.php', $.param({'task':'getall'}))
        .success(function(data, status, headers, config) {
        	if(data){
        	  $scope.allItems = data;
        	 // $scope.search();
        	}
        	else alert('whoops : '+ data);
        }).error(function(data, status, headers, config) {
        	alert('Failed Status: '+data);
        });
    };
    
    $scope.getSentMessages = function () {
        $http.post('handle_note.php', $.param({'task':'getsent'}))
        .success(function(data, status, headers, config) {
            $scope.sentItems = data;
        }).error(function(data, status, headers, config) {
        	alert('Failed Status: '+data);
        });
    }
    /* end inbox functions */
    
    // initialize
    $scope.init();
    
}])// end inboxCtrl
.controller('messageCtrl', ['$scope', function ($scope) {
    
    $scope.message = function(idx) {
        return items(idx);
    };
    
}]);// end messageCtrl

$(document).ready(function(){	    
 
	function goHome(){
    	window.location.replace("./home.php");
    }
	
	 $("#pnotesend").on("submit", function(e) {
		 	// re-enable title for submit
		 	$(e.currentTarget).find('select[name="title"]').prop( "disabled", false );
		 	var towho = $(e.currentTarget).find('select[name="selSendto"]').val();
		 	var mode = $(e.currentTarget).find('input[name="task"]').val();
		 	if(mode == 'add')
		 		$(e.currentTarget).find('input[name="sendto"]').val(towho);
	        var postData = $(this).serializeArray();
	        var formURL = $(this).attr("action");
	        $.ajax({
	            url: formURL,
	            type: "POST",
	            data: postData,
	            success: function(data, textStatus, jqXHR) {
	                /*$('#modalCompose .modal-header .modal-title').html("Result");
	                $('#modalCompose .modal-body').html(data);*/
	            	$("#submitForm").remove();
	            	$("#modalCompose").modal('hide');
	            	window.location.replace("./messages.php");
	            },
	            error: function(jqXHR, status, error) {
	                console.log(status + ": " + error);
	            }
	        });
	        e.preventDefault();
	    });
	     
	    $("#submitForm").on('click', function() {
	        $("#pnotesend").submit();
	    });
	    $('#modalCompose').on('show.bs.modal', function(e) {
	        //get data attributes of the clicked element
	        var mode = $(e.relatedTarget).data('mode');
	        var towho = $(e.relatedTarget).data('whoto');
	        var title = $(e.relatedTarget).data('mtitle');
	        var exists = false;
        	$('#title option').each(function(){
        	    if (this.value == title) {
        	        exists = true;
        	        return false;
        	    }
        	});
	        //populate the hidden action 
	        $(e.currentTarget).find('input[name="task"]').val(mode);
	        if(mode == 'reply'){
	        	$('#modalCompose .modal-header .modal-title').html("Compose Reply Message");
	        	$(e.currentTarget).find('input[name="sendto"]').val(towho);
	        	$(e.currentTarget).find('select[name="selSendto"]').prop( "disabled", true );
	        	if(exists == false){
	        		$(e.currentTarget).find('select[name="title"]').prepend('<option>'+title);
	        	}
	        	$(e.currentTarget).find('select[name="title"]').val(title);
	        	$(e.currentTarget).find('select[name="title"]').prop( "disabled", true );
	        }
	        else{
	        	$('#modalCompose .modal-header .modal-title').html("Compose New Message");
	        	$(e.currentTarget).find('select[name="selSendto"]').prop( "disabled", false );
	        	$(e.currentTarget).find('select[name="title"]').prop( "disabled", false );
	        	$(e.currentTarget).find('input[name="sendto"]').val(towho);
	        }
	    });
});
