/* +-----------------------------------------------------------------------------+
 *    OpenEMR - Open Source Electronic Medical Record
 *    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *    @author  Jacob T.Paul <jacob@zhservices.com>
 *    @author  Basil P T <basil@zhservices.com>
 * +------------------------------------------------------------------------------+
 */

// Global variables
var selected_user = '';
var selected_component = '';
var allowed_array = new Array();
var denied_array = new Array();
//

function createtree() {
  $("#td_componets,#td_users,#td_allowed_users,#td_denied_users").treeview({
    animated: "fast",
    collapsed: true,
    control: "#control_div"
  });
}

$(document).mouseup(function(e) {
  var container = $(".popup_items");
  if (!container.is(e.target) && container.has(e.target).length === 0) {
    $(".popup_items").css("display", "none");
  }
});

$(window).load(function() {
  $(".scrollable").customScrollbar();
});

$(document).one("mouseover", function() {
  $(".scrollable").customScrollbar("resize");
  $("#expand_tree").click();
});

$(document).ready(function() {
  $(".popup_items").draggable();
  createtree();
  $(".draggable2").draggable({
    containment: "#outer_table",
    scroll: false,
    revert: true,
    drag: function() {
      selected_user = this.id;
    },
  });
  $(".draggable3").draggable({
    containment: "#outer_table",
    scroll: false,
    revert: true,
    drag: function() {
      selected_user = this.id;
    },
  });
  $(".draggable4").draggable({
    containment: "#outer_table",
    scroll: false,
    revert: true,
    drag: function() {
      selected_user = this.id;
    },
  });
  $(".delete_droppable").droppable({
    accept: ".draggable3,.draggable4",
    activeClass: "deleted",
    over: function(event, ui) {
      $(".delete_droppable").addClass("red");
      setTimeout(function() {
        $(".delete_droppable").removeClass("red");
      }, 400);
    },
    drop: function(event, ui) {
			var user_delete 	= false;
			var invalid_group = false;
      var arr_id = selected_user.split("-");
      if (arr_id[1] == 0) {
				if($("#" + selected_user).css("opacity") == "1"){
					$("#li_" + selected_user).css("display", "none");
					$("#li_" + selected_user).find('li').css("display", "none");
				} else {
					invalid_group = true;
				}
        
      } else {
				user_delete = true;
      }

      if (selected_user.indexOf("user_group_allowed_") != -1) {
        if (arr_id[1] == 0 && !user_delete && !invalid_group) {
          index = allowed_array.indexOf("li_" + selected_user);
          if (index != -1) allowed_array.splice(index, 1);
          else {
            $("#li_" + selected_user).find("li").each(function() {
              index_1 = allowed_array.indexOf($(this).attr("id"));
              if (index_1 != -1) allowed_array.splice(index_1, 1);
            });
          }
        } 


      } else if (selected_user.indexOf("user_group_denied_") != -1) {
        if (arr_id[1] == 0 && !user_delete && !invalid_group) {
          index = denied_array.indexOf("li_" + selected_user);
          if (index != -1) denied_array.splice(index, 1);
          else {
            $("#li_" + selected_user).find("li").each(function() {
              index_1 = denied_array.indexOf($(this).attr("id"));
              if (index_1 != -1) denied_array.splice(index_1, 1);
            });
          }
        } 
      }
			if(user_delete){
				var resultTranslated = js_xl('User Cannot be Deleted');
				$("#messages").html(resultTranslated.msg);
				setTimeout(function() {
				$("#messages").html("");
      }, 3000);
			} else if(invalid_group) {
				var resultTranslated = js_xl('Please Select an Active Group');
				$("#messages").html(resultTranslated.msg);
				setTimeout(function() {
					$("#messages").html("");
				}, 3000);
			} else {
				saveAcl();
			}
      
    },
  });
  $(".droppableAllowed").droppable({
    accept: ".draggable2,.draggable4",
    drop: function() {
      var dragged_from_denied = false;
      if (selected_user.indexOf("user_group_denied_") != -1) {
        dragged_from_denied = true;
        new_id = selected_user.replace("user_group_denied_", "user_group_allowed_");
        denied_id = selected_user;
        var arr_id = selected_user.split("-");
        if (arr_id[1] != 0) {
          var li_cout = 0;
          $($($("#li_" + selected_user).parent().get(0)).parent().get(0)).find("li").each(function() {
            if ($(this).css("display") == "list-item") li_cout++;
          });
          $("#li_" + selected_user).css("display", "none");
        }
      } else {
        new_id = selected_user.replace("user_group_", "user_group_allowed_");
        denied_id = selected_user.replace("user_group_", "user_group_denied_");
      }
			target_visibility = $("#li_" + new_id).css("display");
      $("#li_" + new_id).css("display", "");
      var arr_id = selected_user.split("-");
      if (arr_id[1] == 0) {
        // Add to array -- allowed
        if (dragged_from_denied) {
					source_opacity = $("#"+selected_user).css("opacity");
					target_opacity = $("#" + new_id).css("opacity");
					new_opacity		 = (target_opacity == "1" && target_visibility != "none") ? "1" : source_opacity;
          $("#" + new_id).css("opacity",new_opacity);
          index = denied_array.indexOf("li_" + selected_user);
          if (index != -1) {
            if (allowed_array.indexOf(new_id) == -1) allowed_array.push("li_" + new_id);
            $("#li_" + new_id).find("li").each(function() {
              child_id = $(this).attr("id");
              index = allowed_array.indexOf(child_id);
              if (index != -1) allowed_array.splice(index, 1);
            });
          } else {
            if (allowed_array.indexOf(new_id) == -1) {
              $("#li_" + selected_user).find("li").each(function() {
                if ($(this).css("display") == "list-item") {
                  id = $(this).attr("id").replace("user_group_denied_", "user_group_allowed_");
                  if (allowed_array.indexOf(id) == -1) {
                    allowed_array.push(id);
                  }
                }
              });
            }
          }
        } else {
          if (allowed_array.indexOf(new_id) == -1) allowed_array.push("li_" + new_id);
          $("#li_" + new_id).find("li").each(function() {
            child_id = $(this).attr("id");
            index = allowed_array.indexOf(child_id);
            if (index != -1) allowed_array.splice(index, 1);
          });
        }

        // Remove From Denied Array
        index = denied_array.indexOf("li_" + denied_id);
        if (index != -1) denied_array.splice(index, 1);
        parent_id = $($($("#li_" + denied_id).parent().get(0)).parent().get(0)).attr("id");
        $("#li_" + denied_id).find("li").each(function() {
          child_id = $(this).attr("id");
          index = denied_array.indexOf(child_id);
          if (index != -1) denied_array.splice(index, 1);
        });

        if (dragged_from_denied) {
          index = denied_array.indexOf("li_" + selected_user);
          if (index != -1) $("#li_" + new_id).find('li').css("display", "");
          else {
            $("#li_" + selected_user).find("li").each(function() {
              if ($(this).css("display") == "list-item") {
                id = $(this).attr("id").replace("user_group_denied_", "user_group_allowed_");
                $("#" + id).css("display", "");
                $("#" + id).find("div:eq(1)").css("opacity", "1");
              }
            });
          }
        } else {
          $("#li_" + new_id).find('li').css("display", "");
          $("#li_" + new_id).find("div:eq(1)").css("opacity", "1");
        }
        $("#li_" + denied_id).find('li').css("display", "none");
        $("#li_" + denied_id).css("display", "none");

      } else {
        // Add to array -- allowed
        parent_id = $($($("#li_" + new_id).parent().get(0)).parent().get(0)).attr("id");
        if (allowed_array.indexOf("li_" + new_id) == -1 && allowed_array.indexOf(parent_id) == -1) allowed_array.push("li_" + new_id);

        $($($("#li_" + new_id).parent().get(0)).parent().get(0)).css("display", "");
        if (new_id.substr(-2) != "-0" && (allowed_array.indexOf(parent_id) == -1 || !dragged_from_denied)) {
          $($($("#li_" + new_id).parent().get(0)).parent().get(0)).find("div:eq(1)").css("opacity", "0.5");
        }
        var li_cout = 0;
        $("#li_" + denied_id).css("display", "none");
        $($($("#li_" + denied_id).parent().get(0)).parent().get(0)).find("li").each(function() {
          if ($(this).css("display") == "list-item") li_cout++;
        });
				if (li_cout == 0 && $("#"+parent_id.replace("li_","").replace("allowed","denied")).css("opacity") != "1") {
          $($($("#li_" + denied_id).parent().get(0)).parent().get(0)).css("display", "none");
        }

        index = denied_array.indexOf("li_" + denied_id);
        if (index != -1) denied_array.splice(index, 1);
      }
      saveAcl();
    }
  });
  $(".droppableDenied").droppable({
    accept: ".draggable2,.draggable3",
    drop: function() {
      var dragged_from_allowed = false;
      if (selected_user.indexOf("user_group_allowed_") != -1) {
        dragged_from_allowed = true;
        new_id = selected_user.replace("user_group_allowed_", "user_group_denied_");
        denied_id = selected_user;
        var arr_id = selected_user.split("-");
        if (arr_id[1] != 0) {
          var li_cout = 0;
          $($($("#li_" + selected_user).parent().get(0)).parent().get(0)).find("li").each(function() {
            if ($(this).css("display") == "list-item") li_cout++;
          });
          $("#li_" + selected_user).css("display", "none");
        }
      } else {
        new_id = selected_user.replace("user_group_", "user_group_denied_");
        denied_id = selected_user.replace("user_group_", "user_group_allowed_");
      }
			
			target_visibility = $("#li_" + new_id).css("display");
      $("#li_" + new_id).css("display", "");
      var arr_id = selected_user.split("-");
      if (arr_id[1] == 0) {
				source_opacity = $("#"+selected_user).css("opacity");
				target_opacity = $("#" + new_id).css("opacity");
				new_opacity		 = (target_opacity == "1" && target_visibility != "none") ? "1" : source_opacity;
				$("#" + new_id).css("opacity",new_opacity);
        // Add to array -- denied
        if (dragged_from_allowed) {
          index = allowed_array.indexOf("li_" + selected_user);
          if (index != -1) {
            if (denied_array.indexOf(new_id) == -1) denied_array.push("li_" + new_id);
            $("#li_" + new_id).find("li").each(function() {
              child_id = $(this).attr("id");
              index = denied_array.indexOf(child_id);
              if (index != -1) denied_array.splice(index, 1);
            });
          } else {
            if (denied_array.indexOf(new_id) == -1) {
              $("#li_" + selected_user).find("li").each(function() {
                if ($(this).css("display") == "list-item") {
                  id = $(this).attr("id").replace("user_group_allowed_", "user_group_denied_");
                  if (denied_array.indexOf(id) == -1) {
                    denied_array.push(id);
                  }
                }
              });
            }
          }
        } else {
          if (denied_array.indexOf(new_id) == -1) denied_array.push("li_" + new_id);
          $("#li_" + new_id).find("li").each(function() {
            child_id = $(this).attr("id");
            index = denied_array.indexOf(child_id);
            if (index != -1) denied_array.splice(index, 1);
          });
        }

        // Remove From Allowed Array
        index = allowed_array.indexOf("li_" + denied_id);
        if (index != -1) allowed_array.splice(index, 1);
        parent_id = $($($("#li_" + denied_id).parent().get(0)).parent().get(0)).attr("id");
        $("#li_" + denied_id).find("li").each(function() {
          child_id = $(this).attr("id");
          index = allowed_array.indexOf(child_id);
          if (index != -1) allowed_array.splice(index, 1);
        });
        if (dragged_from_allowed) {
          index = allowed_array.indexOf("li_" + selected_user);
          if (index != -1) $("#li_" + new_id).find('li').css("display", "");
          else {
            $("#li_" + selected_user).find("li").each(function() {
              if ($(this).css("display") == "list-item") {
                id = $(this).attr("id").replace("user_group_allowed_", "user_group_denied_");
                $("#" + id).css("display", "");
                $("#" + id).find("div:eq(1)").css("opacity", "1");
              }
            });
          }
        } else {
          $("#li_" + new_id).find('li').css("display", "");
          $("#li_" + new_id).find("div:eq(1)").css("opacity", "1");
        }
        $("#li_" + denied_id).find('li').css("display", "none");
        $("#li_" + denied_id).css("display", "none");

      } else {

        // Add to array -- denied
        parent_id = $($($("#li_" + new_id).parent().get(0)).parent().get(0)).attr("id");
        if (denied_array.indexOf("li_" + new_id) == -1 && denied_array.indexOf(parent_id) == -1) denied_array.push("li_" + new_id);

        $($($("#li_" + new_id).parent().get(0)).parent().get(0)).css("display", "");
        if (new_id.substr(-2) != "-0" && (denied_array.indexOf(parent_id) == -1 || !dragged_from_allowed)) {
          $($($("#li_" + new_id).parent().get(0)).parent().get(0)).find("div:eq(1)").css("opacity", "0.5");
        }
        $("#li_" + denied_id).css("display", "none");
        var li_cout = 0;

        $($($("#li_" + denied_id).parent().get(0)).parent().get(0)).find("li").each(function() {
          if ($(this).css("display") == "list-item") li_cout++;
        });
        if (li_cout == 0 && $("#"+parent_id.replace("li_","").replace("denied","allowed")).css("opacity") != "1") {
          $($($("#li_" + denied_id).parent().get(0)).parent().get(0)).css("display", "none");
        }

        index = allowed_array.indexOf("li_" + denied_id);
        if (index != -1) allowed_array.splice(index, 1);
      }
      saveAcl();
    }
  });
  $(".module_check").click(function() {
    var clicked_id = $(this).attr("id");
    var clicked_arr = clicked_id.split("_");
    if ($(this).is(":checked"))
      $(".group_" + clicked_arr[1]).attr("checked", true);
    else
      $(".group_" + clicked_arr[1]).removeAttr("checked");
    saveGroupAcl();
  });
  $(".component_check").click(function() {
    saveGroupAcl();
  });
});

