<!DOCTYPE html>
<html>
<head>
  <title>Image Capture</title>
  <style>
    /* Add some basic styling to make the UI look decent */
    body {
      font-family: Arial, sans-serif;
      text-align: center;
    }
    
    #camera-container {
      width: 500px;
      height: 375px;
      border: 1px solid #ccc;
      margin: 20px auto;
    }
    
    .button {
      padding: 10px 20px;
      font-size: 16px;
      cursor: pointer;
    }
    
    .button:hover {
      background-color: #eee;
    }
  </style>
</head>
<body>
  <h1>Image Capture</h1>
  
  <div id="camera-container"></div>
  <button class="button" onclick="captureImage()">Capture Image</button>
  <!-- <button class="button" onclick="resetCamera()">Reset Camera</button> -->
  <br><img id="imgElem"/>
  <br>
  <!-- <button class="button" onclick="sendImage()">Submit Image</button> -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
  <!-- <script src="webcam.min.js"></script> -->
  <script>
    Webcam.set({
      width: 500,
      height: 375,
      image_format: 'jpeg',
      jpeg_quality: 100,
    });
    
    Webcam.attach('#camera-container');
    
    function captureImage() {
      Webcam.snap((data_uri) => {
        console.log(data_uri);
        imgElem.setAttribute('src',data_uri);
        
        // data_uri = data_uri.replace('data:image/jpeg;base64,', '');
      });
    }
    
    // function resetCamera() {
    //   Webcam.reset();
    //   Webcam.attach('#camera-container');

    // }
  </script>
</body>
</html>