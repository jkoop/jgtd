<?php

use App\Template;

function init(): void {
    global $config;
    global $nonce;

    session_set_cookie_params([
        "lifetime" => 0, // until the browser is closed
        "path" => "/", // the whole site
        // "domain" => 
        // secure
        "httponly" => true,
        "samesite" => true,
    ]);

    // assure that the storage directory exists
    if (!is_dir(storagePath("/"))) mkdir(storagePath("/"));

    // assure that there's a git repo in the storage directory
    exec("git -C " . escapeshellarg(storagePath("/")) . " branch", result_code: $exitCode);
    if ($exitCode != 0) exec("git -C " . escapeshellarg(storagePath("/")) . " init");

    $saveConfig = false;
    $config = readYamlFile(storagePath("/config.yml")) ?: [ "version" => 1 ];

    if (!array_key_exists("password", $config)) {
        $newPassword = bin2hex(random_bytes(24));
        $config["password"] = password_hash($newPassword, null);
        $saveConfig = true;
        error_log("\n\n\nYour new password is:\n$newPassword\n\n\n");
    }

    if (
        !array_key_exists("timezone", $config) ||
        date_default_timezone_set($config["timezone"]) == false
    ) {
        $config["timezone"] = "America/Winnipeg";
        date_default_timezone_set($config["timezone"]);
        $saveConfig = true;
    }

    if (!array_key_exists("secret", $config)) {
        $config["secret"] = bin2hex(random_bytes(24));
        $saveConfig = true;
    }

    if ($saveConfig) {
        writeYamlFile(storagePath("/config.yml"), $config);
    }

    $nonce = bin2hex(random_bytes(12));
    header("Content-Security-Policy: script-src 'nonce-$nonce'");
}

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

function nonce(): string {
    global $nonce;
    return "nonce=\"$nonce\"";
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

function slugify(string $string, bool $limitLength = false): string {
    static $transliterator = Transliterator::create("Any-Latin; Latin-ASCII");
    $string = $transliterator->transliterate($string);
    $string = strtolower($string);
    $string = str_replace("'", "", $string);
    $string = preg_replace("/[^A-Za-z0-9-_]/", "-", $string);
    $string = trim($string, "-");

    if ($limitLength) {
        $string = substr($string, 0, 200);
        $string = trim($string, "-");
    }

    return $string;
}

const STORAGE_PATH = __DIR__ . '/../storage';

function storagePath(string $path): string {
    return STORAGE_PATH . "/" . $path;
}

const PUBLIC_PATH = __DIR__ . '/../html';

function publicPath(string $path): string {
    return PUBLIC_PATH . "/" . $path;
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

function writeYamlFile(string $path, array $data): void {
    sortForStorage($data);
    @mkdir(dirname($path), recursive: true);
    yaml_emit_file($path, $data, YAML_UTF8_ENCODING, YAML_LN_BREAK, YAML_EMIT_CALLBACKS) || die("Couldn't write file.");
}

function sortForStorage(array &$array): void {
    ksort($array);
    foreach ($array as &$value) {
        if (is_array($value)) sortForStorage($value);
    }
}

init();
