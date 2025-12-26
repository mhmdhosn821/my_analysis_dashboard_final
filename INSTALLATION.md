# Installation & Testing Guide

## 🚀 Quick Installation

### Option 1: Direct Upload
1. Download `zagros-ultimate-dashboard.php` from this repository
2. Upload to `/wp-content/plugins/` directory on your WordPress site
3. Go to WordPress Admin → Plugins
4. Activate "Zagros Ultimate Dashboard"

### Option 2: Plugin Folder (Recommended for development)
```bash
# Create plugin directory
mkdir -p /path/to/wordpress/wp-content/plugins/zagros-ultimate-dashboard

# Copy the file
cp zagros-ultimate-dashboard.php /path/to/wordpress/wp-content/plugins/zagros-ultimate-dashboard/

# Set proper permissions
chmod 644 /path/to/wordpress/wp-content/plugins/zagros-ultimate-dashboard/zagros-ultimate-dashboard.php
```

## ⚙️ Configuration Steps

### 1. Google Analytics 4 Setup

#### Step 1: Create Google Cloud Project
```bash
# Visit: https://console.cloud.google.com/
# Click "New Project"
# Name: "Zagros Dashboard" or similar
```

#### Step 2: Enable GA4 Data API
```bash
# In Google Cloud Console:
# APIs & Services → Library
# Search: "Google Analytics Data API"
# Click "Enable"
```

#### Step 3: Create Service Account
1. Go to: IAM & Admin → Service Accounts
2. Click "Create Service Account"
3. Name: `zagros-dashboard-sa`
4. Click "Create and Continue"
5. Skip optional steps
6. Click "Done"

#### Step 4: Generate JSON Key
1. Click on the created service account
2. Go to "Keys" tab
3. Click "Add Key" → "Create new key"
4. Choose "JSON"
5. Download the JSON file

#### Step 5: Grant Access to GA4
1. Go to: https://analytics.google.com/
2. Admin → Property → Property Access Management
3. Click "+" to add user
4. Enter the service account email (from JSON: `client_email`)
5. Assign role: "Viewer"
6. Click "Add"

#### Step 6: Get Property ID
1. In GA4: Admin → Property → Property Details
2. Copy the Property ID
3. Format: `properties/123456789`

### 2. Microsoft Clarity Setup

#### Step 1: Create Clarity Project
```bash
# Visit: https://clarity.microsoft.com/
# Sign in with Microsoft account
# Click "Add new project"
# Enter your website URL
# Copy the tracking code (optional - if you want to install Clarity tracking)
```

#### Step 2: Get Dashboard URL
1. Open your Clarity project
2. Click "Share" or copy the browser URL
3. Format: `https://clarity.microsoft.com/projects/view/[project-id]/dashboard`

### 3. WordPress Configuration

#### Step 1: Access Settings
1. Log in to WordPress Admin
2. Navigate to: Zagros Dashboard → Settings

#### Step 2: Configure GA4
1. **GA4 Property ID**: Paste your property ID
   - Example: `properties/123456789`
2. **Service Account JSON**: Paste the entire JSON content
   - Open the downloaded JSON file
   - Copy all content (including the curly braces)
   - Paste into the textarea

#### Step 3: Configure Clarity
1. **Clarity Embed URL**: Paste your Clarity dashboard URL
   - Example: `https://clarity.microsoft.com/projects/view/abc123/dashboard`

#### Step 4: Save Settings
Click "Save Settings" button

## 🧪 Testing Checklist

### Phase 1: Installation Verification
- [ ] Plugin file uploaded to correct location
- [ ] Plugin appears in WordPress Plugins list
- [ ] Plugin activates without errors
- [ ] No PHP errors in error log
- [ ] Menu item "Zagros Dashboard" appears in admin sidebar

### Phase 2: Settings Page
- [ ] Settings page loads without errors
- [ ] All three input fields are visible
- [ ] Save Settings button works
- [ ] Settings persist after save
- [ ] JSON validation works (try invalid JSON)

### Phase 3: Dashboard - Without Configuration
- [ ] Dashboard page loads
- [ ] Shows "Configuration Required" message
- [ ] Link to settings page works

### Phase 4: Dashboard - With Configuration
- [ ] Dashboard loads with data
- [ ] Three metric cards display correctly
- [ ] Realtime users shows with pulsing dot
- [ ] Chart renders with data
- [ ] Clarity iframe loads (if URL provided)
- [ ] Glass effect styles apply correctly

### Phase 5: Data Verification
- [ ] Active users count updates (check after 2+ minutes)
- [ ] Historical data matches GA4 interface
- [ ] Chart shows 30 days of data
- [ ] Chart dates are formatted correctly
- [ ] Numbers use proper formatting

### Phase 6: Performance
- [ ] First load may take 3-5 seconds (API calls)
- [ ] Subsequent loads within 2 minutes are instant (cache)
- [ ] Page doesn't timeout or hang

