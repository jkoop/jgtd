<?php

namespace App;

final class Login implements Controller {
    public static function entrypoint(): never {
        $password = $_POST["password"];
        if (!validate("string-not-empty", $password)) redirect("/login");

        Auth::attempt($password);

        redirect("/login");
        exit;
    }
}