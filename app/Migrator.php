<?php

namespace App;

final class Migrator {
    public static function migrate(): void {
        try {
            $completedMigrations = [...DB::get("SELECT name FROM migrations")];
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), "no such table")) {
                DB::exec("CREATE TABLE migrations (name TEXT NOT NULL UNIQUE)");
                $completedMigrations = [];
            }
        }

        $completedMigrations = array_column($completedMigrations, "name");
        $uncompletedMigrations = glob(__DIR__ . '/../migrations/*.php');
        $uncompletedMigrations = array_map(fn (string $path): string => basename($path, ".php"), $uncompletedMigrations);

        $migrations = array_diff($uncompletedMigrations, $completedMigrations);
        self::lock() || return;
    }

    private static function lock(): true {
        $fp = fopen(storagePath("private/migrator.lock"), "w");
        $ours = flock($fp, LOCK_EX | LOCK_NB);
    }
}