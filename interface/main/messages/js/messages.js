
async function openInternalNotesPopup(urlStrQtr = '', title = '') {
    let dTitle = title != '' ? title : internal_note_title;
	let url = top.webroot_url + '/interface/main/messages/internal_note.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'internalPop', 'modal-md', '580', '', dTitle, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}

async function openEmailPopup(urlStrQtr = '', title = '') {
    let dTitle = title != '' ? title : email_title;
	let url = top.webroot_url + '/interface/main/messages/email_message.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'emailPop', 'modal-lg', '800', '', dTitle, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}

async function openPhonePopup(urlStrQtr = '', title = '') {
	let dTitle = title != '' ? title : phone_title;
    let url = top.webroot_url + '/interface/main/messages/phone_call.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'phonePop', 'modal-lg', '800', '', dTitle, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}

async function openPortalPopup(urlStrQtr = '', title = '') {
    let dTitle = title != '' ? title : portal_title;
	let url = top.webroot_url + '/interface/main/messages/portal_message.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'portalPop', 'modal-lg', '800', '', dTitle, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}

async function openSMSPopup(urlStrQtr = '', title = '') {
    let dTitle = title != '' ? title : sms_title;
	let url = top.webroot_url + '/interface/main/messages/sms_message.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'smsPop', 'modal-lg', '800', '', dTitle, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}

async function openFaxPopup(urlStrQtr = '', title = '') {
    let dTitle = title != '' ? title : fax_title;
	let url = top.webroot_url + '/interface/main/messages/fax_message.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'faxPop', 'modal-lg', '800', '', dTitle, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}

async function openPostalLetterPopup(urlStrQtr = '', title = '') {
    let dTitle = title != '' ? title : postal_letter_title;
	let url = top.webroot_url + '/interface/main/messages/postal_letter.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'postalletterPop', 'modal-lg', '800', '', dTitle, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}

// To view messages by message type.
function loadMessage(type, id, pid) {
    if (type == 'PHONE') openPhonePopup("?pid="+pid+"&id="+id);
    if (type == 'SMS') openSMSPopup("?pid="+pid+"&id="+id);
    if (type == 'EMAIL') openEmailPopup("?pid="+pid+"&id="+id);
    if (type == 'FAX') openFaxPopup("?pid="+pid+"&id="+id);
    if (type == 'P_LETTER') openPostalLetterPopup("?pid="+pid+"&id="+id);
    if (type == 'SMS_REPLY') openSMSPopup("?pid="+pid+"&msgId="+id+"&action=reply");
}

function resendMsgPopup(type, pid, messageId) {
    if(type == "EMAIL" || type == "email") {
        openEmailPopup('?pid='+pid+'&msgId='+messageId+'&action=resend');
    } else if(type == "SMS" || type == "sms") {
        openSMSPopup('?pid='+pid+'&msgId='+messageId+'&action=resend');
    } else if(type == "FAX" || type == "fax") {
        openFaxPopup('?pid='+pid+'&msgId='+messageId+'&action=resend');
    } else if(type == "P_LETTER" || type == "postal_letter") {
        openPostalLetterPopup('?pid='+pid+'&msgId='+messageId+'&action=resend');
    }
}

function replyMsgPopup(type, pid, messageId) {
    if(type == "EMAIL") {
        openEmailPopup('?pid='+pid+'&msgId='+messageId+'&action=reply');
    } else if(type == "SMS") {
        openSMSPopup('?pid='+pid+'&msgId='+messageId+'&action=reply');
    }
}

// To view message.
function setMessage(type, message_id, pid) {
    loadMessage(type, message_id, pid)
}