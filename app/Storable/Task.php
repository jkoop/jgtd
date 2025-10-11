<?php

namespace App\Storable;

final class Task extends Storable {
    public function getWebPath(): string {
        return "/task/$this->slug-$this->id";
    }

    public function getSlug(): string {
        return slugify($this->title);
    }

    public function getStoragePrefix(): string {
        return $this->list;
    }
}