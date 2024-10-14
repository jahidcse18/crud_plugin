<?php

class My_CRUD_Controller {

    public function __construct()
    {
        add_action('admin_init', array( $this, 'handle_form_submit' ) );
    }

    /**
     * Install the table during plugin activation
     */
    public static function install() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'my_crud_table';

        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            description text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Display the list view or the form view depending on action
     */
    public static function list_view() {

        $action = isset($_GET['action']) ? $_GET['action'] : '';
        if ($action === 'add') {
            self::form_view();
        } elseif ($action === 'edit' && isset($_GET['id'])) {
            self::form_view($_GET['id']);
        }
        elseif ($action === 'delete' && isset($_GET['id'])){
            self::handle_delete();
        }else {
            $table = new My_CRUD_List_Table();
            $table->prepare_items();
            include plugin_dir_path(__FILE__) . '../templates/admin-crud-list.php';
        }
    }

    /**
     * Display the form for adding or editing an entry
     */
    public static function form_view($id = 0) {
        $item = null;
        if ($id) {
            $model = new My_CRUD_Model();
            $item = $model->read($id);
        }
        include plugin_dir_path(__FILE__) . '../templates/admin-crud-form.php';
    }

    /**
     * Handle form submission for creating or updating entries
     */
    public static function handle_form_submit() {

        if (!isset($_POST['my_crud_nonce']) || !wp_verify_nonce($_POST['my_crud_nonce'], 'my_crud_form_submit')) {
            wp_die('Invalid nonce.');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['name'], $_POST['description'])) {
                $model = new My_CRUD_Model();
                $data = [
                    'name' => sanitize_text_field(stripslashes($_POST['name'])),
                    'description' => sanitize_textarea_field(stripslashes($_POST['description'])),
                ];
                if (isset($_POST['id']) && !empty($_POST['id'])) {
                    $model->update((int)$_POST['id'], $data);
                } else {
                    $model->create($data);
                }
                wp_redirect(admin_url('admin.php?page=my-crud-plugin'));
                exit;
            } else {
                wp_die('Form fields missing.');
            }
        }
    }

    /**
     * Handle bulk delete action
     */
    public static function handle_bulk_delete() {
        if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && is_array($_POST['id'])) {
            $model = new My_CRUD_Model();
            foreach ($_POST['id'] as $id) {
                $model->delete($id);
            }
            wp_redirect(admin_url('admin.php?page=my-crud-plugin'));
            exit;
        }
    }
    public static function handle_delete() {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = (int)$_GET['id'];
            $model = new My_CRUD_Model();
            $model->delete($id);
            wp_redirect(admin_url('admin.php?page=my-crud-plugin'));
            exit;
        } else {
            wp_die('Invalid ID.');
        }
    }
}
add_action('admin_init', ['My_CRUD_Controller', 'handle_bulk_delete']);
