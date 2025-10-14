<?php

namespace App;

final class Template {
	private function __construct() {
	}

	public static function beforeContent(string|array $title): string {
		if (!is_array($title)) $title = [$title];
		$documentTitle = e(implode(" - ", array_reverse($title)), linkify: false);
		$pageTitle = e(implode(": ",$title));

		$iconT = filemtime(publicPath("/favicon.png"));
		$iconDT = filemtime(publicPath("/favicon-dark.png"));
		$styleT = filemtime(publicPath("/style.css"));
		$scriptT = filemtime(publicPath("/script.js"));
		$nonce = nonce();

		return <<<HTML
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="utf8" />
				<meta name="viewport" content="width=device-width, initial-scale=1" />
				<title>$documentTitle - JGTD</title>
				<link href="/favicon.png?t=$iconT" rel="icon" />
				<link href="/favicon-dark.png?t=$iconDT" rel="icon" media="(prefers-color-scheme: dark)" />
				<link href="/style.css?t=$styleT" rel="stylesheet" />
				<script src="/script.js?t=$scriptT" $nonce></script>
			</head>
			<body>
				<nav>
					<a href="/inbox" x-key-combo="I">Inbox</a> -
					<a href="/new-task" x-key-combo="N">New Task</a>
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
