<?php

namespace App\Storable;

use Generator;

abstract class Storable {
	public const ID_CHAR_POOL = "0123456789abcdefghjkmnpqrstvwxyz";

	public function __construct(private array $attributes = [], private string|null $storagePath = null) {
		if (isset($this->attributes["id"]) == false) {
			$this->attributes["id"] = static::generateId();
		}

		// it's saved with the wrong id. fix it
		if (
			isset($this->storagePath) &&
			!str_ends_with(preg_replace("/\.yml$/", "", $this->storagePath), "-" . $this->attributes["id"])
		) {
			$this->save();
		}
	}

	public static function generateId(): string {
		$maxIndex = strlen(static::ID_CHAR_POOL) - 1;
		return // 32 ** 6 = 1,073,741,824 combinations
			static::ID_CHAR_POOL[random_int(0, $maxIndex)] .
			static::ID_CHAR_POOL[random_int(0, $maxIndex)] .
			static::ID_CHAR_POOL[random_int(0, $maxIndex)] .
			static::ID_CHAR_POOL[random_int(0, $maxIndex)] .
			static::ID_CHAR_POOL[random_int(0, $maxIndex)] .
			static::ID_CHAR_POOL[random_int(0, $maxIndex)];
	}

	public function __get(string $name): mixed {
		if (method_exists($this, "get$name")) return $this->{"get$name"}();
		return $this->attributes[$name] ?? null;
	}

	public function __set(string $name, mixed $value): void {
		$this->attributes[$name] = $value;
	}

	public function getSlug(): string {
		// children are expected to implement this method
		// do not return strings longer than 200 bytes
	}

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

	public static function all(string $prefix): Generator {
		$dirpath = (new static())->storageDirectory;
		$dirpath = $dirpath . "/" . $prefix;
		$dirpath = storagePath($dirpath);
		$filepaths = glob($dirpath . "/**.yml");
		foreach ($filepaths as $filepath) {
			$storable = static::loadFromPath($filepath);
			if ($storable === false) continue;
			yield $storable;
		}
	}

	public static function loadFromPath(string $path): static|null {
		$record = readYamlFile($path);
		if ($record === false) return null;
		return new static($record, $path);
	}

	public static function loadFromId(string $id): static|null {
		if (preg_match("/[^" . static::ID_CHAR_POOL . "]/", $id)) return null; // invalid id never finds anything
		$dirpath = (new static())->storageDirectory;
		$dirpath = storagePath($dirpath);
		$filepaths = glob($dirpath . "/**/*-$id.yml");
		$filepath = $filepaths[0] ?? null;
		if ($filepath === null) return null;
		return static::loadFromPath($filepath);
	}

	public function save(): void {
		if (method_exists($this, "getStoragePrefix")) {
			$prefix = $this->storagePrefix;
		} else {
			$prefix = "";
		}

		$dirpath = storagePath((new static())->storageDirectory);
		if (!is_dir($dirpath)) mkdir($dirpath);

		$dirpath .= "/" . $prefix;
		if (!is_dir($dirpath)) mkdir($dirpath);

		$filepath = $dirpath . "/" . $this->slug . "-" . $this->id . ".yml";
		if (!is_file($filepath)) {
			rename($this->storagePath, $filepath);
			$this->storagePath = $filepath;
		}

		writeYamlFile($filepath, [...$this->attributes, "version" => 1]);
	}
}
