<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TextFileExtractorExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('extract_text_and_files', [$this, 'extractTextAndFiles']),
        ];
    }

    public function extractTextAndFiles(string $content): array
    {
        // Regex for extracting src/href filenames
        preg_match_all('/(?:src|href)="[^"]*\/([^\/"]+\.[a-zA-Z0-9]+)"/', $content, $fileMatches);
        $files = $fileMatches[1] ?? [];

        // Regex for extracting plain text outside of tags
        preg_match_all('/>([^<>\n]+)</', $content, $textMatches);
        $texts = array_map('trim', $textMatches[1] ?? []);

        // Filter out empty strings and non-filenames
        $texts = array_filter($texts, function ($text) {
            return !preg_match('/\.[a-zA-Z0-9]+$/', $text); // Exclude text with extensions
        });

        // Merge and return
        return array_values(array_merge($texts, $files));
    }
}
