// Family Tree Visualization JavaScript

let treeData = [];
let allMembers = [];
let isLoggedIn = false;
let isTextView = false;

// Initialize Tree
document.addEventListener('DOMContentLoaded', function() {
    checkLoginStatus();
    loadTree();
});

// Check if user is logged in
function checkLoginStatus() {
    // Check for session cookie or localStorage
    isLoggedIn = document.body.dataset.loggedIn === 'true';
}

// Gallery state
let galleryState = {
    memberId: null,
    photos: [],
    currentIndex: 0
};

// Toggle between GUI and Text view
function toggleViewMode() {
    isTextView = !isTextView;
    const container = document.getElementById('treeRoot');
    const toggleBtn = document.getElementById('viewToggleBtn');
    
    if (isTextView) {
        // Switch to text view
        container.classList.add('text-view');
        container.classList.remove('gui-view');
        toggleBtn.innerHTML = '🖼️ GUI';
        toggleBtn.title = 'Switch to GUI View';
        renderTextTree();
    } else {
        // Switch to GUI view
        container.classList.remove('text-view');
        container.classList.add('gui-view');
        toggleBtn.innerHTML = '📝 Text';
        toggleBtn.title = 'Switch to Text View';
        renderTree();
    }
}

// Print the tree
function printTree() {
    // If in text view, print directly
    // If in GUI view, temporarily switch to text view for printing
    const wasTextView = isTextView;
    
    if (!isTextView) {
        toggleViewMode();
    }
    
    // Wait for render then print
    setTimeout(() => {
        window.print();
        
        // Switch back if needed
        if (!wasTextView) {
            setTimeout(() => toggleViewMode(), 500);
        }
    }, 300);
}

// Render tree as simple text
function renderTextTree() {
    const container = document.getElementById('treeRoot');
    
    if (treeData.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">🌳</div>
                <h3>No family members yet</h3>
            </div>
        `;
        return;
    }
    
    let html = '<div class="text-tree-container">';
    html += '<h2 class="text-tree-title">Family Tree</h2>';
    
    treeData.forEach(root => {
        html += renderTextBranch(root, 0);
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Render a branch in text format
function renderTextBranch(member, level) {
    const indent = '  '.repeat(level);
    let html = '';
    
    // Get member name - handle different data structures
    let memberName = 'Unknown';
    if (member.name) {
        memberName = member.name;
    } else if (member.firstName || member.lastName) {
        memberName = `${member.firstName || ''} ${member.lastName || ''}`.trim();
    }
    
    // Person line with name and basic info
    let personLine = `${indent}├─ ${memberName}`;
    
    // Add birth/death years if available
    if (member.birthDate || member.deathDate) {
        const birth = member.birthDate ? new Date(member.birthDate).getFullYear() : '?';
        const death = member.deathDate ? new Date(member.deathDate).getFullYear() : '';
        personLine += ` (${birth}${death ? '-' + death : ''})`;
    }
    
    // Add spouse info
    if (member.spouses && member.spouses.length > 0) {
        const spouseNames = member.spouses.map(s => {
            if (s.name) return s.name;
            if (s.firstName || s.lastName) return `${s.firstName || ''} ${s.lastName || ''}`.trim();
            return 'Unknown';
        }).join(', ');
        personLine += ` ⚭ ${spouseNames}`;
    }
    
    html += `<div class="text-tree-line level-${level}">${escapeHtml(personLine)}</div>`;
    
    // Children
    if (member.children && member.children.length > 0) {
        member.children.forEach(child => {
            html += renderTextBranch(child, level + 1);
        });
    }
    
    return html;
}

// Escape HTML for text view
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function loadTree() {
    fetch('api/get-tree.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                treeData = data.tree;
                allMembers = data.members;
                renderTree();
            }
        })
        .catch(err => {
            console.error('Error loading tree:', err);
            document.getElementById('treeRoot').innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">⚠️</div>
                    <h3>Error loading family tree</h3>
                    <p>Please try again later</p>
                </div>
            `;
        });
}

