<?php
require 'aws/aws-autoloader.php'; 
use Aws\DynamoDb\DynamoDbClient;

$client = DynamoDbClient::factory(array(
	'region' => 'ap-southeast-2'
	));

$pageTitle = "View Table";
$tableName = _GET['table'];

if ($tableName)
{
	$pageTitle = $pageTitle . " " . $tableName;
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?php echo $pageTitle;?></title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
</head>
<body>
	<div id="container">
	<?php 

	if ($tableName)
	{
		$itemsIter = $client->getIterator('Scan', array(
			'TableName' => $tableName));

		echo "<table>";
		foreach ($itemsIter as $item)
		{
			echo "<tr>" "</tr>";
		}
		echo "</table>";
	}
	else
	{
		echo "No table by the name " . $tableName . "!";
	}

	?>
	</div>
</body>
</html>