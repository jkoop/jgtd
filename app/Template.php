<?php

namespace App;

final class Template {
    private function __construct() {
    }

    public static function beforeContent(string|array $title): string {
        if (!is_array($title)) $title = [$title];
        $documentTitle = e(implode(" - ", array_reverse($title)));
        $pageTitle = e(implode(": ",$title));

        return <<<HTML
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf8" />
                <meta name="viewport" content="width=device-width, initial-scale=1" />
                <title>$documentTitle - To Do</title>
                <link href="/favicon.png" rel="icon" />
                <link href="/style.css" rel="stylesheet" />
            </head>
            <body>
                <nav>
                    <a href="/inbox">Inbox</a> -
                    <a href="/new-task">New Task</a>
                </nav>
                <main>
                    <h1>$pageTitle</h1>
        HTML;
    }

    public static function afterContent(): string {
        // $diskUsage = exec("du -sh " . escapeshellarg(storagePath('')));
        // $diskUsage = explode("\t", $diskUsage)[0];
        // $diskUsage = e($diskUsage);

        // $pageTime = microtime(true) - START_TIME;
        // $pageTime = number_format($pageTime, 3);
        // $pageTime .= 's';

        return <<<HTML
                </main>
                <footer>
                    Footer
                    <!-- Site takes \$diskUsage of disk; page took \$pageTime -->
                </footer>
            </body>
        </html>
        HTML;
    }
}