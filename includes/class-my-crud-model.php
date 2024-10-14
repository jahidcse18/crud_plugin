<?php

class My_CRUD_Model {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'my_crud_table';
    }

    public function create($data) {
        global $wpdb;
        return $wpdb->insert($this->table_name, $data);
    }

    public function read($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $this->table_name WHERE id = %d", $id), ARRAY_A);
    }

    public function update($id, $data) {
        global $wpdb;
        return $wpdb->update($this->table_name, $data, ['id' => $id]);
    }

    public function delete($id) {
        global $wpdb;
        return $wpdb->delete($this->table_name, ['id' => $id]);
    }

    public function count_all() {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $this->table_name");
    }

    public function get_all($per_page = 10, $page_number = 1) {
        global $wpdb;
        $offset = ($page_number - 1) * $per_page;

        $query = "SELECT * FROM $this->table_name";

        if (!empty($_REQUEST['orderby'])) {
            $query .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $query .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }

        $query .= " LIMIT $per_page OFFSET $offset";

        return $wpdb->get_results($query, ARRAY_A);
    }
}