function selectThis(id) {
  $(".selected_componet").removeClass("selected_componet");
  $("#" + id).addClass("selected_componet");
  selected_component = id;
}

function saveAcl() {
  if (selected_component == '') {
    var resultTranslated = js_xl('Select a Component');
    alert(resultTranslated.msg);
    return;
  }
  $(".scrollable").customScrollbar("resize");
  var selected_module = selected_component;
  var allowed_users = JSON.stringify(allowed_array);
  var denied_users = JSON.stringify(denied_array);
  $.ajax({
    type: "POST",
    url: ajax_path,
    dataType: "html",
    data: {
      ajax_mode: 'save_acl',
      selected_module: selected_module,
      allowed_users: allowed_users,
      denied_users: denied_users
    },
    async: false,
    success: function(thedata) {
      var resultTranslated = js_xl('ACL Updated Successfully');
      $("#messages").html(resultTranslated.msg);
      setTimeout(function() {
        $("#messages").html("");
      }, 3000);
    },
  });
}

function rebuild() {
  denied_array = [];
  allowed_array = [];
  $.ajax({
    type: "POST",
    url: ajax_path,
    dataType: "html",
    data: {
      ajax_mode: 'rebuild',
      selected_module: selected_component,
    },
    async: false,
    success: function(thedata) {
      $(".class_li").css("display", "none");
      obj = JSON.parse(thedata);
      // Allowed Groups

      for (var index in obj['group_allowed']) {
        $("#li_user_group_allowed_" + obj['group_allowed'][index] + "-0").css("display", "");
        $("#li_user_group_allowed_" + obj['group_allowed'][index] + "-0").find("li").css("display", "");
        allowed_array.push("li_user_group_allowed_" + obj['group_allowed'][index] + "-0");
      }

      //Denied Groups
      for (var index in obj['group_denied']) {
        $("#li_user_group_denied_" + obj['group_denied'][index] + "-0").css("display", "");
        $("#li_user_group_denied_" + obj['group_denied'][index] + "-0").find("li").css("display", "");
        denied_array.push("li_user_group_denied_" + obj['group_denied'][index] + "-0");
      }

      // Allowed users
      for (var index in obj['user_allowed']) {
        if ($("#li_user_group_allowed_" + index + "-0").css("display") == "none") {
          $("#li_user_group_allowed_" + index + "-0").css("display", "");
          $("#li_user_group_allowed_" + index + "-0").find("div:eq(1)").css("opacity", "0.5");
        }
        for (var k in obj['user_allowed'][index]) {
          $("#li_user_group_allowed_" + index + "-" + obj['user_allowed'][index][k]).css("display", "");
          $("#li_user_group_denied_" + index + "-" + obj['user_allowed'][index][k]).css("display", "none");
          allowed_array.push("li_user_group_allowed_" + index + "-" + obj['user_allowed'][index][k]);
        }
      }
      //Denied users
      for (var index in obj['user_denied']) {
        if ($("#li_user_group_denied_" + index + "-0").css("display") == "none") {
          $("#li_user_group_denied_" + index + "-0").find("div:eq(1)").css("opacity", "0.5");
          $("#li_user_group_denied_" + index + "-0").css("display", "");
        }
        for (var k in obj['user_denied'][index]) {
          $("#li_user_group_denied_" + index + "-" + obj['user_denied'][index][k]).css("display", "");
          $("#li_user_group_allowed_" + index + "-" + obj['user_denied'][index][k]).css("display", "none");
          denied_array.push("li_user_group_denied_" + index + "-" + obj['user_denied'][index][k]);
        }
      }

    }
  });
}

