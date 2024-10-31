<?php

class Serp_Rank_Keywords_Table extends WP_List_Table {

	private $data;
	private $column_sort_orderby;
	private $column_sort_order;

	public function set_data( $data ) {
		$this->data = $data;
	}
	public function get_columns() {
		$columns = array(
			'keyword'     => __( 'Keyword', 'serp-rank' ),
			'position'    => __( 'Position', 'serp-rank' ),
			'url'         => __( 'URL', 'serp-rank' ),
			'impressions' => __( 'Impressions', 'serp-rank' ),
			'clicks'      => __( 'Clicks', 'serp-rank' ),
		);
		return $columns;
	}
	public function prepare_items() {

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		if ( isset( $_GET['orderby'] ) && in_array( $_GET['orderby'], array_keys( $sortable ) ) ) {
			$this->column_sort_orderby = sanitize_key( $_GET['orderby'] );
		} else {
			$this->column_sort_orderby = 'position';
		}
		if ( isset( $_GET['order'] ) && in_array( $_GET['order'], array( 'desc', 'asc' ) ) ) {
			$this->column_sort_order = sanitize_key( $_GET['order'] );
		} else {
			$this->column_sort_order = 'asc';
		}
		usort( $this->data, array( &$this, 'usort_reorder' ) );

		$per_page     = $this->get_items_per_page( 'keywords_per_page', 50 );
		$current_page = $this->get_pagenum();
		$total_items  = count( $this->data );

		$this->data = array_slice( $this->data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
		$this->items = $this->data;
	}
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'impressions':
			case 'clicks':
			case 'keyword':
			case 'position':
				return $item[ $column_name ];
			case 'url':
				return '<a href="' . $item[ $column_name ] . '">' . $item[ $column_name ] . '</a>';
			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes
		}
	}
	public function get_sortable_columns() {
		$sortable_columns = array(
			'keyword'     => array( 'keyword', false ),
			'position'    => array( 'position', false ),
			'url'         => array( 'url', false ),
			'impressions' => array( 'impressions', false ),
			'clicks'      => array( 'clicks', false ),
		);
		return $sortable_columns;
	}
	public function usort_reorder( $a, $b ) {
		$orderby = $this->column_sort_orderby;
		$order   = $this->column_sort_order;
		if ( 'position' == $orderby || 'impressions' == $orderby ) {
			$result = ( 'asc' == $order ? $a[ $orderby ] >= $b[ $orderby ] : $a[ $orderby ] < $b[ $orderby ] );
			return $result;
		} else {
			$result = strcmp( $a[ $orderby ], $b[ $orderby ] );
			return ( $order === 'asc' ) ? $result : -$result;
		}
	}
	public function display_search_box() {
		?>
			  <form method="get" class="serpr-search">
				  <input type="hidden" name="page" value="keyword-rank-tracker" />
			<?php $this->search_box( 'search', 'search_id' ); ?>
				  <label class="exact"><input type="checkbox" name="exact" <?php checked( isset($_REQUEST['exact']) ? $_REQUEST['exact']: null, 'on' ); ?> /> Exact</label>
			  </form>
			<?php
	}
	public function pagination( $which ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items     = $this->_pagination_args['total_items'];
		$total_pages     = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		if ( 'top' === $which && $total_pages > 1 ) {
			$this->screen->render_screen_reader_content( 'heading_pagination' );
		}

		$output = '<span class="displaying-num">' . sprintf( _n( '%s keyword', '%s keywords', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current              = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();

		$current_url = admin_url( sprintf( 'admin.php?%s', http_build_query( $_GET ) ) );

		$current_url = remove_query_arg( $removable_query_args, $current_url );

		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span></span>';

		$disable_first = $disable_last = $disable_prev = $disable_next = false;

		if ( $current == 1 ) {
			$disable_first = true;
			$disable_prev  = true;
		}
		if ( $current == 2 ) {
			$disable_first = true;
		}
		if ( $current == $total_pages ) {
			$disable_last = true;
			$disable_next = true;
		}
		if ( $current == $total_pages - 1 ) {
			$disable_last = true;
		}

		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				__( 'First page' ),
				'&laquo;'
			);
		}

		if ( $disable_prev ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
				__( 'Previous page' ),
				'&lsaquo;'
			);
		}

		if ( 'bottom' === $which ) {
			$html_current_page  = $current;
			$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
		} else {
			$html_current_page = sprintf(
				"%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
				$current,
				strlen( $total_pages )
			);
		}
		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

		if ( $disable_next ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
				__( 'Next page' ),
				'&rsaquo;'
			);
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				__( 'Last page' ),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class .= ' hide-if-js';
		}
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}

}
