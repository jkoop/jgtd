<?php

use App\Template;

global $path;
$filepath = array_filter($path, fn (string $part): bool => $part != ".."); // security

if (
    $filepath != $path ||
    str_contains(strtolower($_SERVER["REQUEST_URI"]), "%2f")
) {
    redirect("/" . $filepath);
}

noQueryString();

array_shift($filepath);
$filepath = implode("/", $filepath);
$task = readYamlFile(storagePath("tasks/" . $filepath . ".yml"));
if ($task === false) notFound();

?>
<?= Template::beforeContent(title: ["Task", $task["title"]]) ?>

<p><a href="/<?= implode("/", $path) ?>/edit">[edit]</a></p>

<?php if (strlen(trim($task["notes"]))): ?>
    <pre><?= e($task["notes"]) ?></pre>
<?php else: ?>
    <p><i>no notes</i></p>
<?php endif ?>

<?= Template::afterContent() ?>
