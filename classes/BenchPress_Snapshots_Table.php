<?php
// Snapshot List Table class
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class BenchPress_Snapshots_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => __( 'Snapshot', 'benchpress' ),
            'plural'   => __( 'Snapshots', 'benchpress' ),
            'ajax'     => false,
        ]);
    }

    /**
     * Define columns for the Snapshots table.
     */
    public function get_columns() {
        return [
            'id'           => __( 'ID', 'benchpress' ),
            'created_at'   => __( 'Date', 'benchpress' ),
            'snapshot_data'=> __( 'Actions', 'benchpress' ),
        ];
    }

    /**
     * Define sortable columns.
     */
    public function get_sortable_columns() {
        return [
            'id'         => [ 'id', true ],
            'created_at' => [ 'created_at', true ],
        ];
    }

    /**
     * Prepare items for display in the Snapshots table.
     */
    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'benchpress_snapshots';

        $per_page     = 10;
        $current_page = $this->get_pagenum();

        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $offset = ( $current_page - 1 ) * $per_page;
        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );

        $total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ]);
    }

    /**
     * Default column renderer for displaying data.
     */
    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'id':
                return esc_html( $item['id'] );
            case 'created_at':
                return esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item['created_at'] ) ) );
            case 'snapshot_data':
                $encoded_data = esc_attr( json_encode( json_decode( $item['snapshot_data'], true ) ) );
    
                // View (eye) and Delete (trash) buttons
                return sprintf(
                    '<button class="button view-data-btn" data-snapshot="%s" aria-label="%s"><span class="dashicons dashicons-visibility"></span></button>
                     <button class="button delete-snapshot-btn" data-id="%d" aria-label="%s" style="margin-left: 5px;"><span class="dashicons dashicons-trash"></span></button>',
                    $encoded_data,
                    __( 'View Data', 'benchpress' ),
                    esc_attr( $item['id'] ),
                    __( 'Delete Snapshot', 'benchpress' )
                );
            default:
                return print_r( $item, true );
        }
    }    
    
}
