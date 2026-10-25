<?php
/**
 * PDF Settings Configuration File
 * 
 * This file contains configuration settings for PDF generation in the General Dermatology module.
 */

return [
    // Page settings
    'page' => [
        'format' => 'A4',
        'orientation' => 'P', // Portrait
        'margin_left' => 20, // Increased from 15
        'margin_right' => 20, // Increased from 15
        'margin_top' => 20, // Increased from 16
        'margin_bottom' => 20, // Increased from 16
        'margin_header' => 10, // Increased from 9
        'margin_footer' => 10, // Increased from 9
        'height' => 297, // A4 height in mm
        'width' => 210,  // A4 width in mm
    ],
    
    // Font settings
    'font' => [
        'default_size' => 9,
        'header_size' => 12,
        'title_size' => 14,
        'line_height' => 4, // Approximate height of one line of text in mm
    ],
    
    // Layout settings
    'layout' => [
        'plan_spacing' => 10,       // Increased from 8 - Spacing after each plan in mm
        'section_spacing' => 12,    // Increased from 10 - Spacing between sections in mm
        'text_max_width' => 90,     // Decreased from 100 - Maximum width for text wrapping
        'available_space' => 207,   // Adjusted for new margins (page height - margins)
    ],
    
    // Facility information
    'facility' => [
        'phone' => 'TEL 305 404 DERM (3376)',
        'fax' => 'FAX 205 404 MDMS (6367)',
        'address' => '4770 Biscayne Blvd, Suite 900, Miami, FL 33137',
        'website' => 'miamiderms.com',
    ],
];
