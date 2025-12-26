# Zagros Ultimate Dashboard - Features Overview

## 🎯 Plugin Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                  ZAGROS ULTIMATE DASHBOARD                   │
│                    (Single PHP File)                         │
└─────────────────────────────────────────────────────────────┘
         │                           │
         │                           │
    ┌────▼────┐              ┌──────▼──────┐
    │  GA4    │              │  Clarity     │
    │  API    │              │  Iframe      │
    │ (Native)│              │  (Embed)     │
    └─────────┘              └──────────────┘
```

## 📐 UI Layout Structure

```
┌──────────────────────────────────────────────────────────────┐
│                   ZAGROS DASHBOARD HEADER                     │
│                    (Gradient Title)                           │
└──────────────────────────────────────────────────────────────┘

┌──────────────────┐  ┌──────────────────┐  ┌─────────────────┐
│  🟢 ACTIVE USERS │  │  👥 TOTAL USERS  │  │  📊 SESSIONS    │
│   (Realtime)     │  │   (30 Days)      │  │   (30 Days)     │
│                  │  │                  │  │                 │
│      1,234       │  │     45,678       │  │     123,456     │
└──────────────────┘  └──────────────────┘  └─────────────────┘
              ↑ Glass Cards with Blur Effect ↑

┌──────────────────────────────────────────────────────────────┐
│              📈 DAILY SESSIONS CHART                          │
│                                                               │
│  [Chart.js Line Chart - Neon Blue Theme]                     │
│   ╱‾╲                                                         │
│  ╱   ╲╱╲                                                      │
│ ╱        ╲╱╲                                                  │
│╱            ╲                                                 │
│                                                               │
└──────────────────────────────────────────────────────────────┘
              ↑ Large Glass Card with Chart ↑

┌──────────────────────────────────────────────────────────────┐
│         🔍 MICROSOFT CLARITY LIVE DASHBOARD                   │
│                                                               │
│  ┌────────────────────────────────────────────────────────┐  │
│  │                                                        │  │
│  │          [Clarity Iframe - 800px height]              │  │
│  │                                                        │  │
│  │                                                        │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
└──────────────────────────────────────────────────────────────┘
              ↑ Large Glass Card with Iframe ↑
```

## 🎨 Design System

### Color Palette
- **Background**: Linear gradient from `#1a1a2e` to `#16213e`
- **Glass Cards**: `rgba(255, 255, 255, 0.05)` with `backdrop-filter: blur(10px)`
- **Accent Colors**: Neon blue gradient `#00d2ff` to `#3a7bd5`
- **Text**: White with varying opacity (100%, 70%, 50%)
- **Borders**: `rgba(255, 255, 255, 0.1)`

### Typography
- **Font Family**: Vazir (Persian/Arabic compatible)
- **Headers**: 36px bold with gradient
- **Metrics**: 48px bold with gradient clip
- **Labels**: 14px uppercase with letter-spacing

### Effects
- **Glass Morphism**: Frosted glass effect with blur
- **Hover Animation**: Translate Y(-5px) with enhanced shadow
- **Pulsing Dot**: Green dot with pulse animation (realtime indicator)
- **Box Shadows**: Layered shadows for depth

## 🔧 Technical Components

### 1. Google Service Account Authentication
```php
class Zagros_Google_Auth {
    - create_jwt()           // Generate JWT with RS256
    - base64url_encode()     // URL-safe base64 encoding
    - get_access_token()     // Exchange JWT for access token
}
```

### 2. Main Plugin Class
```php
class Zagros_Ultimate_Dashboard {
    - add_admin_menu()           // Register menu items
    - register_settings()        // Register options
    - enqueue_assets()           // Load CSS/JS
    - render_dashboard()         // Main dashboard UI
    - render_settings()          // Settings page
    - get_realtime_data()        // Fetch GA4 realtime
    - get_historical_data()      // Fetch GA4 historical
}
```

### 3. API Endpoints

#### GA4 Realtime API
```
POST https://analyticsdata.googleapis.com/v1beta/{property}:runRealtimeReport
Metrics: activeUsers
Cache: 2 minutes
```

#### GA4 Data API
```
POST https://analyticsdata.googleapis.com/v1beta/{property}:runReport
Dimensions: date
Metrics: sessions, totalUsers
Date Range: 30daysAgo to today
Cache: 1 hour
```

