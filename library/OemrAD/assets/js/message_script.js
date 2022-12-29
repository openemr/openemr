
async function openInternalNotesPopup(urlStrQtr = '', title = '') {
	let url = top.webroot_url + '/interface/main/messages/internal_note.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'internalPop', 'modal-md', '580', '', title, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}

async function openEmailPopup(urlStrQtr = '', title = '') {
	let url = top.webroot_url + '/interface/main/messages/email_message.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'emailPop', 'modal-lg', '800', '', title, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}

async function openPhonePopup(urlStrQtr = '', title = '') {
	let url = top.webroot_url + '/interface/main/messages/phone_call.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'phonePop', 'modal-lg', '800', '', title, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}

async function openPortalPopup(urlStrQtr = '', title = '') {
	let url = top.webroot_url + '/interface/main/messages/portal_message.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'portalPop', 'modal-lg', '800', '', title, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}

async function openSMSPopup(urlStrQtr = '', title = '') {
	let url = top.webroot_url + '/interface/main/messages/sms_message.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'smsPop', 'modal-lg', '800', '', title, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}

async function openFaxPopup(urlStrQtr = '', title = '') {
	let url = top.webroot_url + '/interface/main/messages/fax_message.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'faxPop', 'modal-lg', '800', '', title, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}

async function openPostalLetterPopup(urlStrQtr = '', title = '') {
	let url = top.webroot_url + '/interface/main/messages/postal_letter.php' + urlStrQtr;
	let dialogObj = await dlgopen(url, 'postalletterPop', 'modal-lg', '800', '', title, {
        allowDrag: false,
        allowResize: false
    });

    // loader
    dialogLoader(dialogObj.modalwin);
}