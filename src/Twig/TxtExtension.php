<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class TxtExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('read_file', [$this, 'readFile']),
        ];
    }


    public function readFile(string $filePath): string
    {
        // Get the full path (adjust if necessary)
        $fullPath = __DIR__ . '/../../' . $filePath;

        // Check if the file exists and read it
        return file_exists($fullPath) ? file_get_contents($fullPath) : '';
    }
    
}