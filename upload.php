<?php

require_once 'vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=".getenv('ACCOUNT_NAME').";AccountKey=".getenv('ACCOUNT_KEY');

// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);
$ip_address = $_SERVER['REMOTE_ADDR'];
$containerName = "blockblobs". md5($ip_address);

if (empty($_POST)) die();
if (empty($_FILES)) die();
if (empty($_FILES['image'])) die();

$fileToUpload = $_FILES['image']['tmp_name'];
$filename = $_FILES['image']['name'];

$content = fopen($fileToUpload, "r") or die("Unable to open file!");

//Upload blob
$createBlobOptions = new CreateBlockBlobOptions();
$createBlobOptions->setContentType($_FILES['image']['type']);
$createBlobOptions->setIsStreaming(true);

$blobClient->createBlockBlob($containerName, $filename, $content, $createBlobOptions);

header('Location: index.php');
