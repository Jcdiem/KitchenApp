<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/utils.php"; /** @var $mysqli */

//Create the 'edit ingredient list'
$result = simpleMySQL('SELECT * FROM kitchen.ingredients',$mysqli);
$ingredientsTableHTML = '<thead class="thead-light"><tr><th scope="col">ID</th><th scope="col">Name</th><th scope="col">Unit</th><th scope="col">Amount</th><th scope="col">Par</th><th scope="col">Category</th><th scope="col">Add/Remove</th></tr></thead><tbody id="ingredientsTableBody">';
while($row = $result->fetch_assoc()){
    $ingredientsTableHTML .= '<tr id="' . $row['id'] . 'row"><td>' . $row['id'] . '</td><td>' . $row['name'] . '</td><td>' . $row['unit'] . '</td><td>' . $row['amount'] . '</td><td>' . $row['par'] . '</td><td>' . $row['category'] . '</td><td><button class="btn-info" onclick="addOneItem(' . $row['id'] . ');">Plus</button>/<button class="btn-danger" onclick="removeOneItem(' . $row['id'] . ');">Minus</button></td></tr>';
}
$ingredientsTableHTML .= '</tbody>';

//debug_to_console($ingredientsTableHTML);
//echo('<script>const ingredientsTableHTML = \'' . $ingredientsTableHTML . ' \';</script>');

// Create Shopping List under par table
$result = simpleMySQL('SELECT * FROM kitchen.ingredients WHERE par > amount',$mysqli);
$shoppingListUnderParTableHTML = '<thead class="thead-light"><tr><th scope="col">Name</th><th scope="col">Unit</th><th scope="col">Amount</th><th scope="col">Par</th><th scope="col">Category</th><th scope="col">Add</th></tr></thead><tbody id="shoppingListAutoAddTableBody">';
while($row = $result->fetch_assoc()){
    $shoppingListUnderParTableHTML .= '<tr><td>' . $row['name'] . '</td><td>' . $row['unit'] . '</td><td>' . $row['amount'] . '</td><td>' . $row['par'] . '</td><td>' . $row['category'] . '</td><td><input type="checkbox" name="checkedId[]" value="' . $row['id'] . '"></td></tr>';
}
$shoppingListUnderParTableHTML .= '</tbody>';

// Create Shopping List table & dropdown
//TODO: Have table show how many items in each list
$result = simpleMySQL('SELECT * FROM kitchen.shoppingLists WHERE kitchen.shoppingLists.completed = false',$mysqli);
//Set up the table
$shoppingListTableHTML = '<thead class="thead-light"><tr><th scope="col">Name</th><th scope="col">Created</th><th scope="col">Items</th><th scope="col">Complete</th></tr></thead><tbody id="shoppingListAutoAddTableBody">';
//Set up the dropdown menu
$shoppingListSelectDropdownHTML = '';
while($row = $result->fetch_assoc()){
    //Add table item
    $shoppingListTableHTML .= '<tr><td>' . $row['name'] . '</td><td>' . $row['dateCreated'] . '</td><td>Qty</td><td><a href="shoppinglist.php?listId=' . $row['id'] . '">View List</a></td></tr>';

    //Add item to dropdown menu
    $shoppingListSelectDropdownHTML .= '<option value="'. $row['id'] .'">' . $row['name'] . '</option>';
}
//Finish off table body
$shoppingListTableHTML .= '</tbody>';


?>

<html lang="en">
<head>
    <!--  Template for the dashboard courtesy of https://www.blog.duomly.com/bootstrap-tutorial/  -->
    <meta charset="UTF-8">
    <title>Simple Kitchen App</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"
            integrity="sha384-vtXRMe3mGCbOeY7l30aIg8H9p3GdeSe4IFlP6G8JMa7o7lXvnz3GFKzPxzJdPfGK"
            crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
          integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
            integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV"
            crossorigin="anonymous"></script>
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
        .btn-sm {
            font-size: 1.5rem;
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
<!--                        //TODO: Add a way to view shopping list contents-->
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
        </main>
    </div>
</div>
</body>
<script>
    function shoppingListAddHandler(){
        //TODO: Implement the javascript handler for taking items and adding them
    };

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
            columnsEd: "3,4",
            onEdit: function(columnsEd){
                const amnt = columnsEd[0].childNodes[3].innerHTML;
                const parAmnt = columnsEd[0].childNodes[4].innerHTML;
                const ingredId = columnsEd[0].childNodes[0].innerHTML;
                // console.debug("ajax called: " + amnt + " and " + ingredId);
                $.ajax({
                        type: 'POST',
                        url : "kitchenapp.php",
                        dataType: "json",
                        data: {id:ingredId, amount:amnt, par:parAmnt, action:'editIngredient'},
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