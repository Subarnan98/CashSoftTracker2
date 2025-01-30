<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class CsvExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('read_csv', [$this, 'readCsv']),
        ];
    }

    public function readCsv(string $filePath): array
    {
        $data = [];
        
        if (($handle = fopen($filePath, 'r')) !== false) 
        {
            while (($row = fgetcsv($handle, 1000, ';')) !== false) 
            {
                $data[] = $row;
            }
            
            fclose($handle);
        }

        return $data;
    }
}
