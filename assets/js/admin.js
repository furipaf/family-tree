// Admin Dashboard JavaScript

let members = [];
let editingId = null;

// Rebuild Gallery Function
function rebuildGallery() {
    if (!confirm('This will scan all gallery folders and rebuild the gallery data. Continue?')) {
        return;
    }
    
    const btn = document.querySelector('button[onclick="rebuildGallery()"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '🔄 Rebuilding...';
    btn.disabled = true;
    
    fetch('rebuild-gallery.php')
        .then(res => res.text())
        .then(text => {
            alert('Gallery rebuild complete!\n\n' + text.substring(0, 500));
            btn.innerHTML = originalText;
            btn.disabled = false;
        })
        .catch(err => {
            alert('Error rebuilding gallery: ' + err.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
}

// Modal Functions
function openModal(memberId = null) {
    const modal = document.getElementById('memberModal');
    const form = document.getElementById('memberForm');
    const title = document.getElementById('modalTitle');
    
    editingId = memberId;
    form.reset();
    document.getElementById('photoPreview').innerHTML = '';
    document.getElementById('photoPreview').classList.remove('active');
    document.getElementById('photoPath').value = '';
    
    if (memberId) {
        title.textContent = 'Edit Family Member';
        loadMemberData(memberId);
    } else {
        title.textContent = 'Add Family Member';
        document.getElementById('memberId').value = '';
        document.getElementById('createdAt').value = '';
    }
    
    modal.classList.add('active');
}

function closeModal() {
    const modal = document.getElementById('memberModal');
    modal.classList.remove('active');
    editingId = null;
}

function loadMemberData(memberId) {
    fetch(`../api/get-tree.php`)
        .then(res => res.json())
        .then(data => {
            const member = data.members.find(m => m.id === memberId);
            if (member) {
                document.getElementById('memberId').value = member.id;
                document.getElementById('firstName').value = member.firstName;
                document.getElementById('lastName').value = member.lastName;
                document.getElementById('gender').value = member.gender;
                document.getElementById('birthDate').value = member.birthDate;
                document.getElementById('deathDate').value = member.deathDate;
                document.getElementById('birthPlace').value = member.birthPlace;
                document.getElementById('bio').value = member.bio;
                document.getElementById('parentId').value = member.parentId || '';
                document.getElementById('motherId').value = member.motherId || '';
                document.getElementById('spouseId').value = member.spouseId || '';
                document.getElementById('createdAt').value = member.createdAt;
                document.getElementById('photoPath').value = member.photo || '';
                
                if (member.photo) {
                    const preview = document.getElementById('photoPreview');
                    preview.innerHTML = `<img src="../${member.photo}" alt="Preview">`;
                    preview.classList.add('active');
                }
            }
        });
}

// Form Submission
document.getElementById('memberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const memberData = {
        id: document.getElementById('memberId').value || undefined,
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        gender: document.getElementById('gender').value,
        birthDate: document.getElementById('birthDate').value,
        deathDate: document.getElementById('deathDate').value,
        birthPlace: document.getElementById('birthPlace').value,
        contactNumber: document.getElementById('contactNumber').value,
        address: document.getElementById('address').value,
        bio: document.getElementById('bio').value,
        parentId: document.getElementById('parentId').value || null,
        motherId: document.getElementById('motherId').value || null,
        spouseId: document.getElementById('spouseId').value || null,
        photo: document.getElementById('photoPath').value,
        createdAt: document.getElementById('createdAt').value
    };
    
    fetch('../api/save-member.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(memberData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeModal();
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(err => {
        alert('Error saving member: ' + err.message);
    });
});

// Edit Member
function editMember(memberId) {
    openModal(memberId);
}

// Delete Member
function deleteMember(memberId) {
    if (!confirm('Are you sure you want to delete this family member? This action cannot be undone.')) {
        return;
    }
    
    fetch('../api/delete-member.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: memberId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(err => {
        alert('Error deleting member: ' + err.message);
    });
}

// Close modal on outside click
document.getElementById('memberModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeInviteModal();
    }
});

// Invite Modal Functions
function openInviteModal() {
    const modal = document.getElementById('inviteModal');
    const form = document.getElementById('inviteForm');
    const result = document.getElementById('inviteResult');
    
    form.reset();
    result.style.display = 'none';
    modal.classList.add('active');
}

function closeInviteModal() {
    const modal = document.getElementById('inviteModal');
    modal.classList.remove('active');
}

// Invite Form Submission
document.getElementById('inviteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const data = {
        name: document.getElementById('inviteName').value,
        email: document.getElementById('inviteEmail').value
    };
    
    fetch('../api/generate-invite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const result = document.getElementById('inviteResult');
            const linkInput = document.getElementById('generatedLink');
            linkInput.value = data.link;
            result.style.display = 'block';
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(err => {
        alert('Error generating invite: ' + err.message);
    });
});

