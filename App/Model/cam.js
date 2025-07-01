<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"/>
    Webcam.set({
      width: 500,
      height: 375,
      image_format: 'jpeg',
      jpeg_quality: 100,
    });
    
    // attach to container to preview camera stream
    Webcam.attach('#camera-container');
    
    // function captureImage on button onClick
    function captureImage() {
      Webcam.snap((data_uri) => {
        console.log(data_uri);
        imgElem.setAttribute('src',data_uri);
      });
    }
    