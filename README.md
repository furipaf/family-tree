# Family Tree Application

A beautiful, privacy-focused family tree web application built with PHP and modern web technologies. Perfect for preserving your family heritage and sharing memories across generations.

![Family Tree Screenshot](assets/images/screenshot.png)

## Features

### For Everyone
- **Beautiful Visual Tree** - Interactive hierarchical display with relationship lines
- **Privacy Protection** - Photos blurred for public visitors, clear for members
- **Photo Galleries** - Each family member can have their own photo collection
- **Mobile Responsive** - Works perfectly on desktop, tablet, and mobile
- **Text View & Print** - Simple text format for easy reading and printing

### For Family Members
- **Secure Login** - Password-protected member accounts
- **Clear Photos** - View all family photos without blur effect
- **Search Function** - Find family members quickly by name
- **Profile Management** - Update your own information and password

### For Administrators
- **Member Management** - Add, edit, and delete family members
- **User Management** - Create accounts and assign roles (Member/Admin)
- **Photo Upload** - Drag-and-drop with face cropping for profile pictures
- **Gallery Management** - Upload multiple photos per member
- **Activity Logs** - Complete audit trail of all actions
- **Settings Panel** - Configure WhatsApp, SMTP email, and site preferences
- **Multiple Spouses Support** - Handle complex family structures with mother/father tracking

## Technology Stack

- **Backend:** PHP 7.4+ (No database required - uses JSON files)
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Styling:** CasaOS-inspired dark theme with glass-morphism effects
- **Storage:** JSON files for data, filesystem for photos
- **Hosting:** Optimized for InfinityFree and similar shared hosting

## Installation

### Requirements
- PHP 7.4 or higher
- Apache/Nginx web server
- Write permissions for `data/` and `uploads/` directories

### Quick Setup

1. **Upload files** to your web server
2. **Set permissions:**
   ```bash
   chmod 755 data/
   chmod 755 uploads/
   chmod 644 data/.htaccess
   ```
3. **Access admin panel:**
   - URL1: `https://family-tree.ct.ws/admin/login.php`
   - URL2: `https://family-tree.rf.gd/admin/login.php`
   - Default username: `admin`
   - Default password: `admin123`

4. **Change default password** immediately after first login!

### Configuration

#### WhatsApp Integration
1. Go to Admin → Settings
2. Enter your WhatsApp number (with country code, e.g., `923001234567`)
3. Set default message for access requests

#### Email (SMTP) Setup
1. Enable "Email Sending" in Settings
2. Choose provider: Gmail, Zoho, or Custom
3. Enter SMTP credentials:
   - For Gmail: Use App Password (not regular password)
   - For Zoho: Use your Zoho Mail credentials
4. Send test email to verify

## Usage Guide

### Adding Family Members

1. Login to Admin Dashboard
2. Click "Add Member"
3. Fill in details:
   - **Father/Mother:** Select from existing members
   - **Spouse:** Link married partners
   - **Photos:** Upload profile picture and gallery
4. Save - member appears instantly in the tree

### Managing Multiple Wives

Our unique feature supports polygamous family structures:

1. Add the husband as a member
2. Add each wife as separate members
3. Link wives as spouses to the husband
4. When adding children, specify **both** father AND mother
5. Children display under father with mother tracked for lineage

### Password Reset Options

Members can reset passwords via:
- **Email:** Automated reset link sent to registered email
- **WhatsApp:** QR code to message admin for manual reset

### Print Family Tree

1. Click "Print" button on tree view
2. Automatically switches to text format
3. Clean black & white output perfect for sharing

## File Structure

```
Family-Tree/
├── admin/              # Admin panel files
├── api/                # API endpoints (AJAX)
├── assets/
│   ├── css/           # Stylesheets
│   ├── images/        # Static images
│   ├── js/            # JavaScript files
│   └── uploads/       # Member photos
├── data/              # JSON data storage
│   ├── members.json
│   ├── users.json
│   └── settings.json
├── includes/          # PHP functions & config
├── index.php          # Landing page with login
├── tree.php           # Main family tree view
├── forgot-password.php
├── reset-password.php
└── help.php           # Documentation
```

## Security Features

- **Password Hashing** - All passwords hashed with bcrypt
- **CSRF Protection** - Tokens on all forms
- **Session Management** - Secure PHP sessions
- **File Protection** - .htaccess blocks direct access to data
- **Activity Logging** - Every action recorded with IP and timestamp
- **Privacy Controls** - Role-based photo visibility

## Customization

### Change Colors
Edit `assets/css/style.css` - CSS variables at the top:
```css
:root {
    --accent-cyan: #00b2ff;    /* Primary color */
    --bg-dark: #070a17;         /* Background */
    /* ... more variables */
}
```

### Add Languages
The application is translation-ready. Copy `includes/lang/en.php` and translate all strings.

## Troubleshooting

### Photos Not Uploading
- Check `uploads/` directory permissions (755)
- Verify PHP `upload_max_filesize` limit
- Ensure GD extension is enabled

### Emails Not Sending
- Verify SMTP settings in Admin → Settings
- For Gmail: Use App Password, not regular password
- Check spam folders
- Review `data/email_log.txt` for errors

### Tree Not Loading
- Check browser console for JavaScript errors
- Verify `data/members.json` is valid JSON
- Ensure `api/` directory is accessible (not blocked by .htaccess)

## Support

- **Help Documentation:** `https://family-tree.ct.ws/help.php`
- **Contact Admin:** Click WhatsApp button on any page
- **Video Tutorial:** [YouTube Link]

## License

This project is open source. Feel free to use and modify for your family.

Contact me for source code: https://furipaf.github.io/family-tree/

## Credits

- Design inspired by CasaOS
- Icons: Emoji and custom SVG
- Fonts: Roboto (Google Fonts)

---

**Preserve your family legacy for generations to come!** 🌳
