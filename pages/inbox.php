<?php

use App\Template;

global $path;

if (count($path) > 1) redirect("/inbox");
noQueryString();

$tasks = shell_exec("find " . escapeshellarg(storagePath("tasks")) . " -type f -name '*.yml' -print0");
$tasks = explode("\0", $tasks);
$tasks = array_filter($tasks, "strlen");
$tasks = array_map(function (string $filepath): array {
    $task = readYamlFile($filepath);

    return [
        "inbox" => $task["inbox"],
        "path" => "/task/" . substr($filepath, strlen(storagePath("tasks")) + 1, -4),
        "title" => $task["title"],
    ];
}, $tasks);
$tasks = array_filter($tasks, fn (array $task): bool => $task["inbox"]);
usort($tasks, fn (array $a, array $b): int => slugify($a["title"]) <=> slugify($b["title"]));

?>
<?= Template::beforeContent(title: "Inbox") ?>
<table>
    <thead>
        <tr>
            <th>Title</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tasks as $task): ?>
            <tr>
                <td><a href="<?= e($task["path"]) ?>"><?= e($task["title"]) ?></a></td>
            </tr>
        <?php endforeach ?>

        <?php if (empty($tasks)): ?>
            <tr>
                <td><i>nothing</i></td>
            </tr>
        <?php endif ?>
    </tbody>
</table>
<?= Template::afterContent() ?>
