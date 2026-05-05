<?php
/**
 * PDF Template Editor - OpenEMR Wrapper
 * 
 * Authenticates user and passes customer_id to the JavaScript frontend
 */

require_once(__DIR__ . '/../../../../../globals.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Verify user is authenticated
if (!isset($_SESSION['authUserID'])) {
    die('Access denied. Please log in to OpenEMR.');
}

// Get customer_id from OpenEMR globals (same as practice_id)
$customerId = $GLOBALS['medex_practice_id'] ?? '';
$apiToken = $GLOBALS['medex_api_key'] ?? '';

// Fall back to database if globals not set
if (empty($customerId) || empty($apiToken)) {
    $prefs = sqlQuery("SELECT MedEx_id, ME_api_key FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
    if (!empty($prefs['MedEx_id'])) {
        $customerId = (string)$prefs['MedEx_id'];
    }
    if (!empty($prefs['ME_api_key'])) {
        $apiToken = (string)$prefs['ME_api_key'];
    }
}

if (!$customerId || !$apiToken) {
    die('MedEx not configured. Please configure MedEx in <a href="../settings.php">Settings</a>.');
}

// Get template_id from URL if editing existing
$templateId = $_GET['template_id'] ?? '';

// API base URL - SaaS model
$apiBaseUrl = 'https://medexbank.com/pdf/api.php';

$csrfToken = CsrfUtils::collectCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Template Editor | MedEx</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf_viewer.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #1a1a2e;
            color: #e8e8e8;
            overflow: hidden;
            height: 100vh;
        }
        
        .app-container {
            display: flex;
            height: 100vh;
        }
        
        /* Left Sidebar - Template Info */
        .sidebar-left {
            width: 280px;
            background: #16213e;
            border-right: 1px solid #0f3460;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .sidebar-header {
            padding: 16px;
            border-bottom: 1px solid #0f3460;
            background: #1a1a2e;
        }
        
        .sidebar-header h2 {
            font-size: 18px;
            margin-bottom: 4px;
        }
        
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }
        
        /* Form Controls */
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            color: #a8a8a8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            background: #0f3460;
            border: 1px solid #1e5f8a;
            border-radius: 6px;
            color: #e8e8e8;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #5a8dee;
            box-shadow: 0 0 0 3px rgba(90, 141, 238, 0.2);
        }
        
        .form-control::placeholder {
            color: #6a6a8a;
        }
        
        select.form-control {
            cursor: pointer;
        }
        
        textarea.form-control {
            min-height: 80px;
            resize: vertical;
        }
        
        /* Buttons */
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2c5aa0 0%, #5a8dee 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #3a6ab0 0%, #6a9dfe 100%);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #0f3460;
            color: #e8e8e8;
            border: 1px solid #1e5f8a;
        }
        
        .btn-secondary:hover {
            background: #1e5f8a;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #00875a 0%, #00d1b2 100%);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            color: white;
        }
        
        .btn-block {
            width: 100%;
        }
        
        /* PDF Viewer Area */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #2d2d44;
            min-width: 0;
        }
        
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: #1a1a2e;
            border-bottom: 1px solid #0f3460;
        }
        
        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .toolbar-btn {
            padding: 8px 12px;
            background: #0f3460;
            border: 1px solid #1e5f8a;
            border-radius: 6px;
            color: #e8e8e8;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            transition: all 0.2s;
        }
        
        .toolbar-btn:hover {
            background: #1e5f8a;
        }
        
        .toolbar-btn.active {
            background: #5a8dee;
            border-color: #5a8dee;
        }
        
        .page-info {
            color: #a8a8a8;
            font-size: 14px;
        }
        
        .zoom-controls {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .zoom-level {
            padding: 4px 12px;
            background: #0f3460;
            border-radius: 4px;
            min-width: 60px;
            text-align: center;
        }
        
        .pdf-container {
            flex: 1;
            overflow: auto;
            display: flex;
            justify-content: center;
            padding: 20px;
            background: #3d3d54;
        }
        
        #pdf-canvas {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            background: white;
        }
        
        /* Right Sidebar - Field Mappings */
        .sidebar-right {
            width: 400px;
            background: #16213e;
            border-left: 1px solid #0f3460;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .panel-top {
            flex: 0 0 auto;
            max-height: 40%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border-bottom: 1px solid #0f3460;
        }
        
        .panel-bottom {
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .panel-header {
            padding: 12px 16px;
            background: #1a1a2e;
            border-bottom: 1px solid #0f3460;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        
        .panel-header h3 {
            font-size: 14px;
            font-weight: 600;
        }
        
        .panel-content {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
        }
        
        .field-list-container {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
        }
        
        /* Field List */
        .field-item {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            background: #0f3460;
            border-radius: 6px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }
        
        .field-item:hover {
            background: #1e5f8a;
        }
        
        .field-item.selected {
            border-color: #5a8dee;
            background: #1e4a7a;
        }
        
        .field-item.mapped {
            border-left: 3px solid #00d1b2;
        }
        
        .field-icon {
            width: 28px;
            height: 28px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 12px;
        }
        
        .field-icon.text { background: #3498db; }
        .field-icon.checkbox { background: #9b59b6; }
        .field-icon.radio { background: #e67e22; }
        .field-icon.date { background: #1abc9c; }
        .field-icon.signature { background: #e74c3c; }
        
        .field-info {
            flex: 1;
            min-width: 0;
        }
        
        .field-name {
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .field-mapping {
            font-size: 11px;
            color: #00d1b2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .field-page {
            font-size: 11px;
            color: #a8a8a8;
            margin-left: 8px;
        }
        
        /* Field Mapping Editor */
        .mapping-editor {
            padding: 16px;
        }
        
        .mapping-field-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #0f3460;
        }
        
        .mapping-tabs {
            display: flex;
            margin-bottom: 16px;
            border-bottom: 1px solid #0f3460;
        }
        
        .mapping-tab {
            padding: 10px 16px;
            background: none;
            border: none;
            color: #a8a8a8;
            cursor: pointer;
            font-size: 13px;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        
        .mapping-tab:hover {
            color: #e8e8e8;
        }
        
        .mapping-tab.active {
            color: #5a8dee;
            border-bottom-color: #5a8dee;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Upload Area */
        .upload-area {
            border: 2px dashed #1e5f8a;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        
        .upload-area:hover {
            border-color: #5a8dee;
            background: rgba(90, 141, 238, 0.1);
        }
        
        .upload-area.dragover {
            border-color: #00d1b2;
            background: rgba(0, 209, 178, 0.1);
        }
        
        .upload-icon {
            font-size: 48px;
            color: #5a8dee;
            margin-bottom: 16px;
        }
        
        .upload-text {
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        .upload-hint {
            font-size: 13px;
            color: #a8a8a8;
        }
        
        /* Empty State */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #a8a8a8;
            text-align: center;
            padding: 40px;
        }
        
        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        /* Field Mappings Container */
        .field-mappings-container {
            flex: 1;
            overflow: visible;
            padding: 12px;
        }
        
        /* Loading Spinner */
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #0f3460;
            border-top-color: #5a8dee;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Toast Notifications */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .toast {
            background: #16213e;
            border: 1px solid #0f3460;
            border-radius: 8px;
            padding: 12px 16px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        
        .toast.success { border-left: 3px solid #00d1b2; }
        .toast.error { border-left: 3px solid #e74c3c; }
        .toast.info { border-left: 3px solid #5a8dee; }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        /* Checkbox styling */
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }
        
        .form-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #5a8dee;
        }
        
        .form-check label {
            font-size: 14px;
            cursor: pointer;
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #0f3460;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #1e5f8a;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #2e7faa;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Left Sidebar - Template Info -->
        <div class="sidebar-left">
            <div class="sidebar-header">
                <h2>Template Editor</h2>
                <small style="color: #a8a8a8;">Configure PDF form fields</small>
            </div>
            <div class="sidebar-content">
                <!-- Upload Area (shown when no PDF loaded) -->
                <div id="upload-section">
                    <div class="upload-area" id="upload-area">
                        <div class="upload-icon">📄</div>
                        <div class="upload-text">Drop PDF here</div>
                        <div class="upload-hint">or click to browse</div>
                        <input type="file" id="pdf-input" accept=".pdf" style="display: none;">
                    </div>
                </div>
                
                <!-- Template Info (shown when PDF loaded) -->
                <div id="template-section" style="display: none;">
                    <div class="form-group">
                        <label>Template Name</label>
                        <input type="text" class="form-control" id="template-name" placeholder="Enter template name">
                    </div>
                    
                    <div class="form-group">
                        <label>Category</label>
                        <select class="form-control" id="template-category">
                            <option value="">Select category...</option>
                            <option value="1">DMV & Vehicle</option>
                            <option value="2">Employment & HR</option>
                            <option value="3">Insurance & Authorization</option>
                            <option value="4">Legal & Compliance</option>
                            <option value="5">Medical Records</option>
                            <option value="6">Disability & FMLA</option>
                            <option value="7">School & Education</option>
                            <option value="8">Sports & Recreation</option>
                            <option value="9">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Country</label>
                        <select class="form-control" id="template-country">
                            <option value="">Select country...</option>
                            <option value="United States">United States</option>
                            <option value="Canada">Canada</option>
                            <option value="United Kingdom">United Kingdom</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>State/Province</label>
                        <input type="text" class="form-control" id="template-state" placeholder="e.g., Massachusetts">
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" id="template-description" placeholder="Brief description of this form"></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" id="submit-to-library">
                        <label for="submit-to-library">Share with MedEx Library</label>
                    </div>
                    
                    <button class="btn btn-primary btn-block" onclick="saveTemplate()">
                        💾 Save Template
                    </button>
                    
                    <button class="btn btn-secondary btn-block" style="margin-top: 8px;" onclick="loadNewPdf()">
                        📄 Load Different PDF
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Main Content - PDF Viewer -->
        <div class="main-content">
            <div class="toolbar">
                <div class="toolbar-group">
                    <button class="toolbar-btn" onclick="previousPage()" title="Previous Page">
                        ◀ Prev
                    </button>
                    <span class="page-info">
                        Page <span id="current-page">1</span> of <span id="total-pages">1</span>
                    </span>
                    <button class="toolbar-btn" onclick="nextPage()" title="Next Page">
                        Next ▶
                    </button>
                </div>
                
                <div class="toolbar-group zoom-controls">
                    <button class="toolbar-btn" onclick="zoomOut()" title="Zoom Out">−</button>
                    <span class="zoom-level" id="zoom-level">100%</span>
                    <button class="toolbar-btn" onclick="zoomIn()" title="Zoom In">+</button>
                    <button class="toolbar-btn" onclick="fitToWidth()" title="Fit to Width">↔</button>
                </div>
                
                <div class="toolbar-group">
                    <button class="toolbar-btn" onclick="goBack()" title="Back to List">
                        ← Back to Templates
                    </button>
                </div>
            </div>
            
            <div class="pdf-container" id="pdf-container">
                <div class="empty-state" id="pdf-empty-state">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                    </svg>
                    <h3>No PDF Loaded</h3>
                    <p>Upload a PDF to start mapping fields</p>
                </div>
                <canvas id="pdf-canvas" style="display: none;"></canvas>
            </div>
        </div>
        
        <!-- Right Sidebar - Field Mappings -->
        <div class="sidebar-right">
            <!-- PDF Fields Panel -->
            <div class="panel-top">
                <div class="panel-header">
                    <h3>📋 PDF Form Fields</h3>
                    <span id="field-count" style="font-size: 12px; color: #a8a8a8;">0 fields</span>
                </div>
                <div class="field-list-container" id="field-list">
                    <div class="empty-state" style="padding: 20px;">
                        <p>No form fields detected</p>
                    </div>
                </div>
            </div>
            
            <!-- Field Mapping Panel -->
            <div class="panel-bottom">
                <div class="panel-header">
                    <h3>🔗 Field Mapping</h3>
                </div>
                <div class="field-mappings-container" id="mapping-editor">
                    <div class="empty-state" style="padding: 20px;">
                        <p>Select a field to configure mapping</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>
    
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
    <script>
        // Server-provided configuration (authenticated)
        const API_BASE_URL = '<?php echo $apiBaseUrl; ?>';
        const CUSTOMER_ID = <?php echo (int)$customerId; ?>;
        const CSRF_TOKEN = '<?php echo $csrfToken; ?>';
        const TEMPLATE_ID = '<?php echo htmlspecialchars($templateId); ?>';
        
        // Make it available globally for the mapping script
        window.API_BASE_URL = API_BASE_URL;
        
        console.log('PDF Editor initialized');
        console.log('API_BASE_URL:', API_BASE_URL);
        console.log('CUSTOMER_ID:', CUSTOMER_ID);
        console.log('TEMPLATE_ID:', TEMPLATE_ID);
    </script>
    <script src="admin_pdf_advanced_mapping.js"></script>
    <script>
        // PDF.js configuration
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        
        // State
        let pdfDoc = null;
        let currentPage = 1;
        let scale = 1.0;
        let pdfFields = [];
        let fieldMappings = {};
        let currentTemplateId = TEMPLATE_ID || null;
        let pdfBytes = null;
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            setupUploadArea();
            
            // If we have a template_id, load it
            if (currentTemplateId) {
                loadExistingTemplate(currentTemplateId);
            }
        });
        
        // Upload area setup
        function setupUploadArea() {
            const uploadArea = document.getElementById('upload-area');
            const fileInput = document.getElementById('pdf-input');
            
            uploadArea.addEventListener('click', () => fileInput.click());
            
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                const file = e.dataTransfer.files[0];
                if (file && file.type === 'application/pdf') {
                    loadPdfFile(file);
                }
            });
            
            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    loadPdfFile(file);
                }
            });
        }
        
        // Load PDF file
        async function loadPdfFile(file) {
            try {
                showToast('Loading PDF...', 'info');
                
                const arrayBuffer = await file.arrayBuffer();
                pdfBytes = new Uint8Array(arrayBuffer);
                
                // Load with PDF.js for viewing
                pdfDoc = await pdfjsLib.getDocument({ data: pdfBytes }).promise;
                
                // Extract form fields using pdf-lib
                const pdfLibDoc = await PDFLib.PDFDocument.load(pdfBytes);
                const form = pdfLibDoc.getForm();
                const fields = form.getFields();
                
                pdfFields = fields.map((field, index) => {
                    const type = field.constructor.name.replace('PDF', '').replace('Field', '').toLowerCase();
                    return {
                        name: field.getName(),
                        type: type,
                        index: index,
                        page: 1 // pdf-lib doesn't easily give page, we'll estimate
                    };
                });
                
                // Show template section, hide upload
                document.getElementById('upload-section').style.display = 'none';
                document.getElementById('template-section').style.display = 'block';
                
                // Set template name from filename
                const templateName = file.name.replace('.pdf', '').replace(/_/g, ' ');
                document.getElementById('template-name').value = templateName;
                
                // Render first page
                document.getElementById('total-pages').textContent = pdfDoc.numPages;
                await renderPage(1);
                
                // Show PDF canvas
                document.getElementById('pdf-empty-state').style.display = 'none';
                document.getElementById('pdf-canvas').style.display = 'block';
                
                // Display fields
                displayFields();
                
                showToast(`Loaded ${pdfFields.length} form fields`, 'success');
            } catch (error) {
                console.error('Error loading PDF:', error);
                showToast('Error loading PDF: ' + error.message, 'error');
            }
        }
        
        // Load existing template
        async function loadExistingTemplate(templateId) {
            try {
                showToast('Loading template...', 'info');
                
                const response = await fetch(`${API_BASE_URL}?action=get&template_id=${templateId}`);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Failed to load template');
                }
                
                const template = data.template;
                
                // Set form values
                document.getElementById('template-name').value = template.template_name || '';
                document.getElementById('template-category').value = template.category_id || '';
                document.getElementById('template-country').value = template.country || '';
                document.getElementById('template-state').value = template.state || '';
                document.getElementById('template-description').value = template.description || '';
                document.getElementById('submit-to-library').checked = template.submit_to_library == 1;
                
                // Load the PDF
                const pdfUrl = `${API_BASE_URL}?action=serve&id=${templateId}&customer_id=${CUSTOMER_ID}`;
                const pdfResponse = await fetch(pdfUrl);
                const pdfArrayBuffer = await pdfResponse.arrayBuffer();
                pdfBytes = new Uint8Array(pdfArrayBuffer);
                
                // Load with PDF.js for viewing
                pdfDoc = await pdfjsLib.getDocument({ data: pdfBytes }).promise;
                
                // Extract form fields using pdf-lib
                const pdfLibDoc = await PDFLib.PDFDocument.load(pdfBytes);
                const form = pdfLibDoc.getForm();
                const fields = form.getFields();
                
                pdfFields = fields.map((field, index) => {
                    const type = field.constructor.name.replace('PDF', '').replace('Field', '').toLowerCase();
                    return {
                        name: field.getName(),
                        type: type,
                        index: index,
                        page: 1
                    };
                });
                
                // Load existing mappings
                if (data.mappings && data.mappings.length > 0) {
                    data.mappings.forEach(m => {
                        fieldMappings[m.field_name] = {
                            db_table: m.db_table,
                            db_field: m.db_field,
                            ai_prompt: m.ai_prompt,
                            static_value: m.static_value,
                            format_mask: m.format_mask
                        };
                    });
                }
                
                // Show template section, hide upload
                document.getElementById('upload-section').style.display = 'none';
                document.getElementById('template-section').style.display = 'block';
                
                // Render first page
                document.getElementById('total-pages').textContent = pdfDoc.numPages;
                await renderPage(1);
                
                // Show PDF canvas
                document.getElementById('pdf-empty-state').style.display = 'none';
                document.getElementById('pdf-canvas').style.display = 'block';
                
                // Display fields
                displayFields();
                
                showToast('Template loaded successfully', 'success');
            } catch (error) {
                console.error('Error loading template:', error);
                showToast('Error loading template: ' + error.message, 'error');
            }
        }
        
        // Render PDF page
        async function renderPage(pageNum) {
            const page = await pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: scale });
            
            const canvas = document.getElementById('pdf-canvas');
            const context = canvas.getContext('2d');
            
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            
            await page.render({
                canvasContext: context,
                viewport: viewport
            }).promise;
            
            currentPage = pageNum;
            document.getElementById('current-page').textContent = pageNum;
        }
        
        // Navigation
        function previousPage() {
            if (currentPage > 1) {
                renderPage(currentPage - 1);
            }
        }
        
        function nextPage() {
            if (pdfDoc && currentPage < pdfDoc.numPages) {
                renderPage(currentPage + 1);
            }
        }
        
        // Zoom
        function zoomIn() {
            scale = Math.min(scale + 0.25, 3.0);
            updateZoom();
        }
        
        function zoomOut() {
            scale = Math.max(scale - 0.25, 0.5);
            updateZoom();
        }
        
        function fitToWidth() {
            const container = document.getElementById('pdf-container');
            if (pdfDoc) {
                pdfDoc.getPage(currentPage).then(page => {
                    const viewport = page.getViewport({ scale: 1.0 });
                    scale = (container.clientWidth - 40) / viewport.width;
                    updateZoom();
                });
            }
        }
        
        function updateZoom() {
            document.getElementById('zoom-level').textContent = Math.round(scale * 100) + '%';
            if (pdfDoc) {
                renderPage(currentPage);
            }
        }
        
        // Display fields in sidebar
        function displayFields() {
            const container = document.getElementById('field-list');
            container.innerHTML = '';
            
            if (pdfFields.length === 0) {
                container.innerHTML = '<div class="empty-state" style="padding: 20px;"><p>No form fields detected</p></div>';
                return;
            }
            
            pdfFields.forEach((field, index) => {
                const isMapped = fieldMappings[field.name] && 
                    (fieldMappings[field.name].db_field || 
                     fieldMappings[field.name].ai_prompt || 
                     fieldMappings[field.name].static_value);
                
                const item = document.createElement('div');
                item.className = 'field-item' + (isMapped ? ' mapped' : '');
                item.setAttribute('data-field-index', index);
                
                const iconClass = getFieldIconClass(field.type);
                const mappingText = isMapped ? getMappingText(fieldMappings[field.name]) : 'Not mapped';
                
                item.innerHTML = `
                    <div class="field-icon ${iconClass}">${getFieldIcon(field.type)}</div>
                    <div class="field-info">
                        <div class="field-name" title="${field.name}">${field.name}</div>
                        <div class="field-mapping">${mappingText}</div>
                    </div>
                    <div class="field-page">p${field.page}</div>
                `;
                
                item.addEventListener('click', () => selectField(index));
                container.appendChild(item);
            });
            
            document.getElementById('field-count').textContent = `${pdfFields.length} fields`;
        }
        
        function getFieldIconClass(type) {
            const classes = {
                'text': 'text',
                'checkbox': 'checkbox',
                'radiogroup': 'radio',
                'dropdown': 'text',
                'signature': 'signature'
            };
            return classes[type] || 'text';
        }
        
        function getFieldIcon(type) {
            const icons = {
                'text': 'T',
                'checkbox': '☑',
                'radiogroup': '◉',
                'dropdown': '▼',
                'signature': '✍'
            };
            return icons[type] || 'T';
        }
        
        function getMappingText(mapping) {
            if (mapping.db_field) {
                return `${mapping.db_table}.${mapping.db_field}`;
            }
            if (mapping.ai_prompt) {
                return 'AI: ' + mapping.ai_prompt.substring(0, 20) + '...';
            }
            if (mapping.static_value) {
                return 'Static: ' + mapping.static_value.substring(0, 15) + '...';
            }
            return 'Not mapped';
        }
        
        // Select field for editing
        function selectField(index) {
            // Update selection visual
            document.querySelectorAll('.field-item').forEach(item => {
                item.classList.remove('selected');
            });
            document.querySelector(`[data-field-index="${index}"]`).classList.add('selected');
            
            const field = pdfFields[index];
            const mapping = fieldMappings[field.name] || {};
            
            // Use the advanced mapping UI from admin_pdf_advanced_mapping.js
            if (typeof generateAdvancedMappingUI === 'function') {
                const editorContainer = document.getElementById('mapping-editor');
                editorContainer.innerHTML = generateAdvancedMappingUI(field, mapping, pdfFields, index);
            }
        }
        
        // Save template
        async function saveTemplate() {
            const templateName = document.getElementById('template-name').value.trim();
            if (!templateName) {
                showToast('Please enter a template name', 'error');
                return;
            }
            
            if (!pdfBytes) {
                showToast('No PDF loaded', 'error');
                return;
            }
            
            try {
                showToast('Saving template...', 'info');
                
                const formData = new FormData();
                formData.append('action', 'upload');
                formData.append('customer_id', CUSTOMER_ID);
                formData.append('template_name', templateName);
                formData.append('category_id', document.getElementById('template-category').value);
                formData.append('country', document.getElementById('template-country').value);
                formData.append('state', document.getElementById('template-state').value);
                formData.append('description', document.getElementById('template-description').value);
                formData.append('submit_to_library', document.getElementById('submit-to-library').checked ? '1' : '0');
                formData.append('field_mappings', JSON.stringify(fieldMappings));
                
                // If editing existing template, include the ID
                if (currentTemplateId) {
                    formData.append('template_id', currentTemplateId);
                }
                
                // Add PDF file
                const pdfBlob = new Blob([pdfBytes], { type: 'application/pdf' });
                formData.append('pdf_file', pdfBlob, templateName.replace(/\s+/g, '_') + '.pdf');
                
                const response = await fetch(API_BASE_URL, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    currentTemplateId = result.template_id;
                    showToast(`Template saved! ID: ${result.template_id}`, 'success');
                } else {
                    throw new Error(result.error || 'Failed to save template');
                }
            } catch (error) {
                console.error('Error saving template:', error);
                showToast('Error saving template: ' + error.message, 'error');
            }
        }
        
        // Load new PDF
        function loadNewPdf() {
            document.getElementById('pdf-input').click();
        }
        
        // Go back to templates list
        function goBack() {
            window.location.href = 'index.php';
        }
        
        // Toast notifications
        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Update mapping (called from advanced mapping UI)
        function updateMapping(fieldName, mappingData) {
            fieldMappings[fieldName] = mappingData;
            displayFields(); // Refresh to show mapping status
        }
        
        // Make available globally
        window.updateMapping = updateMapping;
        window.fieldMappings = fieldMappings;
    </script>
</body>
</html>
