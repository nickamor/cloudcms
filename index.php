<?php
require 'aws/aws-autoloader.php'; 
use Aws\DynamoDb\DynamoDbClient;

$client = DynamoDbClient::factory(array(
	'region' => 'ap-southeast-2'
	));
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>List Tables</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
</head>
<body>
	<div id="container">
	<h1>List Tables</h1>
	<?php 
	$tablesIter = $client->getIterator('ListTables');

	echo "<table>";
	echo "<tr><th>Name</th></tr>";
	echo "<tr>"
	foreach ($tablesIter as $tableName) {
		echo "<td><a href='table.php?name=" . $tableName . "'>" . $tableName . "</a><td>";
	}
	echo "</tr>"
	echo "</table>";

	?>
	</div>
</body>
</html>

