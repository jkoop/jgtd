<?php

namespace App\Storable;

final class Task extends Storable {
	public function getWebPath(): string {
		return "/task/$this->id";
	}

	public function getStoragePrefix(): string {
		return $this->list ??= "inbox";
	}
}
