<?php

namespace App;

final class Template {
    private function __construct() {
    }

    public static function beforeContent(string|array $title): string {
        if (!is_array($title)) $title = [$title];
        $documentTitle = e(implode(" - ", array_reverse($title)));
        $pageTitle = e(implode(": ",$title));

        $iconT = filemtime(publicPath("/favicon.png"));
        $styleT = filemtime(publicPath("/style.css"));

        return <<<HTML
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf8" />
                <meta name="viewport" content="width=device-width, initial-scale=1" />
                <title>$documentTitle - JGTD</title>
                <link href="/favicon.png?t=$iconT" rel="icon" />
                <link href="/style.css?t=$styleT" rel="stylesheet" />
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
        return <<<HTML
                </main>
                <footer>
                    Footer
                    <a style="float:right;clear:both" href="https://github.com/jkoop/jgtd" target="_blank">JGTD</a>
                </footer>
            </body>
        </html>
        HTML;
    }
}