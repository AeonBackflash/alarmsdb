<?

/*  By Greg Watson
 *  Started on Jan 28, 2014
 *  Last Modified - March 19, 2014
 *  Finished - March 19, 2014
 *  Finished - March 19, 2014
 *  Updated on April 8th, 2015 - Adding dbc function for MySQL connection.  Include library on index, editrecord and add record is being removed as this is a resource on a restricted server, so I'll have to create my own.
 *
 *  This will be the edit record page for the Alarms Customers Database Tool*/

include("../inc_head.php");

include_once("customalarmfunctions.php");

//Getting the record ID from POST['recID'] to bring up the record.
$logID=$_GET['recID'];

if(isset($_POST['action'])){ //Did we just try to update the record?  If yes...
    $logID=$_POST['recordID'];
    $alarmRecord = array(  //This will be the array to store the data for the records for the database to be used in the SQL queries
        customer => $_POST['customer'],
        recID => $logID,
        APINum => $_POST['APInum'],
        SO => $_POST['SO'],
        note => $_POST['note'],
        active => (isset($_POST['active'])? "True" : "False"),
        radiusID => $_POST['radiusID'],
        alarmcom => (isset($_POST['alarmcom'])? "True" : "False"),
        alarmcomuser => $_POST['alarmcomuser'],
        paneltype => $_POST['paneltype'],
        address => $_POST['address'],
        installdate => $_POST['installdate'],
        addeddate => $_POST['addeddate'],
        closuredate => $_POST['closuredate']    
    );
        
    //setting values for the changelog record.
    
    $changeLog = array(
        user => "Greg", //this can be changed to adjust
        AlarmID => $alarmRecord['recID'],
        reason => "Customer record was updated.",
        account => $alarmRecord['APINum'],
        logdate => 'NOW()'
    );
    
    $errors=ValidateData($alarmRecord);
    if(empty($errors)){    
        
        //Because the query will contain the entire SQL statement, including table name, field names, and their values from the keyed array, this should work nicely
        $query=PrepUpdateQuery($alarmRecord, 'alarmcustomers', $logID);
    
        //Update the record with the query
        
        UpdateRecord($query);
        
        //Adding an entry into the Changes log to note what was changed.
        AddLog($changeLog);
    }
    else { //display the errors and do not update the record.
        echo "The following errors occurred when trying to update the record.  Please review them and correct:  <br /><br />";
        foreach($errors as $messages){
            echo $messages . "<br />";
        };
    }
    
    
    //Reselect the record so we can display the new info in the form fields
    if($logID&&is_numeric($logID)){ //checking to make sure that there is a recordID and that it is numeric to prevent code injection
        $selectedRecord = SelectRecord('alarmcustomers', $logID); //grab the record being worked on

    };
}
else {
    if($logID&&is_numeric($logID)){ //checking to make sure that there is a recordID and that it is numeric to prevent code injection
        $selectedRecord = SelectRecord('alarmcustomers', $logID); //grab the record being worked on

    //Loop through the selected record and replace the alarmRecord keyed array values with the values from selectedRecord values...
    };
};


echo ('
        <h1 align="center"><u>Alarm Customer Database Tool</u></h1>
        
        <div>
            <form action="index.php" method="post">
                <fieldset>
                    <legend>Search:</legend>
                    <input type="text" size="30" name="searchQuery" />
                    <input type="submit" value="GO" />
                </fieldset>
            </form>
        </div>
        <div class="pagenav">
            <a href="addrecord.php">Add New Customer</a><br />
            <a href="index.php">Back to Main</a>
        </div>
        <br />'   
);
    
echo '<style>
    fieldset {
        display:inline;
    }
    
    .formbody{
        display: inline-block;
        float: left;
        width: 20%;
        position: relative;
    }
    
    .formbody label{
        display: inline-block;
        border: hidden 3px;
        font-size: 12px;
        height: 15px;
        margin: 1;

    }
    
    .input{
        display: inline-block;
        float: left;
        width: 45%;
        position: relative;
    }
    .input input {
        display: inline-block;
        margin: 1;
        padding: 1;
        height: 15px;
    }

    .changelog{
            float:left;
            width: 33%;
            display: inline-block;
            position: relative;
    }
    
    .formfooter {
        clear: both;
        width: 33%;
        margin: 0;
    }
    
    .pagenav {
        clear: both;
    }
</style>


<form action="editrecord.php" method="post">
  <fieldset>
      <legend>Record Info:</legend>
      <div class="formbody">
            <label>Customer:</label><br />
            <label>API Account #:</label><br />
            <label>Address:</label> <br />
            <label>Service Order:</label><br />
            <label>Radius ID:</label><br />
            <label>Alarm.com?:</label><br />
            <label>Alarm.com Username<br />
            <label>Panel Type:</label><br />
            <label>Install Date (YYYY-MM-DD):</label><br />
            <label>Active?:</label><br />
            <label>Closure Date (YYYY-MM-DD):</label><br /><br />
            <label>Added to Database:</label><br /><br />
            <label>Notes: </label><br /><br />
            <textarea name="note" rows="4" cols="60">' . $selectedRecord['note'] . '</textarea>
            <input type="hidden" name="recordID" value="' . $selectedRecord['recID'] . '" /><br />
            <input type="submit" name="action" />
        </div>
        <div class="input">
            <input type="text" name="customer" size="60" value="' . $selectedRecord['customer'] . '" /><br />
            <input type="text" name="APInum" size="10" value="' . $selectedRecord['APInum'] . '"/><br />
            <input type="text" name="address" size="60" value="' . $selectedRecord['address'] . '"/><br />
            <input type="text" name="SO" size="8" value="' . $selectedRecord['SO'] . '"/><br />
            <input type="text" name="radiusID" size="12" value="' . $selectedRecord['radiusID'] . '"/><br />
            <input type="checkbox" name="alarmcom" ';
            if($selectedRecord['alarmcom']==True)echo 'checked="checked"';
            echo '" /><br />
            <input type="text" name="alarmcomuser" size="30" value="';
                echo ($selectedRecord['alarmcom']==True?$selectedRecord['alarmcomuser']:"");
            echo '"/><br />
            <input type="text" name="paneltype" size="30" value="' . $selectedRecord['paneltype'] . '"/><br />
            <input type="text" name="installdate" size="10" value="' . $selectedRecord['installdate'] . '"/><br />
            <input type="checkbox" name="active"';
            if($selectedRecord['active']==True) echo ' checked="checked"';
            echo '" /><br />
            <input type="text" name="closuredate" size="10" value="';
            echo ($selectedRecord['active']==True?'':$selectedRecord['closuredate']);
            echo'"/><br /><br />
            '. $selectedRecord['addeddate'] . '
        </div>
        <div class="changelog">';
               DisplayChangeLog($changeLog['AlarmID']); 
        echo '</div>
        
  </fieldset>    
</form>
<br />
</html>';

?>

