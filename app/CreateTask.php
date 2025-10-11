<?php

namespace App;

final class CreateTask implements Controller {
    public static function entrypoint(): never {
        if (!validate("string-not-empty", $_POST["title"] ?? null)) die("Form validation failure: title");
        if (!validate("string", $_POST["notes"] ?? null)) die("Form validation failure: notes");

        $title = $_POST["title"] ?? "";
        $title = trim($title);
        
        $notes = $_POST["notes"] ?? "";
        $notes = str_replace("\r", "", $notes);
        $notes = trim($notes);
        
        $slug = slugify($title);

        $rand = bin2hex(random_bytes(4));

        writeYamlFile(storagePath("tasks/inbox/{$slug}-{$rand}.yml"), [
            "version" => 1,
            "title" => $title,
            "notes" => $notes,
            "created_at" => new \DateTime(),
            "updated_at" => new \DateTime(),
        ]);

        redirect("/inbox");
        exit;
    }
}
