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

$alarmRecord = array(  //This will be the array to store the data for the records for the database to be used in the SQL queries
    customer => $_POST['customer'],
    recID => $_POST['recID'],
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

$changeLog = array(
        user => "Greg",
        alarmID => $alarmRecord['recID'],
        reason => "Customer record was added.",
        account => $alarmRecord['APINum'],
        logdate => 'NOW()'
    );

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
    </div>'   
);

//prepare the query to be used for the AddRecord function and store it in $query
if(isset($_POST['action'])){
    $errors=ValidateData($alarmRecord);
    if(empty($errors)){
        $query=PrepAddQuery($alarmRecord, 'alarmcustomers');
        
        //Because the query will contain the entire SQL statement, including table name, field names, and their values from the keyed array, this should work nicely
        AddRecord($query);
        
        //Now that we have added the record, we need to note the change in the changelog table.  Call AddLog function...
        
        AddLog($changeLog);
    }
    else {
        echo "The following errors occurred when trying to add a record.  Please review them and correct:  <br /><br />";
        foreach($errors as $messages){
            echo $messages . "<br />";
        };
    }
};

?>

<style>
    fieldset {
        display:inline;
    }
    
    .formbody{
        display: inline-block;
        float: left;
        width: 33%;
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
        width: 33%;
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
    
</style>
    <div class="pagenav">
        <a href="index.php">Back to Main</a>
    </div>
    <br />  
    <form action="addrecord.php" method="post">
        <fieldset>
        <legend>Record Info:</legend>
            <div class="formbody">
                  <label>Customer:</label><br />
                  <label>API Account #:</label><br />
                  <label>Address:</label> <br />
                  <label>Service Order:</label><br />
                  <label>Radius ID:</label><br />
                  <label>Alarm.com?:</label><br />
                  <label>Alarm.com Username:</label>
                  <label>Panel Type:</label><br />
                  <label>Install Date (YYYY-MM-DD):</label><br />
                  <label>Active?:</label><br />
                  <label>Closure Date (YYYY-MM-DD):</label><br />
            </div>
            <div class="input">
                <input type="text" name="customer" size="60" value="<? echo $_POST['customer']; ?>" /><br />
                <input type="text" name="APInum" size="10" value="<? echo $_POST['APInum']; ?>" /><br />
                <input type="text" name="address" size="60" value="<? echo $_POST['address']; ?>" /><br />
                <input type="text" name="SO" size="8" value="<?echo $_POST['SO']; ?>"/><br />
                <input type="text" name="radiusID" size="12" value="<? echo $_POST['radiusID']; ?>" /><br />
                <input type="checkbox" name="alarmcom" checked="<? if($_POST['alarmcom']==True) {echo 'checked';} else {echo 'unchecked';}; ?>" /><br />
                <input type="text" name="alarmcomuser" size="30" value="<? echo $_POST['alarmcomuser']; ?>" /><br />
                <input type="text" name="paneltype" size="30" value="<? echo $_POST['paneltype']; ?>" /><br />
                <input type="text" name="installdate" size="10" value="<? echo $_POST['installdate']; ?>" /><br />
                <input type="checkbox" name="active" checked="<? if($_POST['active']==True) {echo 'checked';} else {echo 'unchecked';}; ?>" /><br />
                <input type="text" name="closuredate" size="10" value="<? echo $_POST['closuredate'] ?>" /><br />
            </div>
            <br /><br />
            <div class="formfooter">
                <label style="font-size: 12px">Notes: </label><br /><br />
                <textarea name="note" rows="4" cols="60"><? echo $_POST['note'] ?></textarea><br />
                <input type="submit" name="action" />
            </div>
        </fieldset>    
    </form>
</html>


