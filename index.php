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
	<title></title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
</head>
<body>
	<div id="container">
	<?php 
	echo "Hello World!\n";

	$result = $client->listTables();

	echo "Found " . $result['TableNames'].length() . " tables.\n";

	foreach ($result['TableNames'] as $tableName) {
		echo $tableName . "\n";
	}
	?>
	</div>
</body>
</html>

