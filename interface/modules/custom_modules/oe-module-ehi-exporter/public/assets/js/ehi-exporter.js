(function(window, oeExporter) {

    class ExporterState {
        taskIds = [];
        currentTaskIndex = 0;
        ajaxUrl = "";
        csrfToken = "";

        currentTaskPollingInterval = 0;
        currentTaskPollingTimeout = 5000;

        startExport() {
            this.currentTaskIndex = -1;
            this.runNextExport();

        }
        runNextExport() {
            this.currentTaskIndex++;
            if (this.currentTaskIndex < this.taskIds.length) {
                let callBack = function() {
                    this.startExportRequestForTask(this.taskIds[this.currentTaskIndex]);
                };
                // just give a way to break promise callback chain
                setTimeout(callBack.bind(this), 100);
            } else {
                // if we've finished everything... then we should clear the polling interval
                this.clearPollingForExportStatus();
            }
        }

        showErrorCardForTaskId(taskId, errorMessage='') {
            // hide the processing div template node
            let processingTask = document.querySelector(".template-task-processing[data-task-id='" + taskId + "']");
            if (!processingTask) {
                console.error("Could not find processing task for task id: " + taskId);
                return;
            }
            processingTask.classList.add("d-none");

            // grab the error div template node
            let errorTaskTemplate = document.querySelector(".template-task-failed");
            let errorTask = errorTaskTemplate.cloneNode(true);

            // populate the error div template node with the task id, the patient pids
            errorTask.querySelector(".taskId").innerText = taskId;
            errorTask.dataset['taskId'] = taskId;
            // show the error div template node
            if (errorMessage) {
                errorTask.querySelector(".errorMessage").innerText = errorMessage;
            }
            // TODO: @adunsulag need to handle what happens when they retry the export and we need to move on to the next
            // possible export.  Should we disable all of the buttons until the export has processed everything...
            errorTask.querySelector(".btn-retry-export-task").addEventListener("click", () => {
                errorTask.remove();
                this.startExportRequestForTask(taskId);
            });
            errorTask.classList.remove("d-none");
            processingTask.insertAdjacentElement("afterend", errorTask);
            processingTask.remove(); // remove the processing node at the end since we don't need it.
        }

        startExportRequestForTask(taskId) {
            // hide the queued div template node
            this.showProcessingCardForTaskId(taskId, {taskId: taskId});
            // send off the ajax request to start the export
            let formParams = new FormData();
            formParams.set("taskId", taskId);
            formParams.set("submit", "Start Export");
            formParams.set("action", "startExport");
            formParams.set("_token", this.csrfToken);
            window.top.restoreSession(); // make sure the session is populated before we send off an ajax request
            let resultPromise = window.fetch(this.ajaxUrl, {
                method: 'POST',
                body: new URLSearchParams(formParams)
            });
            let exporterState = this;
            resultPromise.then(response => {
             if (response.ok) {
                 return response.json();
             } else {
                 throw new Error('Failed to receive response from server');
             }
            })
            .then(data => {
                if (data.status == 'failed') {
                    this.showErrorCardForTaskId(taskId, data.error_message);
                    // move onto the next task in the queue
                    return exporterState.runNextExport();
                } else {
                    this.showSuccessCardForTaskId(taskId, data);
                    return exporterState.runNextExport();
                }
            })
            .catch(error => {
                console.log(error);
                this.showErrorCardForTaskId(taskId, error.message);
                return exporterState.runNextExport();
            });
            // TODO: @adunsulag start the polling for the export status
            this.startPollingForExportStatus(taskId);
        }

        startPollingForExportStatus(taskId) {
            if (this.currentTaskPollingInterval > 0) {
                this.clearPollingForExportStatus();
            }
            this.currentTaskPollingInterval = setInterval(this.pollForExportStatus.bind(this), this.currentTaskPollingTimeout, taskId);
        }
        clearPollingForExportStatus() {
            clearInterval(this.currentTaskPollingInterval);
        }
        pollForExportStatus(taskId) {
            let formParams = new FormData();
            formParams.set("taskId", taskId);
            formParams.set("submit", "Get Status");
            formParams.set("action", "statusUpdate");
            formParams.set("_token", this.csrfToken);
            window.top.restoreSession(); // make sure the session is populated before we send off an ajax request
            let resultPromise = window.fetch(this.ajaxUrl, {
                method: 'POST',
                body: new URLSearchParams(formParams)
            });
            let exporterState = this;
            resultPromise.then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error('Failed to receive response from server');
                }
            })
            .then(data => {
                if (data.status == 'failed') {
                    this.showErrorCardForTaskId(taskId, data.error_message);
                } else if (data.status == 'completed') {
                    this.showSuccessCardForTaskId(taskId, data);
                } else {
                    this.showProcessingCardForTaskId(taskId, data);
                }
            })
            .catch(error => {
                this.showErrorCardForTaskId(taskId, error.message);
                console.log(error);
            });
        }

        showProcessingCardForTaskId(taskId, data) {
            let queuedTask = document.querySelector(".template-task-queued[data-task-id='" + taskId + "']");
            queuedTask.classList.add("d-none");

            // if there are any existing processing tasks we need to remove them
            let processingTasks = document.querySelectorAll(".template-task-processing[data-task-id='" + taskId + "']");
            processingTasks.forEach(function (task) {
                task.remove();
            });

            // grab the processing div template node
            let processingTaskTemplate = document.querySelector(".template-task-processing");
            let processingTask = processingTaskTemplate.cloneNode(true);

            // populate the processing div template node with the task id, the patient pids
            processingTask.querySelector(".taskId").innerText = taskId;
            processingTask.dataset['taskId'] = taskId;
            processingTask.querySelector(".patientPids").innerText = queuedTask.querySelector(".patientPids").innerText;
            // show the processing div template node
            if (data.exportedResult) {
                this.populateCardWithResultData(processingTask, taskId, data);
            }
            processingTask.classList.remove("d-none");
            queuedTask.insertAdjacentElement("afterend", processingTask);
        }

        populateCardWithResultData(cardNode, taskId, data) {
            // .exportedTablesList needs to be looped on the data.exportedResult table
            let totalTablesExported = 0;
            let totalRecordsExported = 0;
            if (data.exportedResult) {
                if (data.exportedResult.exportedTables) {
                    let tableNames = Object.keys(data.exportedResult.exportedTables);
                    totalTablesExported = tableNames.length;
                    let itemTemplate = cardNode.querySelector(".exportedTableListItem");
                    let templateParent = itemTemplate.parentNode;
                    for (let i = 0; i < totalTablesExported; i++) {
                        let tableItem = data.exportedResult.exportedTables[tableNames[i]];
                        let exportedTableListItem = itemTemplate.cloneNode(true);
                        exportedTableListItem.classList.remove("d-none");
                        exportedTableListItem.querySelector(".exportedTableName").innerText = tableItem.tableName + ".csv";
                        exportedTableListItem.querySelector(".exportedTableCount").innerText = tableItem.count;
                        totalRecordsExported += tableItem.count;
                        templateParent.appendChild(exportedTableListItem);
                    }
                }
                if (data.exportedResult.exportedDocumentCount >= 0) {
                    cardNode.querySelector(".documentsExportedCount").innerText = data.exportedResult.exportedDocumentCount;
                }
            }
            cardNode.querySelector(".total-tables-exported").innerText = totalTablesExported;
            cardNode.querySelector(".total-records-exported").innerText = totalRecordsExported;

            if (data.includePatientDocuments) {
                cardNode.querySelector(".documentsExportedSection").classList.remove("d-none");
            }
        }

        showSuccessCardForTaskId(taskId, data) {
            let processingTask = document.querySelector(".template-task-processing[data-task-id='" + taskId + "']");
            processingTask.classList.add("d-none");

            // grab the error div template node
            let successTemplate = document.querySelector(".template-result-success");
            let successTask = successTemplate.cloneNode(true);

            successTask.querySelector(".taskId").innerText = taskId;
            successTask.dataset['taskId'] = taskId;

            // .download-link .download-link-name need to be populated
            successTask.querySelector(".download-link-name").innerText = data.downloadName;
            successTask.querySelector(".download-link").href = data.downloadLink;
            successTask.querySelector(".download-link").addEventListener('click', function() {
                window.top.restoreSession(); // make sure the session is populated before the download starts
            });
            // .hash-algo-title, .hash-text need to be populated
            successTask.querySelector(".hash-algo-title").innerText = data.hashAlgoTitle;
            successTask.querySelector(".hash-text").innerText = data.hash;
            this.populateCardWithResultData(successTask, taskId, data);
            successTask.classList.remove("d-none");
            processingTask.insertAdjacentElement("afterend", successTask);
            processingTask.remove(); // remove the processing node at the end since we don't need it.
        }
    }
    let exporterState;

    function displayExportStartDialog(dialogId) {
        let container = document.getElementById(dialogId);
        let modal = new bootstrap.Modal(container, {keyboard: false, focus: true, backdrop: 'static'});
        modal.show();
    }

    oeExporter.displayExportStartDialog = displayExportStartDialog;
    oeExporter.startTaskExports = function (ajaxUrl, csrfToken) {
        let queuedTasks = document.querySelectorAll(".template-task-queued[data-task-id]");
        let queuedTaskIds = [];
        queuedTasks.forEach(function (task) {
            queuedTaskIds.push(+task.dataset.taskId);
        });
        if (queuedTaskIds.length > 0) {
            exporterState = new ExporterState();
            exporterState.ajaxUrl = ajaxUrl;
            exporterState.csrfToken = csrfToken;
            exporterState.taskIds = queuedTaskIds;
            exporterState.startExport();
        }
    };
    window.oeExporter = oeExporter;
})(window, window.oeExporter || window.top.oeExporter || {});