## 📊 Data Flow

```
┌──────────────┐
│ WordPress    │
│ Admin Panel  │
└──────┬───────┘
       │
       ▼
┌──────────────┐      ┌──────────────┐      ┌──────────────┐
│   Settings   │─────▶│  Transient   │─────▶│  Dashboard   │
│    Page      │      │   Cache      │      │     UI       │
└──────────────┘      └──────┬───────┘      └──────────────┘
                             │
                      Cache Miss?
                             │
                             ▼
                      ┌──────────────┐
                      │ Google Auth  │
                      │ (JWT Sign)   │
                      └──────┬───────┘
                             │
                             ▼
                      ┌──────────────┐
                      │  GA4 API     │
                      │  Request     │
                      └──────────────┘
```

## 🔒 Security Features

### Input Sanitization
- `sanitize_text_field()` for Property ID
- JSON validation for Service Account
- `esc_url_raw()` for Clarity URL

### Output Escaping
- `esc_attr()` for HTML attributes
- `esc_url()` for iframe src
- `esc_textarea()` for textarea content

### Capability Checks
- `current_user_can('manage_options')` for all admin pages
- Only administrators can access dashboard and settings

### Iframe Sandbox
- `allow-scripts`: Required for Clarity functionality
- `allow-forms`: For Clarity interactions
- `allow-popups`: For Clarity features
- `allow-same-origin`: REMOVED for enhanced security

### Data Protection
- Transient cache (not permanent storage)
- No sensitive data in frontend JavaScript
- Error logging only (no sensitive data display)

## 🚀 Performance Features

### Caching Strategy
```php
// Realtime data: 2 minutes
set_transient('zagros_realtime_xxx', $data, 120);

// Historical data: 1 hour
set_transient('zagros_historical_xxx', $data, 3600);
```

### Asset Loading
- CDN-based assets (Vazir font, Chart.js)
- Only load on Zagros pages (conditional enqueue)
- Lazy loading for iframe
- Inline CSS (no separate file request)

### API Optimization
- Single API call for all historical metrics
- Batch processing of date ranges
- Early return on cache hit

## 📦 File Structure

```
zagros-ultimate-dashboard.php (27KB)
├── Plugin Header
├── Security Check (!defined('ABSPATH'))
├── Zagros_Google_Auth Class
│   ├── Constructor
│   ├── create_jwt()
│   ├── base64url_encode()
│   └── get_access_token()
├── Zagros_Ultimate_Dashboard Class
│   ├── Constructor & Hooks
│   ├── Admin Menu Methods
│   ├── Settings Registration
│   ├── Asset Enqueuing
│   ├── CSS Styles (get_custom_css)
│   ├── Settings Page Renderer
│   ├── Dashboard Page Renderer
│   ├── GA4 Data Methods
│   │   ├── get_realtime_data()
│   │   └── get_historical_data()
│   └── Helper Methods
└── Initialization Hook
```

## 🎯 Use Cases

### 1. Website Owner
- View realtime visitors at a glance
- Monitor 30-day trends without leaving WordPress
- See heatmaps and session recordings via Clarity

### 2. Digital Marketer
- Quick access to key metrics
- Beautiful visualizations for client presentations
- Combined GA4 and Clarity insights in one place

### 3. Agency
- Manage multiple client analytics
- Professional dashboard design
- Reduced need to switch between tools

## 🌟 Key Differentiators

1. **Pure PHP Authentication**: No composer dependencies
2. **Single File**: Easy installation and maintenance
3. **Hybrid Approach**: Best of both worlds (API + Iframe)
4. **Modern Design**: Glassmorphism trend
5. **Smart Caching**: Optimized for performance
6. **Security First**: Proper sanitization and escaping
7. **Internationalization**: WordPress i18n functions

## 📈 Future Enhancement Ideas

- [ ] Add more GA4 metrics (bounce rate, engagement)
- [ ] Support for multiple properties
- [ ] Export data as CSV
- [ ] Email reports
- [ ] Custom date ranges
- [ ] Dark/Light theme toggle
- [ ] Widget support for dashboard
- [ ] REST API endpoints
- [ ] Multi-language support (translation ready)
- [ ] Custom color schemes
