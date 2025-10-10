<?php

use App\Template;

?>
<?= Template::beforeContent(title: "Not Found") ?>
<p>Either whatever it is that you're looking for doesn't exist where you think it does or you typoed something.</p>
<?= Template::afterContent() ?>
