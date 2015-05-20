<?php
require 'aws/aws-autoloader.php';
use Aws\DynamoDb\DynamoDbClient;

$client = DynamoDbClient::factory ( array (
		'region' => 'ap-southeast-2' 
) );

$pageTitle = "View Table";
$tableName = $_GET ['name'];

if ($tableName) {
	$pageTitle = $pageTitle . " '" . $tableName . "'";
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?php echo $pageTitle;?></title>
<link rel="stylesheet"
	href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
</head>
<body>
	<div id="container">
		<h1>View Table</h1>
	<?php
	
	if ($tableName) {
		try {
			$describeTable = $client->describeTable ( array (
					'TableName' => $tableName 
			) );
			
			echo "<p>Viewing table '" . $tableName . "'.\n</p>\n";
			
			$itemsIter = $client->getIterator ( 'Scan', array (
					'TableName' => $tableName 
			) );
			
			echo "<table>";
			foreach ( $itemsIter as $item ) {
				// echo "<tr>". $item . "</tr>";
				print_r ( $item );
			}
			echo "</table>\n";
		} catch ( Exception $e ) {
			echo "No table by the name '" . $tableName . "'!";
		}
	} else {
		echo "<p>No table name supplied.</p>\n";
	}
	
	?>
	</div>
</body>
</html>