function renderTree() {
    const container = document.getElementById('treeRoot');
    
    if (treeData.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">🌳</div>
                <h3>Your family tree is empty</h3>
                <p>Sign in to the admin panel to add family members</p>
                <a href="admin/login.php" class="btn btn-primary">Admin Login</a>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    
    // Create tree levels
    const treeWrapper = document.createElement('div');
    treeWrapper.className = 'tree';
    
    treeData.forEach(rootMember => {
        treeWrapper.appendChild(createBranch(rootMember));
    });
    
    container.appendChild(treeWrapper);
}

function createBranch(member) {
    const branch = document.createElement('div');
    branch.className = 'tree-branch';
    
    // Create person card container
    const cardContainer = document.createElement('div');
    cardContainer.className = 'card-container';
    cardContainer.style.display = 'flex';
    cardContainer.style.alignItems = 'center';
    
    // Create main person card
    const mainCard = createPersonCard(member);
    
    // Mark if has children for collapse/expand functionality
    if (member.children && member.children.length > 0) {
        mainCard.classList.add('has-children');
        mainCard.dataset.memberId = member.id;
        
        // Add click handler for collapse/expand all descendants
        mainCard.querySelector('.person-card-header').addEventListener('dblclick', function(e) {
            e.stopPropagation();
            toggleBranchCollapse(mainCard, member.id);
        });
        
        // Add tooltip
        mainCard.title = 'Double-click to collapse/expand all descendants';
    }
    
    // Add heart icon if person has spouses
    if (member.spouses && member.spouses.length > 0) {
        const heartIcon = document.createElement('div');
        heartIcon.className = 'spouse-toggle pulse';
        heartIcon.innerHTML = '❤️';
        heartIcon.title = member.spouses.length === 1 ? 'Click to show spouse' : `Click to show ${member.spouses.length} spouses`;
        
        // Add count badge if multiple spouses
        if (member.spouses.length > 1) {
            const countBadge = document.createElement('span');
            countBadge.className = 'spouse-count';
            countBadge.textContent = member.spouses.length;
            heartIcon.appendChild(countBadge);
        }
        
        heartIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSpouseDisplay(this, member.spouses, cardContainer);
        });
        
        mainCard.querySelector('.person-card-inner').appendChild(heartIcon);
    }
    
    cardContainer.appendChild(mainCard);
    
    // Create spouse container (hidden by default)
    if (member.spouses && member.spouses.length > 0) {
        const spouseContainer = document.createElement('div');
        spouseContainer.className = 'spouse-container';
        spouseContainer.id = 'spouse-container-' + member.id;
        
        member.spouses.forEach((spouse, index) => {
            if (index > 0) {
                // Add connector between multiple spouses
                const multiConnector = document.createElement('div');
                multiConnector.className = 'spouse-connector-line';
                multiConnector.style.width = '20px';
                spouseContainer.appendChild(multiConnector);
            }
            
            const spouseCard = createPersonCard(spouse);
            spouseContainer.appendChild(spouseCard);
        });
        
        cardContainer.appendChild(spouseContainer);
    }
    
    branch.appendChild(cardContainer);
    
    // Add children if any
    if (member.children && member.children.length > 0) {
        const connector = document.createElement('div');
        connector.className = 'tree-connector children';
        branch.appendChild(connector);
        
        const childrenContainer = document.createElement('div');
        childrenContainer.className = 'children-wrapper';
        childrenContainer.id = 'children-' + member.id;
        
        member.children.forEach(child => {
            childrenContainer.appendChild(createBranch(child));
        });
        
        branch.appendChild(childrenContainer);
    }
    
    return branch;
}

// Toggle collapse/expand all descendants
function toggleBranchCollapse(card, memberId) {
    const isCollapsed = card.classList.contains('collapsed-branch');
    const branch = card.closest('.tree-branch');
    
    if (isCollapsed) {
        // Expand
        card.classList.remove('collapsed-branch');
        expandAllDescendants(branch);
    } else {
        // Collapse
        card.classList.add('collapsed-branch');
        collapseAllDescendants(branch);
    }
}

