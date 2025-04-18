<?php

/**
 * HIPAAi Chat Module Main Page
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@example.com> // TODO: Update author info
 * @copyright Copyright (c) 2024 Your Name/Company
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// TODO: Add any necessary OpenEMR includes or session checks if needed later.
// require_once(__DIR__ . '/../../../globals.php'); // Example if OpenEMR context is needed

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HIPAAi Chat</title>
    <!-- Include marked.js library for Markdown parsing -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            overflow: hidden; /* Prevent body scrollbars */
        }
        #chat-container {
            display: grid;
            grid-template-columns: 250px 1fr; /* Sidebar and Main Chat */
            grid-template-rows: 1fr auto; /* Chat messages and Input area */
            grid-template-areas:
                "sidebar messages"
                "sidebar input";
            height: 100vh; /* Full viewport height */
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        #chat-sidebar {
            grid-area: sidebar;
            background-color: #f4f4f4;
            border-right: 1px solid #ccc;
            padding: 15px;
            overflow-y: auto;
        }
         #chat-sidebar h3 {
             margin-top: 0;
             border-bottom: 1px solid #ddd;
             padding-bottom: 10px;
         }
        #chat-main {
            grid-area: messages;
            display: flex;
            flex-direction: column;
            height: 100%; /* Fill the grid area */
            overflow: hidden; /* Needed for child scrolling */
        }
        #chat-messages {
            flex-grow: 1; /* Takes available space */
            padding: 15px;
            overflow-y: auto; /* Enable scrolling for messages */
            background-color: #fff;
            border-bottom: 1px solid #ccc;
        }
        #chat-messages p {
            margin: 5px 0;
            padding: 8px 12px;
            background-color: #e2f0ff;
            border-radius: 10px;
            max-width: 80%;
            word-wrap: break-word; /* Wrap long words */
        }
        #chat-input-area {
            grid-area: input;
            display: flex;
            padding: 10px;
            background-color: #f9f9f9;
             border-top: 1px solid #ccc; /* Add separator line */
        }
        #message-input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }
        #send-button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #send-button:hover {
            background-color: #0056b3;
        }
        #chat-messages code {
            background-color: #eee;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: monospace;
        }
        #chat-messages pre {
            background-color: #eee;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        #chat-messages table {
            border-collapse: collapse;
            margin: 10px 0;
            width: auto;
            border: 1px solid #ccc;
        }
        #chat-messages th, #chat-messages td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        #chat-messages th {
            background-color: #f2f2f2;
        }
        #chat-messages ul, #chat-messages ol {
            margin-left: 20px; /* Indent lists */
            margin-top: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div id="chat-container">
        <div id="chat-sidebar">
            <h3>Previous Chats</h3>
            <!-- Previous chat list will go here -->
            <p><i>(Chat history functionality not yet implemented)</i></p>
        </div>
        <div id="chat-main">
            <div id="chat-messages">
                <!-- Chat messages will appear here -->
                <!-- Loading indicator - REMOVED static element -->
            </div>
            <div id="chat-input-area">
                <input type="text" id="message-input" placeholder="Type your message...">
                <button id="send-button">Send</button>
            </div>
        </div>
    </div>

    <script>
        console.log("SCRIPT START");

        // --- Variables ---
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');
        const chatMessages = document.getElementById('chat-messages');
        const backendApiUrl = 'api/openai_chat.php';
        let conversationHistory = [];

        console.log("Variables initialized:", !!messageInput, !!sendButton, !!chatMessages);

        // --- Function Definitions ---

        function displayMessage(role, text) {
             console.log(`Displaying message - Role: ${role}, Text length: ${text.length}`); // Log length instead of full text
            const messageElement = document.createElement('div'); // Use div for potentially complex HTML

            // Styling based on role - apply common styles first
             messageElement.style.margin = '5px 0';
             messageElement.style.padding = '8px 12px';
             messageElement.style.borderRadius = '10px';
             messageElement.style.maxWidth = '80%';
             messageElement.style.wordWrap = 'break-word';
             messageElement.style.backgroundColor = '#f0f0f0'; // Default background
             messageElement.style.textAlign = 'left'; // Default alignment

            if (role === 'assistant') {
                // Parse Markdown to HTML for assistant messages
                // Enable sanitization to prevent XSS
                const dirtyHtml = marked.parse(text);
                messageElement.innerHTML = dirtyHtml; // Use innerHTML to render HTML
                // Assistant specific styles (already default)
                // messageElement.style.backgroundColor = '#f0f0f0';
                // messageElement.style.textAlign = 'left';
            } else if (role === 'user') {
                messageElement.textContent = text; // Keep using textContent for user input
                // User specific styles
                messageElement.style.backgroundColor = '#e2f0ff';
                messageElement.style.marginLeft = 'auto';
                messageElement.style.textAlign = 'right';
            } else { // Error case
                 messageElement.textContent = text; // Use textContent for errors
                 messageElement.style.color = 'red';
                 messageElement.style.fontWeight = 'bold';
                 messageElement.style.backgroundColor = '#fff0f0'; // Light red background for errors
            }
            chatMessages.appendChild(messageElement);
            // Scroll to the bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // --- Restoring sendMessage (non-async) - Step 1 ---
        function sendMessage() {
            console.log("--- sendMessage CALLED (non-async) ---");

            // --- Step 1: Uncomment getting input and displaying user message ---
            const messageText = messageInput.value.trim();
            console.log("Message text:", messageText);

            if (messageText === '') {
                 console.log("Message text is empty, returning.");
                return; // Don't send empty messages
            }

            // Display user message immediately
            displayMessage('user', messageText);
            // -----------------------------------------------------------------

            // --- Step 2: Uncomment history update and UI changes ---
            conversationHistory.push({ role: 'user', content: messageText });
             console.log("Added user message to history:", conversationHistory);

            // Clear input and disable it while waiting
            messageInput.value = '';
            messageInput.disabled = true;
            sendButton.disabled = true;

            // --- Create and display loading indicator dynamically ---
            const indicator = document.createElement('p');
            indicator.id = 'loading-indicator'; // Assign ID for easy removal
            indicator.textContent = 'Assistant is thinking...';
            indicator.style.color = '#888';
            indicator.style.fontStyle = 'italic';
            // Optional: Add alignment or other styles if needed
            // indicator.style.textAlign = 'center';
            chatMessages.appendChild(indicator);
            chatMessages.scrollTop = chatMessages.scrollHeight; // Scroll again after adding indicator
            console.log("UI updated, loading indicator added.");
            // -------------------------------------------------------

            // --- Full fetch call using .then() syntax ---
            console.log("Calling fetch with URL:", backendApiUrl);
            fetch(backendApiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                    // Removed potentially problematic commented-out PHP line
                },
                body: JSON.stringify({ messages: conversationHistory })
            })
            .then(response => {
                console.log("Fetch response received:", response.status, response.ok);
                if (!response.ok) {
                    // Simplified error handling: Throw error directly if response not ok
                    throw new Error(`HTTP error ${response.status}: ${response.statusText || 'Status text unavailable'}`);
                }
                return response.json(); // Parse successful JSON response
            })
            .then(data => {
                 console.log("Received data:", data); // <<< THIS LOG IS IMPORTANT
                // Successfully received and parsed data
                if (data.message && data.message.content) {
                    displayMessage(data.message.role, data.message.content); // <<< Display AI message
                    conversationHistory.push(data.message);
                } else {
                     console.error('Invalid response format:', data);
                     displayMessage('error', 'Error: Received invalid response from server.');
                }
            })
            .catch(error => {
                // Handle fetch errors (network error, CORS, errors thrown above)
                console.error('Fetch Error:', error);
                displayMessage('error', `Error: ${error.message || 'Could not connect to the chat service.'}`);
            })
            .finally(() => {
                // This block executes whether the promise succeeded or failed
                console.log("Fetch finished (finally block).");

                // --- Remove loading indicator ---
                const indicatorToRemove = document.getElementById('loading-indicator');
                if (indicatorToRemove) {
                    indicatorToRemove.remove();
                    console.log("Loading indicator removed.");
                }
                // ---------------------------------

                // Re-enable UI elements
                messageInput.disabled = false;
                sendButton.disabled = false;
                messageInput.focus();
            });
            // ----------------------------------------------------
        }

        // --- Event Listeners (remain hooked to sendMessage) ---

        console.log("Attempting to add click listener to send button...");
        if (sendButton) {
             sendButton.addEventListener('click', sendMessage); // Calls the empty sendMessage
             console.log("Send button click listener ADDED.");
        } else {
             console.error("ERROR: Send button not found!");
        }

        console.log("Attempting to add keypress listener to message input...");
        if (messageInput) {
            messageInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter' || event.keyCode === 13) {
                    // console.log("--- Enter key DETECTED --- "); // Keep commented for now
                    event.preventDefault();
                    sendMessage(); // Calls the empty sendMessage
                }
            });
            console.log("Input keypress listener ADDED.");
        } else {
            console.error("ERROR: Message input not found!");
        }

        // --- Initial Focus ---
         if (messageInput) {
            messageInput.focus();
            console.log("Initial focus set.");
         } else {
             console.error("ERROR: Cannot set focus, message input not found!");
         }

        console.log("SCRIPT END");

    </script>
</body>
</html>
