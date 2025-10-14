<?php

namespace App;

final class Crypt {
	private function __construct() {}

	public static function sign(string $value): string {
		$salt = bin2hex(random_bytes(8));
		$secret = self::getSecret();
		$signature = md5("$value:$salt:$secret");
		return "$value $salt $signature";
	}

	public static function unSign(string $signedValue): string|null {
		$signedValue = explode(" ", $signedValue);
		if (count($signedValue) < 3) return null;
		$signature = array_pop($signedValue);
		$salt = array_pop($signedValue);
		$value = implode(" ", $signedValue);
		if (self::checkSignature($value, $salt, $signature)) return $value;
		return null;
	}

	private static function checkSignature(string $value, string $salt, string $signature): bool {
		$secret = self::getSecret();
		return md5("$value:$salt:$secret") == $signature;
	}

	private static function getSecret(): string {
		global $config;
		return $config["secret"];
	}
}
