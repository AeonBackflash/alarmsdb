<?

/* Custom Functions page for the Alarms Database Tool
    By Greg Watson
 *  Started on Jan 15, 2014
 *  Last Modified - March 19, 2014
 *  Finished - March 19, 2014
 *  Updated on April 8th, 2015 - Adding dbc function for MySQL connection.  Include library on index, editrecord and add record is being removed as this is a resource on a restricted server, so I'll have to create my own. 
*/
function dbc($database){
    $host = ""; //for the server domain name
    $user = ""; //DB username
    $password = ""; // DB password
    
    mysql_connect($host,$user,$password,$database) or die("There was a problem connecting to the MySQL Server: " . mysql_error());
};
function AddRecord($query){
    //open connection to the database
    $resource = dbc("alarms");
    if(mysql_error()) die ("Could not connect to the database or the following errors occurred: " . mysql_error());
    else{
        $results = mysql_query($query, $resource);
        
        //check to make sure that record was added.  If not, return a message why, if so then return that record could be added sucessfully.
        
        if(!$results) die('Cannot add the record: ' . mysql_error());
        else echo("Record added successfully!");
    };
    
    //close the MySQL Connection
    mysql_close($resource);
    
};

function UpdateRecord($query){
    //Open the Database Connection
    $resource=dbc("alarms");
    if(mysql_error()) die ("Could not connect to the database or the following errors occurred: " . mysql_error());
    else{
        $results = mysql_query($query, $resource);
        //If there is a problem, die and explain why.
        if(!$results) die('Cannot update the record: ' . mysql_error());
        // Everything went well, give a big hurrah!
        else echo ("Record successfully updated!");
    };
    mysql_close($resource);
 };
 
 // This function will preform a search.  It will take in the search query string, the table name and the keyed array for the record.  It will return all rows in the SQL query as an associative array.
 
 function SearchRecord($searchQuery, $table, $arrRecord, $startPos, $limit){
    $resource = dbc("alarms");
    if(mysql_error()) die ("Could not connect to the database or the following errors occurred: " . mysql_error());
    else {
        $field = (array_keys($arrRecord));
        $conditionJoinStr = " like '%" . strtoupper($searchQuery) . "%' Or ";
        $query = 'select * from ' . $table .
            ' where ' . implode($conditionJoinStr, $field). ' like "%' .
            strtoupper($searchQuery) . '%" limit ' . $startPos . ',' . $limit . ';';
        $results = mysql_query($query, $resource) or die("Could not complete the request:" . mysql_error());
        //get number of records found
        $recordsReturned = mysql_num_rows($results);
        
        //If nothing is put in the search field, return all the records of the database as an associated array.
        if(empty($searchQuery)) {
            $arrResults = array();
            $query = 'select * from ' . $table . ' limit ' . $startPos . ',' . $limit . ';';
            $results = mysql_query($query, $resource) or die("Could not complete the request:" . mysql_error());
            while($row=mysql_fetch_assoc($results)){
                $arrResults[]=$row;
            }
            return $arrResults;            
        }
        //If the records returned do not equal 0, complete the search results and return the array.
        elseif($recordsReturned > 0) {
            $arrResults=array();
            $results = mysql_query($query, $resource) or die("Could not complete the request:" . mysql_error());
            while($row=mysql_fetch_assoc($results)){
                $arrResults[]=$row;
            }
            return $arrResults;
        }
        // otherwise, there are no records to be found...
        else {
            echo "There are no records based on your query.";            
        }
    }
    mysql_close($resource);
 };
 
 //This function will use the search results from SearchRecord and display it in a table.  Links will be created in the edit column next to each record, so that the page will resubmit itself with all form fields below filled with info from the record
 
 function DisplaySearchResults($arrSearchResults){
    //Set up the Table head and headers    
     if($arrSearchResults){
        echo("
            <table border='1' style='empty-cells:show'>
                <thead>List of Alarm Customers</thead>
                <tr>
                <th>Customers</th>
                <th>Account Number</th>
                <th>Address</th>
                <th>Service Order</th>
                <th>Radius ID</th>
                <th>Alarm.com</th>
                <th>Alarm.com Username</th>
                <th>Panel Type</th>
                <th>Install Date</th>
                <th>Active?</th>
                <th>Closure Date</th>
                <th>Added to Database</th>
                <th>Notes</th>
                <th>Edit Record</th>
            </tr>"
        );
        //We will need to process the associated arrays to take each value for each key and put them in the correct columns
        foreach($arrSearchResults as $row){
            echo ('<tr>
                        <td>' . $row['customer'] . '</td>
                        <td>' . $row['APInum'] . '</td>
                        <td>' . $row['address'] . '</td>
                        <td>' . $row['SO'] . '</td>
                        <td>' . $row['radiusID'] . '</td>'
                );
            if($row['alarmcom']==True) echo "<td>Yes</td>";
            else echo "<td>No</td>";
            echo "<td>" . $row['alarmcomuser'] . "</td>
                <td>" . $row['paneltype'] . "</td>" .
                "<td>" . $row['installdate'] . "</td>";
            if($row['active']==True) echo "<td>Yes</td>";
            else echo "<td>No</td>";
            echo "<td>" . $row['closuredate'] . "</td>" .
                "<td>" . $row['addeddate'] . "</td>" .
                "<td>" . $row['note'] . "</td>" .
                "<td><a href='editrecord.php?recID=" . $row['recID'] . "'>Edit</a></td>" .
                "</tr>";
        };
     }
     else {
        echo "No results found";
     }
    
    //Close the table tag
    echo "</table>";
};

