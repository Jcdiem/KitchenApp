<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/utils.php"; /** @var $mysqli */

$pageHtml = "";


if (isset($_REQUEST['listId'])) {
    if (!($stmnt = $mysqli->prepare('SELECT ingred.name, ingred.amount, ingred.unit FROM kitchen.ShoppingItems as listItem join kitchen.ingredients ingred on listItem.itemId = ingred.id join kitchen.shoppingLists sList on listItem.shoppingListId = sList.id WHERE sList.id = ?;'))) echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
    if (!$stmnt->bind_param("i", $_REQUEST['listId'])) echo "Binding parameters failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
    if (!$stmnt->execute()) echo "Execute failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
    if (!$result = $stmnt->get_result()) echo "Gathering result failed"; //: (" . $stmnt->errno . ") " . $stmnt->error;
    $ingredientsTableHTML = '<thead class="thead-light"><tr><th scope="col">ID</th><th scope="col">Name</th><th scope="col">Amount</th><th scope="col">Unit</th></tr></thead><tbody id="ingredientsTableBody">';
    while($row = $result->fetch_assoc()){
        $ingredientsTableHTML .= '<tr><td>' . $row['id'] . '</td><td>' . $row['name'] . '</td><td>' . $row['unit'] . '</td><td>' . $row['amount'] . '</td></tr>';
    }
    $ingredientsTableHTML .= '</tbody>';
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
<!--    TODO: fix the html rendering-->
    <table id="ingredientsTable" class="table ingredientsTable"><?=$ingredientsTableHTML?></table>
</div>
</body>
</html>