### Phase 7: Error Handling
- [ ] Invalid JSON shows error message
- [ ] Invalid Property ID shows graceful error
- [ ] API failures don't crash the page
- [ ] Errors are logged (check debug.log)

## 🐛 Troubleshooting

### Problem: Plugin doesn't appear in plugins list
**Solution:**
```bash
# Check file permissions
ls -la /wp-content/plugins/zagros-ultimate-dashboard.php
# Should be: -rw-r--r--

# Fix if needed
chmod 644 /wp-content/plugins/zagros-ultimate-dashboard.php
```

### Problem: "Configuration Required" message persists
**Checklist:**
- [ ] GA4 Property ID is in format `properties/123456789`
- [ ] Service Account JSON is valid JSON (use jsonlint.com)
- [ ] JSON contains `private_key` and `client_email` fields
- [ ] Service account email is added to GA4 property

### Problem: No data showing (shows "—")
**Debug steps:**
```php
// Enable WordPress debug logging
// Add to wp-config.php:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Check error log
tail -f /wp-content/debug.log
```

**Common causes:**
1. Service account not added to GA4 property
2. Wrong property ID format
3. API not enabled in Google Cloud
4. Private key format issue in JSON

### Problem: Clarity iframe not loading
**Checklist:**
- [ ] URL is correct format
- [ ] Dashboard is set to "shared" in Clarity
- [ ] Browser allows iframes (check console)
- [ ] No CSP (Content Security Policy) blocking

### Problem: Chart not rendering
**Debug:**
1. Check browser console for JavaScript errors
2. Verify Chart.js loaded: `console.log(typeof Chart)`
3. Check if chart data exists: View page source, search for `chart_data`

### Problem: Styling looks broken
**Causes:**
- Theme CSS conflicts
- Admin color scheme interference

**Solution:**
```css
/* Add to theme's admin CSS if needed */
.zagros-dashboard * {
    box-sizing: border-box;
}
```

## 📊 Sample Data Format

### GA4 Service Account JSON (structure)
```json
{
  "type": "service_account",
  "project_id": "your-project-id",
  "private_key_id": "xxxxx",
  "private_key": "-----BEGIN PRIVATE KEY-----\nXXXXX\n-----END PRIVATE KEY-----\n",
  "client_email": "zagros-dashboard@your-project.iam.gserviceaccount.com",
  "client_id": "123456789",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/..."
}
```

### Expected Dashboard Response
```
Active Users: 42 (with pulsing green dot)
Total Users (30 Days): 12,345
Total Sessions (30 Days): 45,678
Chart: Line graph with 30 data points
Clarity: Loaded iframe
```

## 🔍 Verification Commands

### Check PHP Syntax
```bash
php -l zagros-ultimate-dashboard.php
# Expected: No syntax errors detected
```

### Check File Size
```bash
ls -lh zagros-ultimate-dashboard.php
# Expected: ~27KB
```

### Count Classes and Functions
```bash
grep -c "^class\|function" zagros-ultimate-dashboard.php
# Expected: 43+ (2 classes, multiple methods)
```

### Verify Security Headers
```bash
head -n 15 zagros-ultimate-dashboard.php | grep "ABSPATH"
# Expected: if (!defined('ABSPATH')) line present
```

## 🎯 Expected Behavior

### First Visit (No Cache)
1. User visits dashboard
2. Plugin authenticates with Google (JWT generation)
3. Plugin fetches realtime data (~1-2 seconds)
4. Plugin fetches historical data (~2-3 seconds)
5. Data cached for future use
6. Dashboard renders with data
**Total time: 3-5 seconds**

### Subsequent Visits (Within Cache Period)
1. User visits dashboard
2. Data retrieved from cache instantly
3. Dashboard renders immediately
**Total time: <1 second**

### After Cache Expires
- Realtime cache: After 2 minutes
- Historical cache: After 1 hour
- Process repeats like first visit

## 📝 Success Criteria

✅ Plugin installs without errors
✅ Settings save successfully
✅ Dashboard loads with all UI elements
✅ GA4 data fetches and displays correctly
✅ Clarity iframe embeds successfully
✅ Caching works (verified by quick reloads)
✅ No PHP errors in log
✅ No JavaScript errors in console
✅ Design looks professional and modern
✅ All animations work smoothly

## 🎓 Additional Resources

- [Google Analytics Data API Documentation](https://developers.google.com/analytics/devguides/reporting/data/v1)
- [WordPress Plugin Development](https://developer.wordpress.org/plugins/)
- [Microsoft Clarity Documentation](https://docs.microsoft.com/en-us/clarity/)
- [Chart.js Documentation](https://www.chartjs.org/docs/latest/)

## 💡 Pro Tips

1. **Testing Multiple Configurations**: Use different WordPress user roles to test access control
2. **Cache Debugging**: Set cache time to 10 seconds during development
3. **API Quota**: GA4 has 50,000 requests per day limit - caching is essential
4. **Backup JSON**: Keep a backup of your service account JSON securely
5. **Monitor Logs**: Watch debug.log during first setup to catch issues early
