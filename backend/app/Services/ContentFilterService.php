<?php

namespace App\Services;

class ContentFilterService
{
    /**
     * Check if the given text contains inappropriate content.
     */
    public function hasBadContent(?string $text): bool
    {
        if (empty($text)) {
            return false;
        }

        // 1. Check for consecutive asterisks (e.g., **, ***, which are often used to mask vulgar terms)
        if (preg_match('/\*{2,}/', $text)) {
            return true;
        }

        // 2. Check for bad words using UTF-8 word boundaries
        // List of common sensitive Vietnamese words/abbreviations:
        // - cc, cl, vl, vcl, cđm
        // - đm, dm, đmm, dmm, cmn
        // - đéo, deo, đẽo, đệt, det
        // - chó, chó má, vãi l, vail
        $badWords = [
            'cc', 'cl', 'vl', 'vcl', 'cđm',
            'đm', 'dm', 'đmm', 'dmm', 'cmn',
            'đéo', 'deo', 'đẽo', 'đệt', 'det',
            'chó', 'chó má', 'vãi l', 'vail',
        ];

        // Construct a pattern that matches any of the bad words, ensuring they are independent words (not part of other words)
        // (?<!\p{L}) and (?!\p{L}) are Unicode-aware word boundary markers.
        // The 'u' modifier is crucial for UTF-8 matching, and 'i' makes it case-insensitive.
        $pattern = '/(?<!\p{L})('.implode('|', array_map('preg_quote', $badWords)).')(?!\p{L})/ui';

        if (preg_match($pattern, $text)) {
            return true;
        }

        return false;
    }
}
