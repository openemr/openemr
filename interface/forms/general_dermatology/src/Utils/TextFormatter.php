<?php

namespace GeneralDermatology\Utils;

/**
 * Text Formatter Utility Class
 * 
 * Provides helper methods for text formatting and manipulation
 */
class TextFormatter
{
    /**
     * Convert a database field name to a human-readable label
     * 
     * @param string $fieldName The database field name
     * @return string The formatted label
     */
    public static function fieldToLabel(string $fieldName): string
    {
        return ucwords(str_replace('_', ' ', $fieldName));
    }
    
    /**
     * Safely get a value with a default if not set
     * 
     * @param array $array The source array
     * @param string $key The key to look for
     * @param mixed $default The default value if not found
     * @return mixed The value or default
     */
    public static function safeGetValue(array $array, string $key, $default = 'None')
    {
        return isset($array[$key]) && !empty($array[$key]) ? $array[$key] : $default;
    }
    
    /**
     * Format a date string to a readable format
     * 
     * @param string $dateString The date string to format
     * @param string $format The desired output format
     * @return string The formatted date
     */
    public static function formatDate(string $dateString, string $format = 'F j, Y'): string
    {
        if (empty($dateString)) {
            return '';
        }
        
        // Handle date-time strings by splitting
        if (strpos($dateString, ' ') !== false) {
            $parts = explode(' ', $dateString);
            $dateString = $parts[0];
        }
        
        return date($format, strtotime($dateString));
    }
    
    /**
     * Calculate the height of text based on wrapping
     * 
     * @param string $text The text to calculate height for
     * @param float $fontHeight The height of one line of text
     * @param int $maxWidth The maximum width for wrapping
     * @return float The calculated height
     */
    public static function calculateTextHeight(string $text, float $fontHeight, int $maxWidth): float
    {
        if (empty($text)) {
            return 0;
        }
        
        $lines = explode("<br>", wordwrap($text, $maxWidth, "<br>\n"));
        return count($lines) * $fontHeight;
    }
    
    /**
     * Safely implode an array with a delimiter, handling empty arrays
     * 
     * @param array $array The array to implode
     * @param string $delimiter The delimiter to use
     * @param string $default The default value if array is empty
     * @return string The imploded string or default
     */
    public static function safeImplode(array $array, string $delimiter = ', ', string $default = 'None'): string
    {
        return !empty($array) ? implode($delimiter, $array) : $default;
    }
    
    /**
     * Clean and sanitize text for output
     * 
     * @param string $text The text to clean
     * @return string The cleaned text
     */
    public static function cleanText($text): string
    {
        if (empty($text)) {
            return '';
        }
        
        // Use OpenEMR's text() function if available, otherwise use htmlspecialchars
        if (function_exists('text')) {
            return text($text);
        }
        
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
