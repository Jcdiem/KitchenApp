<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/utils.php"; /** @var $mysqli */

//Start the 'all ingredient' javascript arrays
$ingredientsCategoryAutoCompleteArray = '[';
$ingredientsUnitAutoCompleteArray = '[';

$result = simpleMySQL('SELECT DISTINCT unit FROM kitchen.ingredients',$mysqli);
while($row = $result->fetch_assoc()){
    $ingredientsUnitAutoCompleteArray .= '"' . $row['unit'] . '",';
}
$result = simpleMySQL('SELECT DISTINCT category FROM kitchen.ingredients',$mysqli);
while($row = $result->fetch_assoc()){
    $ingredientsCategoryAutoCompleteArray .= '"' . $row['category'] . '",';
}
// Take off the trailing comma from arrays
// Cap off the arrays
$ingredientsCategoryAutoCompleteArray = substr_replace($ingredientsCategoryAutoCompleteArray,"",-1) . ']';
$ingredientsUnitAutoCompleteArray = substr_replace($ingredientsUnitAutoCompleteArray,"",-1) . ']';


//Create the 'edit ingredient list'
$result = simpleMySQL('SELECT * FROM kitchen.ingredients',$mysqli);
$ingredientsTableHTML = '<thead class="thead-light"><tr><th scope="col">ID</th><th scope="col">Name</th><th scope="col">Unit</th><th scope="col">Amount</th><th scope="col">Par</th><th scope="col">Category</th><th scope="col">Add/Remove</th></tr></thead><tbody id="ingredientsTableBody">';
while($row = $result->fetch_assoc()){
    //Make the row for the table
    $ingredientsTableHTML .= '<tr id="' . $row['id'] . 'row"><td>' . $row['id'] . '</td><td>' . $row['name'] . '</td><td>' . $row['unit'] . '</td><td>' . $row['amount'] . '</td><td>' . $row['par'] . '</td><td>' . $row['category'] . '</td><td><button class="btn-info" onclick="addOneItem(' . $row['id'] . ');">Plus</button>/<button class="btn-danger" onclick="removeOneItem(' . $row['id'] . ');">Minus</button></td></tr>';
}
// Cap off the table
$ingredientsTableHTML .= '</tbody>';

// Create Shopping List under par table
$result = simpleMySQL('SELECT * FROM kitchen.ingredients WHERE par > amount',$mysqli);
$shoppingListUnderParTableHTML = '<thead class="thead-light"><tr><th scope="col">Name</th><th scope="col">Unit</th><th scope="col">Amount</th><th scope="col">Par</th><th scope="col">Category</th><th scope="col">Add</th></tr></thead><tbody id="shoppingListAutoAddTableBody">';
while($row = $result->fetch_assoc()){
    $shoppingListUnderParTableHTML .= '<tr><td>' . $row['name'] . '</td><td>' . $row['unit'] . '</td><td>' . $row['amount'] . '</td><td>' . $row['par'] . '</td><td>' . $row['category'] . '</td><td><input type="checkbox" name="checkedId[]" value="' . $row['id'] . '"></td></tr>';
}
$shoppingListUnderParTableHTML .= '</tbody>';

