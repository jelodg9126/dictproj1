
<html>

    <head>

        <meta http-equiv="content-type" content="text/html; charset=utf-8" />

        <title>Camera app by Code-Arc</title>

        

    </head>

    <body>

    <div class="camera">

      <video id="video"></video>

      <canvas id="canvas"></canvas>

      <div class="controls">

        <button id="capture-button">Capture</button>

        <button id="gallery-button">Submit Image</button>

        <button id="switch-camera-button">Switch Camera</button>
      </div>

      <div id="gallery"></div>

    </div>
    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const captureButton = document.getElementById('capture-button');
        const galleryButton = document.getElementById('gallery-button');
        const gallery = document.getElementById('gallery');
        const switchCameraButton = document.getElementById('switch-camera-button');

        let galleryImages = "";
        let facingMode = 'enviroment';

// Access the user's webcam

function startCamera() {
  
  navigator.mediaDevices.getUserMedia({ video: { facingMode: facingMode} })
    .then(stream => {
      
      video.srcObject = stream;

      video.play();

    })

    .catch(error => {

      console.error('Error accessing the webcam:', error);

    });

}

// Switch between the front and rear camera

switchCameraButton.addEventListener('click', () => {

  facingMode = facingMode === 'user' ? 'environment' : 'user';

  video.pause();

  video.srcObject.getTracks().forEach(track => {

    track.stop();

  });

  startCamera();

});

// Take a photo when the capture button is clicked

captureButton.addEventListener('click', () => {

  // Set the canvas dimensions to match the video element
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  // Draw the video frame onto the canvas
  const context = canvas.getContext('2d');
  context.drawImage(video, 0, 0, canvas.width, canvas.height);
  // Convert the canvas image to a data URL and store it in the galleryImages array
  const dataURL = canvas.toDataURL();
  galleryImages.push(dataURL);

});

// Save all the captured images to the gallery button

galleryButton.addEventListener('click', () => {

  // Clear the existing gallery

  gallery.innerHTML = '';

  if (galleryImages.length > 0) {

    // Display all the images in the gallery

    galleryImages.forEach(image => {

      const img = document.createElement('img');

      img.src = image;

      gallery.appendChild(img);

      
    });

  }

});

// Start the camera on page loading 

startCamera();


    </script>
    </body>

</html>