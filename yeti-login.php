<?php
/**
 * Plugin Name: Yeti Login
 * Plugin URI:  https://github.com/Jyager31/yeti-login
 * Description: A fun, animated yeti character for the WordPress login page with dark styling and GSAP animations.
 * Version:     1.1.2
 * Author:      Josh Yager
 * Author URI:  https://thedevq.com/
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yeti-login
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'YETI_LOGIN_VERSION', '1.1.2' );
define( 'YETI_LOGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'YETI_LOGIN_PATH', plugin_dir_path( __FILE__ ) );


/* ==========================================================================
   Auto-updates from GitHub
   ========================================================================== */

require_once YETI_LOGIN_PATH . 'plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$yeti_login_updater = PucFactory::buildUpdateChecker(
    'https://github.com/Jyager31/yeti-login/',
    __FILE__,
    'yeti-login'
);
$yeti_login_updater->setBranch( 'master' );


/* ==========================================================================
   Login Page Hooks
   ========================================================================== */

/**
 * Enqueue login page assets.
 */
function yeti_login_enqueue_scripts() {
    wp_enqueue_style( 'yeti-login', YETI_LOGIN_URL . 'assets/css/style-login.css', array(), YETI_LOGIN_VERSION );
    wp_enqueue_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', array(), '3.12.5', true );
    wp_enqueue_script( 'yeti-login-js', YETI_LOGIN_URL . 'assets/js/login-yeti.js', array( 'gsap' ), YETI_LOGIN_VERSION, true );
}
add_action( 'login_enqueue_scripts', 'yeti_login_enqueue_scripts' );


/**
 * Dequeue any theme login stylesheets that would conflict.
 * Runs late (priority 99) so theme styles are already registered.
 */
function yeti_login_dequeue_theme_styles() {
    global $wp_styles;

    if ( empty( $wp_styles->registered ) ) {
        return;
    }

    $theme_dir = get_template_directory_uri();
    $child_dir = get_stylesheet_directory_uri();

    foreach ( $wp_styles->registered as $handle => $style ) {
        // Skip our own stylesheet.
        if ( $handle === 'yeti-login' ) {
            continue;
        }

        $src = $style->src;

        // Dequeue any stylesheet from the theme that has "login" in the filename.
        if ( ( strpos( $src, $theme_dir ) !== false || strpos( $src, $child_dir ) !== false )
            && stripos( $src, 'login' ) !== false
        ) {
            wp_dequeue_style( $handle );
            wp_deregister_style( $handle );
        }
    }

    // Dequeue theme copies of login-yeti JS to avoid double-loading.
    global $wp_scripts;
    if ( ! empty( $wp_scripts->registered ) ) {
        foreach ( $wp_scripts->registered as $handle => $script ) {
            if ( $handle === 'yeti-login-js' ) {
                continue;
            }
            $src = $script->src;
            if ( ( strpos( $src, $theme_dir ) !== false || strpos( $src, $child_dir ) !== false )
                && stripos( $src, 'login-yeti' ) !== false
            ) {
                wp_dequeue_script( $handle );
                wp_deregister_script( $handle );
            }
        }
    }
}
add_action( 'login_enqueue_scripts', 'yeti_login_dequeue_theme_styles', 99 );


/**
 * Output CSS variable for background image in login head.
 */
function yeti_login_head() {
    $bg = get_option( 'yeti_login_bg' );
    if ( empty( $bg ) ) {
        $bg = YETI_LOGIN_URL . 'assets/images/black-brick.jpg';
    }
    ?>
    <style>
        body.login {
            --yeti-bg: url('<?php echo esc_url( $bg ); ?>');
        }
    </style>
    <?php
}
add_action( 'login_head', 'yeti_login_head' );


/**
 * Inject logo and yeti SVG above the login form.
 */
