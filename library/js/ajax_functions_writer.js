// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 3
// of the License, or (at your option) any later version.
// modified to use activeElement for general edit sjpadgett@gmail.com 06/18/2019
//

/**
 *
 * @type {CKEDITOR.ClassicEditor}  This assumes there is a single ajax_functions_writer.js file inside the frame.  If there are multiples this will create problems.
 */
let oeCustomTemplateEditor = null;
/**
 *
 * @param {CKEDITOR.ClassicEditor} editor
 */
function initAjaxFunctionWritersWithEditor(editor) {
    oeCustomTemplateEditor = editor;
}
function moveOptions_11(theSelFrom, theSelTo) {
    if (!oeCustomTemplateEditor) {
        console.error("CKEDITOR instance not found. Verify initAjaxFunctionWritersWithEditor was called");
        return;
    }
    document.getElementById(theSelFrom).style.color = "red";
    document.getElementById(theSelFrom).style.fontStyle = "italic";
    var str = document.getElementById(theSelFrom).innerHTML;
    oeCustomTemplateEditor.model.change(writer => {
        let data = oeCustomTemplateEditor.getData();
        if (data == '<br>') {
            oeCustomTemplateEditor.setData("");
        }
    });
    var patt = /\?\?/;
    var result = patt.test(str);
    if (result) {
        url = 'quest_popup.php?content=' + encodeURIComponent(str);
        // patched out 1/19/24 sjp better to be a dialog.
        //window.open(url, 'quest_pop', 'width=640,height=190,menubar=no,toolbar=0,location=0, directories=0, status=0,left=400,top=375');
        dlgopen(url,'quest_pop', 640, 250, '', '', {
            allowDrag: true,
            allowResize: true,
        });
    } else {
        val = str;
        oeCustomTemplateEditor.model.change(writer => {
            let textNode = writer.createText(val);
            oeCustomTemplateEditor.model.insertContent(textNode);
        });
    }
}

function updateEditorContent(content) {
    if (!oeCustomTemplateEditor) {
        console.error("CKEDITOR instance not found. Verify initAjaxFunctionWritersWithEditor was called");
        return;
    }
    oeCustomTemplateEditor.model.change(writer => {
        let textNode = writer.createText(content);
        oeCustomTemplateEditor.model.insertContent(textNode);
    });
}

function nl2br(str) {
    return str.replace(/(?:\r\n|\r|\n)/g, '<br />');
}

function br2nl(str) {
    return str.replace(/<\s*\/?br\s*[/]?>/gi, "\r\n");
}

/**
 * Retrieves the textbox content to put in the editor window from the calling document page.
 * If the content has a *** in it, it will split the content and put the first part in the editor window.
 * @param id - the id of the textbox to get content from
 * @param ccFlag - if 'id' then use getElementById, otherwise use querySelector with name attribute
 * @returns {string}
 */
function getCallingDocumentEditorContent(id, ccFlag) {
    let val = '';
    if (ccFlag) {
        // text edits
        if (ccFlag === 'id') {
            val = window.opener.document.getElementById(id).value;
        } else {
            // must be name attr.
            val = window.opener.document.querySelector('textarea[name=' + id + ']').value;
            if (val === null) {
                val = window.opener.document.querySelector("input[name=" + id + "]").value;
            }
        }
        document.getElementById('textarea1').value = nl2br(val);
        return;
    } else {
        val = window.opener.document.getElementById(id).value;
    }
    arr = val.split("|*|*|*|");
    return arr[0];
}
function edit(id, ccFlag = '') {

    document.getElementById('textarea1').value = getCallingDocumentEditorContent(id, ccFlag);
}

