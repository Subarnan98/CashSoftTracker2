<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        SymfonySetList::SYMFONY_60,
        DoctrineSetList::DOCTRINE_ORM_29,
    ]);
};