function copyInviteLink(code) {
    const protocol = window.location.protocol;
    const host = window.location.host;
    const link = `${protocol}//${host}/Family-Tree/?invite=${code}`;
    
    navigator.clipboard.writeText(link).then(() => {
        alert('Invite link copied to clipboard!');
    }).catch(() => {
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = link;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Invite link copied to clipboard!');
    });
}

function copyGeneratedLink() {
    const linkInput = document.getElementById('generatedLink');
    linkInput.select();
    document.execCommand('copy');
    alert('Link copied to clipboard!');
}

// Close invite modal on outside click
document.getElementById('inviteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeInviteModal();
    }
});

// ========================================
// IMAGE CROPPING FUNCTIONALITY
// ========================================

let cropState = {
    image: null,
    scale: 1,
    translateX: 0,
    translateY: 0,
    isDragging: false,
    startX: 0,
    startY: 0
};

// Modified photo upload handler
document.getElementById('photoFile').addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        const file = e.target.files[0];
        
        // Validate file
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            alert('File too large. Maximum size is 5MB');
            return;
        }
        
        // Load image for cropping
        const reader = new FileReader();
        reader.onload = function(event) {
            openCropModal(event.target.result);
        };
        reader.readAsDataURL(file);
    }
});

function openCropModal(imageSrc) {
    const modal = document.getElementById('cropModal');
    const cropImg = document.getElementById('cropImage');
    
    // Reset crop state
    cropState = {
        image: imageSrc,
        scale: 1,
        translateX: 0,
        translateY: 0,
        isDragging: false,
        startX: 0,
        startY: 0
    };
    
    cropImg.src = imageSrc;
    cropImg.style.transform = 'translate(0px, 0px) scale(1)';
    
    modal.classList.add('active');
    
    // Setup drag handlers
    setupCropDragHandlers();
}

function closeCropModal() {
    const modal = document.getElementById('cropModal');
    modal.classList.remove('active');
    
    // Clear file input if cancelled
    if (!document.getElementById('photoPath').value) {
        document.getElementById('photoFile').value = '';
    }
}

function setupCropDragHandlers() {
    const cropImg = document.getElementById('cropImage');
    const wrapper = document.querySelector('.crop-wrapper');
    
    // Mouse events
    cropImg.addEventListener('mousedown', startDrag);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', endDrag);
    
    // Touch events
    cropImg.addEventListener('touchstart', startDrag, { passive: false });
    document.addEventListener('touchmove', drag, { passive: false });
    document.addEventListener('touchend', endDrag);
    
    // Wheel zoom
    wrapper.addEventListener('wheel', handleWheelZoom, { passive: false });
}

function startDrag(e) {
    e.preventDefault();
    cropState.isDragging = true;
    
    const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
    const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
    
    cropState.startX = clientX - cropState.translateX;
    cropState.startY = clientY - cropState.translateY;
}

function drag(e) {
    if (!cropState.isDragging) return;
    e.preventDefault();
    
    const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
    const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
    
    cropState.translateX = clientX - cropState.startX;
    cropState.translateY = clientY - cropState.startY;
    
    updateCropTransform();
}

function endDrag() {
    cropState.isDragging = false;
}

function handleWheelZoom(e) {
    e.preventDefault();
    const delta = e.deltaY > 0 ? -0.1 : 0.1;
    zoomCrop(delta);
}

function zoomCrop(delta) {
    cropState.scale = Math.max(0.5, Math.min(3, cropState.scale + delta));
    updateCropTransform();
}

function updateCropTransform() {
    const cropImg = document.getElementById('cropImage');
    cropImg.style.transform = `translate(${cropState.translateX}px, ${cropState.translateY}px) scale(${cropState.scale})`;
}

function applyCrop() {
    const cropImg = document.getElementById('cropImage');
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    // Set canvas size to 400x400 (2x the display size for retina)
    const outputSize = 400;
    canvas.width = outputSize;
    canvas.height = outputSize;
    
    // Get the crop frame dimensions and position
    const wrapper = document.querySelector('.crop-wrapper');
    const wrapperRect = wrapper.getBoundingClientRect();
    const frameSize = 200; // Display size of crop frame
    const frameCenterX = wrapperRect.width / 2;
    const frameCenterY = wrapperRect.height / 2;
    
    // Calculate image position and scaling
    const img = new Image();
    img.onload = function() {
        // Get the displayed image dimensions
        const imgRect = cropImg.getBoundingClientRect();
        const imgNaturalWidth = img.naturalWidth;
        const imgNaturalHeight = img.naturalHeight;
        
        // Calculate scale factor between natural and displayed size
        const displayScale = imgRect.width / imgNaturalWidth;
        const totalScale = displayScale * cropState.scale;
        
        // Calculate the crop area in original image coordinates
        const cropCenterX = frameCenterX - (imgRect.left - wrapperRect.left + cropState.translateX);
        const cropCenterY = frameCenterY - (imgRect.top - wrapperRect.top + cropState.translateY);
        
        const sourceX = (cropCenterX - frameSize / 2) / totalScale;
        const sourceY = (cropCenterY - frameSize / 2) / totalScale;
        const sourceSize = frameSize / totalScale;
        
        // Draw the cropped image
        ctx.drawImage(
            img,
            sourceX, sourceY, sourceSize, sourceSize,
            0, 0, outputSize, outputSize
        );
        
        // Convert to blob and upload
        canvas.toBlob(function(blob) {
            const file = new File([blob], 'cropped-photo.jpg', { type: 'image/jpeg' });
            uploadCroppedPhoto(file);
        }, 'image/jpeg', 0.9);
    };
    img.src = cropState.image;
}

