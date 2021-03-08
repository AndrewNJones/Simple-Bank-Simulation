<?php
//Andrew Jones
//CS 316 - Project 2
//3/5/21
//Purpose: This is a PHP CGI file that communicates with a MYSQL database to create a simple bank with account values that can be manipulated.

//contains our database credentials
require_once 'db_creds.inc';

//This PDO will be used to connect to the database
$PDO = NULL;

//this function gets our PDO credentials to help us connect to the database
function get_pdo(){
	global $PDO;
	if ($PDO == NULL) {
		try {
			//our PDO is populated with data from our included 'db_creds.inc file
			$PDO = new PDO(K_CONNECTION_STRING, K_USERNAME, K_PASSWORD);
			//this allows the PDO to output errors when not working properly
			$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//the catch function will catch all errors in the above process
		}catch(PDOException $e){
			die("ERROR: Could not connect. " . $e->getMessage());
			}
		}
	}

//this function creates our database table if it doesnt already exist
function createTable(){
	global $PDO;

	//runs get_pdo to establish connection
	get_pdo();
	try {

		//this sql creates a new table accounts with columns checking and savings
		$sql = "CREATE TABLE IF NOT EXISTS accounts(
			checking decimal(15,2) NOT NULL,
			savings decimal(15,2) NOT NULL)";

		//we use pdo to execute the sql string
		$PDO->exec($sql);
	}catch(PDOException $e){
		die("ERROR: Was not able to execute $sql. " . $e->getMessage());	}
}

//this creates a new row within our table (for this assignment, onlt one row is ever inserted)
function insertRow(){
	global $PDO;
	get_pdo();
	try{
		//inserts a new row with initial values of 100 for checking and savings
		$sql = "INSERT INTO accounts (checking, savings) VALUES (100.00, 100.00)";
		$PDO->exec($sql);
		echo "Records inserted sucessfully. ";
	}catch(PDOException $e){
		die("ERROR: could not execute $sql. " . $e->getMessagew());
	}
}

//this function prints our values in a table in html. The function is called within the html at the end of the file
function getValues(){
	global $PDO;
	get_pdo();
	try
	{
		//selects all data from accounts
		$sql = "SELECT * FROM accounts";
		$result = $PDO->query($sql);

		//executes if our table is not empty
		if($result->rowCount() > 0){
			//our heading is printed
			echo "<table style='border:2px solid blue;'>";
                	echo "<tr>";
			echo "<th>checking</th>";
			echo "<th>savings</th>";
			echo "</tr>";
			while ($row = $result->fetch()){
				//our data is pulled from the database and inserted into our table
				echo "<tr>";
				echo "<td>" . $row['checking'] . "</td>";
				echo "<td>" . $row['savings'] . "</td>";
				echo "</tr>";
			}
			echo "</table>";
		}
	}catch(PDOException $e){
		die("ERROR: Could not execute $sql. " . $e->getMessage());
	}
}

//money is added to the checking account
function addChecking($input){
	//input from text form is changed from a string to a float
	$input = floatval($input);
	global $PDO;
	get_pdo();
	try{
		//our checking value is updated by adding an unspecified value
		$sql = "UPDATE accounts SET checking=checking+?";

		//the sql is prepared by the PDO
		$prepared = $PDO->prepare($sql);

		//the prepared sql is executed with our input data
		$prepared->execute(array($input));
	}catch(PDOException $e){
		die("Error: Could not able to execute $sql. " . $e->getMessage());
	}
}

//the addChecking function is repeated, but the input value is added to the savings account instead
function addSavings($input){
	$input = floatval($input);
	global $PDO;
	get_pdo();
	try{
		$sql = "UPDATE accounts SET savings=savings+?";
		$prepared = $PDO->prepare($sql);
		$prepared->execute(array($input));
	}catch(PDOException $e){
		die("ERROR: Could not able to execute $sql. " . $e->getMessage());
	}
}

//money is transfered from the savings account to the checking account
function transferChecking($input){
	$input = floatval($input);
	global $PDO;
	get_pdo();

	//this try adds money to the checking account
	try{
		$sql_1 = "UPDATE accounts SET checking=checking+?";
		$prepared_1 = $PDO->prepare($sql_1);
		$prepared_1->execute(array($input));
	}catch(PDOException $e){
		die("ERROR: Could not able to execute $sql. " . $e->getMessage());
	}

	//this try subtracts money from the savings account
	try {
		$sql_2 = "UPDATE accounts SET savings=savings-?";
		$prepared_2 = $PDO->prepare($sql_2);
		$prepared_2->execute(array($input));
	}catch(PDOException $e){
		die("ERROR: Could not able to execute $sql. " . $e->getMessage());
	}
}

//the transferChecking() function is copied, but money is transfered from checking to savings
function transferSavings($input){
	$input = floatval($input);
	global $PDO;
	get_pdo();
	try {
		$sql_1 = "UPDATE accounts SET savings=savings+?";
		$prepared_1 = $PDO->prepare($sql_1);
		$prepared_1->execute(array($input));
	}catch(PDOException $e){
		die("ERROR: Could not able to execute $sql. " . $e->getMessage());
	}
	try {
		$sql_2 = "UPDATE accounts SET checking=checking-?";
		$prepared_2 = $PDO->prepare($sql_2);
		$prepared_2->execute(array($input));
	}catch(PDOException $e){
		die("ERROR: Could not able to execute $sql. " . $e->getMessage());
	}
}

//checks if data was submitted to 'dc'(deposit checking)
if (!empty($_POST['dc']))
{

//runs add checking with the input value as the argument
addChecking($_POST['dc']);
}

//checks if data was submitted to 'ds'(deposit savings)
if (!empty($_POST['ds']))
{
	addSavings($_POST['ds']);
}

//checks if data was submitted to 'cs'(checking to savings)
if (!empty($_POST['cs']))
{
	transferSavings($_POST['cs']);
}

//checks if data was submitted to 'sc'(savings to checking)
if (!empty($_POST['sc']))
{
	transferChecking($_POST['sc']);
}

?>
<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset = "utf-8">
		<title> First Bank of HTML </title>
	</head>
	<body>
		<h1 style= font-weight:bold;> Welcome to the First Bank of HTML&#x2122;</h1>
	
		<p>Where all our clients are served!<br>
		<span style= background-color:yellow;> This web site is under construction, as is our bank!
		</span>
		</p>

		<h2 style= font-weight:bold>Services offered</h2>
		<ol>
			<li>Current account information <br>
			<?php getValues(); ?>
			</li>
			<li>
			<form method="POST" action="bank-2.php">
				<label style="float: left;">Deposit money into checking </label>
				<input type="text" id ="dc" name="dc">
				<input type="submit" value="submit">
			</form>
			</li>
			<li>	
			<form method="POST" action="bank-2.php">
				<label style="float: left;">Deposit money into savings </label>
				<input type="text" id="ds" name="ds">
				<input type="submit" value="submit">
			</form>
			</li>
			<li>
			<form method="POST" action="bank-2.php">
				<label style="float: left;">Transfer money from checking into savings </label>
				<input type="text" id="cs" name="cs">
				<input type="submit" value="submit">
			</form>
			</li>
			<li>
			<form method="POST" action="bank-2.php">
				<label style="float: left;">Transfer money from savings to checking </label>
				<input type="text" id="sc" name="sc">
				<input type="submit" value="submit">
			</form>
			</li>
			</ol>
	</body>
	</html>
	
