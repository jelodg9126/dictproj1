// Outgoing Page JavaScript

// Mapping for office codes to display names
const officeDisplayNames = {
    'dictbulacan': 'Provincial Office Bulacan',
    'dictaurora': 'Provincial Office Aurora',
    'dictbataan': 'Provincial Office Bataan',
    'dictpampanga': 'Provincial Office Pampanga',
    'dictPampanga': 'Provincial Office Pampanga',
    'dicttarlac': 'Provincial Office Tarlac',
    'dictzambales': 'Provincial Office Zambales',
    'dictothers': 'Provincial Office Others',
    'dictNE': 'Provincial Office Nueva Ecija',
    'dictne': 'Provincial Office Nueva Ecija',
    'dictNUEVAECIJA': 'Provincial Office Nueva Ecija',
    'maindoc': 'DICT Region 3 Office',
    'Rdictpampanga': 'Provincial Office Pampanga',
    'RdictPampanga': 'Provincial Office Pampanga',
    'RdictTarlac': 'Provincial Office Tarlac',
    'RdictBataan': 'Provincial Office Bataan',
    'RdictBulacan': 'Provincial Office Bulacan',
    'RdictAurora': 'Provincial Office Aurora',
    'RdictZambales': 'Provincial Office Zambales',
    'RdictNuevaEcija': 'Provincial Office Nueva Ecija',
    'RdictNE': 'Provincial Office Nueva Ecija',
    'Rmaindoc': 'DICT Region 3 Office',
    // Add more as you encounter new codes!
};

function getOfficeDisplayName(code) {
    if (!code) return '';
    var lower = code.toLowerCase();
    for (var key in officeDisplayNames) {
        if (key.toLowerCase() === lower) return officeDisplayNames[key];
    }
    return code;
}

// Debounce function for search input
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Initialize all event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeViewButtons();
    initializePODPreview();
    initializeModalCloseHandlers();
    initializeSignatureLightboxes();
    initializePODLightboxes();
    initializeFilterForm();
    initializeFilterToggle();
    initializeFormModal();
});

// Initialize view button functionality
function initializeViewButtons() {
    document.querySelectorAll('.view-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var rowData = btn.getAttribute('data-row');
            if (!rowData) return;
            
            var data = JSON.parse(rowData);
            populateDetailsModal(data);
            document.getElementById('detailsModal').style.display = 'flex';
        });
    });
}

