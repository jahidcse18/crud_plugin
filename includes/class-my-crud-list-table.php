<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class My_CRUD_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'crud_entry',
            'plural'   => 'crud_entries',
            'ajax'     => false
        ]);
    }

    public function prepare_items() {
        $this->process_bulk_action();
        global $wpdb;
        $table_name = $wpdb->prefix . 'my_crud_table';
        $orderby = !empty($_GET['orderby']) ? esc_sql($_GET['orderby']) : 'id';
        $order = !empty($_GET['order']) ? esc_sql($_GET['order']) : 'ASC';
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $this->items = $wpdb->get_results("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT $offset, $per_page", ARRAY_A);

        // Set pagination
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);
        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
    }

    public function get_columns() {
        $columns = [
            'cb'         => '<input type="checkbox" />',
            'id'         => 'ID',
            'name'       => 'Name',
            'description'=> 'Description'
        ];
        return $columns;
    }

    public function get_sortable_columns() {
        return [
            'id'   => ['id', true],
            'name' => ['name', false]
        ];
    }

    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />', $item['id']
        );
    }

    public function get_bulk_actions() {
        return [
            'bulk_delete' => 'Delete'
        ];
    }

    public function process_bulk_action() {
        if ('bulk_delete' === $this->current_action()) {
            // Security check
            if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'])) {
                wp_die('Security check failed.');
            }

            $ids = isset($_REQUEST['id']) ? array_map('absint', $_REQUEST['id']) : [];

            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $model = new My_CRUD_Model();
                    $model->delete($id);
                }

                // Redirect after deleting
                wp_redirect(admin_url('admin.php?page=my-crud-plugin'));
                exit;
            }
        }
    }

    public function column_name($item) {
        $actions = [
            'edit' => sprintf('<a href="?page=%s&action=%s&id=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']),
            'delete' => sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id'])
        ];

        return sprintf('%1$s %2$s', $item['name'], $this->row_actions($actions));
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
            case 'name':
            case 'description':
            return esc_html(stripslashes($item[$column_name]));
            default:
                return print_r($item, true);
        }
    }

}