function collapseAllDescendants(branch) {
    const childrenWrapper = branch.querySelector(':scope > .children-wrapper');
    if (childrenWrapper) {
        childrenWrapper.style.display = 'none';
        
        // Also hide connector
        const connector = branch.querySelector(':scope > .tree-connector');
        if (connector) {
            connector.style.display = 'none';
        }
        
        // Recursively collapse all nested branches
        const nestedBranches = childrenWrapper.querySelectorAll(':scope > .tree-branch');
        nestedBranches.forEach(nestedBranch => {
            const nestedCard = nestedBranch.querySelector('.person-card.has-children');
            if (nestedCard) {
                nestedCard.classList.add('collapsed-branch');
            }
            collapseAllDescendants(nestedBranch);
        });
    }
}

function expandAllDescendants(branch) {
    const childrenWrapper = branch.querySelector(':scope > .children-wrapper');
    if (childrenWrapper) {
        childrenWrapper.style.display = 'flex';
        
        // Also show connector
        const connector = branch.querySelector(':scope > .tree-connector');
        if (connector) {
            connector.style.display = 'block';
        }
        
        // Recursively expand all nested branches
        const nestedBranches = childrenWrapper.querySelectorAll(':scope > .tree-branch');
        nestedBranches.forEach(nestedBranch => {
            const nestedCard = nestedBranch.querySelector('.person-card.has-children');
            if (nestedCard && !nestedCard.classList.contains('collapsed-branch')) {
                expandAllDescendants(nestedBranch);
            }
        });
    }
}

// Get connector class based on relationship status
function getConnectorClass(member) {
    if (member.spouseId || member.spouses) {
        // Check relationship status
        if (member.relationshipStatus === 'divorced') {
            return 'divorced';
        }
        return 'married';
    }
    return 'children';
}

function toggleSpouseDisplay(heartIcon, spouses, container) {
    const spouseContainer = container.querySelector('.spouse-container');
    if (!spouseContainer) return;
    
    const isVisible = spouseContainer.classList.contains('visible');
    
    // Close all other spouse displays first
    document.querySelectorAll('.spouse-container.visible').forEach(el => {
        if (el !== spouseContainer) {
            el.classList.remove('visible');
            // Remove connector line from other containers
            const connector = el.previousElementSibling;
            if (connector && connector.classList.contains('spouse-connector-line')) {
                connector.remove();
            }
        }
    });
    
    // Reset all heart icons
    document.querySelectorAll('.spouse-toggle').forEach(icon => {
        if (icon !== heartIcon) {
            icon.classList.add('pulse');
        }
    });
    
    if (isVisible) {
        // Hide spouse
        spouseContainer.classList.remove('visible');
        heartIcon.classList.add('pulse');
        
        // Remove connector line
        const connector = spouseContainer.previousElementSibling;
        if (connector && connector.classList.contains('spouse-connector-line')) {
            connector.remove();
        }
    } else {
        // Show spouse
        spouseContainer.classList.add('visible');
        heartIcon.classList.remove('pulse');
        
        // Add connector line between main card and spouse container
        const connector = document.createElement('div');
        connector.className = 'spouse-connector-line';
        container.insertBefore(connector, spouseContainer);
    }
}

