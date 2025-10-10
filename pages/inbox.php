<?php

use App\Template;

global $path;

if (count($path) > 1) redirect("/inbox");
noQueryString();

$tasks = glob(storagePath("/tasks/inbox/*.yml"));
$tasks = array_map(function (string $filepath): array {
    $task = readYamlFile($filepath);

    return [
        "path" => "@todo",
        "title" => $task["title"],
    ];
}, $tasks);
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