// Create Shopping List table & dropdown
$result = simpleMySQL('SELECT * FROM kitchen.shoppingLists WHERE kitchen.shoppingLists.completed = false',$mysqli);
//Set up the table
$shoppingListTableHTML = '<thead class="thead-light"><tr><th scope="col">Name</th><th scope="col">Created</th><th scope="col">Items</th><th scope="col">Complete</th></tr></thead><tbody id="shoppingListAutoAddTableBody">';
//Set up the dropdown menu
$shoppingListSelectDropdownHTML = '';
while($row = $result->fetch_assoc()){
    //Query for the count of items with this shopping list ID
    if (!($stmnt = $mysqli->prepare('SELECT COUNT(*) FROM kitchen.ShoppingItems WHERE shoppingListId = ?;'))) echo "Prepare failed";//: (" . $mysqli->errno . ") " . $mysqli->error;
    if (!$stmnt->bind_param("i",$row['id'])) echo "Binding parameters failed: (" . $stmnt->errno . ") " . $stmnt->error;
    if (!$stmnt->execute()) echo "Execute failed";//: (" . $stmnt->errno . ") " . $stmnt->error;
    if (!$quantityResult = $stmnt->get_result()) echo "Gathering result failed"; //: (" . $stmnt->errno . ") " . $stmnt->error;

    //Add table item
    $shoppingListTableHTML .= '<tr><td>' . $row['name'] . '</td><td>' . $row['dateCreated'] . '</td><td>' . $quantityResult->fetch_column() . '</td><td><a href="shoppinglist.php?listId=' . $row['id'] . '">View List</a></td></tr>';

    //Add item to dropdown menu
    $shoppingListSelectDropdownHTML .= '<option value="'. $row['id'] .'">' . $row['name'] . '</option>';
}
//Finish off table body
$shoppingListTableHTML .= '</tbody>';


//Create recipe list table & dropdown
$result = simpleMySQL('SELECT * FROM kitchen.Recipes;',$mysqli);

//Table
$recipeListTableHTML = '<thead class="thead-light"><tr><th scope="col">Title</th><th scope="col">Open</th></tr></thead><tbody>';

// Add each recipe and a button to open it
while($row = $result->fetch_assoc()){
    $recipeListTableHTML .= '<tr><td>'. $row['title'] .'</td><td><a href="recipeapp.php?recipeId='.$row['id'].'">View Recipe</a></td></tr>';
}

//Finish table body
$recipeListTableHTML .= '</tbody>';

?>

<html lang="en">
<head>
    <!--  Template for the dashboard courtesy of https://www.blog.duomly.com/bootstrap-tutorial/  -->
    <meta charset="UTF-8">
    <title>Simple Kitchen App</title>
    <script src="https://code.jquery.com/jquery-3.6.2.min.js"
            crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
          integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
            integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV"
            crossorigin="anonymous"></script>
<!--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">-->
<!--    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>-->
    <script src="js/bootstable.min.js"></script>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        @import url("https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css");
        main {
            padding-top: 90px;
        }
        button{
            background: none;
            color: inherit;
            border: none;
            padding: 0;
            font: inherit;
            cursor: pointer;
            outline: inherit;
        }
        .form-group{
            padding-left: 1rem;
        }
        .sidebar {
            position: fixed;
            left: 0;
            bottom: 0;
            top: 0;
            z-index: 100;
            padding: 70px 0 0 10px;
            border-right: 1px solid #d3d3d3;
        }

        .left-sidebar {
            position: sticky;
            top:0;
            height: calc(100vh - 70px)
        }

        .sidebar-nav li .nav-link {
            color: #333;
            font-weight: 500;
        }

        .tab {
            display:none;
        }

        .tab.active {
            display:block;
        }
        .submissionField {
            width: 75%;
            height: 300px;
            border: 1px solid #555555;
            padding: 5px;
        }
        textarea {
            resize: none;
        }


        .categoryOutline{
            outline: #555555 solid 1px;
        }

    </style>
</head>
<body>
<!--TODO: Create a way to save what tab was last used-->
<nav class="navbar navbar-dark fixed-top bg-primary flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="#">Simple Kitchen Application</a>
    <ul class="navbar-nav px-3">
        <li class="nav-item text-nowrap">
            <a class="nav-link" href="logout.php">Logout</a>
        </li>
    </ul>
