<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
checkAuth();

$users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Family Tree Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        .role-admin {
            background: rgba(255, 71, 87, 0.15);
            color: #ff4757;
            border: 1px solid rgba(255, 71, 87, 0.3);
        }
        .role-member {
            background: rgba(0, 178, 255, 0.15);
            color: var(--accent-cyan);
            border: 1px solid rgba(0, 178, 255, 0.3);
        }
        .users-grid {
            display: grid;
            gap: 12px;
        }
        .user-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            transition: var(--transition);
        }
        .user-card:hover {
            border-color: var(--accent-cyan);
        }
        .user-info {
            flex: 1;
        }
        .user-name {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-email {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 2px;
        }
        .user-date {
            font-size: 12px;
            color: var(--text-muted);
        }
        .user-actions {
            display: flex;
            gap: 8px;
        }
        .password-hint {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        .admin-note {
            background: rgba(255, 165, 2, 0.1);
            border: 1px solid rgba(255, 165, 2, 0.3);
            border-radius: var(--radius-md);
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--text-secondary);
        }
    </style>
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
            <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
            <a href="logs.php" class="btn btn-secondary">📋 Logs</a>
            <a href="settings.php" class="btn btn-secondary">⚙️ Settings</a>
            <a href="../index.php" target="_blank" class="btn btn-secondary">View Tree</a>
            <a href="logout.php" class="btn btn-outline">Logout</a>
        </div>
    </nav>

    <main class="admin-main">
        <div class="admin-section">
            <div class="admin-header">
                <h1>User Management</h1>
                <button class="btn btn-primary" onclick="openUserModal()">
                    <span>+</span> Add User
                </button>
            </div>

            <div class="admin-note">
                💡 <strong>Tip:</strong> Create users here to give family members access. User ID is their email address. 
                You can assign Admin role to give full management access, or Member role for view-only access.
            </div>

            <div class="users-grid">
                <?php foreach ($users as $user): ?>
                    <div class="user-card glass-card" data-id="<?php echo $user['id']; ?>">
                        <div class="user-info">
                            <div class="user-name">
                                <?php echo $user['name']; ?>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo $user['role']; ?>
                                </span>
                            </div>
                            <div class="user-email">📧 <?php echo $user['email']; ?></div>
                            <div class="user-date">
                                Created: <?php echo date('M j, Y', strtotime($user['createdAt'])); ?>
                                <?php if (!empty($user['updatedAt']) && $user['updatedAt'] !== $user['createdAt']): ?>
                                    • Updated: <?php echo date('M j, Y', strtotime($user['updatedAt'])); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="user-actions">
                            <button class="btn-icon" onclick="editUser('<?php echo $user['id']; ?>')" title="Edit">
                                ✏️
                            </button>
                            <button class="btn-icon" onclick="deleteUser('<?php echo $user['id']; ?>')" title="Delete">
                                🗑️
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">👥</div>
                        <h3>No users yet</h3>
                        <p>Create users to give family members access to view photos</p>
                        <button class="btn btn-primary" onclick="openUserModal()">Add First User</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content glass-card" style="max-width: 500px;">
            <div class="modal-header">
                <h2 id="userModalTitle">Add User</h2>
                <button class="modal-close" onclick="closeUserModal()">&times;</button>
            </div>
            <form id="userForm" class="member-form">
                <input type="hidden" id="userId">
                
                <div class="form-group">
                    <label for="userName">Full Name *</label>
                    <input type="text" id="userName" class="glass-input" required placeholder="John Doe">
                </div>

                <div class="form-group">
                    <label for="userEmail">Email Address (User ID) *</label>
                    <input type="email" id="userEmail" class="glass-input" required placeholder="john@example.com">
                </div>

                <div class="form-group">
                    <label for="userRole">Role *</label>
                    <select id="userRole" class="glass-input" required>
                        <option value="member">Member (View Only)</option>
                        <option value="admin">Admin (Full Access)</option>
                    </select>
                    <p class="password-hint">Members can view clear photos and galleries. Admins can manage the entire tree.</p>
                </div>

                <div class="form-group">
                    <label for="userPassword">Password *</label>
                    <input type="password" id="userPassword" class="glass-input" required 
                           placeholder="Min 6 characters" minlength="6">
                    <p class="password-hint" id="passwordHint">Required for new users. User can change this later.</p>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let users = <?php echo json_encode($users); ?>;
        let editingUserId = null;

        function openUserModal(userId = null) {
            const modal = document.getElementById('userModal');
            const form = document.getElementById('userForm');
            const title = document.getElementById('userModalTitle');
            const passwordHint = document.getElementById('passwordHint');
            const passwordInput = document.getElementById('userPassword');
            
            editingUserId = userId;
            form.reset();
            
            if (userId) {
                title.textContent = 'Edit User';
                passwordInput.required = false;
                passwordHint.textContent = 'Leave blank to keep current password. User can change this later.';
                
                const user = users.find(u => u.id === userId);
                if (user) {
                    document.getElementById('userId').value = user.id;
                    document.getElementById('userName').value = user.name;
                    document.getElementById('userEmail').value = user.email;
                    document.getElementById('userRole').value = user.role;
                }
            } else {
                title.textContent = 'Add User';
                passwordInput.required = true;
                passwordHint.textContent = 'Required for new users. User can change this later.';
                document.getElementById('userId').value = '';
            }
            
            modal.classList.add('active');
        }

        function closeUserModal() {
            const modal = document.getElementById('userModal');
            modal.classList.remove('active');
            editingUserId = null;
        }

        function editUser(userId) {
            openUserModal(userId);
        }

        function deleteUser(userId) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                return;
            }
            
            fetch('../api/delete-user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId })
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
                alert('Error deleting user: ' + err.message);
            });
        }

        // Form submission
        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userData = {
                id: document.getElementById('userId').value || undefined,
                name: document.getElementById('userName').value,
                email: document.getElementById('userEmail').value,
                role: document.getElementById('userRole').value,
                password: document.getElementById('userPassword').value
            };
            
            fetch('../api/save-user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeUserModal();
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(err => {
                alert('Error saving user: ' + err.message);
            });
        });

        // Close modal on outside click
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserModal();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeUserModal();
            }
        });
    </script>
</body>
</html>
