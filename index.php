<?php
/**----------------------------------------------------------------------------------
* Microsoft Developer & Platform Evangelism
*
* Copyright (c) Microsoft Corporation. All rights reserved.
*
* THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND, 
* EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE IMPLIED WARRANTIES 
* OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR PURPOSE.
*----------------------------------------------------------------------------------
* The example companies, organizations, products, domain names,
* e-mail addresses, logos, people, places, and events depicted
* herein are fictitious.  No association with any real company,
* organization, product, domain name, email address, logo, person,
* places, or events is intended or should be inferred.
*----------------------------------------------------------------------------------
**/

/** -------------------------------------------------------------
# Azure Storage Blob Sample - Demonstrate how to use the Blob Storage service. 
# Blob storage stores unstructured data such as text, binary data, documents or media files. 
# Blobs can be accessed from anywhere in the world via HTTP or HTTPS. 
#
# Documentation References: 
#  - Associated Article - https://docs.microsoft.com/en-us/azure/storage/blobs/storage-quickstart-blobs-php 
#  - What is a Storage Account - http://azure.microsoft.com/en-us/documentation/articles/storage-whatis-account/ 
#  - Getting Started with Blobs - https://azure.microsoft.com/en-us/documentation/articles/storage-php-how-to-use-blobs/
#  - Blob Service Concepts - http://msdn.microsoft.com/en-us/library/dd179376.aspx 
#  - Blob Service REST API - http://msdn.microsoft.com/en-us/library/dd135733.aspx 
#  - Blob Service PHP API - https://github.com/Azure/azure-storage-php
#  - Storage Emulator - http://azure.microsoft.com/en-us/documentation/articles/storage-use-emulator/ 
#
**/

require_once 'vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=".getenv('ACCOUNT_NAME').";AccountKey=".getenv('ACCOUNT_KEY');

// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);

$fileToUpload = "HalloBro.txt";
$ip_address = $_SERVER['REMOTE_ADDR'];
$containerName = "blockblobs". md5($ip_address);

$result = $blobClient->listContainers();
$containers = $result->getContainers();
$list_container_names = array_map(function ($container) {
    return $container->getName();
}, $containers);

// check container exist or not
if (!in_array($containerName, $list_container_names)) {

    try {
        // Create container options object.
        $createContainerOptions = new CreateContainerOptions();

        // Set public access policy. Possible values are
        // PublicAccessType::CONTAINER_AND_BLOBS and PublicAccessType::BLOBS_ONLY.
        // CONTAINER_AND_BLOBS:
        // Specifies full public read access for container and blob data.
        // proxys can enumerate blobs within the container via anonymous
        // request, but cannot enumerate containers within the storage account.
        //
        // BLOBS_ONLY:
        // Specifies public read access for blobs. Blob data within this
        // container can be read via anonymous request, but container data is not
        // available. proxys cannot enumerate blobs within the container via
        // anonymous request.
        // If this value is not specified in the request, container data is
        // private to the account owner.
        $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);

        // Set container metadata.
        $createContainerOptions->addMetaData("key1", "value1");
        $createContainerOptions->addMetaData("key2", "value2");

        // Create container.
        $blobClient->createContainer($containerName, $createContainerOptions);
    }
    catch(ServiceException $e){
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.":A ".$error_message."<br />";
    }
    catch(InvalidArgumentTypeException $e){
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.":B ".$error_message."<br />";
    }
}

// List blobs.
$listBlobsOptions = new ListBlobsOptions();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Galeri</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
</head>
<body>
    <!-- As a heading -->
    <nav class="navbar navbar-light bg-light">
      <span class="navbar-brand mb-0 h1"><strong>Dicoding</strong></span>
    </nav>

    <br>

    <div class="container">
        <h1>Galeri</h1>
        <div class="row">
            <div class="col-md-6">
                <form action="upload.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="">Pilih gambar </label>
                        <input type="file" name="image" accept="image/*" class="form-control-file">
                    </div>
                    <input type="submit" name="submit" class="btn btn-primary" value="Upload">
                </form>
            </div>
        </div>

        <br>
        
        <div class="row">
<?php
if (in_array($containerName, $list_container_names)) {
    do {
        $result = $blobClient->listBlobs($containerName);
        foreach ($result->getBlobs() as $key => $blob)
        {
?>
            <div class="col-md-6">
                <div class="card mb-6 shadow-sm" style="margin-bottom: 1.5rem !important;">
                <img id="img-<?php echo $key+1; ?>" src="<?php echo $blob->getUrl(); ?>" alt="" class="card-img-top">
                    <div class="card-body">
                        <p class="card-text"><?php echo $blob->getName(); ?></p>
                        <div id="desc-<?php echo $key+1; ?>" class="alert alert-success" role="alert" style="display: none;">
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary" onclick="processImage('<?php echo $key+1; ?>')">Analisa</button>
                        </div>
                    </div>
                </div>
            </div>
<?php
        }

        $listBlobsOptions->setContinuationToken($result->getContinuationToken());
    } while($result->getContinuationToken());
}
?>
        </div>
    </div>

    <br>
    
    <footer class="bd-footer">
        <div class="container">
            <p>@ 2019</p>
        </div>
    </footer>

<script type="text/javascript">
function processImage(id) {
    // **********************************************
    // *** Update or verify the following values. ***
    // **********************************************

    // Replace <Subscription Key> with your valid subscription key.
    var subscriptionKey = "728b687ed75e4b3c8b2dfeedfc277810";

    // You must use the same Azure region in your REST API method as you used to
    // get your subscription keys. For example, if you got your subscription keys
    // from the West US region, replace "westcentralus" in the URL
    // below with "westus".
    //
    // Free trial subscription keys are generated in the "westus" region.
    // If you use a free trial subscription key, you shouldn't need to change
    // this region.
    var uriBase = "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";
    //var uriBase = "https://southeastasia.api.cognitive.microsoft.com/";

    // Request parameters.
    var params = {
        "visualFeatures": "Categories,Description,Color",
        "details": "",
        "language": "en",
    };

    // Display the image.
    var sourceImageUrl = document.querySelector("#img-"+id).src;

    // Make the REST API call.
    $.ajax({
        url: uriBase + "?" + $.param(params),

        // Request headers.
        beforeSend: function(xhrObj){
            xhrObj.setRequestHeader("Content-Type","application/json");
            xhrObj.setRequestHeader(
            "Ocp-Apim-Subscription-Key", subscriptionKey);
        },

        type: "POST",

        // Request body.
        data: '{"url": ' + '"' + sourceImageUrl + '"}',
    })

    .done(function(data) {
        let captions = (data.description.captions).map(caption => caption.text);

        // Show formatted JSON on webpage.
        $("#desc-"+id).text(JSON.stringify(captions, null, 2));
        $("#desc-"+id).show();
    })

    .fail(function(jqXHR, textStatus, errorThrown) {
        // Display error message.
        var errorString = (errorThrown === "") ? "Error. " :
            errorThrown + " (" + jqXHR.status + "): ";
        errorString += (jqXHR.responseText === "") ? "" :
            jQuery.parseJSON(jqXHR.responseText).message;
        alert(errorString);
    });
};
</script>
</body>
</html>
