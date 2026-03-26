<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
checkAuth();

$members = getMembers();
$invites = json_decode(file_get_contents(INVITES_FILE), true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Family Tree</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="admin-page">
    <nav class="admin-nav">
        <div class="nav-brand">
            <svg width="32" height="32" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="16" r="8" stroke="#00b2ff" stroke-width="2"/>
                <circle cx="12" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                <circle cx="36" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                <path d="M20 22L14 30" stroke="#00b2ff" stroke-width="2"/>
                <path d="M28 22L34 30" stroke="#00b2ff" stroke-width="2"/>
            </svg>
            <span>Family Tree Admin</span>
        </div>
        <div class="nav-actions">
            <a href="users.php" class="btn btn-secondary">👥 Users</a>
            <a href="logs.php" class="btn btn-secondary">📋 Logs</a>
            <a href="settings.php" class="btn btn-secondary">⚙️ Settings</a>
            <a href="../index.php" target="_blank" class="btn btn-secondary">View Tree</a>
            <button type="button" class="btn btn-secondary" onclick="rebuildGallery()">🔄 Rebuild Gallery</button>
            <a href="logout.php" class="btn btn-outline">Logout</a>
        </div>
    </nav>

    <main class="admin-main">
        <!-- Invites Section -->
        <div class="admin-section">
            <div class="admin-header">
                <h1>Family Invites</h1>
                <button class="btn btn-primary" onclick="openInviteModal()">
                    <span>+</span> Send Invite
                </button>
            </div>
            
            <div class="invites-list">
                <?php if (empty($invites)): ?>
                    <div class="empty-state small">
                        <p>No invites sent yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach (array_reverse($invites) as $invite): ?>
                        <div class="invite-card glass-card <?php echo $invite['used'] ? 'used' : ''; ?>">
                            <div class="invite-info">
                                <div class="invite-name"><?php echo $invite['name'] ?: 'Unknown'; ?></div>
                                <div class="invite-email"><?php echo $invite['email']; ?></div>
                                <div class="invite-date">Created: <?php echo date('M j, Y', strtotime($invite['createdAt'])); ?></div>
                            </div>
                            <div class="invite-status">
                                <?php if ($invite['used']): ?>
                                    <span class="badge badge-used">Used</span>
                                <?php else: ?>
                                    <span class="badge badge-pending">Pending</span>
                                    <button class="btn-icon" onclick="copyInviteLink('<?php echo $invite['code']; ?>')" title="Copy Link">
                                        📋
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Members Section -->
        <div class="admin-section">
            <div class="admin-header">
                <h1>Family Members</h1>
                <button class="btn btn-primary" onclick="openModal()">
                    <span>+</span> Add Member
                </button>
            </div>

            <div class="members-grid">
            <?php foreach ($members as $member): ?>
                <div class="member-card glass-card" data-id="<?php echo $member['id']; ?>">
                    <div class="member-photo">
                        <?php if ($member['photo']): ?>
                            <img src="../<?php echo $member['photo']; ?>" alt="<?php echo $member['firstName']; ?>">
                        <?php else: ?>
                            <div class="photo-placeholder">
                                <?php echo strtoupper(substr($member['firstName'], 0, 1) . substr($member['lastName'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="member-info">
                        <h3><?php echo $member['firstName'] . ' ' . $member['lastName']; ?></h3>
                        <p class="member-dates">
                            <?php 
                            echo $member['birthDate'] ? date('Y', strtotime($member['birthDate'])) : '?';
                            echo ' - ';
                            echo $member['deathDate'] ? date('Y', strtotime($member['deathDate'])) : 'Present';
                            ?>
                        </p>
                        <?php if ($member['birthPlace']): ?>
                            <p class="member-place">📍 <?php echo $member['birthPlace']; ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="member-actions">
                        <button class="btn-icon" onclick="editMember('<?php echo $member['id']; ?>')" title="Edit">
                            ✏️
                        </button>
                        <button class="btn-icon" onclick="deleteMember('<?php echo $member['id']; ?>')" title="Delete">
                            🗑️
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($members)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🌳</div>
                    <h3>No family members yet</h3>
                    <p>Start building your family tree by adding your first member</p>
                    <button class="btn btn-primary" onclick="openModal()">Add First Member</button>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Member Modal -->
    <div id="memberModal" class="modal">
        <div class="modal-content glass-card">
            <div class="modal-header">
                <h2 id="modalTitle">Add Family Member</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form id="memberForm" class="member-form">
                <input type="hidden" id="memberId">
                <input type="hidden" id="createdAt">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name *</label>
                        <input type="text" id="firstName" class="glass-input" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name *</label>
                        <input type="text" id="lastName" class="glass-input" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" class="glass-input">
                            <option value="unknown">Unknown</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="parentId">Father</label>
                        <select id="parentId" class="glass-input">
                            <option value="">None (Root)</option>
                            <?php foreach ($members as $m): ?>
                                <?php if ($m['gender'] !== 'female'): ?>
                                <option value="<?php echo $m['id']; ?>">
                                    <?php echo $m['firstName'] . ' ' . $m['lastName']; ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="motherId">Mother</label>
                        <select id="motherId" class="glass-input">
                            <option value="">None</option>
                            <?php foreach ($members as $m): ?>
                                <?php if ($m['gender'] === 'female'): ?>
                                <option value="<?php echo $m['id']; ?>">
                                    <?php echo $m['firstName'] . ' ' . $m['lastName']; ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="spouseId">Spouse/Partner</label>
                        <select id="spouseId" class="glass-input">
                            <option value="">None</option>
                            <?php foreach ($members as $m): ?>
                                <option value="<?php echo $m['id']; ?>">
                                    <?php echo $m['firstName'] . ' ' . $m['lastName']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="relationshipStatus">Relationship Status</label>
                        <select id="relationshipStatus" class="glass-input">
                            <option value="married">Married</option>
                            <option value="divorced">Divorced</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="birthPlace">Birth Place</label>
                        <input type="text" id="birthPlace" class="glass-input" placeholder="City, Country">
                    </div>
                    <div class="form-group">
                        <label for="contactNumber">Contact Number</label>
                        <input type="tel" id="contactNumber" class="glass-input" placeholder="+1 234 567 8900">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contactNumber">Contact Number</label>
                        <input type="tel" id="contactNumber" class="glass-input" placeholder="+1 234 567 8900">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" class="glass-input" placeholder="Street, City, Country">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="birthDate">Birth Date</label>
                        <input type="date" id="birthDate" class="glass-input">
                    </div>
                    <div class="form-group">
                        <label for="deathDate">Death Date</label>
                        <input type="date" id="deathDate" class="glass-input">
                    </div>
                </div>

                <div class="form-group">
                    <label for="photo">Photo</label>
                    <div class="photo-upload">
                        <input type="file" id="photoFile" accept="image/*" class="glass-input">
                        <input type="hidden" id="photoPath">
                        <div id="photoPreview" class="photo-preview"></div>
                    </div>
                    <p class="form-hint">Upload a photo, then crop to focus on the face</p>
                </div>

                <div class="form-group">
                    <label for="bio">Biography</label>
                    <textarea id="bio" class="glass-input" rows="3" placeholder="Brief biography..."></textarea>
                </div>

                <!-- Gallery Upload Section -->
                <div class="gallery-upload-section">
                    <label>Photo Gallery</label>
                    <div class="gallery-upload-btn" onclick="document.getElementById('galleryFiles').click()">
                        <div class="upload-icon">📤</div>
                        <div>Click to upload gallery photos</div>
                        <small style="color: var(--text-muted);">JPG, PNG, GIF up to 10MB each</small>
                    </div>
                    <input type="file" id="galleryFiles" multiple accept="image/*" style="display: none;">
                    <div id="adminGalleryGrid" class="admin-gallery-grid">
                        <!-- Gallery thumbnails will appear here -->
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Member</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Crop Modal -->
    <div id="cropModal" class="modal">
        <div class="modal-content glass-card" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Crop Profile Photo</h2>
                <button class="modal-close" onclick="closeCropModal()">&times;</button>
            </div>
            <div class="crop-container">
                <div class="crop-wrapper">
                    <img id="cropImage" src="" alt="Crop Preview">
                    <div class="crop-overlay">
                        <div class="crop-frame">
                            <div class="crop-guide"></div>
                        </div>
                    </div>
                </div>
                <div class="crop-controls">
                    <button type="button" class="btn btn-secondary" onclick="zoomCrop(-0.1)">➖</button>
                    <span class="crop-label">Drag to position • Scroll to zoom</span>
                    <button type="button" class="btn btn-secondary" onclick="zoomCrop(0.1)">➕</button>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeCropModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyCrop()">Apply Crop</button>
            </div>
        </div>
    </div>

    <!-- Invite Modal -->
    <div id="inviteModal" class="modal">
        <div class="modal-content glass-card" style="max-width: 450px;">
            <div class="modal-header">
                <h2>Invite Family Member</h2>
                <button class="modal-close" onclick="closeInviteModal()">&times;</button>
            </div>
            <form id="inviteForm" class="member-form">
                <div class="form-group">
                    <label for="inviteName">Name</label>
                    <input type="text" id="inviteName" class="glass-input" placeholder="Family member's name">
                </div>
                
                <div class="form-group">
                    <label for="inviteEmail">Email *</label>
                    <input type="email" id="inviteEmail" class="glass-input" placeholder="email@example.com" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeInviteModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Invite</button>
                </div>
            </form>
            
            <div id="inviteResult" class="invite-result" style="display: none;">
                <div class="result-label">Share this link:</div>
                <div class="result-link-wrapper">
                    <input type="text" id="generatedLink" class="glass-input" readonly>
                    <button class="btn btn-primary" onclick="copyGeneratedLink()">Copy</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
