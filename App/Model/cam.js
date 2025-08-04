<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"/>
    let currentCamera = 'user'; // 'user' for front, 'environment' for rear

    function switchCamera() {
        // Turn off current camera
        Webcam.reset();
        
        // Toggle between front and rear camera
        currentCamera = currentCamera === 'user' ? 'environment' : 'user';
        
        // Set new camera constraints
        Webcam.set({
            width: 500,
            height: 375,
            image_format: 'jpeg',
            jpeg_quality: 100,
            constraints: {
                facingMode: currentCamera
            }
        });
        
        // Reattach to container with new settings
        Webcam.attach('#camera-container');
    }

    // Initial camera setup
    Webcam.set({
        width: 500,
        height: 375,
        image_format: 'jpeg',
        jpeg_quality: 100,
        constraints: {
            facingMode: currentCamera
        }
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