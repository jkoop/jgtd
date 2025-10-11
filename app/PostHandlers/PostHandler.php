<?php

namespace App\PostHandlers;

interface PostHandler {
    public static function entrypoint(): never;
}