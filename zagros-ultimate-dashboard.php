<?php
/**
 * Plugin Name: Zagros Ultimate Dashboard
 * Plugin URI: https://github.com/mhmdhosn821/my_analysis_dashboard_final
 * Description: A hybrid dashboard integrating Google Analytics 4 (native charts) and Microsoft Clarity (iframe) with a glassmorphism design system
 * Version: 1.0.0
 * Author: Zagros Team
 * Author URI: https://github.com/mhmdhosn821
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: zagros-ultimate-dashboard
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Google Service Account Authentication Class
 * Handles JWT generation and signing without external libraries
 */
class Zagros_Google_Auth {
    private $service_account_json;
    
    public function __construct($service_account_json) {
        $this->service_account_json = $service_account_json;
    }
    
    /**
     * Generate JWT token for Google Service Account
     */
    private function create_jwt($scope) {
        $service_account = json_decode($this->service_account_json, true);
        
        if (!$service_account || !isset($service_account['private_key'], $service_account['client_email'])) {
            throw new Exception('Invalid service account JSON');
        }
        
        $now = time();
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
        
        $claim_set = [
            'iss' => $service_account['client_email'],
            'scope' => $scope,
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ];
        
        $header_encoded = $this->base64url_encode(json_encode($header));
        $claim_set_encoded = $this->base64url_encode(json_encode($claim_set));
        
        $signature_input = $header_encoded . '.' . $claim_set_encoded;
        
        // Sign with private key
        $private_key = openssl_pkey_get_private($service_account['private_key']);
        if (!$private_key) {
            throw new Exception('Failed to load private key');
        }
        
        openssl_sign($signature_input, $signature, $private_key, OPENSSL_ALGO_SHA256);
        openssl_free_key($private_key);
        
        $signature_encoded = $this->base64url_encode($signature);
        
        return $signature_input . '.' . $signature_encoded;
    }
    
    /**
     * Base64 URL encoding
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Get access token from Google
     */
    public function get_access_token() {
        $scope = 'https://www.googleapis.com/auth/analytics.readonly';
        
        try {
            $jwt = $this->create_jwt($scope);
            
            $response = wp_remote_post('https://oauth2.googleapis.com/token', [
                'body' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt
                ],
                'timeout' => 30
            ]);
            
            if (is_wp_error($response)) {
                throw new Exception('Failed to get access token: ' . $response->get_error_message());
            }
            
            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            if (!isset($body['access_token'])) {
                throw new Exception('No access token in response');
            }
            
            return $body['access_token'];
        } catch (Exception $e) {
            error_log('Zagros Auth Error: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * Main Plugin Class
 */
class Zagros_Ultimate_Dashboard {
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Zagros Dashboard',
            'Zagros Dashboard',
            'manage_options',
            'zagros-dashboard',
            [$this, 'render_dashboard'],
            'dashicons-chart-area',
            3
        );
        
        add_submenu_page(
            'zagros-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'zagros-settings',
            [$this, 'render_settings']
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('zagros_settings', 'zagros_ga4_property_id', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ]);
        
        register_setting('zagros_settings', 'zagros_service_account_json', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_json'],
            'default' => ''
        ]);
        
        register_setting('zagros_settings', 'zagros_clarity_embed_url', [
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => ''
        ]);
    }
    
    /**
     * Sanitize JSON input
     */
    public function sanitize_json($input) {
        $decoded = json_decode($input, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $input;
        }
        add_settings_error('zagros_settings', 'invalid_json', 'Invalid JSON format');
        return get_option('zagros_service_account_json');
    }
    
