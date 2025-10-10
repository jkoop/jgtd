<?php

use App\Template;

global $path;

if (count($path) > 1) redirect("/new-task");
noQueryString();

?>
<?= Template::beforeContent(title: "New Task") ?>

<form method="post">
    <div>
        <label for="title">Title</label>
        <input id="title" name="title" autofocus required />
        <span>What would need to happen for you to check this off as "done"?</span>
    </div>

    <div>
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" style="resize:block;height:100px"></textarea>
    </div>

    <div>
        <button type="submit" class="has-tooltip">
            Save
            <span class="tooltip">Ctrl + Enter</span>
        </button>
    </div>
</form>

<?= Template::afterContent() ?>
