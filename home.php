<html>
<head>
  <title>3D Hubs Order Page</title>
</head>
<body>
  <script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '793119057490756',
      xfbml      : true,
      version    : 'v2.6'
    });

    // ADD ADDITIONAL FACEBOOK CODE HERE
  };

  (function(d, s, id){
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {return;}
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
  </script>

  <?php

  $target_dir = "uploads/";
  $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
  $uploadOk = 1;
  $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
  // Check if image file is a actual image or fake image
  if(isset($_POST["submitForm"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
      echo "File is an image - " . $check["mime"] . ".";
      $uploadOk = 1;
    } else {
      echo "File is not an image.";
      $uploadOk = 0;
    }
  }
  // Check if file already exists
  if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
  }

  // Allow certain file formats
  if($imageFileType != "stl" && $imageFileType != "obj" && $imageFileType != "amf") {
    echo "Sorry, only STL, OBJ, AMF files are allowed.";
    $uploadOk = 0;
  }
  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
  } else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
      echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    } else {
      echo "Sorry, there was an error uploading your file.";
    }
  }


  if isset($_POST["submitForm"])
  {
    $settings = array(
      'consumer_key' => 'iLzN8DMu3LS8rG4HggGidoq6S7G23NuU',
      'consumer_secret' => 'W9X3fjzNVDyJAUdMkpQxmprqRFdXNGsn'
    );

    define('API_HOST', 'https://www.3dhubs.com');

    require( dirname(__FILE__) .'/../vendor/autoload.php');

    use GuzzleHttp\Client;
    use GuzzleHttp\Subscriber\Oauth\Oauth1;

    $client = new Client([
      'base_url' => API_HOST . '/api/v1/',
      'defaults' => ['auth' => 'oauth'],
    ]);

    $oauth = new Oauth1([
      'consumer_key'    => $settings['consumer_key'],
      'consumer_secret' => $settings['consumer_secret'],
    ]);

    $client->getEmitter()->attach($oauth);

    ////////////////////////////////////////////////////////////////////////////////
    // Send the models to the API.
    ////////////////////////////////////////////////////////////////////////////////

    // Define the file to be uploaded
    $files = array();
    if (is_dir($dir)){
      if ($dh = opendir($dir)){

        while (($file = readdir($dh)) !== false){
          echo "filename:" . $file . "<br>";
          //adds the file to files array
          array_push($files,$file);
        }
        closedir($dh);
      }
    }
    
    foreach($files as $filename => $quantity){
      $data = [
        'file' => base64_encode(file_get_contents(dirname(__FILE__) . '/' . $filename)),
        'fileName' => $filename,
      ];

      $request = $client->createRequest('POST', 'model');
      $postBody = $request->getBody();
      foreach($data as $name => $value){
        $postBody->setField($name, $value);
      }
      // Make the request to add a model.
      $res = $client->send($request);

      // Get the result.
      $result = $res->json();

      // Save the modelid
      $modelIds[$result['modelId']] = $quantity;

      echo 'Saved model number ' . $result['modelId'] . PHP_EOL;
    }

    ////////////////////////////////////////////////////////////////////////////////
    // Create the cart with the uploaded models.
    ////////////////////////////////////////////////////////////////////////////////
    $data = array();
    foreach($modelIds as $modelId => $quantity){
      $data['items[' . $modelId . '][modelId]'] = $modelId;
      $data['items[' . $modelId . '][quantity]'] = $quantity;
    }
    $request = $client->createRequest('POST', 'cart');

    $postBody = $request->getBody();
    foreach($data as $name => $value){
      $postBody->setField($name, $value);
    }

    //Make the request to add a model.
    $res = $client->send($request);

    // All done, output result.
    $result = $res->json();
    echo 'All done. Visit the url to claim the cart!' . PHP_EOL;
    echo $result['url'] . PHP_EOL;

  }

  echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" id="addCart">';
  ?>
  <label for="fileToUpload"><br/>
    <input type="file" name="fileToUpload" id="fileToUpload">

    <input type="submit" name="submitForm" value="Finalise Cart" />
    &nbsp;
    <input type="reset" value="Reset" />
  </form>
</body>
</html>