function yeti_login_message( $message ) {
    $logo_url  = get_option( 'yeti_login_logo' );
    $logo_link = get_option( 'yeti_login_logo_link' );

    if ( empty( $logo_link ) ) {
        $logo_link = home_url( '/' );
    }

    ob_start();

    if ( ! empty( $logo_url ) ) : ?>
        <div class="yeti-login-logo">
            <a href="<?php echo esc_url( $logo_link ); ?>" target="_blank" rel="noopener">
                <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
            </a>
        </div>
    <?php endif; ?>

    <div class="svgContainer">
        <div>
            <svg class="mySVG" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 200 200">
                <defs>
                    <circle id="armMaskPath" cx="100" cy="100" r="100"/>
                </defs>
                <clipPath id="armMask">
                    <use xlink:href="#armMaskPath" overflow="visible"/>
                </clipPath>
                <circle cx="100" cy="100" r="100" fill="#a9ddf3"/>
                <g class="body">
                    <path class="bodyBGnormal" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="#FFFFFF" d="M200,158.5c0-20.2-14.8-36.5-35-36.5h-14.9V72.8c0-27.4-21.7-50.4-49.1-50.8c-28-0.5-50.9,22.1-50.9,50v50H35.8C16,122,0,138,0,157.8L0,213h200L200,158.5z"/>
                    <path fill="#DDF1FA" d="M100,156.4c-22.9,0-43,11.1-54.1,27.7c15.6,10,34.2,15.9,54.1,15.9s38.5-5.8,54.1-15.9C143,167.5,122.9,156.4,100,156.4z"/>
                </g>
                <g class="earL">
                    <g class="outerEar" fill="#ddf1fa" stroke="#3a5e77" stroke-width="2.5">
                        <circle cx="47" cy="83" r="11.5"/>
                        <path d="M46.3 78.9c-2.3 0-4.1 1.9-4.1 4.1 0 2.3 1.9 4.1 4.1 4.1" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                    <g class="earHair">
                        <rect x="51" y="64" fill="#FFFFFF" width="15" height="35"/>
                        <path d="M53.4 62.8C48.5 67.4 45 72.2 42.8 77c3.4-.1 6.8-.1 10.1.1-4 3.7-6.8 7.6-8.2 11.6 2.1 0 4.2 0 6.3.2-2.6 4.1-3.8 8.3-3.7 12.5 1.2-.7 3.4-1.4 5.2-1.9" fill="#fff" stroke="#3a5e77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                </g>
                <g class="earR">
                    <g class="outerEar">
                        <circle fill="#DDF1FA" stroke="#3A5E77" stroke-width="2.5" cx="153" cy="83" r="11.5"/>
                        <path fill="#DDF1FA" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M153.7,78.9c2.3,0,4.1,1.9,4.1,4.1c0,2.3-1.9,4.1-4.1,4.1"/>
                    </g>
                    <g class="earHair">
                        <rect x="134" y="64" fill="#FFFFFF" width="15" height="35"/>
                        <path fill="#FFFFFF" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M146.6,62.8c4.9,4.6,8.4,9.4,10.6,14.2c-3.4-0.1-6.8-0.1-10.1,0.1c4,3.7,6.8,7.6,8.2,11.6c-2.1,0-4.2,0-6.3,0.2c2.6,4.1,3.8,8.3,3.7,12.5c-1.2-0.7-3.4-1.4-5.2-1.9"/>
                    </g>
                </g>
                <path class="chin" d="M84.1 121.6c2.7 2.9 6.1 5.4 9.8 7.5l.9-4.5c2.9 2.5 6.3 4.8 10.2 6.5 0-1.9-.1-3.9-.2-5.8 3 1.2 6.2 2 9.7 2.5-.3-2.1-.7-4.1-1.2-6.1" fill="none" stroke="#3a5e77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path class="face" fill="#DDF1FA" d="M134.5,46v35.5c0,21.815-15.446,39.5-34.5,39.5s-34.5-17.685-34.5-39.5V46"/>
                <path class="hair" fill="#FFFFFF" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M81.457,27.929c1.755-4.084,5.51-8.262,11.253-11.77c0.979,2.565,1.883,5.14,2.712,7.723c3.162-4.265,8.626-8.27,16.272-11.235c-0.737,3.293-1.588,6.573-2.554,9.837c4.857-2.116,11.049-3.64,18.428-4.156c-2.403,3.23-5.021,6.391-7.852,9.474"/>
                <g class="eyebrow">
                    <path fill="#FFFFFF" d="M138.142,55.064c-4.93,1.259-9.874,2.118-14.787,2.599c-0.336,3.341-0.776,6.689-1.322,10.037c-4.569-1.465-8.909-3.222-12.996-5.226c-0.98,3.075-2.07,6.137-3.267,9.179c-5.514-3.067-10.559-6.545-15.097-10.329c-1.806,2.889-3.745,5.73-5.816,8.515c-7.916-4.124-15.053-9.114-21.296-14.738l1.107-11.768h73.475V55.064z"/>
                    <path fill="#FFFFFF" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M63.56,55.102c6.243,5.624,13.38,10.614,21.296,14.738c2.071-2.785,4.01-5.626,5.816-8.515c4.537,3.785,9.583,7.263,15.097,10.329c1.197-3.043,2.287-6.104,3.267-9.179c4.087,2.004,8.427,3.761,12.996,5.226c0.545-3.348,0.986-6.696,1.322-10.037c4.913-0.481,9.857-1.34,14.787-2.599"/>
                </g>
                <g class="eyeL">
                    <circle cx="85.5" cy="78.5" r="3.5" fill="#3a5e77"/>
                    <circle cx="84" cy="76" r="1" fill="#fff"/>
                </g>
                <g class="eyeR">
                    <circle cx="114.5" cy="78.5" r="3.5" fill="#3a5e77"/>
                    <circle cx="113" cy="76" r="1" fill="#fff"/>
                </g>
                <g class="mouth">
                    <path class="mouthBG" fill="#617E92" d="M100.2,101c-0.4,0-1.4,0-1.8,0c-2.7-0.3-5.3-1.1-8-2.5c-0.7-0.3-0.9-1.2-0.6-1.8c0.2-0.5,0.7-0.7,1.2-0.7c0.2,0,0.5,0.1,0.6,0.2c3,1.5,5.8,2.3,8.6,2.3s5.7-0.7,8.6-2.3c0.2-0.1,0.4-0.2,0.6-0.2c0.5,0,1,0.3,1.2,0.7c0.4,0.7,0.1,1.5-0.6,1.9c-2.6,1.4-5.3,2.2-7.9,2.5C101.7,101,100.5,101,100.2,101z"/>
                    <path class="mouthOutline" fill="none" stroke="#3A5E77" stroke-width="2.5" stroke-linejoin="round" d="M100.2,101c-0.4,0-1.4,0-1.8,0c-2.7-0.3-5.3-1.1-8-2.5c-0.7-0.3-0.9-1.2-0.6-1.8c0.2-0.5,0.7-0.7,1.2-0.7c0.2,0,0.5,0.1,0.6,0.2c3,1.5,5.8,2.3,8.6,2.3s5.7-0.7,8.6-2.3c0.2-0.1,0.4-0.2,0.6-0.2c0.5,0,1,0.3,1.2,0.7c0.4,0.7,0.1,1.5-0.6,1.9c-2.6,1.4-5.3,2.2-7.9,2.5C101.7,101,100.5,101,100.2,101z"/>
                </g>
                <path class="nose" d="M97.7 79.9h4.7c1.9 0 3 2.2 1.9 3.7l-2.3 3.3c-.9 1.3-2.9 1.3-3.8 0l-2.3-3.3c-1.3-1.6-.2-3.7 1.8-3.7z" fill="#3a5e77"/>
                <g class="arms" clip-path="url(#armMask)">
                    <g class="armL" style="visibility: hidden;">
                        <polygon fill="#DDF1FA" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" points="121.3,98.4 111,59.7 149.8,49.3 169.8,85.4"/>
                        <path fill="#DDF1FA" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" d="M134.4,53.5l19.3-5.2c2.7-0.7,5.4,0.9,6.1,3.5v0c0.7,2.7-0.9,5.4-3.5,6.1l-10.3,2.8"/>
                        <path fill="#DDF1FA" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" d="M150.9,59.4l26-7c2.7-0.7,5.4,0.9,6.1,3.5v0c0.7,2.7-0.9,5.4-3.5,6.1l-21.3,5.7"/>
                        <g class="twoFingers">
                            <path fill="#DDF1FA" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" d="M158.3,67.8l23.1-6.2c2.7-0.7,5.4,0.9,6.1,3.5v0c0.7,2.7-0.9,5.4-3.5,6.1l-23.1,6.2"/>
                            <path fill="#A9DDF3" d="M180.1,65l2.2-0.6c1.1-0.3,2.2,0.3,2.4,1.4v0c0.3,1.1-0.3,2.2-1.4,2.4l-2.2,0.6L180.1,65z"/>
                            <path fill="#DDF1FA" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" d="M160.8,77.5l19.4-5.2c2.7-0.7,5.4,0.9,6.1,3.5v0c0.7,2.7-0.9,5.4-3.5,6.1l-18.3,4.9"/>
                            <path fill="#A9DDF3" d="M178.8,75.7l2.2-0.6c1.1-0.3,2.2,0.3,2.4,1.4v0c0.3,1.1-0.3,2.2-1.4,2.4l-2.2,0.6L178.8,75.7z"/>
                        </g>
                        <path fill="#A9DDF3" d="M175.5,55.9l2.2-0.6c1.1-0.3,2.2,0.3,2.4,1.4v0c0.3,1.1-0.3,2.2-1.4,2.4l-2.2,0.6L175.5,55.9z"/>
                        <path fill="#A9DDF3" d="M152.1,50.4l2.2-0.6c1.1-0.3,2.2,0.3,2.4,1.4v0c0.3,1.1-0.3,2.2-1.4,2.4l-2.2,0.6L152.1,50.4z"/>
                        <path fill="#FFFFFF" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M123.5,97.8c-41.4,14.9-84.1,30.7-108.2,35.5L1.2,81c33.5-9.9,71.9-16.5,111.9-21.8"/>
                        <path fill="#FFFFFF" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M108.5,60.4c7.7-5.3,14.3-8.4,22.8-13.2c-2.4,5.3-4.7,10.3-6.7,15.1c4.3,0.3,8.4,0.7,12.3,1.3c-4.2,5-8.1,9.6-11.5,13.9c3.1,1.1,6,2.4,8.7,3.8c-1.4,2.9-2.7,5.8-3.9,8.5c2.5,3.5,4.6,7.2,6.3,11c-4.9-0.8-9-0.7-16.2-2.7"/>
                        <path fill="#FFFFFF" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M94.5,103.8c-0.6,4-3.8,8.9-9.4,14.7c-2.6-1.8-5-3.7-7.2-5.7c-2.5,4.1-6.6,8.8-12.2,14c-1.9-2.2-3.4-4.5-4.5-6.9c-4.4,3.3-9.5,6.9-15.4,10.8c-0.2-3.4,0.1-7.1,1.1-10.9"/>
                        <path fill="#FFFFFF" stroke="#3A5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M97.5,63.9c-1.7-2.4-5.9-4.1-12.4-5.2c-0.9,2.2-1.8,4.3-2.5,6.5c-3.8-1.8-9.4-3.1-17-3.8c0.5,2.3,1.2,4.5,1.9,6.8c-5-0.6-11.2-0.9-18.4-1c2,2.9,0.9,3.5,3.9,6.2"/>
                    </g>
                    <g class="armR" style="visibility: hidden;">
                        <path fill="#ddf1fa" stroke="#3a5e77" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2.5" d="M265.4 97.3l10.4-38.6-38.9-10.5-20 36.1z"/>
                        <path fill="#ddf1fa" stroke="#3a5e77" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2.5" d="M252.4 52.4L233 47.2c-2.7-.7-5.4.9-6.1 3.5-.7 2.7.9 5.4 3.5 6.1l10.3 2.8M226 76.4l-19.4-5.2c-2.7-.7-5.4.9-6.1 3.5-.7 2.7.9 5.4 3.5 6.1l18.3 4.9M228.4 66.7l-23.1-6.2c-2.7-.7-5.4.9-6.1 3.5-.7 2.7.9 5.4 3.5 6.1l23.1 6.2M235.8 58.3l-26-7c-2.7-.7-5.4.9-6.1 3.5-.7 2.7.9 5.4 3.5 6.1l21.3 5.7"/>
                        <path fill="#a9ddf3" d="M207.9 74.7l-2.2-.6c-1.1-.3-2.2.3-2.4 1.4-.3 1.1.3 2.2 1.4 2.4l2.2.6 1-3.8zM206.7 64l-2.2-.6c-1.1-.3-2.2.3-2.4 1.4-.3 1.1.3 2.2 1.4 2.4l2.2.6 1-3.8zM211.2 54.8l-2.2-.6c-1.1-.3-2.2.3-2.4 1.4-.3 1.1.3 2.2 1.4 2.4l2.2.6 1-3.8zM234.6 49.4l-2.2-.6c-1.1-.3-2.2.3-2.4 1.4-.3 1.1.3 2.2 1.4 2.4l2.2.6 1-3.8z"/>
                        <path fill="#fff" stroke="#3a5e77" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M263.3 96.7c41.4 14.9 84.1 30.7 108.2 35.5l14-52.3C352 70 313.6 63.5 273.6 58.1"/>
                        <path fill="#fff" stroke="#3a5e77" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M278.2 59.3l-18.6-10 2.5 11.9-10.7 6.5 9.9 8.7-13.9 6.4 9.1 5.9-13.2 9.2 23.1-.9M284.5 100.1c-.4 4 1.8 8.9 6.7 14.8 3.5-1.8 6.7-3.6 9.7-5.5 1.8 4.2 5.1 8.9 10.1 14.1 2.7-2.1 5.1-4.4 7.1-6.8 4.1 3.4 9 7 14.7 11 1.2-3.4 1.8-7 1.7-10.9M314 66.7s5.4-5.7 12.6-7.4c1.7 2.9 3.3 5.7 4.9 8.6 3.8-2.5 9.8-4.4 18.2-5.7.1 3.1.1 6.1 0 9.2 5.5-1 12.5-1.6 20.8-1.9-1.4 3.9-2.5 8.4-2.5 8.4"/>
                    </g>
                </g>
            </svg>
        </div>
    </div>
    <?php
    $svg = ob_get_clean();
    return $svg . $message;
}
add_filter( 'login_message', 'yeti_login_message' );