</nav>
<div class="container-fluid">
    <div class="row">
        <div id="sidebar" class="col-md-2 bg-light d-none d-md-block sidebar">
            <div class="left-sidebar">
                <ul class="nav nav-pills flex-column sidebar-nav">
                        <li class="nav-item">
                            <a class="nav-link tablinks" href="#ingredientAddTab">
                                <svg class="bi bi-chevron-right" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6.646 3.646a.5.5 0 01.708 0l6 6a.5.5 0 010 .708l-6 6a.5.5 0 01-.708-.708L12.293 10 6.646 4.354a.5.5 0 010-.708z" clip-rule="evenodd"/></svg>
                                Add Ingredient
                            </a>
                        </li>
                    <li class="nav-item">
                        <a class="nav-link tablinks active" href="#ingredientList">
                            <svg class="bi bi-chevron-right" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6.646 3.646a.5.5 0 01.708 0l6 6a.5.5 0 010 .708l-6 6a.5.5 0 01-.708-.708L12.293 10 6.646 4.354a.5.5 0 010-.708z" clip-rule="evenodd"/></svg>
                            Ingredient List
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link tablinks" href="#shoppingList">
                            <svg class="bi bi-chevron-right" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6.646 3.646a.5.5 0 01.708 0l6 6a.5.5 0 010 .708l-6 6a.5.5 0 01-.708-.708L12.293 10 6.646 4.354a.5.5 0 010-.708z" clip-rule="evenodd"/></svg>
                            Shopping Lists
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link tablinks" href="#recipeList">
                            <svg class="bi bi-chevron-right" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6.646 3.646a.5.5 0 01.708 0l6 6a.5.5 0 010 .708l-6 6a.5.5 0 01-.708-.708L12.293 10 6.646 4.354a.5.5 0 010-.708z" clip-rule="evenodd"/></svg>
                            Recipes
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <main role="main" class="tab-content col-md-9 ml-sm-auto col-lg-10 px-4">
            <div id="ingredientAddTab" class="tab">
                <h3> Adding Ingredient </h3>
                <form id="ingredientAddForm" action="kitchenapp.php" method="post">
                    <input type="hidden" name="formType" value="addIngredient">
                    <div class="form-group">
                        <label for="ingredientAddName">Name: </label>
                        <input id="ingredientAddName" required name="name" type="text">
                    </div>
                    <div class="form-group">
                        <label for="ingredientAddAmount">Amount: </label>
                        <input id="ingredientAddAmount" required name="amount" type="number" step="any">
                    </div>
                    <div class="form-group">
                        <label for="ingredientAddUnit">Unit Name: </label>
                        <input id="ingredientAddUnit" required name="unit" type="text">
                    </div>
                    <div class="form-group">
                        <label for="ingredientAddCategory">Category: </label>
                        <input id="ingredientAddCategory" required name="category" type="text">
                    </div>
                    <div class="form-group">
                        <label for="ingredientAddPar">Par: </label>
                        <input id="ingredientAddPar" required name="par" type="number">
                    </div>
                    <input type="submit" class="btn-info btn-lg">
                </form>
            </div>
            <div id="ingredientList" class="tab active">
                <h3>Ingredients</h3>
                <form id="ingredientListSearch">
                    <label for="ingredientListSearchInput">Search: </label>
                    <input id="ingredientListSearchInput">
                </form>
                <table id="ingredientsTable" class="table ingredientsTable"><?=$ingredientsTableHTML?></table>
            </div>
            <div id="shoppingList" class="tab">
                <h2>Shopping List Management</h2>
                <div class="categoryOutline">
                    <h3>Shopping Lists</h3>
                    <table id="shoppingListTable" class="table shoppingListTable"><?=$shoppingListTableHTML?></table>
                </div>
                <div class="categoryOutline">
                    <h3>Create Shopping List</h3>
                    <form action="kitchenapp.php" method="post">
                        <input hidden name="formType" value="addShoppingList">
                        <div class="form-group">
                            <label for="shoppingListName">Name of List: </label>
                            <input id="shoppingListName" name="name" type="text">
                        </div>
                        <input type="submit" class="btn-info btn-lg">
                    </form>
                </div>
                <div class="categoryOutline">
                    <h3>Adding Items</h3>
                    <form action="kitchenapp.php" method="POST">
                        <input hidden name="formType" value="addShoppingListItems">
                        <p>Select the list you would like to use</p>
                        <select id="shoppingListAddListSelectDropdown" name="shoppingList">
                            <?=$shoppingListSelectDropdownHTML?>
                        </select>

                        <div class="form-group">
                            <label for="shoppingListAddAllSubpar">Add Everything Under Par on Submit:</label>
                            <input id="shoppingListAddAllSubpar" name="allSubpar" type="checkbox" checked>
                        </div>

                        <input type="submit" class="btn-lg btn-warning">
                        <table id="shoppingListAutoAddTable" class="table shoppingListTable"><?=$shoppingListUnderParTableHTML?></table>
                    </form>
                </div>
            </div>
            <div id="recipeList" class="tab">
                <h1>THIS IS THE RECIPE LIST</h1>
                <table id="recipeListAutoAddTable" class="table"><?=$recipeListTableHTML?></table>
            </div>
        </main>
    </div>