function createPersonCard(member) {
    const card = document.createElement('div');
    card.className = 'person-card';
    card.dataset.id = member.id;
    
    const initials = (member.firstName[0] + member.lastName[0]).toUpperCase();
    const birthYear = member.birthDate ? new Date(member.birthDate).getFullYear() : '?';
    const deathYear = member.deathDate ? new Date(member.deathDate).getFullYear() : 'Present';
    const hasGallery = member.gallery && Array.isArray(member.gallery) && member.gallery.length > 0;
    const galleryCount = hasGallery ? member.gallery.length : 0;
    
    // Determine if image should be blurred (blur for public, clear for logged-in)
    const blurClass = (!isLoggedIn && member.photo) ? 'blur-image' : '';
    const blurOverlay = (!isLoggedIn && member.photo) ? '<div class="blur-overlay"><span class="blur-icon">🔒</span></div>' : '';
    
    // Gallery button HTML - always show if gallery exists
    const galleryBtnHtml = hasGallery 
        ? `<div class="gallery-btn" onclick="event.stopPropagation(); openGallery('${member.id}')" title="${galleryCount} photo${galleryCount !== 1 ? 's' : ''} in gallery">📷</div>` 
        : '';
    
    // Gallery link for expanded details
    const galleryLinkHtml = hasGallery
        ? `<div class="detail-row gallery-link-row" onclick="event.stopPropagation(); openGallery('${member.id}')">
            <span class="detail-icon">📷</span>
            <div class="detail-content">
                <div class="detail-label">Photo Gallery</div>
                <div class="gallery-link-text">View ${galleryCount} photo${galleryCount !== 1 ? 's' : ''} →</div>
            </div>
           </div>`
        : '';
    
    card.innerHTML = `
        <div class="person-card-inner">
            <div class="person-card-header" onclick="toggleCard(this)">
                <div class="person-avatar ${blurClass}">
                    ${member.photo 
                        ? `<img src="${member.photo}" alt="${member.firstName}">${blurOverlay}${galleryBtnHtml}`
                        : `<div class="person-avatar-placeholder">${initials}</div>${galleryBtnHtml}`
                    }
                </div>
                <div class="person-name">${member.firstName} ${member.lastName}</div>
                <div class="person-years">${birthYear} - ${deathYear}</div>
                <div class="card-toggle">▼</div>
            </div>
            <div class="person-details">
                ${member.birthPlace ? `
                    <div class="detail-row">
                        <span class="detail-icon">📍</span>
                        <div class="detail-content">
                            <div class="detail-label">Birth Place</div>
                            <div>${member.birthPlace}</div>
                        </div>
                    </div>
                ` : ''}
                ${member.birthDate ? `
                    <div class="detail-row">
                        <span class="detail-icon">🎂</span>
                        <div class="detail-content">
                            <div class="detail-label">Birth Date</div>
                            <div>${formatDate(member.birthDate)}</div>
                        </div>
                    </div>
                ` : ''}
                ${member.deathDate ? `
                    <div class="detail-row">
                        <span class="detail-icon">✝️</span>
                        <div class="detail-content">
                            <div class="detail-label">Death Date</div>
                            <div>${formatDate(member.deathDate)}</div>
                        </div>
                    </div>
                ` : ''}
                ${member.phone ? `
                    <div class="detail-row">
                        <span class="detail-icon">📞</span>
                        <div class="detail-content">
                            <div class="detail-label">Contact</div>
                            <div>${member.phone}</div>
                        </div>
                    </div>
                ` : ''}
                ${member.address ? `
                    <div class="detail-row">
                        <span class="detail-icon">🏠</span>
                        <div class="detail-content">
                            <div class="detail-label">Address</div>
                            <div>${member.address}</div>
                        </div>
                    </div>
                ` : ''}
                ${member.bio ? `
                    <div class="detail-row">
                        <span class="detail-icon">📝</span>
                        <div class="detail-content">
                            <div class="detail-label">About</div>
                            <div>${member.bio}</div>
                        </div>
                    </div>
                ` : ''}
                ${galleryLinkHtml}
            </div>
        </div>
    `;
    
    return card;
}

