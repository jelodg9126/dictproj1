
document.addEventListener('DOMContentLoaded', function(){
    console.log('js loaded!')
    
    // Desktop sidebar elements
    const toggleBtn = document.querySelector('#toggleSidebar');
    const sidebar = document.querySelector('#sidebar');
    // Mobile sidebar elements
    const burgerBtn = document.querySelector('#burger');
    const mobileSidebar = document.querySelector('#mobileSidebar');
    const mobileSidebarContent = document.querySelector('#mobileSidebarContent');
    // const closeMobileSidebar = document.querySelector('#closeMobileSidebar');
    
    // Mobile dropdown elements
    const mobileDocumentsDropdown = document.querySelector('#mobileDocumentsDropdown');
    const mobileDocumentsDropdownMenu = document.querySelector('#mobileDocumentsDropdownMenu');
    
    console.log('Desktop elements:', { toggleBtn, sidebar });
    console.log('Mobile elements:', { burgerBtn, mobileSidebar, mobileSidebarContent, closeMobileSidebar });


    // Mobile sidebar functionality
    if (burgerBtn && mobileSidebar && mobileSidebarContent) {
        burgerBtn.addEventListener('click', function(){
            console.log("Mobile sidebar toggle working!")
            mobileSidebar.classList.remove('hidden');
            document.body.classList.add('sidebar-open');
            // Use setTimeout to ensure the element is visible before adding the transform class
            setTimeout(() => {
                mobileSidebarContent.classList.remove('-translate-x-full');
            }, 10);
        });
    }


    // Close mobile sidebar when clicking on overlay
    if (mobileSidebar && mobileSidebarContent) {
        mobileSidebar.addEventListener('click', function(e){
            if (e.target === mobileSidebar) {
                console.log("Closing mobile sidebar via overlay click");
                mobileSidebarContent.classList.add('-translate-x-full');
                document.body.classList.remove('sidebar-open');
                setTimeout(() => {
                    mobileSidebar.classList.add('hidden');
                }, 300);
            }
        });
    }

    // Mobile documents dropdown toggle
    if (mobileDocumentsDropdown && mobileDocumentsDropdownMenu) {
        mobileDocumentsDropdown.addEventListener('click', function(){
            console.log("Mobile documents dropdown toggle");
            const isHidden = mobileDocumentsDropdownMenu.classList.contains('hidden');
            
            if (isHidden) {
                mobileDocumentsDropdownMenu.classList.remove('hidden');
                // Rotate the arrow
                const arrow = mobileDocumentsDropdown.querySelector('svg');
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            } else {
                mobileDocumentsDropdownMenu.classList.add('hidden');
                // Reset the arrow
                const arrow = mobileDocumentsDropdown.querySelector('svg');
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
            }
        });
    }

    // Close mobile sidebar when clicking on navigation links
    const mobileNavLinks = mobileSidebar?.querySelectorAll('a[href]');
    if (mobileNavLinks) {
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', function(){
                // Only close if it's not a dropdown toggle
                if (!this.closest('#mobileDocumentsDropdown')) {
                    console.log("Closing mobile sidebar via nav link click");
                    mobileSidebarContent.classList.add('-translate-x-full');
                    document.body.classList.remove('sidebar-open');
                    setTimeout(() => {
                        mobileSidebar.classList.add('hidden');
                    }, 300);
                }
            });
        });
    }
});
