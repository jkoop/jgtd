<?php

namespace App;

final class Router {
    public static function route(): never {
        global $path;

        $path = $_SERVER["REQUEST_URI"];
        $path = explode("?", $path)[0];
        $path = preg_replace("#^/#", "", $path); // remove leading slash
        $compare = $path;

        $path = preg_replace("#/+#", "/", $path);
        $path = preg_replace("#/$#", "", $path);

        if ($path != $compare) {
            $newUri = "/" . $path;

            if (str_contains($_SERVER["REQUEST_URI"], "?")) {
                $newUri .= substr($_SERVER["REQUEST_URI"], strlen($compare) + 1);
            }

            redirect("Location: $newUri");
        }

        $path = explode("/", $path);
        $path = array_map("urldecode", $path);

        $method = $_SERVER["REQUEST_METHOD"];

        switch ($path[0]) {
            case "":
                switch ($method) {
                    case "HEAD":
                    case "GET":
                        redirect("/new-task");
                    default:
                        methodNotAllowed(["HEAD", "GET"]);
                }

            case "inbox":
                switch ($method) {
                    case "HEAD":
                    case "GET":
                        Auth::require();
                        page("inbox");
                    default:
                        methodNotAllowed(["HEAD", "GET"]);
                }

            case "task":
                switch ($method) {
                    case "HEAD":
                    case "GET":
                        Auth::require();
                        page("task");
                    default:
                        methodNotAllowed(["HEAD", "GET"]);
                }

            case "new-task":
                switch ($method) {
                    case "HEAD":
                    case "GET":
                        Auth::require();
                        page("new-task");
                    case "POST":
                        Auth::require();
                        CreateTask::entrypoint();
                    default:
                        methodNotAllowed(["HEAD", "GET", "POST"]);
                }

            case "login":
                switch ($method) {
                    case "HEAD":
                    case "GET":
                        page("login");
                    case "POST":
                        Login::entrypoint();
                    default:
                        methodNotAllowed(["HEAD", "GET", "POST"]);
                }
        }

        notFound();
    }
}
