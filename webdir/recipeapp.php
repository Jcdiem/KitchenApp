<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/utils.php"; /** @var $mysqli */

$ingredientsTableHTML = "";
$outOfStock = false;
$recipeTitle = "";
$recipeStepsListHTML = "";

if (isset($_REQUEST['recipeId'])) {

    // Get the ingredients required to make the recipe
    if (!($stmnt = $mysqli->prepare('SELECT ingred.name, ingred.amount, ingred.unit, recipeItem.ingredientAmount FROM kitchen.RecipeItems as recipeItem join kitchen.ingredients ingred on recipeItem.IngredientId = ingred.id join kitchen.Recipes recipe on recipeItem.recipeId = recipe.id WHERE recipe.id = ?;'))) echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
    if (!$stmnt->bind_param("i", $_REQUEST['recipeId'])) echo "Binding parameters failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
    if (!$stmnt->execute()) echo "Execute failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
    if (!$result = $stmnt->get_result()) echo "Gathering result failed"; //: (" . $stmnt->errno . ") " . $stmnt->error;
    $ingredientsTableHTML = '<thead class="thead-light"><tr><th scope="col">Name</th><th scope="col">Required Amnt</th><th scope="col">Current Stock</th><th scope="col">Unit</th></tr></thead><tbody id="ingredientsTableBody">';
    while($row = $result->fetch_assoc()){
        if ($row['ingredientAmount'] > $row['amount']){
            $outOfStock = true;
        }
        $ingredientsTableHTML .= '<tr class="tableRow"><td>' . $row['name'] . '</td><td>' . $row['ingredientAmount'] . '</td><td>' . $row['amount'] . '</td><td>' . $row['unit'] . '</td></tr>';
    }
    $ingredientsTableHTML .= '</tbody>';

    //Get the name and instructions of the recipe
    if (!($stmnt = $mysqli->prepare('SELECT title, instructions FROM kitchen.Recipes WHERE id = ?;'))) echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
    if (!$stmnt->bind_param("i", $_REQUEST['recipeId'])) echo "Binding parameters failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
    if (!$stmnt->execute()) echo "Execute failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
    if (!$result = $stmnt->get_result()) echo "Gathering result failed"; //: (" . $stmnt->errno . ") " . $stmnt->error;
    $row = $result->fetch_assoc();
    $recipeTitle = $row['title'];
    $recipeStepsListHTML = base64_decode($row['instructions']);

    //Create button, based on stock or not
    if(!$outOfStock){
//TODO:        Make out of stock add to shopping list
        $useStockButtonHTML = '<button class="btn-warning">Not Enough Stock</button>';
    }
    else{
        $useStockButtonHTML = '<button class="btn-success">Use Required Stock</button>';
    }

?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kitchen - Lists</title>
    <script src="https://code.jquery.com/jquery-3.6.2.min.js"
            crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="js/bootstable.min.js"></script>
    <link rel="stylesheet" href="/css/main.css">
</head>
<style>
    body{
        /*Override display:flex*/
        display: inline;
    }
</style>

<body>
<div id="styleDivTable">
    <div id="returnContainer">
        <a href="/kitchen.php" class="d-block">
            <button>
                Back to Main Kitchen
            </button>
        </a>
    </div>
    <div id="RecipeContainer" class="col-md-8 offset-md-2">
        <!--    TODO: Stylize the recipe page -->
        <h1><?=$recipeTitle?></h1>
        <hr>
        <h3>Ingredients Required</h3>
        <table id="ingredientsTable" class="table ingredientsTable"><?=$ingredientsTableHTML?></table>
        <?=$useStockButtonHTML?>
        <hr>
        <h3>Instructions</h3>
        <?=$recipeStepsListHTML?>
    </div>
</div>
</body>
<script>
    function depleteUsedIngredients(){
        console.debug("DEPLETED INGREDIENTS!");
    }
</script>
</html>
<?php
}
// IF no recipeId provided
else{?>
<html>
<h1>NO RECIPE ID PROVIDED!</h1>
<div id="returnContainer">
    <a href="/kitchen.php" class="d-block">
        <button>
            Back to Main Kitchen
        </button>
    </a>
</div>
</html>
<?php
}