function toggleCard(header) {
    const card = header.closest('.person-card');
    card.classList.toggle('expanded');
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

// Search functionality
function searchMembers(query) {
    if (!query) {
        // Show all cards
        document.querySelectorAll('.person-card').forEach(card => {
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
        });
        return;
    }
    
    query = query.toLowerCase();
    
    document.querySelectorAll('.person-card').forEach(card => {
        const name = card.querySelector('.person-name').textContent.toLowerCase();
        const details = card.querySelector('.person-details').textContent.toLowerCase();
        
        if (name.includes(query) || details.includes(query)) {
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
            card.classList.add('expanded');
        } else {
            card.style.opacity = '0.3';
            card.style.transform = 'scale(0.95)';
        }
    });
}

// Expand/Collapse All
function expandAll() {
    document.querySelectorAll('.person-card').forEach(card => {
        card.classList.add('expanded');
    });
}

function collapseAll() {
    document.querySelectorAll('.person-card').forEach(card => {
        card.classList.remove('expanded');
    });
}

// Zoom controls
let currentZoom = 1;

function zoomIn() {
    currentZoom = Math.min(currentZoom + 0.1, 1.5);
    applyZoom();
}

function zoomOut() {
    currentZoom = Math.max(currentZoom - 0.1, 0.5);
    applyZoom();
}

function resetZoom() {
    currentZoom = 1;
    applyZoom();
}

function applyZoom() {
    const tree = document.querySelector('.tree');
    if (tree) {
        tree.style.transform = `scale(${currentZoom})`;
        tree.style.transformOrigin = 'top center';
    }
}

// ========================================
// GALLERY FUNCTIONS
// ========================================

function openGallery(memberId) {
    const member = allMembers.find(m => m.id === memberId);
    if (!member || !member.gallery || member.gallery.length === 0) return;
    
    galleryState.memberId = memberId;
    galleryState.photos = member.gallery;
    galleryState.currentIndex = 0;
    
    const modal = document.getElementById('galleryModal');
    const grid = document.getElementById('galleryGrid');
    const title = document.getElementById('galleryTitle');
    const count = document.getElementById('galleryCount');
    
    title.textContent = `${member.firstName} ${member.lastName}'s Gallery`;
    count.textContent = `${member.gallery.length} photo${member.gallery.length !== 1 ? 's' : ''}`;
    
    grid.innerHTML = '';
    member.gallery.forEach((photo, index) => {
        const item = document.createElement('div');
        item.className = `gallery-item ${!isLoggedIn ? 'blur-item' : ''}`;
        item.onclick = () => openFullImage(index);
        
        item.innerHTML = `
            <img src="${photo}" alt="Gallery photo ${index + 1}">
            ${!isLoggedIn ? '<div class="blur-overlay-gallery"><span>🔒</span></div>' : ''}
        `;
        
        grid.appendChild(item);
    });
    
    modal.classList.add('active');
}

function closeGallery() {
    const modal = document.getElementById('galleryModal');
    modal.classList.remove('active');
}

function openFullImage(index) {
    if (!isLoggedIn) {
        alert('Please log in to view full images');
        return;
    }
    
    galleryState.currentIndex = index;
    const modal = document.getElementById('fullImageModal');
    const img = document.getElementById('fullImage');
    const counter = document.getElementById('imageCounter');
    
    img.src = galleryState.photos[index];
    counter.textContent = `${index + 1} / ${galleryState.photos.length}`;
    
    modal.classList.add('active');
}

function closeFullImage() {
    const modal = document.getElementById('fullImageModal');
    modal.classList.remove('active');
}

function prevImage() {
    const newIndex = galleryState.currentIndex > 0 ? galleryState.currentIndex - 1 : galleryState.photos.length - 1;
    openFullImage(newIndex);
}

function nextImage() {
    const newIndex = galleryState.currentIndex < galleryState.photos.length - 1 ? galleryState.currentIndex + 1 : 0;
    openFullImage(newIndex);
}

// Close modals on outside click
document.addEventListener('click', function(e) {
    const galleryModal = document.getElementById('galleryModal');
    const fullImageModal = document.getElementById('fullImageModal');
    
    if (e.target === galleryModal) {
        closeGallery();
    }
    if (e.target === fullImageModal) {
        closeFullImage();
    }
});

// Keyboard navigation for gallery
document.addEventListener('keydown', function(e) {
    const fullImageModal = document.getElementById('fullImageModal');
    if (!fullImageModal.classList.contains('active')) return;
    
    if (e.key === 'Escape') {
        closeFullImage();
    } else if (e.key === 'ArrowLeft') {
        prevImage();
    } else if (e.key === 'ArrowRight') {
        nextImage();
    }
});