    /**
     * Enqueue assets
     */
    public function enqueue_assets($hook) {
        if (strpos($hook, 'zagros-') !== false) {
            // Enqueue Vazir font
            wp_enqueue_style('vazir-font', 'https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css');
            
            // Enqueue Chart.js
            wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [], null, true);
            
            // Custom styles
            wp_add_inline_style('vazir-font', $this->get_custom_css());
        }
    }
    
    /**
     * Get custom CSS
     */
    private function get_custom_css() {
        return "
            .zagros-dashboard {
                font-family: 'Vazir', sans-serif;
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                min-height: 100vh;
                padding: 20px;
                margin-left: -20px;
                margin-right: -20px;
            }
            
            .zagros-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
                max-width: 1400px;
                margin: 0 auto;
            }
            
            .zagros-glass-card {
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 16px;
                padding: 24px;
                color: #fff;
                box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            
            .zagros-glass-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 48px 0 rgba(31, 38, 135, 0.5);
            }
            
            .zagros-metric-card {
                text-align: center;
            }
            
            .zagros-metric-value {
                font-size: 48px;
                font-weight: bold;
                margin: 16px 0;
                background: linear-gradient(45deg, #00d2ff, #3a7bd5);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            
            .zagros-metric-label {
                font-size: 14px;
                color: rgba(255, 255, 255, 0.7);
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            .zagros-chart-container {
                grid-column: span 3;
                min-height: 400px;
            }
            
            .zagros-clarity-container {
                grid-column: span 3;
                min-height: 800px;
            }
            
            .zagros-clarity-container iframe {
                width: 100%;
                height: 800px;
                border: none;
                border-radius: 12px;
            }
            
            .zagros-pulsing-dot {
                display: inline-block;
                width: 12px;
                height: 12px;
                background: #00ff00;
                border-radius: 50%;
                margin-right: 8px;
                animation: pulse 2s infinite;
                box-shadow: 0 0 10px #00ff00;
            }
            
            @keyframes pulse {
                0%, 100% {
                    opacity: 1;
                    transform: scale(1);
                }
                50% {
                    opacity: 0.5;
                    transform: scale(1.2);
                }
            }
            
            .zagros-realtime-label {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .zagros-error {
                background: rgba(255, 59, 48, 0.1);
                border: 1px solid rgba(255, 59, 48, 0.3);
                border-radius: 12px;
                padding: 20px;
                color: #ff3b30;
                text-align: center;
                margin: 20px 0;
            }
            
            .zagros-settings {
                max-width: 800px;
                margin: 20px auto;
                background: #fff;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            .zagros-settings h1 {
                color: #1a1a2e;
                margin-bottom: 30px;
            }
            
            .zagros-settings label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #333;
            }
            
            .zagros-settings input[type='text'],
            .zagros-settings textarea {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                margin-bottom: 20px;
                font-family: monospace;
            }
            
            .zagros-settings textarea {
                min-height: 200px;
                font-size: 12px;
            }
            
            .zagros-settings .submit {
                margin-top: 20px;
            }
            
            .zagros-loading {
                text-align: center;
                padding: 40px;
                color: rgba(255, 255, 255, 0.7);
            }
            
            .zagros-header {
                text-align: center;
                margin-bottom: 30px;
                color: #fff;
            }
            
            .zagros-header h1 {
                font-size: 36px;
                font-weight: bold;
                background: linear-gradient(45deg, #00d2ff, #3a7bd5);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                margin: 0;
            }
        ";
    }
    
    /**
     * Render settings page
     */
    public function render_settings() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        ?>
        <div class="wrap zagros-settings">
            <h1>Zagros Dashboard Settings</h1>
            
            <?php settings_errors('zagros_settings'); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('zagros_settings');
                do_settings_sections('zagros_settings');
                ?>
                
                <div>
                    <label for="zagros_ga4_property_id">GA4 Property ID</label>
                    <input type="text" 
                           id="zagros_ga4_property_id" 
                           name="zagros_ga4_property_id" 
                           value="<?php echo esc_attr(get_option('zagros_ga4_property_id')); ?>" 
                           placeholder="properties/123456789" />
                    <p class="description">Example: properties/123456789</p>
                </div>
                
                <div>
                    <label for="zagros_service_account_json">Service Account JSON</label>
                    <textarea id="zagros_service_account_json" 
                              name="zagros_service_account_json" 
                              placeholder='{"type": "service_account", "project_id": "...", ...}'><?php echo esc_textarea(get_option('zagros_service_account_json')); ?></textarea>
                    <p class="description">Paste your Google Cloud Service Account JSON key here</p>
                </div>
                
                <div>
                    <label for="zagros_clarity_embed_url">Microsoft Clarity Embed URL</label>
                    <input type="text" 
                           id="zagros_clarity_embed_url" 
                           name="zagros_clarity_embed_url" 
                           value="<?php echo esc_attr(get_option('zagros_clarity_embed_url')); ?>" 
                           placeholder="https://clarity.microsoft.com/projects/view/..." />
                    <p class="description">Your Microsoft Clarity shared dashboard URL</p>
                </div>
                
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render dashboard
     */
    public function render_dashboard() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $property_id = get_option('zagros_ga4_property_id');
        $service_account_json = get_option('zagros_service_account_json');
        $clarity_url = get_option('zagros_clarity_embed_url');
        
        if (empty($property_id) || empty($service_account_json)) {
            ?>
            <div class="zagros-dashboard">
                <div class="zagros-error">
                    <h2>⚙️ Configuration Required</h2>
                    <p>Please configure your GA4 Property ID and Service Account JSON in the <a href="<?php echo admin_url('admin.php?page=zagros-settings'); ?>" style="color: #fff; text-decoration: underline;">Settings</a> page.</p>
                </div>
            </div>
            <?php
            return;
        }
        
        // Fetch GA4 data
        $realtime_data = $this->get_realtime_data($property_id, $service_account_json);
        $historical_data = $this->get_historical_data($property_id, $service_account_json);
        
        ?>
        <div class="zagros-dashboard">
            <div class="zagros-header">
                <h1>📊 Zagros Ultimate Dashboard</h1>
            </div>
            
            <div class="zagros-grid">
                <!-- Active Users Card -->
                <div class="zagros-glass-card zagros-metric-card">
                    <div class="zagros-metric-label zagros-realtime-label">
                        <span class="zagros-pulsing-dot"></span>
                        Active Users (Realtime)
                    </div>
                    <div class="zagros-metric-value">
                        <?php echo $realtime_data['active_users'] ?? '—'; ?>
                    </div>
                </div>
                
                <!-- Total Users Card -->
                <div class="zagros-glass-card zagros-metric-card">
                    <div class="zagros-metric-label">Total Users (30 Days)</div>
                    <div class="zagros-metric-value">
                        <?php echo $historical_data['total_users'] ?? '—'; ?>
                    </div>
                </div>
                
                <!-- Total Sessions Card -->
                <div class="zagros-glass-card zagros-metric-card">
                    <div class="zagros-metric-label">Total Sessions (30 Days)</div>
                    <div class="zagros-metric-value">
                        <?php echo $historical_data['total_sessions'] ?? '—'; ?>
                    </div>
                </div>
                
                <!-- Chart Card -->
                <div class="zagros-glass-card zagros-chart-container">
                    <h3 style="margin-top: 0; color: rgba(255, 255, 255, 0.9);">📈 Daily Sessions (Last 30 Days)</h3>
                    <?php if (isset($historical_data['chart_data'])): ?>
                        <canvas id="zagros-chart"></canvas>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const ctx = document.getElementById('zagros-chart').getContext('2d');
                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: <?php echo json_encode($historical_data['chart_data']['labels']); ?>,
                                    datasets: [{
                                        label: 'Sessions',
                                        data: <?php echo json_encode($historical_data['chart_data']['data']); ?>,
                                        borderColor: '#00d2ff',
                                        backgroundColor: 'rgba(0, 210, 255, 0.1)',
                                        borderWidth: 3,
                                        fill: true,
                                        tension: 0.4,
                                        pointRadius: 4,
                                        pointHoverRadius: 6,
                                        pointBackgroundColor: '#00d2ff',
                                        pointBorderColor: '#fff',
                                        pointBorderWidth: 2
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            grid: {
                                                color: 'rgba(255, 255, 255, 0.1)'
                                            },
                                            ticks: {
                                                color: 'rgba(255, 255, 255, 0.7)'
                                            }
                                        },
                                        x: {
                                            grid: {
                                                color: 'rgba(255, 255, 255, 0.1)'
                                            },
                                            ticks: {
                                                color: 'rgba(255, 255, 255, 0.7)'
                                            }
                                        }
                                    }
                                }
                            });
                        });
                        </script>
                    <?php else: ?>
                        <div class="zagros-loading">No chart data available</div>
                    <?php endif; ?>
                </div>
                
                <!-- Clarity Card -->
                <?php if (!empty($clarity_url)): ?>
                <div class="zagros-glass-card zagros-clarity-container">
                    <h3 style="margin-top: 0; color: rgba(255, 255, 255, 0.9);">🔍 Microsoft Clarity Live Dashboard</h3>
                    <iframe src="<?php echo esc_url($clarity_url); ?>" 
                            sandbox="allow-scripts allow-forms allow-popups"
                            loading="lazy"></iframe>
                </div>
                <?php else: ?>
                <div class="zagros-glass-card zagros-clarity-container">
                    <h3 style="margin-top: 0; color: rgba(255, 255, 255, 0.9);">🔍 Microsoft Clarity Live Dashboard</h3>
                    <div class="zagros-loading">
                        <p>Please configure your Microsoft Clarity Embed URL in the <a href="<?php echo admin_url('admin.php?page=zagros-settings'); ?>" style="color: #00d2ff; text-decoration: underline;">Settings</a> page.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get realtime data from GA4
     */
    private function get_realtime_data($property_id, $service_account_json) {
        // Check cache
        $cache_key = 'zagros_realtime_' . md5($property_id);
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        try {
            $auth = new Zagros_Google_Auth($service_account_json);
            $access_token = $auth->get_access_token();
            
            if (!$access_token) {
                throw new Exception('Failed to get access token');
            }
            
            // Call GA4 Realtime API
            $url = "https://analyticsdata.googleapis.com/v1beta/{$property_id}:runRealtimeReport";
            
            $body = [
                'metrics' => [
                    ['name' => 'activeUsers']
                ]
            ];
            
            $response = wp_remote_post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($body),
                'timeout' => 30
            ]);
            
            if (is_wp_error($response)) {
                throw new Exception('API request failed: ' . $response->get_error_message());
            }
            
            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            $active_users = 0;
            if (isset($data['rows'][0]['metricValues'][0]['value'])) {
                $active_users = intval($data['rows'][0]['metricValues'][0]['value']);
            }
            
            $result = ['active_users' => $active_users];
            
            // Cache for 2 minutes
            set_transient($cache_key, $result, 120);
            
            return $result;
            
        } catch (Exception $e) {
            error_log('Zagros Realtime Error: ' . $e->getMessage());
            return ['active_users' => '—'];
        }
    }
    
    /**
     * Get historical data from GA4
     */
    private function get_historical_data($property_id, $service_account_json) {
        // Check cache
        $cache_key = 'zagros_historical_' . md5($property_id);
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        try {
            $auth = new Zagros_Google_Auth($service_account_json);
            $access_token = $auth->get_access_token();
            
            if (!$access_token) {
                throw new Exception('Failed to get access token');
            }
            
            // Call GA4 Data API
            $url = "https://analyticsdata.googleapis.com/v1beta/{$property_id}:runReport";
            
            $body = [
                'dateRanges' => [
                    [
                        'startDate' => '30daysAgo',
                        'endDate' => 'today'
                    ]
                ],
                'dimensions' => [
                    ['name' => 'date']
                ],
                'metrics' => [
                    ['name' => 'sessions'],
                    ['name' => 'totalUsers']
                ],
                'orderBys' => [
                    [
                        'dimension' => [
                            'dimensionName' => 'date'
                        ]
                    ]
                ]
            ];
            
            $response = wp_remote_post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($body),
                'timeout' => 30
            ]);
            
            if (is_wp_error($response)) {
                throw new Exception('API request failed: ' . $response->get_error_message());
            }
            
            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            $total_sessions = 0;
            $total_users = 0;
            $chart_labels = [];
            $chart_data = [];
            
            if (isset($data['rows'])) {
                foreach ($data['rows'] as $row) {
                    $date = $row['dimensionValues'][0]['value'];
                    $sessions = intval($row['metricValues'][0]['value']);
                    $users = intval($row['metricValues'][1]['value']);
                    
                    $total_sessions += $sessions;
                    $total_users += $users;
                    
                    // Format date for chart (YYYYMMDD -> MMM DD)
                    $date_obj = DateTime::createFromFormat('Ymd', $date, wp_timezone());
                    $formatted_date = $date_obj ? $date_obj->format('M d') : date('M d', strtotime($date));
                    $chart_labels[] = $formatted_date;
                    $chart_data[] = $sessions;
                }
            }
            
            $result = [
                'total_sessions' => number_format_i18n($total_sessions),
                'total_users' => number_format_i18n($total_users),
                'chart_data' => [
                    'labels' => $chart_labels,
                    'data' => $chart_data
                ]
            ];
            
            // Cache for 1 hour
            set_transient($cache_key, $result, 3600);
            
            return $result;
            
        } catch (Exception $e) {
            error_log('Zagros Historical Error: ' . $e->getMessage());
            return [
                'total_sessions' => '—',
                'total_users' => '—'
            ];
        }
    }
}

// Initialize the plugin
function zagros_ultimate_dashboard_init() {
    Zagros_Ultimate_Dashboard::get_instance();
}
add_action('plugins_loaded', 'zagros_ultimate_dashboard_init');
