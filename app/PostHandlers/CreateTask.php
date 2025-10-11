<?php

namespace App\PostHandlers;

use App\Storable\Task;

final class CreateTask implements PostHandler {
    public static function entrypoint(): never {
        if (!validate("string-not-empty", $_POST["title"] ?? null)) die("Form validation failure: title");
        if (!validate("string", $_POST["notes"] ?? null)) die("Form validation failure: notes");

        $title = $_POST["title"] ?? "";
        $title = trim($title);
        
        $notes = $_POST["notes"] ?? "";
        $notes = str_replace("\r", "", $notes);
        $notes = trim($notes);
        
        $task = new Task([
            "title" => $title,
            "notes" => $notes,
            "list" => "inbox",
            "created_at" => new \DateTime(),
            "updated_at" => new \DateTime(),
        ]);
        $task->save();

        redirect("/inbox");
        exit;
    }
}
