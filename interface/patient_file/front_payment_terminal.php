<?php
require_once(__DIR__ . "/../globals.php");

use OpenEMR\Core\Header;

$total = $_GET['total'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo xlt("POS Payments") ?></title>
    <meta name="description" content="In-person payment on Stripe" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php Header::setupHeader(['opener']); ?>
    <script src="https://js.stripe.com/terminal/v1/"></script>
    <script>
        let amount = <?php echo js_escape(str_replace('.', '', $total)); ?>;
        // run anonymous function to get invoice data for metadata.
        const encDates = (() => {
            let i = 0, c;
            let invDates = '';
            opener.$('#table_display tbody tr').each(function () {
                if (this.className == 'table-active') {
                    return false;
                }
                if(i > 4) {
                    return false; // breaks on max 5 encounters
                }
                invDates += 'item' + ++i + ': ';
                c = 0;
                $(this).find('td').each(function() {
                    if (++c < 3) {
                        invDates += this.innerText + ' ';
                    }
                });
            });
            return invDates;
        })();

        const terminal = StripeTerminal.create({
            onFetchConnectionToken: fetchConnectionToken,
            onUnexpectedReaderDisconnect: unexpectedDisconnect,
        });

        function unexpectedDisconnect() {
            alert("Disconnected from reader");
            console.log("Disconnected from reader");
        }

        function fetchConnectionToken() {
            // The SDK manages the ConnectionToken's lifecycle.
            return fetch('./front_payment_cc.php?mode=terminal_token', {method: "POST"}).then(function (response) {
                return response.json();
            }).then(function (data) {
                return data.secret;
            });
        }

        let discoveredReaders;
        let customerId;
        // Handler for a "Discover readers" button
        function discoverReaderHandler() {
            const config = {simulated: false};
            terminal.discoverReaders(config).then(function (discoverResult) {
                if (discoverResult.error) {
                    alert(discoverResult.error.message);
                    console.log('Failed to discover: ', discoverResult.error);
                } else if (discoverResult.discoveredReaders.length === 0) {
                    alert(xl('No available readers.'));
                } else {
                    discoveredReaders = discoverResult.discoveredReaders;
                    log('Terminal Discover Reader', discoveredReaders);
                    connectReaderHandler(discoveredReaders);
                }
            });
        }

        // Handler for a "Connect Reader" button
        function connectReaderHandler(discoveredReaders) {
            // Just select the first reader here.
            if (!discoveredReaders) {
                alert(xl("Error No selected Readers"));
                return false;
            }
            const selectedReader = discoveredReaders[0];
            terminal.connectReader(selectedReader).then(function (connectResult) {
                if (connectResult.error) {
                    alert(connectResult.error.message);
                    console.log('Failed to connect: ', connectResult.error);
                } else {
                    document.getElementById("collect-button").classList.remove("d-none");
                    console.log('Connected to reader: ', connectResult.reader.label);
                    log('Connect to Reader', connectResult)
                }
            });
        }

        function fetchPaymentIntentClientSecret(amount) {
            const bodyContent = JSON.stringify({
                amount: amount,
                encs: encDates
            });
            return fetch('./front_payment_cc.php?mode=terminal_create', {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: bodyContent
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                return data.client_secret;
            });
        }

        let paymentIntentId;
        let isChargePending = false;

        function collectPayment(amount) {
            try {
                fetchPaymentIntentClientSecret(amount).then(function (client_secret) {
                    terminal.collectPaymentMethod(client_secret).then(function (result) {
                        if (result.error) {
                            alert(result.error.message);
                        } else {
                            log('Collect Payment Method', result.paymentIntent);
                            terminal.processPayment(result.paymentIntent).then(function (result) {
                                if (result.error) {
                                    console.log(result.error);
                                    alert(result.error.message);
                                } else if (result.paymentIntent) {
                                    paymentIntentId = result.paymentIntent.id;
                                    log('Process Payment', result.paymentIntent);
                                    isChargePending = true;
                                    document.getElementById("collect-button").classList.add("d-none");
                                    document.getElementById("capture-button").classList.remove("d-none");
                                    document.getElementById("refund-button").classList.remove("d-none");
                                }
                            });
                        }
                    });
                });
            } catch (e) {
                alert(e.message);
            }
        }

        function capture(paymentIntentId) {
            return fetch('./front_payment_cc.php?mode=terminal_capture', {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({"id": paymentIntentId})
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                if (data.error) {
                    log('Capture Payment Error', data.error);
                    console.log(data.error);
                    alert(data.error);
                    return false;
                }
                opener.document.getElementById("check_number").value = data.id;
                opener.$("[name='form_save']").click();
                dlgclose();
            });
        }

        function cancel(paymentIntentId) {
            return fetch('./front_payment_cc.php?mode=cancel_intent', {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({"id": paymentIntentId})
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                if (data.error) {
                    log('Cancel Payment Error', data.error);
                    console.log(data.error);
                    alert(data.error);
                    return false;
                }
                isChargePending = false;
                document.getElementById("refund-button").classList.add("d-none");
                document.getElementById("collect-button").classList.remove("d-none");
                document.getElementById("capture-button").classList.add("d-none");
                log('Cancel Payment', data.status);
            });
        }

        $(function () {
            const collectButton = document.getElementById('collect-button');
            collectButton.addEventListener('click', async (event) => {
                collectPayment(amount);
            });

            const captureButton = document.getElementById('capture-button');
            captureButton.addEventListener('click', async (event) => {
                capture(paymentIntentId);
            });

            const cancelIntentButton = document.getElementById('refund-button');
            cancelIntentButton.addEventListener('click', async (event) => {
                cancel(paymentIntentId);
            });

            const cancelButton = parent.document.getElementById('closeBtn');
            cancelButton.addEventListener('click', async (event) => {
                if (isChargePending) {
                    if (confirm(xl("There is a charge transaction that has not been captured." + "\n" + xl("Are you sure?")))) {
                        dlgclose();
                    }
                    return false;
                }
                dlgclose();
            });
            // get/init reader
            discoverReaderHandler();
        });

        function log(method, message) {
            let logs = document.getElementById("logs");
            let title = document.createElement("div");
            let log = document.createElement("div");
            let lineCol = document.createElement("div");
            let logCol = document.createElement("div");
            title.classList.add('row');
            title.classList.add('log-title');
            title.textContent = method;
            log.classList.add('row');
            log.classList.add('log');
            let hr = document.createElement("hr");
            let pre = document.createElement("pre");
            let code = document.createElement("code");
            code.textContent = formatJson(JSON.stringify(message, undefined, 2));
            pre.append(code);
            log.append(pre);
            logs.prepend(hr);
            logs.prepend(log);
            logs.prepend(title);
        }

        function stringLengthOfInt(number) {
            return number.toString().length;
        }

        function padSpaces(lineNumber, fixedWidth) {
            // Always indent by 2 and then maybe more, based on the width of the line
            // number.
            return " ".repeat(2 + fixedWidth - stringLengthOfInt(lineNumber));
        }

        function formatJson(message) {
            let lines = message.split('\n');
            let json = "";
            let lineNumberFixedWidth = stringLengthOfInt(lines.length);
            for (let i = 1; i <= lines.length; i += 1) {
                line = i + padSpaces(i, lineNumberFixedWidth) + lines[i - 1];
                json = json + line + '\n';
            }
            return json
        }
    </script>
</head>
<body>
    <div class="container-fluid ">
        <div class="row">
            <div class="col-sm-6 offset-sm-3">
                <h4><span class="m-1"><?php echo xlt("Paying Amount") ?></span><i>$</i><span><?php echo text($total); ?></span></h4>
            </div>
        </div>
        <div class="row m-1">
            <button id="collect-button" class="btn btn-primary btn-transmit m-1 d-none"><?php echo xlt("Collect Payment")?></button>
            <button id="capture-button" class="btn btn-primary btn-transmit m-1 d-none"><?php echo xlt("Post Payment")?></button>
            <button id="refund-button" class="btn btn-primary btn-transmit m-1 d-none"><?php echo xlt("Cancel Payment")?></button>
        </div>
        <hr />
        <div class="row ml-2"><h5><?php echo xlt("Transaction Progress") ?></h5></div>
        <div class="col-sm-12 p-2 bg-secondary" id="logs"><i class="fa fa-spinner fa-spin fa-2x"></i></div>

    </div>
</body>
</html>