// Populate details modal with data
function populateDetailsModal(data) {
    var displayOffice = getOfficeDisplayName(data.officeName);
    var displayReceivingOffice = getOfficeDisplayName(data.addressTo);
    
    document.getElementById('detailsOfficeName').value = displayOffice;
    document.getElementById('detailsSenderName').value = data.senderName || '';
    document.getElementById('detailsEmailAdd').value = data.emailAdd || '';
    document.getElementById('detailsStatus').value = data.status || '';
    
    var dateTimeInput = document.getElementById('detailsDateAndTime');
    var dateTimeLabel = document.getElementById('detailsDateAndTimeLabel');
    if (data.status && data.status.toLowerCase() === 'received' && data.dateReceived) {
        dateTimeInput.value = data.dateReceived ? new Date(data.dateReceived).toLocaleString() : '';
        dateTimeLabel.textContent = 'Date & Time Received';
    } else {
        dateTimeInput.value = data.dateAndTime ? new Date(data.dateAndTime).toLocaleString() : '';
        dateTimeLabel.textContent = 'Date & Time Created';
    }
    
    document.getElementById('detailsDocumentTitle').value = data.doctitle || '';
    document.getElementById('detailsDestinationOffice').value = getOfficeDisplayName(data.addressTo) || '';
    
    // Handle sender signature
    var senderSig = document.getElementById('detailsSenderSignature');
    if (data.transactionID) {
        senderSig.src = '/dictproj1/modules/get_signature.php?id=' + data.transactionID;
        senderSig.style.display = 'inline';
    } else {
        senderSig.src = '';
        senderSig.style.display = 'none';
    }
    
    // Handle sender POD
    var senderPodImg = document.getElementById('detailsSenderPod');
    var senderPodNoImage = document.getElementById('senderPodNoImage');
    if (data.pod && data.transactionID) {
        senderPodImg.src = '/dictproj1/modules/get_pod.php?id=' + data.transactionID;
        senderPodImg.style.display = 'inline';
        senderPodNoImage.style.display = 'none';
    } else {
        senderPodImg.src = '';
        senderPodImg.style.display = 'none';
        senderPodNoImage.style.display = 'inline';
    }
    
    // Handle receiver name
    var receiverNameInput = document.getElementById('detailsReceiverName');
    receiverNameInput.value = data.receivedBy || '';
    if (!data.receivedBy) receiverNameInput.placeholder = 'No receiver';
    
    // Handle receiver signature
    var receiverSig = document.getElementById('detailsReceiverSignature');
    var receiverSigNoImage = document.getElementById('receiverSignatureNoImage');
    if (data.transactionID) {
        receiverSig.src = '/dictproj1/modules/get_signature.php?id=' + data.transactionID + '&type=receiver';
        receiverSig.style.display = 'inline';
        receiverSigNoImage.style.display = 'none';
        receiverSig.onerror = function() {
            receiverSig.style.display = 'none';
            receiverSigNoImage.style.display = 'inline';
        };
    } else {
        receiverSig.src = '';
        receiverSig.style.display = 'none';
        receiverSigNoImage.style.display = 'inline';
    }
    
    // Handle receiver POD
    var receiverPodImg = document.getElementById('detailsReceiverPod');
    var receiverPodNoImage = document.getElementById('receiverPodNoImage');
    if (data.transactionID) {
        receiverPodImg.src = '/dictproj1/modules/get_receiver_pod.php?id=' + data.transactionID;
        receiverPodImg.style.display = 'inline';
        receiverPodNoImage.style.display = 'none';
        receiverPodImg.onerror = function() {
            receiverPodImg.style.display = 'none';
            receiverPodNoImage.style.display = 'inline';
        };
    } else {
        receiverPodImg.src = '';
        receiverPodImg.style.display = 'none';
        receiverPodNoImage.style.display = 'inline';
    }
}

// Initialize POD preview functionality
function initializePODPreview() {
    var tableBody = document.querySelector('tbody');
    if (tableBody) {
        tableBody.addEventListener('click', function(e) {
            var btn = e.target.closest('.preview-pod-btn');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                var id = btn.getAttribute('data-id');
                var modal = document.getElementById('podPreviewModal');
                var img = document.getElementById('podPreviewImg');
                img.src = '/dictproj1/modules/get_pod.php?id=' + id;
                modal.style.display = 'flex';
                return;
            }
        });
    }
}

// Initialize modal close handlers
function initializeModalCloseHandlers() {
    // POD preview modal close
    var closePodPreviewModal = document.getElementById('closePodPreviewModal');
    if (closePodPreviewModal) {
        closePodPreviewModal.onclick = function() {
            document.getElementById('podPreviewModal').style.display = 'none';
            document.getElementById('podPreviewImg').src = '';
        };
    }
    
    var podPreviewModal = document.getElementById('podPreviewModal');
    if (podPreviewModal) {
        podPreviewModal.onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.getElementById('podPreviewImg').src = '';
            }
        };
    }
    
    // Details modal close
    var closeDetailsModal = document.getElementById('closeDetailsModal');
    if (closeDetailsModal) {
        closeDetailsModal.onclick = function() {
            document.getElementById('detailsModal').style.display = 'none';
        };
    }
    
    var detailsModal = document.getElementById('detailsModal');
    if (detailsModal) {
        detailsModal.onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        };
    }
    
    // Form modal close
    document.querySelectorAll('#formModal .close').forEach(function(closeBtn) {
        closeBtn.onclick = function() {
            document.getElementById('formModal').style.display = 'none';
        };
    });
}

