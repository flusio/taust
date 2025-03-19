<?php

/**
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/public/index.php',
        __DIR__ . '/src',
    ])
    ->withSkip([
        __DIR__ . '/src/views',
    ])
    ->withSets([
        SetList::PHP_82,
        SetList::PHP_83,
        SetList::PHP_84,
        SetList::TYPE_DECLARATION,
    ]);
