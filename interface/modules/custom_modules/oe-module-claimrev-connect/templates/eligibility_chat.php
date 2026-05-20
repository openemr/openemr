<?php

/**
 * AI Conversation tab for eligibility results.
 *
 * Variables expected from caller:
 *   $pid                  - Active patient pid (inherited from eligibility.php)
 *   $chatProductResultIds - Map of product ID => claimRevResultId
 *   $chatPayerCode        - The payer code (optional)
 *   $chatPrKey            - Unique key for this payer responsibility tab
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/** @var int $pid */
/** @var string $chatPrKey */
/** @var array<int, string> $chatProductResultIds */
/** @var string $chatPayerCode */

declare(strict_types=1);

use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;

$chatId = 'cr-chat-' . attr($chatPrKey);
$chatCsrfToken = CsrfHelper::collectCsrfToken('eligibility');

// Product labels
$productLabels = [
    1 => xl('Eligibility'),
    2 => xl('Demographics'),
    3 => xl('Coverage Discovery'),
    5 => xl('MBI Finder'),
];

// Default to first available product
$defaultObjectId = '';
foreach ($chatProductResultIds as $rid) {
    if ($rid !== '') {
        $defaultObjectId = $rid;
        break;
    }
}
?>

<div id="<?php echo attr($chatId); ?>" class="cr-chat-container" style="border:1px solid #dee2e6; border-radius:4px; display:flex; flex-direction:column; height:500px;">
    <!-- Header -->
    <div style="padding:12px 16px; border-bottom:1px solid #dee2e6; background:#f8f9fa;">
        <div class="d-flex align-items-center">
            <i class="fa fa-robot fa-lg text-primary mr-2"></i>
            <div>
                <strong><?php echo xlt("Eligibility Assistant"); ?></strong>
                <br><small class="text-muted"><?php echo xlt("Ask questions about this eligibility response"); ?></small>
            </div>
            <?php if (count($chatProductResultIds) > 1) { ?>
                <div class="ml-auto mr-2">
                    <select class="form-control form-control-sm cr-chat-context" id="<?php echo attr($chatId); ?>-context" data-chat="<?php echo attr($chatId); ?>">
                        <?php foreach ($chatProductResultIds as $prodId => $resultId) {
                            if ($resultId === '') {
                                continue;
                            }
                            $label = $productLabels[$prodId] ?? ('Product ' . $prodId);
                            ?>
                            <option value="<?php echo attr($resultId); ?>"><?php echo text($label); ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>
            <span class="badge badge-info <?php echo count($chatProductResultIds) <= 1 ? 'ml-auto' : ''; ?>"><?php echo xlt("Beta"); ?></span>
        </div>
    </div>

    <!-- Messages Area -->
    <div id="<?php echo attr($chatId); ?>-messages" style="flex:1; overflow-y:auto; padding:16px;">
        <!-- Empty state / suggestions -->
        <div id="<?php echo attr($chatId); ?>-empty" class="text-center py-4">
            <i class="fa fa-comments fa-3x text-muted mb-3" style="opacity:0.3;"></i>
            <h6><?php echo xlt("Start a Conversation"); ?></h6>
            <p class="text-muted small"><?php echo xlt("Ask me anything about this eligibility response"); ?></p>
            <div class="mt-3">
                <small class="text-muted font-weight-bold"><?php echo xlt("Try asking"); ?>:</small>
                <div class="mt-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm m-1 cr-chat-suggestion" data-chat="<?php echo attr($chatId); ?>"
                        data-question="What is the patient's deductible?">
                        <?php echo xlt("What is the deductible?"); ?>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm m-1 cr-chat-suggestion" data-chat="<?php echo attr($chatId); ?>"
                        data-question="Is this patient covered for outpatient services?">
                        <?php echo xlt("Outpatient coverage?"); ?>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm m-1 cr-chat-suggestion" data-chat="<?php echo attr($chatId); ?>"
                        data-question="Summarize the key benefits">
                        <?php echo xlt("Summarize benefits"); ?>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm m-1 cr-chat-suggestion" data-chat="<?php echo attr($chatId); ?>"
                        data-question="What are the co-pay amounts?">
                        <?php echo xlt("Co-pay amounts?"); ?>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm m-1 cr-chat-suggestion" data-chat="<?php echo attr($chatId); ?>"
                        data-question="Does this patient have any coverage limitations?">
                        <?php echo xlt("Coverage limitations?"); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <div style="padding:12px 16px; border-top:1px solid #dee2e6; background:#f8f9fa;">
        <div class="input-group">
            <input type="text" class="form-control cr-chat-input" id="<?php echo attr($chatId); ?>-input"
                   placeholder="<?php echo attr(xl('Ask a question...')); ?>"
                   data-chat="<?php echo attr($chatId); ?>"
                   data-object-id="<?php echo attr($defaultObjectId); ?>"
                   data-payer-code="<?php echo attr($chatPayerCode); ?>">
            <div class="input-group-append">
                <button type="button" class="btn btn-primary cr-chat-send" id="<?php echo attr($chatId); ?>-send"
                        data-chat="<?php echo attr($chatId); ?>">
                    <i class="fa fa-paper-plane"></i>
                </button>
            </div>
        </div>
        <small class="text-muted mt-1 d-block">
            <i class="fa fa-info-circle"></i>
            <?php echo xlt("AI responses are based on eligibility data and may not be 100% accurate. Always verify critical information."); ?>
        </small>
    </div>
</div>

<script>
(function() {
    // Only initialize once
    if (window.crChatInitialized) return;
    window.crChatInitialized = true;

    // Unique response ID counter to avoid stale references
    var responseCounter = 0;

    function sendChatMessage(chatId) {
        var input = document.getElementById(chatId + '-input');
        var messagesDiv = document.getElementById(chatId + '-messages');
        var emptyState = document.getElementById(chatId + '-empty');
        var sendBtn = document.getElementById(chatId + '-send');

        var question = input.value.trim();
        if (!question) return;

        // Use context selector if available, otherwise fall back to input data attribute
        var contextSelect = document.getElementById(chatId + '-context');
        var objectId = contextSelect ? contextSelect.value : input.dataset.objectId;
        var payerCode = input.dataset.payerCode;

        // Hide empty state
        if (emptyState) emptyState.style.display = 'none';

        // Add user message
        var userMsg = document.createElement('div');
        userMsg.className = 'd-flex justify-content-end mb-3';
        userMsg.innerHTML = '<div style="background:#007bff; color:white; padding:8px 14px; border-radius:16px 16px 2px 16px; max-width:75%; word-wrap:break-word;">' +
            escapeHtml(question) + '</div>';
        messagesDiv.appendChild(userMsg);

        // Add bot thinking indicator with unique ID
        responseCounter++;
        var responseId = chatId + '-response-' + responseCounter;
        var botMsg = document.createElement('div');
        botMsg.className = 'd-flex mb-3';
        botMsg.innerHTML = '<div class="mr-2"><i class="fa fa-robot text-primary"></i></div>' +
            '<div id="' + responseId + '" style="background:#f1f3f5; padding:8px 14px; border-radius:16px 16px 16px 2px; max-width:75%; word-wrap:break-word;">' +
            '<i class="fa fa-circle-notch fa-spin text-muted"></i> <span class="text-muted">' + <?php echo xlj("Thinking..."); ?> + '</span></div>';
        messagesDiv.appendChild(botMsg);

        // Scroll to bottom
        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        // Clear input and disable
        input.value = '';
        input.disabled = true;
        sendBtn.disabled = true;

        $.ajax({
            url: '../../modules/custom_modules/oe-module-claimrev-connect/public/eligibility_chat.php',
            type: 'POST',
            data: {
                pid: <?php echo js_escape((string) $pid); ?>,
                sharpRevenueObjectId: objectId,
                question: question,
                payerCode: payerCode,
                csrf_token: <?php echo js_escape($chatCsrfToken); ?>
            },
            dataType: 'json',
            success: function(response) {
                var responseDiv = document.getElementById(responseId);
                if (response.success && response.answer) {
                    responseDiv.innerHTML = formatMarkdown(response.answer);
                } else {
                    responseDiv.innerHTML = '<span class="text-danger">' + escapeHtml(response.message || <?php echo xlj("Unable to get a response"); ?>) + '</span>';
                }
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
                input.disabled = false;
                sendBtn.disabled = false;
                input.focus();
            },
            error: function() {
                var responseDiv = document.getElementById(responseId);
                if (responseDiv) {
                    responseDiv.innerHTML = '<span class="text-danger">' + <?php echo xlj("Sorry, I was unable to process your request. Please try again."); ?> + '</span>';
                }
                input.disabled = false;
                sendBtn.disabled = false;
                input.focus();
            }
        });
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    function formatMarkdown(text) {
        // Basic markdown: bold, italic, line breaks, bullet lists
        var html = escapeHtml(text);
        // Bold **text**
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        // Italic *text*
        html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
        // Bullet lists
        html = html.replace(/^[-*]\s+(.+)$/gm, '<li>$1</li>');
        html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
        // Line breaks
        html = html.replace(/\n\n/g, '<br><br>');
        html = html.replace(/\n/g, '<br>');
        return html;
    }

    // Bind Enter key on inputs
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.classList.contains('cr-chat-input')) {
            e.preventDefault();
            sendChatMessage(e.target.dataset.chat);
        }
    });

    // Bind send buttons
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.cr-chat-send');
        if (btn) {
            sendChatMessage(btn.dataset.chat);
        }
    });

    // Bind suggestion buttons
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.cr-chat-suggestion');
        if (btn) {
            var chatId = btn.dataset.chat;
            var input = document.getElementById(chatId + '-input');
            if (input) {
                input.value = btn.dataset.question;
                sendChatMessage(chatId);
            }
        }
    });
})();
</script>