function SelectToSave(textara, ccFlag = '', contentToSave = "") {
    let textAreaContent = '';
    let mainForm = window.opener.document;
    if (ccFlag) {
        // text templates
        let textEl = '';
        textAreaContent = contentToSave;
        if (ccFlag === 'id') {
            textEl = mainForm.getElementById(textara);
            if (textEl === null) {
                alert(xl('Can not find where to insert.'));
                return;
            }
        } else {
            textEl = mainForm.querySelector('textarea[name=' + textara + ']');
            if (textEl === null) {
                // maybe input for sentence
                textEl = mainForm.querySelector("input[name=" + textara + "]");
            }
        }
        textEl.value = jsText(br2nl(textAreaContent));
    } else {
        // must be html for nation note.
        textAreaContent = contentToSave;
        if (mainForm.getElementById(textara + '_div'))
            mainForm.getElementById(textara + '_div').innerHTML = textAreaContent;
        if (mainForm.getElementById(textara + '_optionTD') && document.getElementById('options'))
            mainForm.getElementById(textara + '_optionTD').innerHTML = document.getElementById('options').innerHTML;
        if (mainForm.getElementById(textara)) {
            mainForm.getElementById(textara).value = textAreaContent;
            if (document.getElementById('options')) {
                mainForm.getElementById(textara).value += "|*|*|*|" + document.getElementById('options').innerHTML;
            }
        }
    }
    // close our dialog however insert turns out.
    dlgclose();
}

function supportDragAndDrop(thedata) {
    let tempEl = document.createElement('div'), finalEl = '';
    tempEl.innerHTML = thedata;
    for (let i = 0; i < tempEl.children.length; i++) {
        let ele = tempEl.children[i], temp;
        if (ele.id) {
            ele.classList.add("draggable")
            temp = document.createElement('div')
            temp.classList.add("droppable");
            temp.appendChild(ele.cloneNode(true));
            finalEl += temp.outerHTML;
        } else {
            finalEl += ele.outerHTML;
        }
    }
    return finalEl;
}

function TemplateSentence(val) {
    if (val) {
        document.getElementById('share').style.display = '';
    } else {
        document.getElementById('share').style.display = 'none';
    }
    $.ajax({
        type: "POST",
        url: "ajax_code.php",
        dataType: "html",
        data: {
            templateid: val
        },
        success: function (thedata) {
            //alert(thedata)
            document.getElementById('template_sentence').innerHTML = supportDragAndDrop(thedata);
        },
        error: function () {
            //alert("fail");
        }
    });
    return;
}

function delete_item(id) {
    //alert(id);
    if (confirm("Do you really wants to delete this?")) {
        $.ajax({
            type: "POST",
            url: "ajax_code.php",
            dataType: "html",
            data: {
                templateid: document.getElementById('template').value,
                item: id,
                source: "delete_item"
            },
            success: function (thedata) {
                //alert(thedata)
                document.getElementById('template_sentence').innerHTML = supportDragAndDrop(thedata);
            },
            error: function () {
                //alert("fail");
            }
        });
        return;
    }
    return false;
}

function add_item() {
    document.getElementById('new_item').style.display = '';
    document.getElementById('item').focus();
}

function cancel_item(id) {
    if (document.getElementById('new_item'))
        document.getElementById('new_item').style.display = 'none';
    if (document.getElementById('update_item' + id))
        document.getElementById('update_item' + id).style.display = 'none';
}

function save_item() {
    $.ajax({
        type: "POST",
        url: "ajax_code.php",
        dataType: "html",
        data: {
            item: document.getElementById('item').value,
            templateid: document.getElementById('template').value,
            source: "add_item"

        },
        success: function (thedata) {
            //alert(thedata)
            document.getElementById('template_sentence').innerHTML = supportDragAndDrop(thedata);
            cancel_item('');
        },
        error: function () {
            //alert("fail");
        }
    });
    return;
}

function update_item_div(id) {
    document.getElementById('update_item' + id).style.display = '';
    document.getElementById('update_item_txt' + id).focus();
}

function update_item(id) {
    $.ajax({
        type: "POST",
        url: "ajax_code.php",
        dataType: "html",
        data: {
            item: id,
            templateid: document.getElementById('template').value,
            content: document.getElementById('update_item_txt' + id).value,
            source: "update_item"

        },
        success: function (thedata) {
            //alert(thedata)
            document.getElementById('template_sentence').innerHTML = supportDragAndDrop(thedata);
            cancel_item(id);
        },
        error: function () {
            //alert("fail");
        }
    });
    return;
}
