<?php

namespace App\PostHandlers;

use App\Auth;

final class Login implements PostHandler {
    public static function entrypoint(): never {
        $password = $_POST["password"];
        if (!validate("string-not-empty", $password)) redirect("/login");

        Auth::attempt($password);

        redirect("/login");
        exit;
    }
}