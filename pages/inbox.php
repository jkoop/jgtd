<?php

use App\Storable\Task;
use App\Template;

global $path;

if (count($path) > 1) redirect("/inbox");
noQueryString();

$tasks = Task::all("inbox");
$tasks = [...$tasks];
usort($tasks, fn (Task $a, Task $b): int => slugify($a->title) <=> slugify($b->title));

?>
<?= Template::beforeContent(title: "Inbox") ?>

<?php if (empty($tasks)): ?>
    <p><i>empty list</i></p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><a href="<?= e($task->webPath) ?>"><?= e($task->title, linkify: false) ?></a></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>

<?= Template::afterContent() ?>