/**
 * Set the login header URL.
 */
function yeti_login_header_url() {
    $link = get_option( 'yeti_login_logo_link' );
    return ! empty( $link ) ? $link : home_url( '/' );
}
add_filter( 'login_headerurl', 'yeti_login_header_url' );


/**
 * Set the login header text.
 */
function yeti_login_header_text() {
    return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'yeti_login_header_text' );


/**
 * Output credit line in login footer.
 */
function yeti_login_footer_credit() {
    echo '<div class="yeti-login-credit">Yeti by <a href="https://darinsenneff.com" target="_blank" rel="noopener">Darin S.</a> | Plugin by <a href="https://thedevq.com/" target="_blank" rel="noopener">DevQ</a></div>';
}
add_action( 'login_footer', 'yeti_login_footer_credit' );


/* ==========================================================================
   Settings Page
   ========================================================================== */

/**
 * Add settings page under Settings menu.
 */
function yeti_login_add_settings_page() {
    add_options_page(
        __( 'Yeti Login', 'yeti-login' ),
        __( 'Yeti Login', 'yeti-login' ),
        'manage_options',
        'yeti-login',
        'yeti_login_render_settings_page'
    );
}
add_action( 'admin_menu', 'yeti_login_add_settings_page' );


/**
 * Register settings.
 */
