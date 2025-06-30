

document.addEventListener('DOMContentLoaded', function(){

     const input = document.querySelector('#password') ;
     const icon = document.querySelector('#eye');
     const icon2 = document.querySelector('#eye2');

     icon.addEventListener('click', function(){
         
          if(input.type === 'password'){

               input.type= 'text'
              

          } else{
             
                 input.type ='password'
              
              
          }





     })







})