 document.addEventListener('DOMContentLoaded', function() {
            const dropdownButton = document.getElementById('documentsDropdown');
            const dropdownMenu = document.getElementById('documentsDropdownMenu');
            const dropdownArrow = dropdownButton.querySelector('svg:last-child');

            if (dropdownButton && dropdownMenu) {
                dropdownButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isOpen = !dropdownMenu.classList.contains('hidden');

                    if (isOpen) {
                        dropdownMenu.classList.add('hidden');
                        dropdownArrow.style.transform = 'rotate(0deg)';
                    } else {
                        dropdownMenu.classList.remove('hidden');
                        dropdownArrow.style.transform = 'rotate(180deg)';
                    }
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.add('hidden');
                        dropdownArrow.style.transform = 'rotate(0deg)';
                    }
                });

                // Keep dropdown open if current page is incoming, outgoing, received, or endorsed
                const currentPage = '<?php echo $currentPage; ?>';
                if (['incoming', 'outgoing', 'received', 'endorsed'].includes(currentPage)) {
                    dropdownMenu.classList.remove('hidden');
                    dropdownArrow.style.transform = 'rotate(180deg)';
                }
            }
        });