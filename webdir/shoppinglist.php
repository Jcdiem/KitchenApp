<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/utils.php"; /** @var $mysqli */

$pageHtml = "";


if (isset($_REQUEST['listId'])) {

}
else{
    $pageHtml = "<h2>No list specified, please return and try again.</h2>";
}
?>

<html>
<head>
    <meta charset="UTF-8">
    <title>Kitchen - Lists</title>
</head>
<body>
<div id="phpContainer"><?=$pageHtml?></div>
<div id="returnContainer">
    <a href="/kitchen.php" class="d-block">
        <button>
            Back to Main Kitchen
        </button>
    </a>
</div>
</body>
</html>

