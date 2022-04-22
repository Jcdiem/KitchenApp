<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/force_login.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/db.php"; /** @var $mysqli */

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

function simpleMySQL($mysqlStrIn,$mysqli){
    if (!($stmnt = $mysqli->prepare($mysqlStrIn))) {
        echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$stmnt->execute()) echo "Execute failed";// : (" . $stmnt->errno . ") " . $stmnt->error;
    if (!$result = $stmnt->get_result()) echo "Gathering result failed"; //: (" . $stmnt->errno . ") " . $stmnt->error;
    return $result;
}