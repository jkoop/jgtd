<?php

use App\Auth;
use App\Template;

global $path;

if (Auth::check()) redirect(Auth::DEFAULT_PATH);
if (count($path) > 1) redirect("/login");
noQueryString();

?>
<?= Template::beforeContent(title: "Login") ?>

<form method="post">
    <div style="display:flex;flex-direction:column">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" style="width: 200px;" autofocus required />
    </div>

    <div>
        <button type="submit">Save</button>
    </div>
</form>

<details>
    <summary>To reset your password</summary>
    <ol>
        <li>Remove the appropriate line from <code>storage/config.yml</code></li>
        <li>Refresh this page</li>
        <li>Read the server logs for the new password; it'll be near the bottom</li>
    </ol>
</details>

<?= Template::afterContent() ?>
