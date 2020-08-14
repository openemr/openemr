// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 3
// of the License, or (at your option) any later version.
// modified to use activeElement for general edit sjpadgett@gmail.com 06/18/2019
//

function moveOptions_11(theSelFrom, theSelTo) {
    document.getElementById(theSelFrom).style.color = 'red';
    document.getElementById(theSelFrom).style.fontStyle = 'italic';
    const str = document.getElementById(theSelFrom).innerHTML;
    if (window.frames[0].document.body.innerHTML === '<br />') {
        window.frames[0].document.body.innerHTML = '';
    }
    const patt = /\?\?/;
    const result = patt.test(str);
    if (result) {
        url = `quest_popup.php?content=${str}`;
        window.open(url, 'quest_pop', 'width=640,height=190,menubar=no,toolbar=0,location=0, directories=0, status=0,left=400,top=375');
        // dlgopen(url,'quest_pop', '', 640, 190);
    } else {
        val = str;
        CKEDITOR.instances.textarea1.insertText(val);
    }
}

function movePD(val, theSelTo) {
    let textAreaContent = window.frames[0].document.body.innerHTML;
    const textFrom = val;
    if (textAreaContent !== '') {
        textAreaContent += `  ${textFrom}`;
    } else {
        textAreaContent += textFrom;
    }
    window.frames[0].document.body.innerHTML = textAreaContent;
}

function edit(id, ccFlag = '') {
    let val = '';
    if (ccFlag) {
        if (ccFlag === 'id') {
            val = window.opener.document.getElementById(id).value;
        } else {
            // must be name attr.
            val = window.opener.document.querySelector(`textarea[name=${id}]`).value;
            if (val === null) {
                val = window.opener.document.querySelector(`input[name=${id}]`).value;
            }
        }
    } else {
        val = window.opener.document.getElementById(id).value;
    }

    arr = val.split('|*|*|*|');
    [document.getElementById('textarea1').value] = arr;
}

function ascii_write(asc, theSelTo) {
    const isChrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
    const isIe = navigator.userAgent.toLowerCase().indexOf('msie') > -1;
    const isApple = navigator.userAgent.toLowerCase().indexOf('apple') > -1;

    if (asc === 13) {
        if (!isIe) {
            const plugin = CKEDITOR.plugins.enterkey;
            const { enterBr } = plugin;
            const editor = CKEDITOR.instances.textarea1;
            forceMode = editor.config.forceEnterMode;
            mode = editor.config.enterMode;
            editor.fire('saveSnapshot'); // Save undo step.
            enterBr(editor, mode, null, forceMode);
            if (isChrome || isApple) {
                enterBr(editor, mode, null, forceMode);
            }
        } else {
            CKEDITOR.instances.textarea1.insertText('\r\n');
        }
    } else if (asc === 'para') {
        const textFrom = '\r\n\r\n';
        CKEDITOR.instances.textarea1.insertText(textFrom);
        if (isChrome || isApple) {
            CKEDITOR.instances.textarea1.insertText(textFrom);
        }
    } else {
        if (asc === 32) {
            const textFrom = '  ';
        } else {
            const textFrom = String.fromCharCode(asc);
        }
        CKEDITOR.instances.textarea1.insertText(textFrom);
    }
}
function SelectToSave(textara, ccFlag = '') {
    let textAreaContent = '';
    mainform = window.opener.document;
    if (ccFlag) {
        textAreaContent = window.frames[0].document.body.textContent;
        if (ccFlag === 'id') {
            val = textAreaContent;
            mainform.getElementById(textara).value = textAreaContent;
        } else {
            val = textAreaContent;
            mainform.querySelector(`textarea[name=${textara}]`).value = textAreaContent;
            if (val === null) {
                val = textAreaContent;
                mainform.querySelector(`input[name=${textara}]`).value = textAreaContent;
            }
        }
    } else {
        textAreaContent = window.frames[0].document.body.innerHTML;
        if (mainform.getElementById(`${textara}_div`)) {
            mainform.getElementById(`${textara}_div`).innerHTML = textAreaContent;
        }
        if (mainform.getElementById(`${textara}_optionTD`) && document.getElementById('options')) {
            mainform.getElementById(`${textara}_optionTD`).innerHTML = document.getElementById('options').innerHTML;
        }

        if (mainform.getElementById(textara)) {
            mainform.getElementById(textara).value = textAreaContent;
            if (document.getElementById('options')) {
                mainform.getElementById(textara).value += `|*|*|*|${document.getElementById('options').innerHTML}`;
            }
        }
    }
    dlgclose();
}

