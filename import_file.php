<?php
require 'aws/aws-autoloader.php'; 
use Aws\DynamoDb\DynamoDbClient;

$client = DynamoDbClient::factory(array(
	'region' => 'ap-southeast-2'
	));


use Aws\S3\S3Client;
$s3client = S3Client::factory();


?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Import File</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
</head>
<body>
	<div id="container">
	<h1>Import File</h1>
	<?php 

	//// READ FILE FROM S3 ////

	$s3object = $s3client->getObject(array(
		'Bucket' => "dbapp-uploads",
		'Key' => "cities.tsv"
		));

	$fileRows = explode("\n", $s3object['Body']);

	echo "<h2>Items</h2>";
	echo "<table>";
	echo "<tr><th>name</th><th>countrycode</th><th>district</th><th>population</th></tr\n";
	foreach ($fileRows as $row) {
		$value = str_getcsv($row, "\t");

		$parse = array(
			'name' => $value[1],
			'countrycode' => $value[2],
			'district' => $value[3],
			'population' => $value[4]);

		echo "<tr>";
		echo "<td>" . $parse['name'] . "</td>";
		echo "<td>" . $parse['countrycode'] . "</td>";
		echo "<td>" . $parse['district'] . "</td>";
		echo "<td>" . $parse['population'] . "</td>";
		echo "</tr>\n";
	}
	echo "</table>";

	//// ADD ITEMS TO TABLE ////
	

	?>
	</div>
</body>
</html>

