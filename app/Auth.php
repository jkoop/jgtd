<?php

namespace App;

final class Auth {
	private const INTENDED_COOKIE_NAME = "intended_uri";
	private const LOGIN_COOKIE_NAME = "login";
	public const DEFAULT_PATH = "/";

	private function __construct() {}

	/**
	 * @return bool true if logged in, false otherwise
	 */
	public static function check(): bool {
		$loggedIn = Cookie::get(self::LOGIN_COOKIE_NAME) == true;

		if ($loggedIn && Cookie::get(self::INTENDED_COOKIE_NAME) !== null) {
			Cookie::set(self::INTENDED_COOKIE_NAME, null);
		}

		return $loggedIn;
	}

	/**
	 * Redirects to login page if not logged in
	 * @return void
	 */
	public static function require(): void {
		if (self::check()) return;

		Cookie::set(self::INTENDED_COOKIE_NAME, $_SERVER["REQUEST_URI"]);
		redirect("/login");
	}

	/**
	 * Logs in user if credentials are ok and redirects to home page. Does nothing if creds are bad.
	 * @return void
	 */
	public static function attempt(string $password): void {
		global $config;

		if (password_verify($password, $config["password"])) {
			Cookie::set(self::LOGIN_COOKIE_NAME, true, 7 * 24 * 60 * 60);
			$intendedUri = Cookie::get(self::INTENDED_COOKIE_NAME);
			Cookie::set(self::INTENDED_COOKIE_NAME, null);
			if ($intendedUri !== null) redirect($intendedUri);
			redirect(self::DEFAULT_PATH);
		}
	}
}