function uploadCroppedPhoto(file) {
    const formData = new FormData();
    formData.append('photo', file);
    
    fetch('../api/upload-photo.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('photoPath').value = data.path;
            const preview = document.getElementById('photoPreview');
            preview.innerHTML = `<img src="../${data.path}" alt="Preview">`;
            preview.classList.add('active');
            closeCropModal();
        } else {
            alert('Upload failed: ' + data.error);
        }
    })
    .catch(err => {
        alert('Upload error: ' + err.message);
    });
}

// Close crop modal on outside click
document.getElementById('cropModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCropModal();
    }
});

// ========================================
// GALLERY MANAGEMENT
// ========================================

let currentGallery = [];
let currentMemberId = null;

// Gallery file input handler
document.getElementById('galleryFiles').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    if (files.length === 0) return;
    
    const memberId = document.getElementById('memberId').value;
    if (!memberId) {
        alert('Please save the member first before adding gallery photos');
        return;
    }
    
    files.forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            alert(`File ${file.name} is too large. Max 10MB`);
            return;
        }
        uploadGalleryPhoto(memberId, file);
    });
});

function uploadGalleryPhoto(memberId, file) {
    const formData = new FormData();
    formData.append('memberId', memberId);
    formData.append('photo', file);
    
    fetch('../api/save-gallery-photo.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            currentGallery.push(data.path);
            renderAdminGallery();
        } else {
            alert('Upload failed: ' + data.error);
        }
    })
    .catch(err => {
        alert('Upload error: ' + err.message);
    });
}

function renderAdminGallery() {
    const grid = document.getElementById('adminGalleryGrid');
    grid.innerHTML = '';
    
    currentGallery.forEach((photo, index) => {
        const item = document.createElement('div');
        item.className = 'admin-gallery-item';
        item.innerHTML = `
            <img src="../${photo}" alt="Gallery photo">
            <button class="delete-photo" onclick="deleteGalleryPhoto('${photo}', ${index})" title="Delete">×</button>
        `;
        grid.appendChild(item);
    });
}

function deleteGalleryPhoto(photoPath, index) {
    if (!confirm('Delete this photo from gallery?')) return;
    
    const memberId = document.getElementById('memberId').value;
    
    fetch('../api/delete-gallery-photo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ memberId: memberId, photoPath: photoPath })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            currentGallery.splice(index, 1);
            renderAdminGallery();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(err => {
        alert('Error deleting photo: ' + err.message);
    });
}

// Override loadMemberData to include gallery
const originalLoadMemberData = loadMemberData;
loadMemberData = function(memberId) {
    fetch(`../api/get-tree.php`)
        .then(res => res.json())
        .then(data => {
            const member = data.members.find(m => m.id === memberId);
            if (member) {
                document.getElementById('memberId').value = member.id;
                document.getElementById('firstName').value = member.firstName;
                document.getElementById('lastName').value = member.lastName;
                document.getElementById('gender').value = member.gender;
                document.getElementById('birthDate').value = member.birthDate;
                document.getElementById('deathDate').value = member.deathDate;
                document.getElementById('birthPlace').value = member.birthPlace || '';
                document.getElementById('contactNumber').value = member.contactNumber || '';
                document.getElementById('address').value = member.address || '';
                document.getElementById('bio').value = member.bio;
                document.getElementById('parentId').value = member.parentId || '';
                document.getElementById('spouseId').value = member.spouseId || '';
                document.getElementById('createdAt').value = member.createdAt;
                document.getElementById('photoPath').value = member.photo || '';
                
                // Load gallery
                currentMemberId = member.id;
                currentGallery = member.gallery || [];
                renderAdminGallery();
                
                if (member.photo) {
                    const preview = document.getElementById('photoPreview');
                    preview.innerHTML = `<img src="../${member.photo}" alt="Preview">`;
                    preview.classList.add('active');
                }
            }
        });
};

// Clear gallery when opening modal for new member
const originalOpenModal = openModal;
openModal = function(memberId = null) {
    originalOpenModal(memberId);
    if (!memberId) {
        currentGallery = [];
        currentMemberId = null;
        renderAdminGallery();
    }
};
