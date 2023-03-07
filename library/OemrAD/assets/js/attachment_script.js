// File Attachments
$.fn.attachment = function (opts = {}) {
    if(this[0] == undefined) return false;

    this.docsList = opts.docsList ? opts.docsList : [];
    this.attachments = opts.attachments ? opts.attachments : [];
    this.clickable_link = opts.clickable_link ? opts.clickable_link : false;

    this.selectedFileList = [];
    this.demoins_inc_demographic = null;
    
    let fileIdCounter = 0;
    const attachmentEvent = new Event('change');

    this.fileUploader = function(evt) {
        var output = [];
        for (var i = 0; i < evt.target.files.length; i++) {
            fileIdCounter++;
            var file = evt.target.files[i];
            var fileId = 'file_' + fileIdCounter;

            this.selectedFileList.push({
                type: 'files',
                id: fileId,
                file: file
            });
        }

        evt.target.value = null;

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    };

    this.fileUploaderRemoveFile = function(itemId) {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    tempThis.selectedFileList.splice(index, 1);
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.fileUploaderPrepareFile = function() {
        let itemList = [];
        let tempThis = this;

        let ulClass = opts['ulClass'] == undefined ? "list-group" : opts['ulClass'];
        let liClass = opts['liClass'] == undefined ? "list-group-item list-group-item-primary px-3 py-1" : opts['liClass'];
        let defaultliClass = opts['defaultLiClass'] == undefined ? "list-group-item list-group-item-primary px-4 py-2.5" : opts['defaultLiClass'];
        let typeClass = opts['typeClass'] == undefined ? "badge badge-dark" : opts['typeClass'];

        //Replace exsting content;
        tempThis[0].innerHTML = "";

        //Ul Item
        let ulItem = document.createElement('ul');
        ulItem.className = ulClass;

        this.selectedFileList.forEach(function (item, index) {
            // Skip iteration 
            if(item['hidden'] != undefined && item['hidden'] === true) {
                return;
            }

            if(item.type == "files") {
                //Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.fileUploaderRemoveFile(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;
                liItem.innerHTML = "<span><span>" + escape(item.file.name) + "</span> - <span>" + item.file.size + " bytes.&nbsp;<span class=\"" + typeClass + "\">File</span>&nbsp;</span> - </span>";
                liItem.appendChild(removeLink);

                ulItem.appendChild(liItem);
            } if(item.type == "local_files") {
                let itemData = item.item ? item.item : {};
                let iData = itemData['data'] ? itemData['data'] : {};

                // Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.fileLocalFileRemoveFile(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;
                liItem.innerHTML = "<span><span>" + escape(iData.file_name) + "</span> <span class=\"" + typeClass + "\">File</span>&nbsp - </span>";
                liItem.appendChild(removeLink);

                ulItem.appendChild(liItem);
            } else if(item.type == "documents") {
                let itemData = item.item ? item.item : {};

                //Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.removeDocument(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;
                let clickFun = tempThis.clickable_link === true ? "handlegotoReport('"+itemData.data['doc_id']+"','"+itemData['pid']+"')" : "";

                liItem.innerHTML = "<span><a href=\"javascript:void(0);\" onClick=\""+clickFun+"\">" + itemData['text_title'] + "</a>&nbsp;<span class=\"" + typeClass + "\">Document</span>&nbsp;- </span>";
                liItem.appendChild(removeLink);

                ulItem.appendChild(liItem);
            } else if(item.type == "encounters") {
                let itemData = item.item ? item.item : {};

                //Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.removeEncounter(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;

                let clickFun = tempThis.clickable_link === true ? "handleGoToEncounter('"+itemData.data['encounter_id']+"','"+itemData['pid']+"')" : "";
                
                liItem.innerHTML = "<span><a href=\"javascript:void(0);\" onClick=\""+clickFun+"\">" + removeBackslash(itemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Encounter</span>&nbsp;- </span>";
                liItem.appendChild(removeLink);

                ulItem.appendChild(liItem);
            } else if(item.type == "encounter_forms") {
                let itemData = item.item ? item.item : {};
                let formid = itemData['id'] ? itemData['id'] : '';

                if(itemData['parentId'] == undefined) {
                    //Item remove link.
                    let removeLink = document.createElement('a');
                    removeLink.innerHTML = 'Remove';
                    removeLink.href = "javascript:void(0);";
                    removeLink.onclick = () => {
                        tempThis.removeEncounterForm(item.id);
                    }

                    //Li items.
                    let liItem = document.createElement('li');
                    liItem.className = liClass;

                    let clickFun = tempThis.clickable_link === true ? "handleGoToEncounter('"+itemData.data['formid']+"','"+itemData['pid']+"')" : "";

                    liItem.innerHTML = "<span><a href=\"javascript:void(0);\">" + removeBackslash(itemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Encounter Form</span>&nbsp;- </span>";

                    //Generate Child

                    //Child Ul Item
                    let isChildExists = false;
                    let culItem = document.createElement('ul');
                    //culItem.className = ulClass;

                    tempThis.selectedFileList.forEach(function (cItem, cIndex) {
                        if(cItem.type == "encounter_forms") {
                            let cItemData = cItem.item ? cItem.item : {};
                            if(cItemData['parentId'] == formid) {
                                //Child Item remove link.
                                let cremoveLink = document.createElement('a');
                                cremoveLink.innerHTML = 'Remove';
                                cremoveLink.href = "javascript:void(0);";
                                cremoveLink.onclick = () => {
                                    tempThis.removeEncounterForm(cItem.id);
                                }

                                //Child Li items.
                                let cliItem = document.createElement('li');
                                //cliItem.className = liClass;
                                cliItem.innerHTML = "<span><a href=\"javascript:void(0);\">" + removeBackslash(cItemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Encounter Form</span>&nbsp;- </span>";
                                cliItem.appendChild(cremoveLink);
                                culItem.appendChild(cliItem);

                                isChildExists = true;
                            }
                        }
                    });

                    liItem.appendChild(removeLink);

                    //Add Child
                    if(isChildExists === true) {
                        liItem.appendChild(culItem);
                    }

                    ulItem.appendChild(liItem);
                }
            } else if(item.type == "messages") {
                let itemData = item.item ? item.item : {};

                //Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.removeMessage(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;

                let clickFun = tempThis.clickable_link === true ? "handleGoToMessage('"+itemData.data['message_id']+"','"+itemData['pid']+"')" : "";

                liItem.innerHTML = "<span><a href=\"javascript:void(0);\" onClick=\""+clickFun+"\">" + removeBackslash(itemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Message</span>&nbsp;- </span>";
                liItem.appendChild(removeLink);

                ulItem.appendChild(liItem);
            } else if(item.type == "orders") {
                let itemData = item.item ? item.item : {};

                //Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.removeOrder(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;

                let clickFun = tempThis.clickable_link === true ? "handleGoToOrder('"+itemData.data['order_id']+"','"+itemData['pid']+"')" : "";

                liItem.innerHTML = "<span><a href=\"javascript:void(0);\" onClick=\""+clickFun+"\">" + removeBackslash(itemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Order</span>&nbsp;- </span>";
                liItem.appendChild(removeLink);

                ulItem.appendChild(liItem);
            } else if(item.type == "demos_insurances") {
                let itemData = item.item ? item.item : {};

                //Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.removeDemosIns(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;
                let clickFun = "";
                liItem.innerHTML = "<span><a href=\"javascript:void(0);\" onClick=\""+clickFun+"\">" + removeBackslash(itemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Demos & Ins</span>&nbsp;- </span>";
                liItem.appendChild(removeLink);

                //Generate Child

                //Child Ul Item
                let isChildExists = false;
                let culItem = document.createElement('ul');
                
                if(itemData['childs']) {
                    let iChilds = itemData['childs'];
                    Object.keys(iChilds).forEach(function(key) {
                        let cItemData = iChilds[key];
                        
                        //Child Item remove link.
                        let cremoveLink = document.createElement('a');
                        cremoveLink.innerHTML = 'Remove';
                        cremoveLink.href = "javascript:void(0);";
                        cremoveLink.onclick = () => {
                            tempThis.removeDemosIns(item.id, key);
                        }

                        //Child Li items.
                        let cliItem = document.createElement('li');
                        //cliItem.className = liClass;
                        cliItem.innerHTML = "<span><a href=\"javascript:void(0);\">" + removeBackslash(cItemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Demos & Ins</span>&nbsp;- </span>";
                        cliItem.appendChild(cremoveLink);
                        culItem.appendChild(cliItem);

                        isChildExists = true;
                    });
                }

                //Add Child
                if(isChildExists === true) {
                    liItem.appendChild(culItem);
                }

                ulItem.appendChild(liItem);
            }
        });
    
        // Filter hidden items
        let tSelectedFileList = this.selectedFileList.filter((item) => {
            if(item['hidden'] == undefined || item['hidden'] === false) {
                return item;
            }
        });

        if(tSelectedFileList.length > 0) {
            tempThis[0].appendChild(ulItem);
        } else {
            if(opts.empty_title != "") {
                tempThis[0].innerHTML = "<ul class=\"" + ulClass + "\"><li class=\"" + defaultliClass + "\">" + opts.empty_title + "</li></ul>"
            }
        }
    };

    this.handleDocument = async function(pid) {
        let url = top.webroot_url + "/interface/main/attachment/msg_select_document.php?pid="+pid;
        let dialogObj = "";

        dialogObj = await dlgopen(url,'selectDocPop', 'modal-mlg', '', '', 'Documents', {
            buttons: [
                {text: 'Submit', close: false, click: () => { this.handleDocumentCallBack(dialogObj) }, style: 'primary documentsaveBtn btn-sm'},
                {text: 'Close', close: true, style: 'secondary btn-sm'}
            ],
            sizeHeight: 'full',
            onClosed: '',
            type: 'iframe',
            callBack: {call : '', args : pid}
        });

        //Set Values
        this.setValues(dialogObj.modalwin, this.getItemsList('documents'));

        dialogLoader(dialogObj.modalwin);
    }

    this.handleDocumentCallBack = function(dialogObj) {
        if(dialogObj.modalwin) {
            let type = 'documents';
            let iframeContent = this.getIframeContentWindow(dialogObj.modalwin);
            let documentList = iframeContent.getSelectedDocumentList();
            let tempThis = this;

            this.removeAllItems(type);

            documentList.forEach(function (item, idx) {
                fileIdCounter++;
                let fileId = type + '_' + fileIdCounter;

                tempThis.selectedFileList.push({
                    type: type,
                    id: fileId,
                    item: item,
                    row_item: [item]
                });
            });

            dialogObj.dlgContainer.modal('hide');
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.removeDocument = function(itemId) {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    tempThis.selectedFileList.splice(index, 1);
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.handleEncounter = async function(pid) {
        let url = top.webroot_url + "/interface/main/attachment/msg_select_encounter.php?pid="+pid;
        let dialogObj = "";

        dialogObj = await dlgopen(url,'selectEncounterPop', 'modal-mlg', '', '', 'Encounter', {
            buttons: [
                {text: 'Submit', close: false, click: () => { this.handleEncounterCallBack(dialogObj) }, style: 'primary documentsaveBtn btn-sm'},
                {text: 'Close', close: true, style: 'secondary btn-sm'}
            ],
            sizeHeight: 'full',
            onClosed: '',
            type: 'iframe',
            callBack: {call : '', args : pid}
        });

        //Set Values
        this.setValues(dialogObj.modalwin, this.getItemsList('encounters'));

        dialogLoader(dialogObj.modalwin);
    }

    this.handleEncounterCallBack = function(dialogObj) {
        if(dialogObj.modalwin) {
            let type = 'encounters';
            let iframeContent = this.getIframeContentWindow(dialogObj.modalwin);
            let encounterList = iframeContent.getSelectedEncounterList();
            let tempThis = this;

            this.removeAllItems(type);

            encounterList.forEach(function (item, idx) {
                fileIdCounter++;
                let fileId = type + '_' + fileIdCounter;

                tempThis.selectedFileList.push({
                    type: type,
                    id: fileId,
                    item: item,
                    row_item: [item]
                });
            });

            dialogObj.dlgContainer.modal('hide');
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.removeEncounter = function(itemId) {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    tempThis.selectedFileList.splice(index, 1);
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.handleMessage = async function(pid, opts = {}) {
        let url = top.webroot_url + "/interface/main/attachment/msg_select_messages.php?pid="+pid+"&assigned_to="+opts?.assigned_to;
        let dialogObj = "";

        dialogObj = await dlgopen(url,'selectMsgPop', 'modal-mlg', '', '', 'Message', {
            buttons: [
                {text: 'Submit', close: false, click: () => { this.handleMessageCallBack(dialogObj) }, style: 'primary documentsaveBtn btn-sm'},
                {text: 'Close', close: true, style: 'secondary btn-sm'}
            ],
            sizeHeight: 'full',
            onClosed: '',
            type: 'iframe',
            callBack: {call : '', args : pid}
        });

        //Set Values
        this.setValues(dialogObj.modalwin, this.getItemsList('messages'));

        dialogLoader(dialogObj.modalwin);
    }

    this.handleMessageCallBack = function(dialogObj) {
        if(dialogObj.modalwin) {
            let type = 'messages';
            let iframeContent = this.getIframeContentWindow(dialogObj.modalwin);
            let messageList = iframeContent.getSelectedMessageList();
            let tempThis = this;

            this.removeAllItems(type);

            messageList.forEach(function (item, idx) {
                fileIdCounter++;
                let fileId = type + '_' + fileIdCounter;

                tempThis.selectedFileList.push({
                    type: type,
                    id: fileId,
                    item: item,
                    row_item: [item]
                });
            });

            dialogObj.dlgContainer.modal('hide');
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.removeMessage = function(itemId) {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    tempThis.selectedFileList.splice(index, 1);
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.handleOrder = async function(pid) {
        let url = top.webroot_url + "/interface/main/attachment/msg_select_order.php?pid="+pid;
        let dialogObj = "";

        dialogObj = await dlgopen(url,'selectOrderPop', 'modal-mlg', '', '', 'Order', {
            buttons: [
                {text: 'Submit', close: false, click: () => { this.handleOrderCallBack(dialogObj) }, style: 'primary documentsaveBtn btn-sm'},
                {text: 'Close', close: true, style: 'secondary btn-sm'}
            ],
            sizeHeight: 'full',
            onClosed: '',
            type: 'iframe',
            callBack: {call : '', args : pid}
        });

        //Set Values
        this.setValues(dialogObj.modalwin, this.getItemsList('orders'));

        dialogLoader(dialogObj.modalwin);
    }

    this.handleOrderCallBack = function(dialogObj) {
        if(dialogObj.modalwin) {
            let type = 'orders';
            let iframeContent = this.getIframeContentWindow(dialogObj.modalwin);
            let orderList = iframeContent.getSelectedOrderList();
            let tempThis = this;

            this.removeAllItems(type);

            orderList.forEach(function (item, idx) {
                fileIdCounter++;
                let fileId = type + '_' + fileIdCounter;

                tempThis.selectedFileList.push({
                    type: type,
                    id: fileId,
                    item: item,
                    row_item: [item]
                });
            });

            dialogObj.dlgContainer.modal('hide');
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.removeOrder = function(itemId) {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    tempThis.selectedFileList.splice(index, 1);
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.handleEncounterForm = async function(pid) {
        let url = top.webroot_url + "/interface/main/attachment/msg_select_encounter_form.php?pid="+pid;
        let dialogObj = "";

        dialogObj = await dlgopen(url,'selectEncounterFormPop', 'modal-mlg', '', '', 'Encounters & Forms', {
            buttons: [
                {text: 'Submit', close: false, click: () => { this.handleEncounterFormCallBack(dialogObj) }, style: 'primary documentsaveBtn btn-sm'},
                {text: 'Close', close: true, style: 'secondary btn-sm'}
            ],
            sizeHeight: 'full',
            onClosed: '',
            type: 'iframe',
            callBack: {call : '', args : pid}
        });

        //Set Values
        this.setValues(dialogObj.modalwin, this.getItemsList('encounter_forms'));

        dialogLoader(dialogObj.modalwin);
    }

    this.handleEncounterFormCallBack = function(dialogObj) {
        if(dialogObj.modalwin) {
            let type = 'encounter_forms';
            let iframeContent = this.getIframeContentWindow(dialogObj.modalwin);
            let encounterFormList = iframeContent.getSelectedEncounterFormList();
            let tempThis = this;

            this.removeAllItems(type);

            encounterFormList.forEach(function (item, idx) {
                fileIdCounter++;
                let fileId = type + '_' + fileIdCounter;

                tempThis.selectedFileList.push({
                    type: type,
                    id: fileId,
                    item: item,
                    row_item: [item]
                });
            });

            dialogObj.dlgContainer.modal('hide');
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.removeEncounterForm = function(itemId) {
        let tempThis = this;
        let deleteItems = [];

        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    let itemData = item.item ? item.item : {};
                    let formid = itemData['id'] ? itemData['id'] : '';

                    deleteItems.push(index);

                    if(itemData['parentId'] == undefined && formid != '') {
                        tempThis.selectedFileList.forEach(function (citem, cindex) {
                            if(citem.type == "encounter_forms") {
                                let cItemData = citem.item ? citem.item : {};
                                if(cItemData['parentId'] == formid) {
                                    deleteItems.push(cindex);
                                }
                            }
                        });
                    }
                }
            }
        });

        for (var i = deleteItems.length -1; i >= 0; i--) {
            tempThis.selectedFileList.splice(deleteItems[i], 1);
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.handleDemosIns = async function(pid) {
        let url = top.webroot_url + "/interface/main/attachment/msg_select_demos_ins.php?pid="+pid;
        let dialogObj = "";

        dialogObj = await dlgopen(url,'selectDemosInsPop', 'modal-mlg', '', '', 'Demos & Insurances', {
            buttons: [
                {text: 'Submit', close: false, click: () => { this.handleDemosInsCallBack(dialogObj) }, style: 'primary documentsaveBtn btn-sm'},
                {text: 'Close', close: true, style: 'secondary btn-sm'}
            ],
            sizeHeight: 'full',
            onClosed: '',
            type: 'iframe',
            callBack: {call : '', args : pid}
        });

        //Set Values
        this.setValues(dialogObj.modalwin, this.getItemsList('demos_insurances'));

        if(dialogObj.modalwin && this.demoins_inc_demographic != null) {
            $(dialogObj.modalwin).find('iframe')[0].contentWindow.demoins_inc_demographic = this.demoins_inc_demographic;
        }

        dialogLoader(dialogObj.modalwin);
    }

    this.handleDemosInsCallBack = function(dialogObj) {
        if(dialogObj.modalwin) {
            let type = 'demos_insurances';
            let iframeContent = this.getIframeContentWindow(dialogObj.modalwin);
            let demoInsList = iframeContent.getSelectedDemoInsList();
            let tempThis = this;

            this.demoins_inc_demographic = iframeContent.getNeedToIncludeDemographic();

            this.removeAllItems(type);

            demoInsList.forEach(function (item, idx) {
                fileIdCounter++;
                let fileId = type + '_' + fileIdCounter;

                tempThis.selectedFileList.push({
                    type: type,
                    id: fileId,
                    item: item,
                    row_item: [item]
                });
            });

            dialogObj.dlgContainer.modal('hide');
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.removeDemosIns = function(itemId, cItemId = '') {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    if(itemId != '' && cItemId != '') {
                        let itemData = item['item'] ? item['item'] : {};
                        if(itemData['childs']) {
                            let iChilds = itemData['childs'];
                            Object.keys(iChilds).forEach(function(key) {
                                let cItemData = iChilds[key];

                                if(key == cItemId) {
                                    delete tempThis.selectedFileList[index]['item']['childs'][cItemId];
                                    delete tempThis.selectedFileList[index]['row_item'][0]['childs'][cItemId];
                                }
                            });

                            if(Object.keys(tempThis.selectedFileList[index]['item']['childs']).length === 0) {
                                tempThis.selectedFileList.splice(index, 1);
                            }
                        }
                    } else {
                        tempThis.selectedFileList.splice(index, 1);
                    }
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.fileLocalFileRemoveFile = function(itemId) {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    tempThis.selectedFileList.splice(index, 1);
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    //Remove all items by type.
    this.removeAllItems = function(type) {
        let tempThis = this;

        //Remove all.
        const items = this.selectedFileList.filter(function (item) {
            if(item.type != type) { 
                return item;
            }
        });

        this.selectedFileList = items;
    }

    // Get items list by type.
    this.getItemsList = function(type) {
        if(["documents", "encounters", "messages", "orders", "encounter_forms", "demos_insurances", "local_files"].includes(type)) {
            let items = [];
            this.selectedFileList.forEach(function (item, index) {
                if(item.type == type) {
                    if(Array.isArray(item['row_item'])) {
                        items = items.concat(item.row_item);
                    }
                }
            });
            return items;
        } else {
            let items = {};
            this.selectedFileList.forEach(function (item, index) {
                if(item.type == type) {
                    if(item['row_item'] != '') {
                        items = {...items, ...item.row_item};
                    }
                }
            });
            return items;
        }
    }

    // Get items list by type.
    this.getItemsDataList = function(type) {
        if(type == "files") {
            let items = [];
            this.selectedFileList.forEach(function (item, index) {
                if(item.type == type) {
                    //if(Array.isArray(item['file'])) {
                        items = items.concat([{file: item.file}]);
                    //}
                }
            });
            return items;
        } if(type == "demos_insurances") {
            let items = [];
            this.selectedFileList.forEach(function (item, index) {
                if(item.type == type) {
                    if(Array.isArray(item['row_item']) && item['row_item'][0]['data']) {
                        let nData = item.row_item[0].data;
                        let nChildsData = item.row_item[0].childs;

                        if(nData) {
                            nData['childs'] = {};
                            Object.keys(nChildsData).forEach(function(key) {
                                nData['childs'][key] = nChildsData[key].data;
                            });
                        }
                        items = items.concat([nData]);
                    }
                }
            });
            return items;
        } if(["documents", "encounters", "messages", "orders", "encounter_forms", "local_files"].includes(type)) {
            let items = [];
            this.selectedFileList.forEach(function (item, index) {
                if(item.type == type) {
                    if(Array.isArray(item['row_item']) && item['row_item'][0]['data']) {
                        items = items.concat([item.row_item[0].data]);
                    }
                }
            });
            return items;
        } else {
            let items = {};
            this.selectedFileList.forEach(function (item, index) {
                if(item.type == type) {
                    if(item['row_item'] != '' && item['row_item'][0]['data'] != '') {
                        items = {...items, ...item.row_item[0].data};
                    }
                }
            });
            return items;
        }
    }

    //Set items list by type.
    this.setItemsList = function(itemList, hidden = false) {
        let tempThis = this;

        jQuery.each(itemList, function(type, items) {
            // If it is array
            if(Array.isArray(items)) {
                items.forEach(function (item, itemIndex) {
                    fileIdCounter++;
                    let fileId = type + '_' + fileIdCounter;

                    tempThis.selectedFileList.push({
                        type: type,
                        id: fileId,
                        item: item,
                        hidden: hidden,
                        row_item: [item]
                    });
                });
            } else {
                if(type == "demoins_inc_demographic") {
                    tempThis.demoins_inc_demographic = items;
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.prepareFiles = function() {
        this.fileUploaderPrepareFile();

        if (opts.onPrepareFiles) {
            let callParam = {
                orders: this.getItemsDataList('orders'),
                messages: this.getItemsDataList('messages'),
                encounters: this.getItemsDataList('encounters'),
                encounter_forms: this.getItemsDataList('encounter_forms'),
                documents: this.getItemsDataList('documents'),
                files: this.getItemsDataList('files'),
                local_files: this.getItemsDataList('local_files'),
                demos_insurances: this.getItemsDataList('demos_insurances')
            }

            if (typeof opts.onPrepareFiles == 'string') {
                window[opts.onPrepareFiles](callParam);
            } else {
                opts.onPrepareFiles.call(this, callParam);
            }
        }
    }

    this.getIframeContentWindow = function(modalwin) {
        return $(modalwin).find('iframe')[0].contentWindow;
    }

    this.setValues = function(modalwin, values) {
        let tempThis = this;
        if(modalwin) {
            $(modalwin).find('iframe')[0].contentWindow.items = values;
        }
    }

    this.appendDataToForm = function(formData) {
        //Organize the file data
        let fileItems = this.getItemsDataList('files');
        formData.append("files_length", fileItems.length);
        for (var i = 0; i < fileItems.length; i++) {
            formData.append("files["+i+"]", fileItems[i].file);
        }

        //organize the document data
        let documentItems = this.getItemsDataList('documents');
        if(documentItems && documentItems.length > 0) {
            formData.append("documents", JSON.stringify(documentItems));
        }

        //organize the document data
        let noteItems = this.getItemsDataList('notes');
        if(noteItems && noteItems.length > 0) {
            formData.append("notes", JSON.stringify(noteItems));
        }

        //organize the order data
        let orderItems = this.getItemsDataList('orders');
        if(orderItems && orderItems.length > 0) {
            formData.append("orders", JSON.stringify(orderItems));
        }

        // organize the encounter data
        let encounterFormItems = this.getItemsDataList('encounter_forms');
        if(encounterFormItems && encounterFormItems.length > 0) {
            /*var tempencounters = {};
            jQuery.each(encounterFormItems, function(i, n){
                let record = { text_title : n['title'], form_id: n['value'], id : i, pid : n['pid'] };
                if(n['parentId'] == undefined) {
                    tempencounters[i] = record;
                    tempencounters[i]['child'] = [];
                } else {
                    if(tempencounters[n['parentId']] != undefined) {
                        tempencounters[n['parentId']]['child'].push(record);
                    } else {
                        tempencounters[i] = record;
                    }
                }
            });*/
            formData.append("encounter_forms", JSON.stringify(encounterFormItems));
        }

        // organize the encounter data
        let demoInsItems = this.getItemsDataList('demos_insurances');
        if(demoInsItems && demoInsItems.length > 0) {
            formData.append("demos_insurances", JSON.stringify(demoInsItems));
        }

        // organize the local file
        let localfileItems = this.getItemsDataList('local_files');
        if(localfileItems) {
            formData.append("local_files", JSON.stringify(localfileItems));
        }

        // organize the local file
        let attachItems = this.getItemsDataList('attachment_files');
        if(attachItems) {
            formData.append("attachment_files", JSON.stringify(attachItems));
        }

        //formData.append("isCheckEncounterDemo", this.checkEncounterDemo);
        formData.append("demoins_inc_demographic", this.demoins_inc_demographic);
    }

    //Intial
    this.prepareFiles();

    // Listen for the event.
    this[0].addEventListener('change', (e) => {
        this.prepareFiles();
    }, false);

    return this;
}