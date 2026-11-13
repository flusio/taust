<?php

/**
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/public/index.php',
        __DIR__ . '/src',
    ])
    ->withPhpSets()
    ->withComposerBased()
    ->withPreparedSets(
        deadCode: true,
        typeDeclarations: true,
    )
    ->withImportNames(
        importNames: false,
        removeUnusedImports: true,
    )
    ->withSkip([
        ClassPropertyAssignToConstructorPromotionRector::class,
        ReadOnlyPropertyRector::class,
    ]);
