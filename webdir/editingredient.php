<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/utils.php"; /** @var $mysqli */

if(isset($_REQUEST['id'])){
    $fullSubmit = filter_var($_REQUEST['fullSubmit'],FILTER_VALIDATE_BOOLEAN);


    print_r($_REQUEST);

    if($fullSubmit == false) {
        if (!($stmnt = $mysqli->prepare('SELECT * FROM kitchen.ingredients WHERE id=?'))) echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
        if (!$stmnt->bind_param("i", $_REQUEST['id'])) echo "Binding parameters failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
        if (!$stmnt->execute()) echo "Execute failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
        if (!$result = $stmnt->get_result()) echo "Gathering result failed: (";// . $stmnt->errno . ") " . $stmnt->error;
        $row = mysqli_fetch_assoc($result);

        if(empty($row)) {
            echo("<br>");
            echo("<h1>WARNING: Broken data, please inform developer!</h1>");?>
            <br>
            <a href="/kitchen.php" class="d-block">
                <button>
            Kitchen App
            </button>
            </a>
            <?php
            exit();
        }
    ?>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Kitchen :: DB Operation</title>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"
                    integrity="sha384-vtXRMe3mGCbOeY7l30aIg8H9p3GdeSe4IFlP6G8JMa7o7lXvnz3GFKzPxzJdPfGK"
                    crossorigin="anonymous"></script>
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
                  integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
                    integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV"
                    crossorigin="anonymous"></script>
            <link rel="stylesheet" href="/css/main.css">
        </head>
        <body class="flex-column">
        <form action="editingredient.php" method="post">
            <input type="hidden" name="fullSubmit" value="true">
            <input type="hidden" name="id" value="<?= $_REQUEST['id'] ?>">
            <div class="form-group">
                <label for="ingredientAddName">Name: </label>
                <input id="ingredientAddName" required name="name" type="text" value="<?= $row['name'] ?>">
            </div>
            <div class="form-group">
                <label for="ingredientAddAmount">Amount: </label>
                <input id="ingredientAddAmount" required name="amount" type="number" step="any" value="<?= $row['amount'] ?>">
            </div>
            <div class="form-group">
                <label for="ingredientAddUnit">Unit Name: </label>
                <input id="ingredientAddUnit" required name="unit" type="text" value="<?= $row['unit'] ?>">
            </div>
            <div class="form-group">
                <label for="ingredientAddCategory">Category: </label>
                <input id="ingredientAddCategory" required name="category" type="text" value="<?= $row['category'] ?>">
            </div>
            <input type="submit" class="btn-info btn-lg">
        </form>
        </body>
    <?php }
    elseif ($_REQUEST['fullSubmit'] == true && isset($_REQUEST['name']) && isset($_REQUEST['unit']) && isset($_REQUEST['amount']) && isset($_REQUEST['category'])){
        $success = false;
        print_r('Attempted to submit changes!');

        if (!($stmnt = $mysqli->prepare('UPDATE kitchen.ingredients t SET t.name = ?, t.unit = ?, t.amount = ?, t.category = ? WHERE t.id = ?'))) echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
        if (!$stmnt->bind_param("ssdsi", $_REQUEST['name'],$_REQUEST['unit'],$_REQUEST['amount'],$_REQUEST['category'],$_REQUEST['id'])) echo "Binding parameters failed: (" . $stmnt->errno . ") " . $stmnt->error;
        if (!$stmnt->execute()) echo "Execute failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
        else $success = true;

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
        <?php }
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
    }
}
else{?>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Kitchen :: DB Operation</title>
    </head>
    <body class="flex-column">
    <h1>Critical failure!</h1>
    <p>Please inform a dev before heading back to the kitchen page.</p>
    <a href="/kitchen.php" class="d-block">
        <button>
            Kitchen App
        </button>
    </a>
    </body>
<?php }