function yeti_login_register_settings() {
    register_setting( 'yeti_login_settings', 'yeti_login_logo', array(
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default'           => '',
    ) );

    register_setting( 'yeti_login_settings', 'yeti_login_logo_link', array(
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default'           => '',
    ) );

    register_setting( 'yeti_login_settings', 'yeti_login_bg', array(
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default'           => '',
    ) );

    add_settings_section(
        'yeti_login_main',
        __( 'Login Page Settings', 'yeti-login' ),
        function () {
            echo '<p>' . esc_html__( 'Customize the appearance of your WordPress login page.', 'yeti-login' ) . '</p>';
        },
        'yeti-login'
    );

    add_settings_field(
        'yeti_login_logo',
        __( 'Logo Image', 'yeti-login' ),
        'yeti_login_render_logo_field',
        'yeti-login',
        'yeti_login_main'
    );

    add_settings_field(
        'yeti_login_logo_link',
        __( 'Logo Link', 'yeti-login' ),
        'yeti_login_render_logo_link_field',
        'yeti-login',
        'yeti_login_main'
    );

    add_settings_field(
        'yeti_login_bg',
        __( 'Background Image', 'yeti-login' ),
        'yeti_login_render_bg_field',
        'yeti-login',
        'yeti_login_main'
    );
}
add_action( 'admin_init', 'yeti_login_register_settings' );


