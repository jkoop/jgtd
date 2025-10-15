<?php

namespace App\Storable;

use DateTime;
use Exception;
use Generator;

abstract class Storable {
	public const ID_CHAR_POOL = "0123456789abcdefghjkmnpqrstvwxyz";

	private readonly array $original;

	public function __construct(private array $attributes = [], private string|null $storagePath = null) {
		if (isset($this->attributes["id"]) == false) {
			$this->attributes["id"] = static::generateId();
		}

		if (isset($this->storagePath)){
			$this->original = $attributes;

			// it's saved with the wrong id. fix it
			if (basename($this->storagePath, ".yml") != $this->attributes["id"]) {
				$this->save();
			}
		} else {
			$this->original = [];
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
			if ($storable == null) continue;
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
		$filepaths = glob($dirpath . "/**/$id.yml");
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

		$filepath = $dirpath . "/" . $this->id . ".yml";
		if (!is_file($filepath)) {
			rename($this->storagePath, $filepath);
			$this->storagePath = $filepath;
		}

		if (!isset($this->attributes["created_at"])) {
			$this->attributes["created_at"] = new DateTime();
			$this->attributes["updated_at"] = new DateTime();
		}

		if ($this->original != $this->attributes) {
			$this->attributes["updated_at"] = new DateTime();
		}

		self::lock();
		writeYamlFile($filepath, [...$this->attributes, "version" => 0]);
	}

	private static $lockHandle = null;

	/**
	 * Lock storage repo for our use
	 * @blocks
	 * @return void
	 */
	public static function lock(): void {
		if (self::$lockHandle != null) return; // we already have it

		$gitignore = file_get_contents(storagePath("/.gitignore")) ?: "";
		$gitignore = explode("\n", $gitignore);
		if ($gitignore[0] == "") array_shift($gitignore);
		if (!array_search("/repo.lock", $gitignore)) {
			$gitignore[] = "/repo.lock";
			file_put_contents(storagePath("/.gitignore"), implode("\n", $gitignore) . "\n");
		}

		self::$lockHandle = fopen(storagePath("/repo.lock"), "w");

		if (
			self::$lockHandle == false ||
			flock(self::$lockHandle, LOCK_EX) == false
		) throw new Exception("Couldn't lock storage repo.");

		git_exec("commit -m 'Found some unexpected staged changes [BOT]'");
		git_exec("add --all");
		git_exec("commit -m 'Found some unexpected changes [BOT]'");
	}

	/**
	 * Commit the changes to the storage repo, and unlocks the repo
	 * @return void
	 */
	public final static function commit(): void {
		if (self::$lockHandle == null) return;
		git_exec("add --all");
		git_exec("commit -m 'Commit changes [BOT]'");
	}
}

register_shutdown_function([Storable::class, "commit"]);
