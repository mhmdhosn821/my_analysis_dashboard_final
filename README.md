# Zagros Ultimate Dashboard - WordPress Plugin

A powerful hybrid analytics dashboard plugin that integrates **Google Analytics 4** (with native charts) and **Microsoft Clarity** (via iframe) into a beautiful glassmorphism-themed WordPress admin interface.

## 🌟 Features

### Google Analytics 4 Integration
- **Native Chart Rendering**: Real-time data visualization using Chart.js (no iframes)
- **Server-to-Server Authentication**: Custom Google Service Account implementation using pure PHP (no external libraries)
- **Realtime Metrics**: Live active users with pulsing animation
- **Historical Data**: 30-day sessions and user statistics
- **Smart Caching**: 1-hour cache for historical data, 2-minute cache for realtime data

### Microsoft Clarity Integration
- **Embedded Dashboard**: Seamlessly integrated Clarity dashboard via secure iframe
- **Consistent Design**: Wrapped in the same glassmorphism design system
- **Security**: Sandboxed iframe with proper permissions

### Design System
- **Glassmorphism Theme**: Modern dark mode with glass-effect cards
- **Vazir Font**: Beautiful Persian/Arabic-compatible typography via CDN
- **Responsive Grid Layout**: CSS Grid-based responsive design
- **Smooth Animations**: Hover effects, pulsing indicators, and transitions
- **Neon Gradient Accents**: Eye-catching blue gradient colors

## 📦 Installation

1. **Upload the Plugin**:
   - Copy `zagros-ultimate-dashboard.php` to `/wp-content/plugins/` directory
   - Or upload via WordPress admin: Plugins → Add New → Upload Plugin

2. **Activate**:
   - Go to Plugins page in WordPress admin
   - Activate "Zagros Ultimate Dashboard"

3. **Configure**:
   - Navigate to "Zagros Dashboard" → "Settings" in admin menu
   - Fill in the required credentials (see Configuration section below)

## ⚙️ Configuration

### Google Analytics 4 Setup

1. **Create a Google Cloud Project**:
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create a new project or select an existing one

2. **Enable GA4 Data API**:
   - Navigate to "APIs & Services" → "Library"
   - Search for "Google Analytics Data API"
   - Click "Enable"

3. **Create Service Account**:
   - Go to "IAM & Admin" → "Service Accounts"
   - Click "Create Service Account"
   - Give it a name (e.g., "zagros-dashboard")
   - Grant it appropriate permissions
   - Click "Create Key" → Choose "JSON"
   - Download the JSON key file

4. **Grant Service Account Access to GA4**:
   - Go to [Google Analytics](https://analytics.google.com/)
   - Select your GA4 property
   - Go to Admin → Property → Property Access Management
   - Add the service account email as a "Viewer"

5. **Get Property ID**:
   - In GA4 Admin → Property → Property Details
   - Copy the Property ID (format: `properties/123456789`)

6. **Configure in WordPress**:
   - Paste the Property ID in "GA4 Property ID" field
   - Paste the entire JSON key content in "Service Account JSON" field

### Microsoft Clarity Setup

1. **Get Clarity Project**:
   - Go to [Microsoft Clarity](https://clarity.microsoft.com/)
   - Select your project
   - Click "Share" or get the dashboard URL
   - Copy the full URL

2. **Configure in WordPress**:
   - Paste the URL in "Microsoft Clarity Embed URL" field

## 🎨 Dashboard Layout

The dashboard is organized in a responsive grid:

### Top Row (3 Cards):
1. **Active Users (Realtime)**: Live user count with pulsing green indicator
2. **Total Users (30 Days)**: Cumulative unique users
3. **Total Sessions (30 Days)**: Total session count

### Middle Row (1 Large Card):
- **Daily Sessions Chart**: Interactive line chart showing session trends over 30 days

### Bottom Row (1 Large Card):
- **Microsoft Clarity Dashboard**: Embedded live Clarity analytics

## 🔒 Security Features

- **Secure Authentication**: OpenSSL-based JWT signing for Google Service Account
- **Input Sanitization**: All inputs are sanitized and validated
- **Sandboxed Iframes**: Clarity iframe uses proper sandbox attributes
- **Transient Caching**: No sensitive data stored in database permanently
- **Capability Checks**: Only administrators can access the dashboard

## 🚀 Performance Optimization

- **Smart Caching Strategy**:
  - Realtime data: 2-minute cache
  - Historical data: 1-hour cache
  - Prevents API quota exhaustion
- **Lazy Loading**: Clarity iframe loads lazily
- **CDN Assets**: External resources loaded from reliable CDNs

## 🛠️ Technical Implementation

### Pure PHP Google Authentication
The plugin implements Google Service Account authentication without any external libraries:
- JWT generation and signing using OpenSSL
- Base64 URL encoding
- Direct API calls using WordPress HTTP API

### API Endpoints Used
- **GA4 Realtime API**: `analyticsdata.googleapis.com/v1beta/{property}:runRealtimeReport`
- **GA4 Data API**: `analyticsdata.googleapis.com/v1beta/{property}:runReport`
- **Google OAuth**: `oauth2.googleapis.com/token`

### Error Handling
- Graceful degradation when APIs fail
- User-friendly error messages
- Detailed error logging for debugging

## 📋 Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **PHP Extensions**: 
  - OpenSSL (for JWT signing)
  - JSON (usually enabled by default)
- **WordPress Functions**: 
  - `wp_remote_post()` for API calls
  - `set_transient()` / `get_transient()` for caching

## 🎯 Use Cases

Perfect for:
- WordPress site owners who want analytics in their admin panel
- Agencies managing multiple client sites
- Content creators tracking their site performance
- E-commerce sites monitoring traffic and user behavior

## 🐛 Troubleshooting

### "Configuration Required" Message
- Ensure GA4 Property ID is entered correctly (format: `properties/123456789`)
- Verify Service Account JSON is valid JSON format
- Check that the JSON contains `private_key` and `client_email` fields

### No Data Showing
- Verify the service account has "Viewer" access to your GA4 property
- Check that the Property ID matches your GA4 property
- Wait a few minutes for data to populate
- Check WordPress error logs for detailed error messages

### Clarity Not Loading
- Verify the Clarity URL is correct and accessible
- Check browser console for iframe errors
- Ensure the Clarity project allows embedding

## 📝 License

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html

## 🤝 Contributing

This is an open-source project. Feel free to submit issues or pull requests.

## 📧 Support

For issues and questions, please use the GitHub issue tracker.

---

**Developed by Zagros Team** 🏔️