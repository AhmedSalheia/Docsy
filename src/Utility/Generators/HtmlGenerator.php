<?php

namespace Docsy\Utility\Generators;

use Docsy\Collection;
use Docsy\Docsy;
use Docsy\Folder;
use Docsy\Request;
use Docsy\Utility\Variable;

class HtmlGenerator extends AbstractGenerator
{
    public static function generate(Docsy $docsy, string $collection = "", array $options = []): string
    {
        ob_start();
        ?>
        <html lang="en">
        <head><title>API Docs</title></head>
        <body>
        <h1>API Documentation</h1>
        <?php foreach ($docsy->collections() as $collection): ?>
            <h2><?= htmlspecialchars($collection->name) ?></h2>
            <?php foreach ($collection->requests() as $request): ?>
                <h3><?= $request->method->value ?> <?= htmlspecialchars($request->path) ?></h3>
                <p><?= nl2br(htmlspecialchars($request->description)) ?></p>
                <!-- Continue rendering parameters, etc. -->
            <?php endforeach ?>
        <?php endforeach ?>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    protected function transformCollection(Collection $collection, array $options = []): string|array
    {
        return [];
    }

    protected static function transformVariables(Variable $variable, array $options = []): string
    {
        // TODO: Implement transformVariables() method.
    }

    protected static function transformFolder(Folder $folder, int $level = 1, array $options = []): string
    {
        // TODO: Implement transformFolder() method.
    }

    protected static function transformRequest(Request $request, int $level = 1, array $options = []): string
    {
        // TODO: Implement transformRequest() method.
    }
}