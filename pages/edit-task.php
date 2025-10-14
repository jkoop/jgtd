<?php

use App\Storable\Task;
use App\Template;

global $path;

if (count($path) > 3) redirect("/task/" . $path[1] . "/edit");
noQueryString();

if (count($path) < 3) notFound();

$id = $path[1];
$id = explode("-", $id);
$id = array_pop($id);
if ($id == null) notFound();

$task = Task::loadFromId($id);
if ($task == null) notFound();

$canonicalPath = $task->webPath;
$canonicalPath = trim($canonicalPath, "/");
$canonicalPath .= "/edit";
$canonicalPath = explode("/", $canonicalPath);

if ($path != $canonicalPath) redirect($task->webPath . "/edit");

?>
<?= Template::beforeContent(title: ["Task", $task->title]) ?>

<p><a href="/<?= implode("/", array_slice($path, 0, -1)) ?>">[back]</a></p>

<form method="post">
    <div>
        <label for="title">Title</label>
        <input id="title" name="title" value="<?= e($task->title) ?>" required />
        <span>What would need to happen for you to check this off as "done"?</span>
    </div>

    <div>
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" style="resize:block;height:100px"><?= e($task->notes) ?></textarea>
    </div>

    <div>
        <button type="submit" class="has-tooltip">
            Save
            <span class="tooltip">Ctrl + Enter</span>
        </button>
    </div>
</form>

<?= Template::afterContent() ?>
