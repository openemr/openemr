<?php
/**
 * This is a test file to help PhpStorm recognize the project structure
 * You can delete this file after PhpStorm properly recognizes your project
 */

// Include the PDFGenerationService
require_once __DIR__ . '/src/Bootstrap.php';

use GeneralDermatology\Services\PDFGenerationService;

// Just a simple test to verify the class is recognized
$pdfService = new PDFGenerationService();

echo "Project structure test successful!";
