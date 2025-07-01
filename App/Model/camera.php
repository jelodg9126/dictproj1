<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Camera</title>
</head>
<body>
  <video id="video"></video>
  <button id="snap">Take Snapshot</button>
  <script>
    const video = document.getElementById('video');
    navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => {
    video.srcObject = stream;
  })
  .catch(err => console.error("Error:", err));
  
  document.getElementById('snap').addEventListener('click', () => {
  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(video, 0, 0);
  const dataURL = canvas.toDataURL();
  
  // sending the captured image to process_form.php
  const formData = new FormData();
  formData.append('image', dataURL);
  fetch('process_form.php', { method: 'POST', body: formData })
    .then(response => response.text())
    .then(data => console.log(data));
});
  </script>
</body>
</html>