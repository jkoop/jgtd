<?php

namespace App\PostHandlers;

use App\Storable\Task;

final class SaveTask implements PostHandler {
	public static function entrypoint(): never {
		global $path;
		if (count($path) < 2) notFound();

		$id = $path[1];
		$id = trim($id, "-");
		$id = explode("-", $id);
		$id = array_pop($id);
		if (strlen($id) < 1) notFound();

		$task = Task::loadFromId($id);
		if ($task == null) notFound();

		if (!validate("string-not-empty", $_POST["title"] ?? null)) die("Form validation failure: title");
		if (!validate("string", $_POST["notes"] ?? null)) die("Form validation failure: notes");

		$title = $_POST["title"] ?? "";
		$title = trim($title);

		$notes = $_POST["notes"] ?? "";
		$notes = str_replace("\r", "", $notes);
		$notes = trim($notes);

		$task->title = $title;
		$task->notes = $notes;
		$task->save();

		redirect($task->webPath);
		exit;
	}
}
