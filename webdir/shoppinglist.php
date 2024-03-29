<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/utils.php"; /** @var $mysqli */

$pageHtml = "";


if (isset($_REQUEST['listId'])) {
    if (!($stmnt = $mysqli->prepare('SELECT ingred.name, ingred.amount, ingred.unit FROM kitchen.ShoppingItems as listItem join kitchen.ingredients ingred on listItem.itemId = ingred.id join kitchen.shoppingLists sList on listItem.shoppingListId = sList.id WHERE sList.id = ?;'))) echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
    if (!$stmnt->bind_param("i", $_REQUEST['listId'])) echo "Binding parameters failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
    if (!$stmnt->execute()) echo "Execute failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
    if (!$result = $stmnt->get_result()) echo "Gathering result failed"; //: (" . $stmnt->errno . ") " . $stmnt->error;
    $ingredientsTableHTML = '<thead class="thead-light"><tr><th scope="col">Name</th><th scope="col">Current Stock</th><th scope="col">Unit</th></tr></thead><tbody id="ingredientsTableBody">';
    while($row = $result->fetch_assoc()){
        $ingredientsTableHTML .= '<tr class="tableRow"><td>' . $row['name'] . '</td><td>' . $row['amount'] . '</td><td>' . $row['unit'] . '</td></tr>';
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
    <script src="https://code.jquery.com/jquery-3.6.2.min.js"
            crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
          integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
            integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV"
            crossorigin="anonymous"></script>
    <script src="js/bootstable.min.js"></script>
    <link rel="stylesheet" href="/css/main.css">
</head>
<style>
    .clickedBackground{
        background-color: greenyellow;
    }
</style>
<script>
    $(document).ready(function() {
        $('.tableRow').click(function(){
            $(this).toggleClass('clickedBackground');
        });
    });
</script>


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