function saveGroupAcl() {
  var ACL_DATA = {};
  ACL_DATA['allowed'] = {};
  ACL_DATA['denied'] = {};
  var i = -1;
  $("#table_acl").find("input:checkbox").each(function() {
    var id = $(this).attr("id");
    id_arr = id.split("_");

    if ($(this).is(":checked")) {
      i++;
      if (!ACL_DATA['allowed'][id_arr[0]]) ACL_DATA['allowed'][id_arr[0]] = {};
      ACL_DATA['allowed'][id_arr[0]][i] = id_arr[1];
    } else {
      i++;
      if (!ACL_DATA['denied'][id_arr[0]]) ACL_DATA['denied'][id_arr[0]] = {};
      ACL_DATA['denied'][id_arr[0]][i] = id_arr[1];
    }
  });
  $.ajax({
    type: "POST",
    url: ajax_path,
    dataType: "html",
    data: {
      ajax_mode: 'save_acl_advanced',
      acl_data: JSON.stringify(ACL_DATA),
      module_id: module_id
    },
    async: false,
    success: function(thedata) {
      var resultTranslated = js_xl('ACL Updated Successfully');
      $("#messages_div").html(resultTranslated.msg);
      setTimeout(function() {
        $("#messages_div").html("");
      }, 3000);
    },
  })
}

