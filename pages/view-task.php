<?php

use App\Storable\Task;
use App\Template;

global $path;

if (count($path) > 2) redirect("/task/" . $path[1]);
noQueryString();

if (count($path) < 2) notFound();

$id = $path[1];
if ($id == null) notFound();

$task = Task::loadFromId($id);
if ($task == null) notFound();

$canonicalPath = $task->webPath;
$canonicalPath = trim($canonicalPath, "/");
$canonicalPath = explode("/", $canonicalPath);

if ($path != $canonicalPath) redirect($task->webPath);

?>
<?= Template::beforeContent(title: ["Task", $task->title]) ?>

<p><a href="/<?= implode("/", $path) ?>/edit" x-key-combo="E">[edit]</a></p>

<?php if (empty(trim($task->notes))): ?>
	<p><i>no notes</i></p>
<?php else: ?>
	<pre><?= e($task->notes) ?></pre>
<?php endif ?>

<?= Template::afterContent() ?>