function removeHTMLTags(strInputCode) {
    /*
        This line is optional, it replaces escaped brackets with real ones,
        i.e. < is replaced with < and > is replaced with >
    */
    strInputCode = strInputCode.replace(/&(lt|gt);/g, function (strMatch, p1) {
        return (p1 === 'lt') ? '<' : '>';
    });
    const strTagStrippedText = strInputCode.replace(/<\/?[^>]+(>|$)/g, '');
}

function supportDragAndDrop(thedata) {
    const tempEl = document.createElement('div');
    let finalEl = '';
    tempEl.innerHTML = thedata;
    for (let i = 0; i < tempEl.children.length; i += 1) {
        const ele = tempEl.children[i];
        let temp;
        if (ele.id) {
            ele.classList.add('draggable');
            temp = document.createElement('div');
            temp.classList.add('droppable');
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
        type: 'POST',
        url: 'ajax_code.php',
        dataType: 'html',
        data: {
            templateid: val,
        },
        success: (thedata) => {
            // alert(thedata)
            document.getElementById('template_sentence').innerHTML = supportDragAndDrop(thedata);
        },
        error: () => {
            // alert("fail");
        },
    });
}

function delete_item(id) {
    // alert(id);
    if (window.confirm('Do you really wants to delete this?')) {
        $.ajax({
            type: 'POST',
            url: 'ajax_code.php',
            dataType: 'html',
            data: {
                templateid: document.getElementById('template').value,
                item: id,
                source: 'delete_item',
            },
            success: (thedata) => {
                // alert(thedata)
                document.getElementById('template_sentence').innerHTML = supportDragAndDrop(thedata);
            },
            error: () => {
                // alert("fail");
            },
        });
    }
}

function add_item() {
    document.getElementById('new_item').style.display = '';
    document.getElementById('item').focus();
}

function cancel_item(id) {
    if (document.getElementById('new_item')) {
        document.getElementById('new_item').style.display = 'none';
    }

    if (document.getElementById(`update_item${id}`)) {
        document.getElementById(`update_item${id}`).style.display = 'none';
    }
}

function save_item() {
    $.ajax({
        type: 'POST',
        url: 'ajax_code.php',
        dataType: 'html',
        data: {
            item: document.getElementById('item').value,
            templateid: document.getElementById('template').value,
            source: 'add_item',
        },
        success: (thedata) => {
            // alert(thedata)
            document.getElementById('template_sentence').innerHTML = supportDragAndDrop(thedata);
            cancel_item('');
        },
        error: () => {
            // alert("fail");
        },
    });
}

function update_item_div(id) {
    document.getElementById(`update_item${id}`).style.display = '';
    document.getElementById(`update_item_txt${id}`).focus();
}

function update_item(id) {
    $.ajax({
        type: 'POST',
        url: 'ajax_code.php',
        dataType: 'html',
        data: {
            item: id,
            templateid: document.getElementById('template').value,
            content: document.getElementById(`update_item_txt${id}`).value,
            source: 'update_item',
        },
        success: (thedata) => {
            // alert(thedata)
            document.getElementById('template_sentence').innerHTML = supportDragAndDrop(thedata);
            cancel_item(id);
        },
        error: () => {
            // alert("fail");
        },
    });
}