/**
 * Enqueue media uploader on the settings page.
 */
function yeti_login_admin_scripts( $hook ) {
    if ( $hook !== 'settings_page_yeti-login' ) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script(
        'yeti-login-admin',
        YETI_LOGIN_URL . 'assets/js/admin.js',
        array( 'jquery' ),
        YETI_LOGIN_VERSION,
        true
    );
    wp_add_inline_style( 'wp-admin', '
        .yeti-login-image-field .yeti-login-preview {
            width: 200px;
            min-height: 80px;
            border: 2px dashed #c3c4c7;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
            background: #f0f0f1;
            transition: border-color 0.2s;
        }
        .yeti-login-image-field .yeti-login-preview:hover { border-color: #2271b1; }
        .yeti-login-image-field .yeti-login-preview.has-image { border-style: solid; background: #000; }
        .yeti-login-image-field .yeti-login-preview img { max-width: 100%; height: auto; display: block; }
        .yeti-login-image-field .yeti-login-preview .placeholder { color: #8c8f94; font-size: 13px; }
        .yeti-login-image-field .yeti-login-actions { margin-top: 8px; display: flex; gap: 6px; }
    ' );
}
add_action( 'admin_enqueue_scripts', 'yeti_login_admin_scripts' );


/**
 * Render logo image field.
 */
function yeti_login_render_logo_field() {
    $value = get_option( 'yeti_login_logo', '' );
    ?>
    <div class="yeti-login-image-field">
        <input type="hidden" id="yeti_login_logo" name="yeti_login_logo" value="<?php echo esc_url( $value ); ?>" />
        <div class="yeti-login-preview<?php echo $value ? ' has-image' : ''; ?>" id="yeti_login_logo_preview" data-target="#yeti_login_logo">
            <?php if ( $value ) : ?>
                <img src="<?php echo esc_url( $value ); ?>" alt="" />
            <?php else : ?>
                <span class="placeholder"><?php esc_html_e( 'Click to upload logo', 'yeti-login' ); ?></span>
            <?php endif; ?>
        </div>
        <div class="yeti-login-actions">
            <button type="button" class="button yeti-login-upload" data-target="#yeti_login_logo"><?php esc_html_e( 'Upload', 'yeti-login' ); ?></button>
            <button type="button" class="button yeti-login-remove" data-target="#yeti_login_logo" <?php echo $value ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Remove', 'yeti-login' ); ?></button>
        </div>
        <p class="description"><?php esc_html_e( 'Upload a logo to display above the yeti. Leave empty to show no logo.', 'yeti-login' ); ?></p>
    </div>
    <?php
}


/**
 * Render logo link field.
 */
function yeti_login_render_logo_link_field() {
    $value = get_option( 'yeti_login_logo_link', '' );
    ?>
    <input type="url" id="yeti_login_logo_link" name="yeti_login_logo_link" value="<?php echo esc_url( $value ); ?>" class="regular-text" placeholder="<?php echo esc_url( home_url( '/' ) ); ?>" />
    <p class="description"><?php esc_html_e( 'URL the logo links to. Defaults to your site home page if left empty.', 'yeti-login' ); ?></p>
    <?php
}


/**
 * Render background image field.
 */
function yeti_login_render_bg_field() {
    $value = get_option( 'yeti_login_bg', '' );
    $display_url = ! empty( $value ) ? $value : YETI_LOGIN_URL . 'assets/images/black-brick.jpg';
    ?>
    <div class="yeti-login-image-field">
        <input type="hidden" id="yeti_login_bg" name="yeti_login_bg" value="<?php echo esc_url( $value ); ?>" />
        <div class="yeti-login-preview has-image" id="yeti_login_bg_preview" data-target="#yeti_login_bg">
            <img src="<?php echo esc_url( $display_url ); ?>" alt="" />
        </div>
        <div class="yeti-login-actions">
            <button type="button" class="button yeti-login-upload" data-target="#yeti_login_bg"><?php esc_html_e( 'Upload', 'yeti-login' ); ?></button>
            <button type="button" class="button yeti-login-remove" data-target="#yeti_login_bg" <?php echo $value ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Remove', 'yeti-login' ); ?></button>
        </div>
        <p class="description"><?php esc_html_e( 'Upload a custom background image. Leave empty to use the default black brick background.', 'yeti-login' ); ?></p>
    </div>
    <?php
}


/**
 * Render settings page.
 */
function yeti_login_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'yeti_login_settings' );
            do_settings_sections( 'yeti-login' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
