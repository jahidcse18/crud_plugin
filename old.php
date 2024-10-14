




Search Nextbit Studio


1

2


Home
1

DMs
2

Activity
3

Later
4

More
0

Nextbit Studio




















Jalal




Messages

Add canvas

Files



Jahid
4:49 PM
https://www.youtube.com/watch?v=NKqogVcqDHA&ab_channel=LearnWebCode

YouTubeYouTube | LearnWebCode
React In WordPress Boilerplate (Both Gutenberg Block Types & Front-End)

New


Jalal
11:24 AM
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * Class Wholesale_Customer_List_Table
 * Manages the display of wholesale customers in a list table format.
 */
class Wholesale_Customer_List_Table extends WP_List_Table {
    /**
     * Prepares the items for the table.
     * This function fetches users with the 'wholesale_customer' role, applies search, sorting, and pagination.
     */
    public function prepare_items() {
        wp_verify_nonce( '_wpnonce' );
        $per_page     = 10;
        $current_page = $this->get_pagenum();
        $offset       = ( $current_page - 1 ) * $per_page;
        $search       = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
        $orderby      = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'user_login';
        $order        = isset( $_REQUEST['order'] ) && in_array( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ), array( 'asc', 'desc' ), true ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'asc';
        $args         = array(
            'role'           => 'wholesale_customer',
            'orderby'        => $orderby,
            'order'          => $order,
            'number'         => $per_page,
            'offset'         => $offset,
            'search'         => $search ? '*' . $search . '*' : '',
            'search_columns' => array( 'user_login', 'user_email' ),
        );

        $users       = get_users( $args );
        $total_users = count( get_users( array( 'role' => 'wholesale_customer' ) ) );
        $this->items = $users;
        $this->set_pagination_args(
            array(
                'total_items' => $total_users,
                'per_page'    => $per_page,
                'total_pages' => ceil( $total_users / $per_page ),
            )
        );
        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
    }
    /**
     * Defines the columns that are displayed in the table.
     *
     * @return array Array of column names.
     */
    public function get_columns() {
        $columns = array(
            'cb'              => '<input type="checkbox" />',
            'username'        => __( 'Username', 'tiered-wholesale-pricing' ),
            'email'           => __( 'Email', 'tiered-wholesale-pricing' ),
            'role'            => __( 'Role', 'tiered-wholesale-pricing' ),
            'wholesale_group' => __( 'Wholesale Group', 'tiered-wholesale-pricing' ),
        );
        return $columns;
    }
    /**
     * Defines sortable columns in the table.
     *
     * @return array Array of sortable columns.
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'username'        => array( 'user_login', true ),
            'email'           => array( 'user_email', false ),
            'role'            => array( 'role', false ),
            'wholesale_group' => array( 'user_group', false ),
        );
        return $sortable_columns;
    }
    /**
     * Handles the display of different columns.
     *
     * @param object $item The current item (user) being displayed.
     * @param string $column_name The name of the column being rendered.
     * @return string The column content.
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'username':
                return '<a href="' . esc_url( get_edit_user_link( $item->ID ) ) . '">' . esc_html( $item->user_login ) . '</a>';
            case 'email':
                return esc_html( $item->user_email );
            case 'role':
                return implode( ', ', $item->roles );
            case 'wholesale_group':
                return esc_html( get_user_meta( $item->ID, 'wholesale_customer_group', true ) );
            default:
                return '';
        }
    }
    /**
     * Generates the checkbox column.
     *
     * @param object $item The current item (user).
     * @return string Checkbox input HTML.
     */
    public function column_cb( $item ) {
        return '<input type="checkbox" name="user_ids[]" value="' . esc_attr( $item->ID ) . '" />';
    }
    /**
     * Processes bulk actions.
     *
     * @since 1.0.0
     */
    public function process_bulk_action() {
        wp_verify_nonce( 'nonce' );
        if ( 'assign_to_group' === $this->current_action() ) {
            $user_ids   = isset( $_POST['user_ids'] ) ? array_map( 'intval', wp_unslash( $_POST['user_ids'] ) ) : array();
            $group_name = isset( $_POST['group_name'] ) ? sanitize_text_field( wp_unslash( $_POST['group_name'] ) ) : '';
            if ( ! empty( $user_ids ) && is_array( $user_ids ) && ! empty( $group_name ) ) {
                foreach ( $user_ids as $user_id ) {
                    if ( get_userdata( $user_id ) ) {
                        update_user_meta( $user_id, 'wholesale_customer_group', $group_name );
                    }
                }
            }
        }
    }

    /**
     * Adds a dropdown for assigning wholesale groups in the table's top navigation.
     *
     * @param string $which The position of the extra tablenav (top or bottom).
     */
    public function extra_tablenav( $which ) {
        if ( 'top' === $which ) {
            $wholesale_group_names = get_option( 'wholesale_group_names', array() );
            ?>
            <div class="alignleft actions">
                <label>
                    <select name="group_name">
                        <option value=""><?php esc_html_e( 'Assign to Group', 'tiered-wholesale-pricing' ); ?></option>
                        <?php foreach ( $wholesale_group_names as $group_name ) : ?>
                            <option value="<?php echo esc_attr( $group_name ); ?>"><?php echo esc_html( $group_name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <input type="hidden" name="action" value="assign_to_group">
                <?php submit_button( __( 'Assign', 'tiered-wholesale-pricing' ), 'button action', 'bulk_action', false, array( 'id' => 'doaction' ) ); ?>
            </div>
            <?php
        }
    }
}
















Message Jalal









Shift + Return to add a new line



