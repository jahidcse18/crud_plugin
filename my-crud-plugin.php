<?php
/**
 * Plugin Name: My CRUD Plugin
 * Description: A simple WordPress CRUD plugin using OOP.
 * Version: 1.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Autoload classes
spl_autoload_register( function( $class ) {
    if ( strpos( $class, 'My_CRUD_' ) !== false ) {
        include_once plugin_dir_path( __FILE__ ) . 'includes/class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';
    }
});

// Register the admin menu
add_action( 'admin_menu', function() {
    add_menu_page(
        'My CRUD Plugin', // Page Title
        'My CRUD',        // Menu Title
        'manage_options', // Capability
        'my-crud-plugin', // Menu Slug
        'My_CRUD_Controller::list_view', // Function to render
        'dashicons-list-view', // Icon
        26
    );
});

add_action('admin_post_my_crud_form_submit', ['My_CRUD_Controller', 'handle_form_submit']);

register_activation_hook( __FILE__, [ 'My_CRUD_Controller', 'install' ] );

