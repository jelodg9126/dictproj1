document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let totalPages = 1;
    let refreshInterval;

    // Function to render pagination controls
    function renderPagination() {
        const paginationContainer = document.getElementById('paginationContainer');
        if (!paginationContainer) return;
        
        paginationContainer.innerHTML = '';
        
        if (totalPages <= 1) return; // Don't show pagination if only one page
        
        // Create pagination HTML
        let paginationHTML = '';
        
        // First/Previous buttons
        if (currentPage > 1) {
            paginationHTML += `
                <button onclick="changePage(1)" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <span class="sr-only">First</span>
                    &laquo;
                </button>
                <button onclick="changePage(${currentPage - 1})" class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <span class="sr-only">Previous</span>
                    &lsaquo;
                </button>`;
        }
        
        // Page numbers
        let start = Math.max(1, currentPage - 2);
        let end = Math.min(totalPages, start + 4);
        start = Math.max(1, end - 4);
        
        for (let i = start; i <= end; i++) {
            const isActive = i === currentPage;
            const isFirst = i === 1 && currentPage === 1;
            const isLast = i === totalPages && currentPage === totalPages;
            
            let classes = [
                'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                isActive ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50',
                isFirst ? 'rounded-l-md' : '',
                (i === totalPages && currentPage === totalPages) ? 'rounded-r-md' : ''
            ].filter(Boolean).join(' ');
            
            paginationHTML += `
                <button onclick="changePage(${i})" class="${classes}" ${isActive ? 'aria-current="page"' : ''}>
                    ${i}
                </button>`;
        }
        
        // Next/Last buttons
        if (currentPage < totalPages) {
            paginationHTML += `
                <button onclick="changePage(${currentPage + 1})" class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <span class="sr-only">Next</span>
                    &rsaquo;
                </button>
                <button onclick="changePage(${totalPages})" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <span class="sr-only">Last</span>
                    &raquo;
                </button>`;
        }
        
        paginationContainer.innerHTML = paginationHTML;
    }

    // Function to change page
    window.changePage = function(newPage) {
        if (newPage < 1 || newPage > totalPages) return;
        currentPage = newPage;
        fetchLogHistory();
    };

    // Function to render log rows
    function renderLogRows(data, pagination) {
        const tbody = document.querySelector('table.w-full tbody');
        if (!tbody) return;
        tbody.innerHTML = '';
        
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-gray-500">No log history records found.</td></tr>';
            return;
        }
        
        data.forEach(function(row) {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.name || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.office || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.login_time ? new Date(row.login_time).toLocaleString() : '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.logout_time ? new Date(row.logout_time).toLocaleString() : '-'}</td>
            `;
            tbody.appendChild(tr);
        });
        
        // Update pagination info
        if (pagination) {
            totalPages = pagination.total_pages;
            renderPagination();
        }
    }

    // Function to fetch log history with pagination
    function fetchLogHistory() {
        fetch(`/dictproj1/modules/get_user_log_history.php?page=${currentPage}`)
            .then(response => response.json())
            .then(json => {
                renderLogRows(json.data || [], json.pagination);
            })
            .catch(error => {
                console.error('Error fetching log history:', error);
            });
    }
    
    // Initial fetch
    fetchLogHistory();
    
    // Set up auto-refresh
    refreshInterval = setInterval(fetchLogHistory, 3000);
    
    // Clean up interval when page is unloaded
    window.addEventListener('beforeunload', () => {
        if (refreshInterval) clearInterval(refreshInterval);
    });

    document.querySelectorAll('.view-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var rowData = btn.getAttribute('data-row');
            var data = rowData ? JSON.parse(rowData) : {};
            document.getElementById('detailsOfficeName').textContent = data.officeName || '';
            document.getElementById('detailsSenderName').textContent = data.senderName || '';
            document.getElementById('detailsDateReceived').textContent = data.dateAndTime || '';
            document.getElementById('detailsReceivedBy').textContent = data.receivedBy || '';
            var transactionID = data.transactionID;
            document.getElementById('detailsSignature').src = '/dictproj1/modules/get_signature.php?id=' + transactionID;
            document.getElementById('detailsPod').src = '/dictproj1/modules/get_pod.php?id=' + transactionID;
            document.getElementById('detailsEndorsedToName').textContent = data.endorsedToName || '';
            // Format and display endorsement timestamp
            var endorsementTimestamp = data.endorsementTimestamp || '';
            if (endorsementTimestamp) {
                var date = new Date(endorsementTimestamp);
                var formattedDate = date.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                document.getElementById('detailsEndorsementTimestamp').textContent = formattedDate;
            } else {
                document.getElementById('detailsEndorsementTimestamp').textContent = 'Not available';
            }
            if (data.hasEndorsedSignature) {
                document.getElementById('detailsEndorsedSignature').src = '/dictproj1/modules/get_endorsed_signature.php?id=' + transactionID;
            } else {
                document.getElementById('detailsEndorsedSignature').src = '';
            }
            if (data.hasEndorsedDocProof) {
                document.getElementById('detailsEndorsedDocProof').src = '/dictproj1/modules/get_endorsed_doc_proof.php?id=' + transactionID;
            } else {
                document.getElementById('detailsEndorsedDocProof').src = '';
            }
            document.getElementById('receivedDetailsModal').style.display = 'flex';
        });
    });
    document.getElementById('closeReceivedDetailsModal').onclick = function() {
        document.getElementById('receivedDetailsModal').style.display = 'none';
    };
    document.getElementById('receivedDetailsModal').onclick = function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    };
    
    // Signature lightbox functionality
    var signatureImg = document.getElementById('detailsSignature');
    if (signatureImg) {
        signatureImg.onclick = function() {
            if (!signatureImg.src || signatureImg.style.display === 'none') return;
            var enlarged = document.getElementById('enlargedSignature');
            enlarged.src = signatureImg.src;
            var lightbox = document.getElementById('signatureLightbox');
            lightbox.style.display = 'flex';
            lightbox.style.opacity = 0;
            setTimeout(() => { lightbox.style.opacity = 1; }, 10);
        };
    }
    var signatureLightbox = document.getElementById('signatureLightbox');
    if (signatureLightbox) {
        signatureLightbox.onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.getElementById('enlargedSignature').src = '';
            }
        };
    }
    var enlargedSignature = document.getElementById('enlargedSignature');
    if (enlargedSignature) {
        enlargedSignature.onclick = function(e) {
            e.stopPropagation();
        };
    }
    
    // POD lightbox functionality
    var podImg = document.getElementById('detailsPod');
    if (podImg) {
        podImg.onclick = function() {
            if (!podImg.src || podImg.style.display === 'none') return;
            var enlarged = document.getElementById('enlargedPod');
            enlarged.src = podImg.src;
            var lightbox = document.getElementById('podLightbox');
            lightbox.style.display = 'flex';
            lightbox.style.opacity = 0;
            setTimeout(() => { lightbox.style.opacity = 1; }, 10);
        };
    }
    var podLightbox = document.getElementById('podLightbox');
    if (podLightbox) {
        podLightbox.onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.getElementById('enlargedPod').src = '';
            }
        };
    }
    var enlargedPod = document.getElementById('enlargedPod');
    if (enlargedPod) {
        enlargedPod.onclick = function(e) {
            e.stopPropagation();
        };
    }
    
    // Endorsed Signature lightbox functionality
    var endorsedSignatureImg = document.getElementById('detailsEndorsedSignature');
    if (endorsedSignatureImg) {
        endorsedSignatureImg.onclick = function() {
            if (!endorsedSignatureImg.src || endorsedSignatureImg.style.display === 'none') return;
            var enlarged = document.getElementById('enlargedEndorsedSignature');
            enlarged.src = endorsedSignatureImg.src;
            var lightbox = document.getElementById('endorsedSignatureLightbox');
            lightbox.style.display = 'flex';
            lightbox.style.opacity = 0;
            setTimeout(() => { lightbox.style.opacity = 1; }, 10);
        };
    }
    var endorsedSignatureLightbox = document.getElementById('endorsedSignatureLightbox');
    if (endorsedSignatureLightbox) {
        endorsedSignatureLightbox.onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.getElementById('enlargedEndorsedSignature').src = '';
            }
        };
    }
    var enlargedEndorsedSignature = document.getElementById('enlargedEndorsedSignature');
    if (enlargedEndorsedSignature) {
        enlargedEndorsedSignature.onclick = function(e) {
            e.stopPropagation();
        };
    }
    
    // Endorsed Document Proof lightbox functionality
    var endorsedDocProofImg = document.getElementById('detailsEndorsedDocProof');
    if (endorsedDocProofImg) {
        endorsedDocProofImg.onclick = function() {
            if (!endorsedDocProofImg.src || endorsedDocProofImg.style.display === 'none') return;
            var enlarged = document.getElementById('enlargedEndorsedDocProof');
            enlarged.src = endorsedDocProofImg.src;
            var lightbox = document.getElementById('endorsedDocProofLightbox');
            lightbox.style.display = 'flex';
            lightbox.style.opacity = 0;
            setTimeout(() => { lightbox.style.opacity = 1; }, 10);
        };
    }
    var endorsedDocProofLightbox = document.getElementById('endorsedDocProofLightbox');
    if (endorsedDocProofLightbox) {
        endorsedDocProofLightbox.onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.getElementById('enlargedEndorsedDocProof').src = '';
            }
        };
    }
    var enlargedEndorsedDocProof = document.getElementById('enlargedEndorsedDocProof');
    if (enlargedEndorsedDocProof) {
        enlargedEndorsedDocProof.onclick = function(e) {
            e.stopPropagation();
        };
    }
}); 