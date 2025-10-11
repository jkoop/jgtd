<?php

namespace App\Storable;

abstract class Storable {
    /**
     * Relative to the base storage path
     * @return string
     */
    public function getStorageDirectory(): string {
        $class = get_class($this);
        $class = explode("\\", $class);
        $class = array_slice($class, 2);
        $class = implode("", $class);
        $class = preg_replace("/([A-Z])/", "-\\1", $class);
        $class = strtolower($class);
        $class = trim($class, "-");
        return $class;
    }
}