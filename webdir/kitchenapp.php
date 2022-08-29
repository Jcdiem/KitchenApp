<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/utils.php"; /** @var $mysqli */
$success = false;
$DEBUG = true;

/** @var $mysqli */
//TODO: Sort API by task being completed
if ($_POST['action'] == 'addOneIngredient' && $_POST['id']) {
    if (!($stmnt = $mysqli->prepare("UPDATE kitchen.ingredients SET amount=amount+1 WHERE id=?"))) {
        echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$stmnt->bind_param("i", $_POST['id'])) echo "Binding parameters failed: (";// . $stmnt->errno . ") " . $stmnt->error;
    if (!$stmnt->execute()) echo "Execute failed: ("; // . $stmnt->errno . ") " . $stmnt->error;
    else {
        $data = array(
            "message" => "Record Updated",
            "status" => 0
        );
        echo json_encode($data);
        return;
    }
    $data = array(
        "message" => "Update failed",
        "status" => 1
    );
    echo json_encode($data);
}
elseif ($_POST['action'] == 'removeOneIngredient' && $_POST['id']) {
    if (!($stmnt = $mysqli->prepare("UPDATE kitchen.ingredients SET amount=amount-1 WHERE id=?"))) {
        echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$stmnt->bind_param("i", $_POST['id'])) echo "Binding parameters failed: (";// . $stmnt->errno . ") " . $stmnt->error;
    if (!$stmnt->execute()) echo "Execute failed: ("; // . $stmnt->errno . ") " . $stmnt->error;
    else {
        $data = array(
            "message" => "Record Updated",
            "status" => 0
        );
        echo json_encode($data);
        return;
    }
    $data = array(
        "message" => "Update failed",
        "status" => 1
    );
    echo json_encode($data);
}
elseif ($_POST['action'] == 'editIngredient' && $_POST['id'] && $_POST['par'] && $_POST['amount']) {
    if (!($stmnt = $mysqli->prepare("UPDATE kitchen.ingredients SET amount=?, par=? WHERE id=?"))) {
        echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$stmnt->bind_param("ddi", $_POST['amount'], $_POST['par'], $_POST['id'])) echo "Binding parameters failed: (";// . $stmnt->errno . ") " . $stmnt->error;
    if (!$stmnt->execute()) echo "Execute failed: ("; // . $stmnt->errno . ") " . $stmnt->error;
    else {
        $data = array(
            "message" => "Record Updated",
            "status" => 0
        );
        echo json_encode($data);
        return;
    }
    $data = array(
        "message" => "update failed",
        "status" => 1
    );
    echo json_encode($data);
}
elseif ($_POST['action'] == 'deleteIngredient' && $_POST['id']) {
    if (!($stmnt = $mysqli->prepare("DELETE FROM kitchen.ingredients WHERE id=?"))) {
        echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$stmnt->bind_param("i",$_POST['id'])) echo "Binding parameters failed: (";// . $stmnt->errno . ") " . $stmnt->error;
    if (!$stmnt->execute()) echo "Execute failed: ("; // . $stmnt->errno . ") " . $stmnt->error;
    else {
        $data = array(
            "message" => "Record Deleted",
            "status" => 0
        );
        echo json_encode($data);
        return;
    }
    $data = array(
        "message" => "Delete failed",
        "status" => 1
    );
}
if(isset($_REQUEST['formType'])){
    if($DEBUG) print_r($_REQUEST);

    switch($_REQUEST['formType']){
        case "addIngredient":
            if(!(isset($_REQUEST['name'])) || !(isset($_REQUEST['amount'])) || !(isset($_REQUEST['unit'])) || !(isset($_REQUEST['category'])) || !(isset($_REQUEST['par'])) ){
                echo "<br> All required fields not used!";
            }
            else{
                if (!($stmnt = $mysqli->prepare('INSERT INTO kitchen.ingredients (name,amount,unit,category,par) VALUES (?,?,?,?,?)'))) echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
                if (!$stmnt->bind_param("sdssi", $_REQUEST['name'],$_REQUEST['amount'],$_REQUEST['unit'],$_REQUEST['category'],$_REQUEST['par'])) echo "Binding parameters failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
                if (!$stmnt->execute()) echo "Execute failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
                else $success = true;
            }
            break;
        case "deleteIngredient":
            if(!(isset($_REQUEST['id']))){
                echo "<br> All required fields not used!";
                break;
            }
            else{
                if (!($stmnt = $mysqli->prepare('DELETE FROM kitchen.ingredients WHERE id = ?'))) echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
                if (!$stmnt->bind_param("i", $_REQUEST['id'])) echo "Binding parameters failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
                if (!$stmnt->execute()) echo "Execute failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
                else $success = true;
            }
            break;
        case "addShoppingList":
            if(!(isset($_REQUEST['name']))){
                echo "<br> All required fields not used!";
                break;
            }
            else{
                if (!($stmnt = $mysqli->prepare('INSERT INTO kitchen.shoppingLists (name) VALUES (?)'))) echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
                if (!$stmnt->bind_param("s", $_REQUEST['name'])) echo "Binding parameters failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
                if (!$stmnt->execute()) echo "Execute failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
                else $success = true;
            }
            break;
        case "addShoppingListItems":
            if(!(isset($_REQUEST['shoppingList'])) || !(isset($_REQUEST['checkedId']))){
                echo "<br> All required fields not used!";
                break;
            }
            else{
                // If add all subpar was checked
                if($_REQUEST['allSubpar'] == 'on'){
                    $result = simpleMySQL('SELECT * FROM kitchen.ingredients WHERE par > amount',$mysqli);
                    while($row = $result->fetch_assoc()){
                        debug_to_console("Adding item " . $row['id'] . " - " . $row['name']);
                        if (!($stmnt = $mysqli->prepare('INSERT INTO kitchen.ShoppingItems (shoppingListId, itemId) SELECT list.id, item.id FROM kitchen.ingredients AS item CROSS JOIN kitchen.shoppingLists AS list WHERE item.id = ? AND list.id = ?'))) echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
                        if (!$stmnt->bind_param("ii", $row['id'], $_REQUEST['shoppingList'])) echo "Binding parameters failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
                        if (!$stmnt->execute()) echo "Execute failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
                        else $success = true;
                    }
                }
                else{
                    foreach ($_REQUEST['checkedId'] as $ingredId){
                        debug_to_console("Adding item " . $ingredId);
                        if (!($stmnt = $mysqli->prepare('INSERT INTO kitchen.ShoppingItems (shoppingListId, itemId) SELECT list.id, item.id FROM kitchen.ingredients AS item CROSS JOIN kitchen.shoppingLists AS list WHERE item.id = ? AND list.id = ?'))) echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
                        if (!$stmnt->bind_param("ii", $ingredId, $_REQUEST['shoppingList'])) echo "Binding parameters failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
                        if (!$stmnt->execute()) echo "Execute failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
                        else $success = true;
                    }
                }
            }
            break;
    }
}



if($success){ ?>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Kitchen :: DB Operation</title>
    </head>
    <body class="flex-column">
    <h1>Success! Redirecting...</h1>
    <p>If redirection takes too long you may press the button below.</p>
    <a href="/kitchen.php" class="d-block">
        <button>
            Kitchen App
        </button>
    </a>
    </body>
<?php
    ob_start();
    header('Location: https://kitchenapp.diemconsulting.net/kitchen.php');
    ob_end_flush();
    die();
}
else { ?>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Kitchen :: DB Operation</title>
    </head>
    <body class="flex-column">
    <h1>Submission failed!</h1>
    <p>Please inform a dev before heading back to the kitchen page.</p>
    <a href="/kitchen.php" class="d-block">
        <button>
            Kitchen App
        </button>
    </a>
    </body>
<?php }
