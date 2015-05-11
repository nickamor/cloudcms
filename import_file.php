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
	<?php 
	$s3object = s3client->getObject(array(
		'Bucket' => "dbapp-upploads",
		'Key' => "cities.tsv"));

	$fileRows = str_getcsv($s3object['body'], "\n");
	foreach ($fileRows as $row => $value) {
		echo "$row = " . $value;
	}

	?>
	</div>
</body>
</html>

