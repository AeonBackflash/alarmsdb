<?php
    
    /*  By Greg Watson
     *  Started on Jan 15, 2014
     *  Last Modified - March 14, 2014
     *  Finished - March 19, 2014
     *
     *  This will be the main page for the Alarms Customers Database Tool*/
        
        // including staff page header
        include("../inc_head.php");
        // including my customer alarm functions.
        
        include_once("customalarmfunctions.php");
        
     $alarmRecord = array(  //This will be the array to store the data for the records for the database to be used in the SQL queries
        customer => $_POST['customer'],
        recID => $_POST['recID'],
        APINum => $_POST['APInum'],
        SO => $_POST['serviceorder'],
        note => $_POST['notes'],
        active => (isset($_POST['active'])? "True" : "False"),
        radiusID => $_POST['username'],
        alarmcom => (isset($_POST['alarmcom'])? "True" : "False"),
        alarmcomuser => $_POST['alarmcomuser'],
        paneltype => $_POST['panel'],
        address => $_POST['address'],
        installdate => $_POST['installdate'],
        addeddate => $_POST['addeddate'],
        closuredate => $_POST['closuredate']    
    );
    
    // Variables for the page navigation in the search results
    
    $page=(isset($_GET['page'])?$_GET['page'] : 1);
    $rowsPerPage=(isset($_POST['rowsperpage'])?$_POST['rowsperpage'] : 20);
    if(isset($_GET['rowsperpage'])) $rowsPerPage=$_GET['rowsperpage'];
    $startRow=(isset($_GET['page'])?($page-1)*$rowsPerPage : 0);

    /*If a record is selected by clicking on the select link beside the record in the search results, use the SelectRecord function to retrieve only that record and store it in the $alarmRecord associative array.*/
    
    if($logID&&is_numeric($logID)){ //checking to make sure that there is a recordID and that it is numeric to prevent code injection
        $selectedRecord = SelectRecord('alarmcustomers', $logID);
            
            //Manually enter all values in the selectedRecords field to the $alarmRecord placeholder.
            
            $_POST['customer']=$selectedRecord['customer'];
            $_POST['recID']=$selectedRecord['recID'];
            $_POST['APINum']=$selectedRecord['APInum'];
            $_POST['SO']=$selectedRecord['SO'];
            $_POST['note']=$selectedRecord['note'];
            $_POST['active']=$selectedRecord['active'];
            $_POST['radiusID']=$selectedRecord['radiusID'];
            $_POST['alarmcom']=$selectedRecord['alarmcom'];
            $_POST['alarmcomuser']=$selectedRecord['alarmcomuser'];
            $_POST['paneltype']=$selectedRecord['paneltype'];
            $_POST['installdate']=$selectedRecord['installdate'];
            $_POST['addeddate']=$selectedRecord['addeddate'];
            $_POST['closuredate']=$selectedRecord['closuredate'];
            $_POST['address']=$selectedRecord['address'];
            
            
        
    }
    else { //otherwise, set the fields to empty or default
        foreach($alarmRecord as $field => $value){
            empty($alarmRecord[$field]);
            $alarmRecord['active']=False;
            $alarmRecord['alarmcom']=False;
        };
    }
    
    // This will build the subheader for the tool itself and also show the Search Box form.
    echo'
        <style>
            fieldset {
                display: inline;
            }
        </style>
        
        <h1 align="center"><u>Alarm Customer Database Tool</u></h1>
        <div>
            <form action="index.php" method="post" name="search">
                <fieldset>
                    <legend>Search:</legend>
                    <input type="text" size="30" name="searchQuery" value="' . $searchQuery . '"/>
                    <input type="submit" value="GO" />
                    <br />
                    Results per page:
                    <select name="rowsperpage">
                        <option value="10"';
                        if($rowsPerPage==10) echo "selected>10";
                        else echo '>10';
                        echo '</option>
                        <option value="20"';
                        if($rowsPerPage==20) echo "selected>20";
                        else echo '>20';
                        echo '</option>
                        <option value="25"';
                        if($rowsPerPage==25) echo "selected>25";
                        else echo '>25';
                        echo '</option>
                        <option value="50"';
                        if($rowsPerPage==50) echo "selected>50";
                        else echo '>50';
                        echo '</option>
                    </select>
                    <input type="hidden" name="page" value="' . $page . '" />
                </fieldset>
            </form>
        </div>'; 

    echo '<br /><br /><a href="addrecord.php">Add New Customer</a>';
    //variable for the searchQuery
    $searchQuery = $_POST['searchQuery'];
    
    //Call the search query function.  It will already know what to do as far as if the string is empty or full...
    $returnedRows = SearchRecord($searchQuery,'alarmcustomers', $alarmRecord, $startRow, $rowsPerPage);
    
    //We need to know how many records were returned so we can calculate how many pages will need to be done.
    echo "<br /><br />";
    BuildPageNav($searchQuery, $alarmRecord, $page, "alarmcustomers", $rowsPerPage);
    echo "<br /><br />";
    DisplaySearchResults($returnedRows);
    echo "<br /><br />";
    BuildPageNav($searchQuery, $alarmRecord, $page, 'alarmcustomers', $rowsPerPage);
?>
