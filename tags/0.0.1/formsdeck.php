<?php

/**
 * Plugin Name: FormsDeck
 * Version: 0.0.1
 * Plugin URI: https://formsdeck.com/
 * Description: Get form responses from the customer's WhatsApp number to yours.
 * Author: Origyn
 * Author URI: https://origyn.company/
 * License: GPLv2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin directory URL
define('FORMSDECK_DIR_URL', plugin_dir_url(__FILE__));

// Enqueue admin styles
function formsdeck_enqueue_admin_styles() {
    wp_enqueue_style('formsdeck-admin-style', plugins_url('CSS/admin.css', __FILE__), array(), time());
}
add_action('admin_enqueue_scripts', 'formsdeck_enqueue_admin_styles');

// Add settings page to the admin menu
function formsdeck_add_menu() {
    $menu_icon = plugins_url('assets/formsdeck menu icon.svg', __FILE__);
    add_menu_page(
        'FormsDeck Settings',
        'FormsDeck',
        'manage_options',
        'formsdeck-settings',
        'formsdeck_settings_page',
        $menu_icon
    );
}
add_action('admin_menu', 'formsdeck_add_menu');

// Settings page content
function formsdeck_settings_page() {
?>
    <div class="wrap fd-settings-page">
        <div class="fd-header">
            <a href="<?php echo esc_url('https://formsdeck.com'); ?>"><img class="fd-logo" src="<?php echo esc_url(FORMSDECK_DIR_URL . 'formsdeck-logo.png'); ?>" alt="<?php echo esc_attr('FormsDeck Logo'); ?>"></a>
        </div>
        <?php
        // Display admin notification if settings were successfully saved
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Settings have been successfully saved!', 'formsdeck'); ?></p>
            </div>
        <?php
        }
        ?>
        <div class="fd-content">
            <div class="left-panel">
                <div class="fd-create-form">Don't have a FormsDeck form yet? <a href='https://formsdeck.com/?utm_source=wordpress' target='_blank' rel='noreferrer'> Create a new one</a></div>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('formsdeck_settings');
                    do_settings_sections('formsdeck-settings');
                    submit_button();
                    ?>
                </form>
            </div>
            <div class="right-panel">
                <div class="fd-feedback">
                    <h3>Show us some love :)</h3>
                    <p>Found FormsDeck useful? Rate it and leave a nice little comment at wordpress.org. We would appreciate that.</p>
                    <a href="https://wordpress.org/support/plugin/formsdeck/reviews/#new-post" target="_blank"><button class="fd-btn">Rate us</button></a>
                </div>

                <div class="fd-follow">
                    <h3>Let's be friends 🤝</h3>
                    <a href="https://www.youtube.com/@OrigynCompany" target="_blank"><button class="fd-btn">Subscribe on YouTube</button></a>
                    <a href="https://twitter.com/FormsDeckHQ" target="_blank"><button class="fd-btn">Follow us on X</button></a>
                </div>
            </div>
        </div>


    </div>
<?php
}


/**
 * Display instructional notice on top of dashboard pages.
 * Removed when user dismisses the notice.
 */
function formsdeck_getting_started_notice() {
    global $current_user;
    $user_id = $current_user->ID;

    if (!get_user_meta($user_id, 'formsdeck_getting_started_notice') && !(isset($_GET['page']) && $_GET['page'] == 'formsdeck')) {

        $output = '<div class="notice" style="display: flex;flex-direction:column;gap:10px;padding:20px;">';
        $output .= '<a href="' . esc_url('https://formsdeck.com/?utm_source=wordpress') . '" class="logo"><img src="' . esc_url(FORMSDECK_DIR_URL . "formsdeck-logo.png") . '" width="150px" alt="' . esc_attr__('formsdeck logo') . '"/></a>';
        $output .= '<div><h4 style="margin: 0;">' . esc_html__('Getting started with your FormsDeck form 🚀') . '</h4><ol>';
        $output .= '<li>' . wp_kses(sprintf(
            __('If you are not an existing FormsDeck user, <a href="%1$s" target="_blank" rel="noreferrer">click here to register.</a>'),
            esc_url('https://formsdeck.com/?utm_source=wordpress')
        ), array('a' => array('href' => array(), 'target' => array(), 'rel' => array()))) . '</li>';
        $output .= '<li>' . esc_html__('Design and publish your form') . '</li>';
        $output .= '<li>' . wp_kses(sprintf(
            __('Copy the widget snippet and visit <a href="%1$s">plugin settings</a> to add FormsDeck to your website.'),
            esc_url(admin_url('admin.php?page=formsdeck-settings'))
        ), array('a' => array('href' => array()))) . '</li>';
        $output .= '</ol></div>';
        $output .= '<a href="' . esc_url(admin_url('admin.php?page=formsdeck-settings')) . '&formsdeck_notice_ignore=1">' . esc_html__('Dismiss') . '</a>';
        $output .= '</div>';

        echo wp_kses_post($output);
    }
}


function formsdeck_notice_ignore() {
    global $current_user;
    $user_id = $current_user->ID;
    /* If user clicks to ignore the notice, add that to their user meta */
    if (isset($_GET['formsdeck_notice_ignore']) && '1' == $_GET['formsdeck_notice_ignore']) {
        add_user_meta($user_id, 'formsdeck_getting_started_notice', 'true', true);
    }
}
add_action('admin_init', 'formsdeck_notice_ignore');


// Register settings and fields
function formsdeck_register_settings() {
    register_setting('formsdeck_settings', 'formsdeck_code');
    register_setting('formsdeck_settings', 'formsdeck_location');

    add_settings_section('formsdeck_section', 'Add Widget', 'formsdeck_section_callback', 'formsdeck-settings');

    add_settings_field('formsdeck_code', 'Widget Snippet', 'formsdeck_code_callback', 'formsdeck-settings', 'formsdeck_section');
    add_settings_field('formsdeck_location', 'Show on', 'formsdeck_location_callback', 'formsdeck-settings', 'formsdeck_section');
}
add_action('admin_init', 'formsdeck_register_settings');

// Section callback
function formsdeck_section_callback() {
    echo 'Insert your Formsdeck form widget snippet and choose where to display.';
}

// Code field callback
function formsdeck_code_callback() {
    $code = get_option('formsdeck_code');
    echo '<textarea name="formsdeck_code" rows="5" cols="50">' . esc_textarea($code) . '</textarea>';
}

// Location field callback
function formsdeck_location_callback() {
    $location = get_option('formsdeck_location');
    $options = array(
        'everywhere' => 'Everywhere',
        'homepage' => 'Homepage Only',
        'except_homepage' => 'Everywhere Except Homepage',
        'nowhere' => 'Nowhere'
    );

    echo '<select name="formsdeck_location">';
    foreach ($options as $key => $value) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($location, $key, false) . '>' . esc_html($value) . '</option>';
    }
    echo '</select>';
}

// Hook to insert code snippet in the site body
function formsdeck_insert_code() {
    $code = get_option('formsdeck_code');
    $location = get_option('formsdeck_location');

    if ($location === 'everywhere' || ($location === 'homepage' && is_front_page()) || ($location === 'except_homepage' && !is_front_page())) {
        echo wp_kses($code, array('script' => array('async' => array(), 'src' => array(), 'id' => array(), 'data-id' => array(), 'data-message' => array())));
    }
}
add_action('wp_footer', 'formsdeck_insert_code');
