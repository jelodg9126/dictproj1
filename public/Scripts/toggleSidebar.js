
document.addEventListener('DOMContentLoaded', function(){
    console.log('js loaded!')
   const menu = document.getElementById('burger');
   const sidebar = document.getElementById('sidebar')

   menu.addEventListener('click', function(){
      console.log("working!")
      sidebar.style.display = 'flex'

   })

})
