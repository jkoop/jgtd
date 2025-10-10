<?php

use App\Migrator;
use App\Template;

date_default_timezone_set("America/Winnipeg");
// Migrator::migrate();

function e(string $string): string {
    return htmlentities($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
}

function methodNotAllowed(array $allow): void {
    global $allowedMethods;
    $allowedMethods = implode(", ", $allow);
    http_response_code(405);
    header("Allow: " . $allowedMethods);
    page("method-not-allowed");
}

function notFound(): never {
    http_response_code(404);
    page("not-found");
}

function noQueryString(): void {
    global $path;
    if (count($_GET)) {
        redirect(pathStringFromArray($path));
    }
}

function page(string $name): never {
    include __DIR__ . "/../pages/$name.php";
    exit;
}

/**
 * @return string Adds leading slash
 */
function pathStringFromArray(array $path): string {
    return "/" . implode("/", array_map("urlencode", $path));
}

function redirect(string $location): never {
    header("Location: $location");
    echo Template::beforeContent("Redirection");
    $location = e($location);
    echo <<<HTML
    <p>Go to <a href="$location">$location</a>.</p>
    HTML;
    echo Template::afterContent();
    exit;
}

function slugify(string $string): string {
    static $transliterator = Transliterator::create("Any-Latin; Latin-ASCII");
    $string = $transliterator->transliterate($string);
    $string = strtolower($string);
    $string = str_replace("'", "", $string);
    $string = preg_replace("/[^A-Za-z0-9-_]/", "-", $string);
    $string = trim($string, "-");
    return $string;
}

function storagePath(string $path): string {
    return __DIR__ . '/../storage/' . $path;
}

function validate(string $type, mixed $value): bool {
    switch ($type) {
        case "date":
            if (!is_string($value)) return false;
            if (!preg_match("/^\d{4}-\d\d-\d\d$/", $value)) return false;
            if (strtotime($value) === false) return false;
            return true;
        case "string-not-empty":
            if (!is_string($value)) return false;
            if (empty(trim($value))) return false;
            return true;
        case "string":
            if (!is_string($value)) return false;
            return true;
    }

    throw new \InvalidArgumentException('$type is invalid.');
}

const YAML_DATETIME_FORMAT = "Y-m-d H:i:s.u O";
const DATETIME_FORMAT = "M j 'y g:ia";

define("YAML_EMIT_CALLBACKS", [
    DateTime::class => function (DateTime $value): array {
        return ["tag" => "!datetime", "data" => $value->format(YAML_DATETIME_FORMAT)];
    },
]);

define("YAML_PARSE_CALLBACKS", [
    "!datetime" => function (string $value, $tag, $flags): DateTime {
        return DateTime::createFromFormat(YAML_DATETIME_FORMAT, $value);
    },
]);

function readYamlFile(string $path): mixed {
    return yaml_parse_file($path, 0, $ndocs, callbacks: YAML_PARSE_CALLBACKS);
}

function writeYamlFile(string $path, array|object $data): void {
    @mkdir(dirname($path), recursive: true);
    yaml_emit_file($path, $data, YAML_UTF8_ENCODING, YAML_LN_BREAK, YAML_EMIT_CALLBACKS) || die("Couldn't write file.");
}