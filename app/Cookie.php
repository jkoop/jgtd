<?php

namespace App;

final class Cookie {
    private function __construct() {}

    /**
     * @return mixed null if not set or signature is invalid
     */
    public static function get(string $name): mixed {
        $value = $_COOKIE[$name] ?? null;
        if ($value === null) return null;

        $value = Crypt::unSign($value);
        if ($value === null) return null;

        return json_decode($value);
    }
    
    /**
     * @param mixed $value unsets the cookie if null
     * @return void
     */
    public static function set(string $name, mixed $value, int $lifetime = 0): void {
        if ($value === null) {
            setcookie($name, "", 1); // expires just after 1970-01-01
            return;
        }

        $value = json_encode($value);
        $value = Crypt::sign($value);
        setcookie($name, $value, $lifetime > 0 ? time() + $lifetime : 0);
    }
}