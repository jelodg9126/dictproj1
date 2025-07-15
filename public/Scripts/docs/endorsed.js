// Endorsed Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.view-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var rowData = btn.getAttribute('data-row');
            var data = rowData ? JSON.parse(rowData) : {};
            document.getElementById('detailsOfficeName').textContent = data.officeName || '';
            document.getElementById('detailsSenderName').textContent = data.senderName || '';
            document.getElementById('detailsDateReceived').textContent = data.dateAndTime || '';
            document.getElementById('detailsReceivedBy').textContent = data.receivedBy || '';
            document.getElementById('detailsDocumentTitle').value = data.doctitle || '';
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