// Initialize signature lightboxes
function initializeSignatureLightboxes() {
    // Sender signature lightbox
    var senderSig = document.getElementById('detailsSenderSignature');
    if (senderSig) {
        senderSig.style.cursor = 'pointer';
        senderSig.onclick = function() {
            if (!senderSig.src || senderSig.style.display === 'none') return;
            var enlarged = document.getElementById('enlargedSignature');
            enlarged.src = senderSig.src;
            var lightbox = document.getElementById('signatureLightbox');
            lightbox.style.display = 'flex';
            lightbox.style.opacity = 0;
            setTimeout(() => { lightbox.style.opacity = 1; }, 10);
        };
    }
    
    // Receiver signature lightbox
    var receiverSig = document.getElementById('detailsReceiverSignature');
    if (receiverSig) {
        receiverSig.style.cursor = 'pointer';
        receiverSig.onclick = function() {
            if (!receiverSig.src || receiverSig.style.display === 'none') return;
            var enlarged = document.getElementById('enlargedSignature');
            enlarged.src = receiverSig.src;
            var lightbox = document.getElementById('signatureLightbox');
            lightbox.style.display = 'flex';
            lightbox.style.opacity = 0;
            setTimeout(() => { lightbox.style.opacity = 1; }, 10);
        };
    }
    
    // Signature lightbox close
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
}

// Initialize POD lightboxes
function initializePODLightboxes() {
    // Sender POD lightbox
    var senderPodImg = document.getElementById('detailsSenderPod');
    if (senderPodImg) {
        senderPodImg.style.cursor = 'pointer';
        senderPodImg.onclick = function() {
            if (!senderPodImg.src || senderPodImg.style.display === 'none') return;
            var enlarged = document.getElementById('enlargedPod');
            enlarged.src = senderPodImg.src;
            var lightbox = document.getElementById('podLightbox');
            lightbox.style.display = 'flex';
            lightbox.style.opacity = 0;
            setTimeout(() => { lightbox.style.opacity = 1; }, 10);
        };
    }
    
    // Receiver POD lightbox
    var receiverPodImg = document.getElementById('detailsReceiverPod');
    if (receiverPodImg) {
        receiverPodImg.style.cursor = 'pointer';
        receiverPodImg.onclick = function() {
            if (!receiverPodImg.src || receiverPodImg.style.display === 'none') return;
            var enlarged = document.getElementById('enlargedPod');
            enlarged.src = receiverPodImg.src;
            var lightbox = document.getElementById('podLightbox');
            lightbox.style.display = 'flex';
            lightbox.style.opacity = 0;
            setTimeout(() => { lightbox.style.opacity = 1; }, 10);
        };
    }
    
    // POD lightbox close
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
}

// Initialize filter form functionality
function initializeFilterForm() {
    var filterForm = document.querySelector('#filterSection form');
    if (filterForm) {
        // For text input (search)
        var searchInput = filterForm.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', debounce(function() {
                filterForm.submit();
            }, 400));
        }
        
        // For dropdowns and other filter fields
        filterForm.querySelectorAll('.filter-input').forEach(function(input) {
            if (input !== searchInput) {
                input.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
        });
    }
}

// Initialize filter toggle functionality
function initializeFilterToggle() {
    var filterSection = document.getElementById('filterSection');
    if (filterSection) {
        // Check if filter panel was previously opened (stored in sessionStorage)
        var wasFilterPanelOpen = sessionStorage.getItem('filterPanelOpen') === 'true';
        
        var hasActiveFilter = false;
        filterSection.querySelectorAll('.filter-input').forEach(function(input) {
            if (input.value && input.value !== '') {
                hasActiveFilter = true;
            }
        });
        
        // Keep panel open if there are active filters OR if it was previously opened
        if (hasActiveFilter || wasFilterPanelOpen) {
            filterSection.style.display = 'block';
            var filterToggleText = document.getElementById('filterToggleText');
            if (filterToggleText) filterToggleText.textContent = 'Hide Filters';
        }
    }
    
    // Filter toggle button
    var filterToggle = document.getElementById('filterToggle');
    var filterToggleText = document.getElementById('filterToggleText');
    if (filterToggle && filterSection) {
        filterToggle.addEventListener('click', function() {
            var isVisible = filterSection.style.display !== 'none';
            filterSection.style.display = isVisible ? 'none' : 'block';
            
            // Store the panel state in sessionStorage
            if (isVisible) {
                // Panel is being closed
                sessionStorage.setItem('filterPanelOpen', 'false');
            } else {
                // Panel is being opened
                sessionStorage.setItem('filterPanelOpen', 'true');
            }
            
            if (filterToggleText) {
                filterToggleText.textContent = isVisible ? 'Show Filters' : 'Hide Filters';
            }
        });
    }
}

// Initialize form modal functionality
function initializeFormModal() {
    // Form modal functionality is handled by modal.js
    // This function can be extended if needed
} 