function addNewItem(section) {
  $(".popup_items").css('display', 'none');
  $("#add_new_" + section).slideDown();
}

function getSectionById(mod_id) {
  $("#add_component_section_identifier").val("");
  $("#add_component_section_name").val("");
  if (mod_id == "") {
    $("#add_component_section_id").html("");
    return;
  }

  $.ajax({
    type: "POST",
    url: ajax_path,
    dataType: "html",
    data: {
      ajax_mode: 'get_sections_by_module',
      module_id: mod_id,
    },
    async: false,
    success: function(thedata) {
      obj = JSON.parse(thedata);
      out = "<option value=''></option>";
      for (var index in obj) {
        out += "<option value='" + index + "'>" + obj[index] + "</option>";
      }
      $("#add_component_section_id").html(out);
    },
  });
}

function addSectionSave() {
  mod_id = $("#add_component_mod_id").val();
  parent_id = $("#add_component_section_id").val();
  section_identifier = $("#add_component_section_identifier").val();
  section_name = $("#add_component_section_name").val();
  if ($.trim(section_identifier) == '' || $.trim(section_name) == '') {
    var resultTranslated = js_xl('Section ID and Name Cannot be Empty');
    alert(resultTranslated.msg);
    return;
  }
  $.ajax({
    type: "POST",
    url: ajax_path,
    dataType: "html",
    data: {
      ajax_mode: 'save_sections_by_module',
      mod_id: mod_id,
      parent_id: parent_id,
      section_identifier: section_identifier,
      section_name: section_name,
    },
    async: false,
    success: function(thedata) {
      var resultTranslated = js_xl('Section saved successfully');
      $("#add_component_section_message").html(resultTranslated.msg);
      setTimeout(function() {
        $("#add_component_section_message").html("");
      }, 3000);
    },
  });
}