</div>
</body>
<script>
    const ingredientCategoryArray = <?=$ingredientsCategoryAutoCompleteArray?>;
    const ingredientUnitArray = <?=$ingredientsUnitAutoCompleteArray?>;

    function autocomplete(inp, arr) {
        /*the autocomplete function takes two arguments,
        the text field element and an array of possible autocompleted values:*/
        var currentFocus;
        /*execute a function when someone writes in the text field:*/
        inp.addEventListener("input", function(e) {
            var a, b, i, val = this.value;
            /*close any already open lists of autocompleted values*/
            closeAllLists();
            if (!val) { return false;}
            currentFocus = -1;
            /*create a DIV element that will contain the items (values):*/
            a = document.createElement("DIV");
            a.setAttribute("id", this.id + "autocomplete-list");
            a.setAttribute("class", "autocomplete-items");
            /*append the DIV element as a child of the autocomplete container:*/
            this.parentNode.appendChild(a);
            /*for each item in the array...*/
            for (i = 0; i < arr.length; i++) {
                /*check if the item starts with the same letters as the text field value:*/
                if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                    /*create a DIV element for each matching element:*/
                    b = document.createElement("DIV");
                    /*make the matching letters bold:*/
                    b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                    b.innerHTML += arr[i].substr(val.length);
                    /*insert a input field that will hold the current array item's value:*/
                    b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                    /*execute a function when someone clicks on the item value (DIV element):*/
                    b.addEventListener("click", function(e) {
                        /*insert the value for the autocomplete text field:*/
                        inp.value = this.getElementsByTagName("input")[0].value;
                        /*close the list of autocompleted values,
                        (or any other open lists of autocompleted values:*/
                        closeAllLists();
                    });
                    a.appendChild(b);
                }
            }
        });
        /*execute a function presses a key on the keyboard:*/
        inp.addEventListener("keydown", function(e) {
            var x = document.getElementById(this.id + "autocomplete-list");
            if (x) x = x.getElementsByTagName("div");
            if (e.keyCode == 40) {
                /*If the arrow DOWN key is pressed,
                increase the currentFocus variable:*/
                currentFocus++;
                /*and make the current item more visible:*/
                addActive(x);
            } else if (e.keyCode == 38) { //up
                /*If the arrow UP key is pressed,
                decrease the currentFocus variable:*/
                currentFocus--;
                /* and make the current item more visible:*/
                addActive(x);
            } else if (e.keyCode == 13) {
                /*If the ENTER key is pressed, prevent the form from being submitted,*/
                e.preventDefault();
                if (currentFocus > -1) {
                    /*and simulate a click on the "active" item:*/
                    if (x) x[currentFocus].click();
                }
            }
        });
        function addActive(x) {
            /*a function to classify an item as "active":*/
            if (!x) return false;
            /*start by removing the "active" class on all items:*/
            removeActive(x);
            if (currentFocus >= x.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (x.length - 1);
            /*add class "autocomplete-active":*/
            x[currentFocus].classList.add("autocomplete-active");
        }
        function removeActive(x) {
            /*a function to remove the "active" class from all autocomplete items:*/
            for (var i = 0; i < x.length; i++) {
                x[i].classList.remove("autocomplete-active");
            }
        }
        function closeAllLists(elmnt) {
            /*close all autocomplete lists in the document,
            except the one passed as an argument:*/
            var x = document.getElementsByClassName("autocomplete-items");
            for (var i = 0; i < x.length; i++) {
                if (elmnt != x[i] && elmnt != inp) {
                    x[i].parentNode.removeChild(x[i]);
                }
            }
        }
        /*execute a function when someone clicks in the document:*/
        document.addEventListener("click", function (e) {
            closeAllLists(e.target);
        });
    }
    function addOneItem(ingredId){
        console.debug("Added one to " + ingredId);
        $.ajax({
            type: 'POST',
            url : "kitchenapp.php",
            dataType: "json",
            data: {id:ingredId, action:'addOneIngredient'},
            success: function (response) {
                console.debug(response)
                if(response.status != 0) {
                    console.error("Adding one failed!");
                }
                else if (response.status == 0){
                    // console.debug();
                    let data = $('#'+ingredId+'row').find("td:eq(3)");
                    data.html(parseFloat(data.html()) + 1);
                }
            }
        });
    };
    function removeOneItem(ingredId){
        console.debug("Removed one with id " + ingredId);
        $.ajax({
            type: 'POST',
            url : "kitchenapp.php",
            dataType: "json",
            data: {id:ingredId, action:'removeOneIngredient'},
            success: function (response) {
                console.debug(response)
                if(response.status != 0) {
                    console.error("Removing one failed!");
                }
                else if (response.status == 0){
                    // console.debug();
                    let data = $('#'+ingredId+'row').find("td:eq(3)");
                    data.html(parseFloat(data.html()) - 1);
                }
            }
        });
    };

    $(document).ready(function() {

        console.debug("Document Ready!");

        //Ingredient Search
        $("#ingredientListSearchInput").on("keyup", function() {
            const value = $(this).val().toLowerCase();
            $("#ingredientsTableBody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        $('#ingredientsTable').SetEditable({
            columnsEd: "1,2,3,4,5",
            onEdit: function(columnsEd){
                const name = columnsEd[0].childNodes[1].innerHTML;
                const unit = columnsEd[0].childNodes[2].innerHTML;
                const amnt = columnsEd[0].childNodes[3].innerHTML;
                const parAmnt = columnsEd[0].childNodes[4].innerHTML;
                const category = columnsEd[0].childNodes[5].innerHTML;
                const ingredId = columnsEd[0].childNodes[0].innerHTML;
                // console.debug("ajax called: " + amnt + " and " + ingredId);
                $.ajax({
                        type: 'POST',
                        url : "kitchenapp.php",
                        dataType: "json",
                        data: {id:ingredId, amount:amnt, par:parAmnt, category:category, unit:unit, name:name, action:'editIngredient'},
                        success: function (response) {
                            console.debug(response)
                            if(response.status != 0) {
                                console.error("Edit operation failed!");
                            }
                        }
                });
            },
            onBeforeDelete: function(columnsEd) {
                const ingredId = columnsEd[0].childNodes[0].innerHTML;
                $.ajax({
                    type: 'POST',
                    url : "kitchenapp.php",
                    dataType: "json",
                    data: {id:ingredId, action:'deleteIngredient'},
                    success: function (response) {
                        console.debug(response)
                        if(response.status != 0) {
                            console.error("Operation failed!");
                        }
                    }
                });
            },
        });
        // $('.ingredientsTable').html(ingredientsTableHTML);

        //Code segment for tab links
        $('.tablinks').on('click', function(e) {
            //console.debug('Hit nav link');
            const currentAttrValue = $(this).attr('href');
            $('.active').removeClass('active');
            $(this).addClass('active');
            $(currentAttrValue).addClass("active");

            e.preventDefault();
        });
    });
</script>
</html>