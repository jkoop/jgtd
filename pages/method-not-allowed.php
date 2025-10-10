<?php

use App\Template;

global $allowedMethods;

?>
<?= Template::beforeContent(title: "Method Not Allowed") ?>
<p>Allowed methods: <?= e($allowedMethods) ?></p>
<?= Template::afterContent() ?>
