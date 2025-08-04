const floatBtn = document.getElementById('chatbot-float-btn');
const floatIframe = document.getElementById('chatbot-float-iframe');
const floatContainer = document.getElementById('chatbot-float-iframe-container');
floatBtn.onclick = function() {
  floatBtn.style.display = 'none';
  floatContainer.style.display = 'block';
};