//this function will select one record to be used in the form of the main page and return an associated array with that record and table fields.

function SelectRecord($table, $recID) {
    $resource=dbc('alarms');
    $query = "select * from " . $table . " where recID=" . $recID . ";" ;
    $results = mysql_query($query, $resource) or die("Could not complete the request:" . mysql_error());
    $arrResults=mysql_fetch_assoc($results);
    return $arrResults;
    mysql_close($resource);
};

//when this function is called, it will display all the changes made to this account

function DisplayChangeLog($alarmID){
    $resource=dbc('alarms') or die('Cannot connect to the database: ' . mysql_error());
    $query="select * from changelog where AlarmID = '" . $alarmID . "' order by logID desc limit 10;";
    $results=mysql_query($query, $resource) or die ('Could not retrieve records: ' . mysql_error());
    $arrChangeLog=array();
    while($row=mysql_fetch_assoc($results)){
                $arrChangeLog[]=$row;
    };
    
    //Prepare the table to be displayed with headers, then list each record for the account
    
    echo('<table border="1">
    <tr>
        <legend>Changes Log</legend>
        <th>Time Stamp</th>
        <th>Account</th>
        <th>Reason</th>
        <th>Changed By</th>
    </tr>         
    ');
    foreach($arrChangeLog as $row){
        echo('<tr>
                <td>' . $row['logdate'] . '</td>
                <td>' . $row['account'] . '</td>
                <td>' . $row['reason'] . '</td>
                <td>' . $row['user'] . '</td>
        </tr>');
    };
    echo '</table> ';
    mysql_close($resource);
};

//This function will validate the array and data for the customer records and return a new keyed array to be used for the AddRecord and UpdateRecord functions.  All checks will be commented below.

function PrepAddQuery($arrRecord, $table){
    
    //We want separate conditions depending if the record array is either being used for updating or if we are adding a record to the table
    
    $queryFields = array();
    $queryValues = array();
        
         //Because we do not want to change the recID or logID when adding a record, we need to unset the corresponding key and value so that it does not cause the SQL insert to fail.  We have to check for both keys as this function is used to add a record to either 'alarmcustomer' or 'changelog'
        
        //loop through the new array and add quotations to all values.  The specific conditionals will adjust the new keys and remove the ones we don't need.
        foreach($arrRecord as $field => $value){
             $arrRecord[$field] = '"' . $value . '"';  
        };
        //check the keyed array for recID, if not found we can assume that logID exist and can be removed.  If found, remove 
        
        unset($arrRecord['logID']);
        
        //For the alarmcustomer insert, if the active checkbox is checked we will need to remove the closuredate key from the array.
        if($arrRecord['active']=='"True"') unset($arrRecord['closuredate']);
        
        //If the notes field is empty, remove notes from the query.
        if(empty($arrRecord['note'])) unset($arrRecord['note']);
        
        //Since we are adding a record, we want to set Added Date record to the current time
        $arrRecord['addeddate']='NOW()';
        //We also need to set the booleen values to either True or False as they will come in as 'true' or 'false'
        $arrRecord['active']=($arrRecord['active']=='"True"'?$arrRecord['active']='true':$arrRecord['active']='false');
        $arrRecord['alarmcom']=($arrRecord['alarmcom']=='"True"'?$arrRecord['alarmcom']='true':$arrRecord['alarmcom']='false');
        
        //If the Alarm.com box is unchecked, make the Alarm.com Username field empty
        if($arrRecord['alarmcom']=='"False"') {$arrRecord['alarmcomuser']="";}

 
        //all values should now be ready to put into strings, so we will need to implode them for the final query.  We need to separate the keys from the values first
        foreach($arrRecord as $field => $value){
            $queryFields = array_keys($arrRecord);
            $queryValues = array_values($arrRecord);
        };
        
        //time to implode queryFields and queryValues with comma separators and export to a string and build the last part of the query used by Add Record
        
        $returnedQuery = "insert into ". $table. "(" . implode(", ",$queryFields) . ") value (" . implode(", ",$queryValues) . ");";
        return $returnedQuery; //rerturn the query to be used for the AddRecord Function.
    
};
    //We can assume that anything else will be considered updating.
    
function PrepUpdateQuery($arrRecord, $table, $recID){
    
    //unsetting addeddate and recID as we do not want these record fields touched when updating a customer's record
       
    unset($arrRecord['recID']);
    unset($arrRecord['addeddate']);  //Because we are not added a record, we can drop the addeddate element from the array as this should not be touched.
    
    //Take the remaining keys and put quotations around them to start
    
    foreach($arrRecord as $field => $value){
         $arrRecord[$field] = '"' . $value . '"';  
    };
    //For the alarmcustomer update, if the active checkbox is checked we will need to remove the closuredate key from the array.
    
    if($arrRecord['active']=='"True"') $arrRecord['closuredate']='""';        
    
    //If the notes field is empty, remove notes from the query.
    
    if(empty($arrRecord['note'])) unset($arrRecord['note']);
    
    //We also need to set the booleen values to either True or False as they will come in as a 1 or 0
    $arrRecord['active']=($arrRecord['active']=='"True"'?$arrRecord['active']='true':$arrRecord['active']='false');
    $arrRecord['alarmcom']=($arrRecord['alarmcom']=='"True"'?$arrRecord['alarmcom']='true':$arrRecord['alarmcom']='false');
    
    //If the Alarm.com box is unchecked, make the Alarm.com Username field empty
    if($arrRecord['alarmcom']=='"False"') {$arrRecord['alarmcomuser']="";}    
    
    //all values should now be ready to put into strings, so we will need to implode them for the final query.  We need to separate the keys from the values first
    $returnedQuery = "update " . $table . " set "; //First part of the query string
    
    //loop through each key and value to build the conditions.
    
    $querymidstr=array();
    foreach($arrRecord as $field => $value){
        $querymidstr[] = $field . " = " . $value;
    };
    $returnedQuery .= implode(", ", $querymidstr);
    $returnedQuery .= " where recID="; 
    $returnedQuery .= $recID;
    $returnedQuery .= ";";

    return $returnedQuery; //rerturn the query to be used in the UpdateRecord Function.
};   

//This function will add a recored to the changelog table to keep a tab every time someone updates or adds a record to the alarmscustomers table

function AddLog($arrLog){
        
        //temp storage for the fields
        $queryFields = array();
        $queryValues = array();
        
        
        unset($arrLog['logID']); //The logID field should auto increment and is not needed for the query
        foreach($arrLog as $fields => $values){
            $arrLog[$fields]='"' . $values . '"';
        };
        $arrLog['logdate']="NOW()";
        $queryFields = array_keys($arrLog);
        $queryValues = array_values($arrLog);
        $query = "insert into changelog (" . implode(", ", $queryFields) . ") values (" . implode(", ", $queryValues) . ");";
        $resource=dbc('alarms') or die('Cannot connect to the database: ' . mysql_error());
        $results=mysql_query($query, $resource) or die("Could not complete the request:" . mysql_error());
        mysql_close($resource);
        
};

//This function will check the records for any errors and process the data and store error messages in an array.

function ValidateData($arrRecord){
    $errMsg = array();
    
    //For the Customer Field, the only condition is that it should not be empty. Also cannot be more than 60 characters
    if(empty($arrRecord['customer'])) $errMsg[]="Customer Field cannot be empty!";
    if(strlen($arrRecord['customer'])>60) $errMsg[]="Customer Field is too long!";
    
    //For the API number, this should not be empty.  Also cannot be more than 8 characters
    if(empty($arrRecord['APINum'])) $errMsg[]="API Number Field cannot be empty!";
    if(strlen($arrRecord['APINum'])>8) $errMsg[]="API Number Field is too long!";
    
    //Address field cannot be blank. Cannot be more than 100 characters
    if(empty($arrRecord['address'])) $errMsg[]="Address Field cannot be empty!";
    if(strlen($arrRecord['address'])>100) $errMsg[]="API Number can only be 8 digits long!";
    
    //Service Order 'SO' field cannot be blank. Can't be more than 8 characters
    if(empty($arrRecord['SO'])) $errMsg[]="Service Order Field cannot be empty!";
    if(strlen($arrRecord['SO'])>8) $errMsg[]="Service Order Field is too long!";
    
    //Panel Type field cannot be empty, can't be more than 250 characters
    if(empty($arrRecord['paneltype'])) $errMsg[]="Panel Type Field cannot be empty!";
    if(strlen($arrRecord['SO'])>250) $errMsg[]="Panel Type Field is too long!";
    
    //Install date field cannot be empty
    if(empty($arrRecord['installdate'])) $errMsg[]="Please enter an install date!";
    
    //If the active box is unchecked (meaning closed account), then the Closure Date field cannot be empty
    if(($arrRecord['active']=='False' && $arrRecord['closuredate']=="")||($arrRecord['active']=='False' && $arrRecord['closuredate']=="0000-00-00")) $errMsg[] = "You cannot have an empty Closure Date if the account is not active!";
    
    //If the active box is checked, just make the closure date field empty or a zero date.
    
    if ($arrRecord['active']=='True'){$arrRecord['closuredate']="";}
    
    //If the Alarm.com box is checked, the Alarm.com Username field cannot be empty
    if($arrRecord['alarmcom']=='True' && $arrRecord['alarmcomuser']=="") {
        $errMsg[] = "If customer has Alarm.com, the username field cannot be empty!";
    }
    
    //Alarm.com Username field cannot be more than 60 characters either
    if(strlen($arrRecord['alarmcomuser']>60)) $errMsg[] = "Alarm.com Username cannot be more than 60 characters.";
   
    return $errMsg;
};

//This function will complete the search, calculate how many pages will be to be referenced to house all the results of the search, then display the page navigation links.  Function will draw in the Search Query, run an SQL query similar to the SearchFunction function to perform the initial search with starting position at the beginning and right through until the end of records.

function BuildPageNav($searchQuery, $arrRecords, $pageNum, $table, $rowsPerPage){
    
    //Count the number of records first.  This will be used in the Search Records
    $field = (array_keys($arrRecords));
    $conditionJoinStr = " like '%" . strtoupper($searchQuery) . "%' Or ";
    $query = 'select * from ' . $table .
            ' where ' . implode($conditionJoinStr, $field). " like '%" .
            strtoupper($searchQuery) . "%';";
    $resource=dbc("alarms") or die("Cannot connect to the database:" . mysql_error());
    $results=mysql_query($query, $resource);
    $rowCount=mysql_num_rows($results);
    $previous=$pageNum-1;
    $next=$pageNum+1;
    
    //Calculate the number of pages in total with ceil
    
    $pages=ceil($rowCount/$rowsPerPage);
    //For the navigation bar, we need to decide what options will be clickable and which ones will not.  Also, we need to build the links to pass the page, start position and limit.
    
    //For the "First Page" and "Previous Page" links...
    if($pageNum==1) echo "First Page <-- ";
    else echo "<a href='index.php?page=1&searchQuery=" . $searchQuery ."&rowsperpage=". $rowsPerPage ."'>First Page</a> <-- ";
    
    if($pageNum<=2) echo " Previous Page <-- ";
    else echo "<a href='index.php?page=". $previous ."&searchQuery=" . $searchQuery ."&rowsperpage=". $rowsPerPage ."'> Previous Page</a><--";
    
    //Loop through all the pages and 
    
    for($i=1; $i<=$pages; $i++){
        echo "[<a href='index.php?page=". $i ."&searchQuery=" . $searchQuery ."&rowsperpage=". $rowsPerPage ."'>" . $i . "</a>] ";
    };
    
    if($pageNum>=$pages-1) echo " --> Next Page --> ";
    else echo " --> <a href='index.php?page=" . $next . "&searchQuery=" . $searchQuery . "&rowsperpage=". $rowsPerPage . "'>Next Page</a> --> ";
    
    if($pageNum==$pages) echo "Last Page<br />";
    else echo "<a href='index.php?page=". $pages . "&searchQuery=" . $searchQuery ."&rowsperpage=". $rowsPerPage . "'>Last Page</a><br /> ";
    
    mysql_close($resource);
};
?>
