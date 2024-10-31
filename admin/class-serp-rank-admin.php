<?php


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Serp_rank
 * @subpackage Serp_rank/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Serp_rank
 * @subpackage Serp_rank/admin
 * @author     Your Name <email@example.com>
 */
class Serp_rank_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $serp_rank    The ID of this plugin.
	 */
	private $serp_rank;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $serp_rank       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $serp_rank, $version ) {

		$this->serp_rank = $serp_rank;
		$this->version   = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Serp_rank_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Serp_rank_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->serp_rank, plugin_dir_url( __FILE__ ) . 'css/serp-rank-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Serp_rank_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Serp_rank_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->serp_rank, plugin_dir_url( __FILE__ ) . 'js/serp-rank-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function admin_menu() {
		$page_hook = add_menu_page(
			__( 'Keyword Rank Tracker', 'serp-rank' ),
			__( 'Keyword Rank Tracker', 'serp-rank' ),
			'manage_options',
			'keyword-rank-tracker',
			array( $this, 'keywords_page' ),
			'dashicons-admin-tools',
			21
		);
		add_action( "load-$page_hook", array( $this, 'add_keywords_page_options' ) );
		add_submenu_page(
			'keyword-rank-tracker',
			__( 'Keyword List', 'serp-rank' ),
			__( 'Keyword List', 'serp-rank' ),
			'manage_options',
			'keyword-rank-tracker',
			array( $this, 'keywords_page' )
		);
		add_submenu_page(
			'keyword-rank-tracker',
			__( 'Settings', 'serp-rank' ),
			__( 'Settings', 'serp-rank' ),
			'manage_options',
			'keyword-rank-tracker_settings',
			array( $this, 'settings_page' )
		);
		add_submenu_page(
			'keyword-rank-tracker',
			__( 'About', 'serp-rank' ),
			__( 'About', 'serp-rank' ),
			'manage_options',
			'keyword-rank-tracker_about',
			function() {
				include_once 'partials/serp-rank-admin-about.php';
			}
		);
	}

	public function add_keywords_page_options() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Keywords', 'serp-rank' ),
			'default' => 50,
			'option'  => 'keywords_per_page',
		);
		add_screen_option( $option, $args );
	}
	public function get_console_pages_data() {
		$account_json      = get_option( 'serpr_account_json' );
		$serpr_active_site = get_option( 'serpr_active_site' );
		$pages             = array();
		if ( $account_json && $serpr_active_site ) {
			try {
				$client      = new Google_Client();
				$account_cfg = json_decode( $account_json, true );
				$client->setAuthConfig( $account_cfg );
				$client->addScope( 'https://www.googleapis.com/auth/webmasters' );
				$serviceWebmasters = new Google_Service_Webmasters( $client );
				$postBody          = new Google_Service_Webmasters_SearchAnalyticsQueryRequest(
					array(
						'startDate'  => date( 'Y-m-d', strtotime( '-10 year' ) ),
						'endDate'    => date( 'Y-m-d' ),
						'dimensions' => array( 'page', 'query' ),
						'rowLimit'   => 25000,
					)
				);

				$searchAnalyticsResponse = $serviceWebmasters->searchanalytics->query( $serpr_active_site, $postBody );
				if ( $searchAnalyticsResponse->rows ) {
					$pages = $searchAnalyticsResponse->rows;
				}
			} catch ( Exception $e ) {
				echo sprintf( '<p class="serpr-error">%s: ' . esc_html( json_decode( $e->getMessage(), true )['error']['message'] ). '</p>', __( 'Auth error', 'serp-rank' ) );
			}
		}
		return $pages;
	}
	public function screen_options( $status, $option, $value ) {
		return $value;
	}
	public function settings_page() {
		global $sites;
		$sites = get_option( 'serpr_sites_data' );
		if ( null == $sites ) {
			$status = '<span class="serpr-error">' . __( 'Unauthenticated', 'serp-rank' ) . '</span>';
		} else {
			$account_json = get_option( 'serpr_account_json' );
			$account_cfg  = json_decode( $account_json, true );
			$status       = '<span class="serpr-success">' . __( 'Authenticated', 'serp-rank' ) . '(' . esc_html( $account_cfg['client_email'] ) . ')</span>';

		}
		?>
		<div class="wrap">
			<?php echo $status; ?>
			<form action="options.php" enctype="multipart/form-data" method="post"> 
				<?php
				settings_errors( 'serp-rank' );
				settings_fields( 'serp-rank' );
				do_settings_sections( 'serp-rank' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
	public function admin_init() {
		$section_group = 'serp-rank';

		$settings_section = 'serpr_main';
		$page             = $section_group;

		add_settings_section(
			$settings_section,
			__( 'Configure Service Account', 'serp-rank' ),
			function() {
				?>
				<ol>
					<li><?php _e( 'Create a project in', 'serp-rank' ); ?> <a href="https://console.developers.google.com/projectcreate?previousPage=%2Fprojectselector2%2Fapis%2Fcredentials%3Fsupportedpurview%3Dproject&project=&folder=&organizationId=0&supportedpurview=project"><?php _e( 'Google API Console', 'serp-rank' ); ?></a>.</li>
					<li><?php _e( 'Make sure you enabled', 'serp-rank' ); ?> <a href="https://console.developers.google.com/apis/library/webmasters.googleapis.com?q=console"><?php _e( 'Google Search Console API', 'serp-rank' ); ?></a>.</li>
					<li><?php _e( 'Navigate to', 'serp-rank' ); ?> <a href="https://console.developers.google.com/apis/credentials"><?php _e( 'API\'s Credentials', 'serp-rank' ); ?></a> <?php _e( 'and create Service account key by clicking "Create credentials" button(create new service account) or use existing Service account', 'serp-rank' ); ?></li>
					<li><?php _e( 'Click on "Edit service account" button, then navigate to "Keys" tabs and click button "Add key", then "Create new key", select JSON key type and click "Create".', 'serp-rank' ); ?></li>
					<li><?php _e( 'Add service account from previous step to the list of allowed accounts of your website in', 'serp-rank' ); ?> <a href="https://search.google.com/search-console"><?php _e( 'Search Console', 'serp-rank' ); ?></a>.</li>
					<li><?php _e( 'Upload JSON from Step #4 via form below and make sure you JSON was successfully authenticated(status "Authenticated" has to be presented). Set an active website from list of allowed websites. Links from active website list will be checked for URL match', 'serp-rank' ); ?></li>
				</ol>
				<?php
			},
			$page
		);
		$setting_name = 'serpr_account_json';
		register_setting(
			$section_group,
			$setting_name,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'handle_account_json_upload' ),
				'default'           => null,
			)
		);
		add_settings_field(
			$setting_name,
			__( 'Update Service Account JSON file', 'serp-rank' ),
			function() {
				echo '<input type="file" id="serpr_account_json" name="serpr_account_json"/>';
			},
			$page,
			$settings_section
		);

		$setting_name = 'serpr_active_site';
		register_setting( $section_group, $setting_name );
		add_settings_field(
			$setting_name,
			__( 'Active site', 'serp-rank' ),
			function() {
				global $sites;
				if ( ! $sites ) {
					echo __( 'No sites available', 'serp-rank' );
					return;
				}

				$serpr_sites_cache = get_transient( 'serpr_sites_cache' );

				if ( ! $serpr_sites_cache ) {
					$serpr_sites_cache = $this->get_console_pages_data();
					if ( $serpr_sites_cache ) {
						$lifetime = get_option( 'serpr_cache_lifetime' );
						set_transient( 'serpr_sites_cache', $serpr_sites_cache, $lifetime );
					}
				}

				$active = get_option( 'serpr_active_site' );

				foreach ( $sites as $key => $value ) {
					$pages = 0;
					$url   = str_replace( 'sc-domain:', '', $value['siteUrl'] );
					if ( $active == $value['siteUrl'] ) {
						$pages = count( $serpr_sites_cache );
						$url  .= ' (' . $pages . ')';
					}
					echo '<label><input type="radio" name="serpr_active_site" value="' . esc_html( $value['siteUrl'] ) . '" ' . checked( $active, $value['siteUrl'], false ) . '/>' . esc_html( $url ) . '</label><br/>';
				}
			},
			$page,
			$settings_section
		);

		$setting_name = 'serpr_cache_lifetime';
		register_setting(
			$section_group,
			$setting_name,
			array(
				'default'           => 3600 * 24,
				'sanitize_callback' => array(
					$this,
					'handle_cache_lifetime_update',
				),
			)
		);
		add_settings_field(
			$setting_name,
			__( 'Cache lifetime (in seconds)', 'serp-rank' ),
			function() {
				$value = get_option( 'serpr_cache_lifetime' );
				echo '<input type="number" min="1" name="serpr_cache_lifetime" id="serpr_cache_lifetime" value="' . esc_html( $value ) . '"/><br/>';
			},
			$page,
			$settings_section
		);

		//excluded words
		$setting_name = 'serpr_excluded_keywords';
		register_setting(
			$section_group,
			$setting_name,
			array(
				'default'           => '',
				/*'sanitize_callback' => array(
					$this,
					'handle_cache_lifetime_update',
				),*/
			)
		);
		add_settings_field(
			$setting_name,
			__( 'Excluded keywords (one per line)', 'serp-rank' ),
			function() {
				$value = get_option( 'serpr_excluded_keywords' );
				echo '<textarea name="serpr_excluded_keywords" rows="5" cols="60" id="serpr_excluded_keywords">' . esc_html( $value ) . '</textarea>';
			},
			$page,
			$settings_section
		);
	}
	public function handle_cache_lifetime_update( $option ) {
		delete_transient( 'serpr_sites_cache' );
		return $option;
	}
	public function handle_account_json_upload( $option ) {
		if ( ! empty( $_FILES['serpr_account_json']['tmp_name'] ) ) {
			if ( 'application/json' == $_FILES['serpr_account_json']['type'] && 0 == $_FILES['serpr_account_json']['error'] ) {
				$json        = file_get_contents( $_FILES['serpr_account_json']['tmp_name'] );
				$account_cfg = json_decode( $json, true );

				if ( json_last_error() === JSON_ERROR_NONE ) {
					try {
						$client = new Google_Client();
						$client->setAuthConfig( $account_cfg );
						$client->addScope( 'https://www.googleapis.com/auth/webmasters' );
						$serviceWebmasters = new Google_Service_Webmasters( $client );
						$listSitesObj      = $serviceWebmasters->sites->listSites();
						if ( $listSitesObj->siteEntry ) {
							$sites = $listSitesObj->siteEntry;
						}
						update_option( 'serpr_sites_data', $sites );
						return $json;
					} catch ( Exception $e ) {
						$error = json_decode( $e->getMessage(), true );
						add_settings_error(
							'serp-rank',
							'serpr_account_json',
							__( 'Auth Error' ) . ': ' . $error['error'] . ' ' . $error['error_description'],
							'error'
						);
					}
				}
			}

			add_settings_error(
				'serp-rank',
				'serpr_account_json',
				__( 'Please, provide valid Google Service Account JSON file', 'serp-rank' ),
				'error'
			);
			delete_option( 'serpr_sites_data' );
			delete_option( 'serpr_account_json' );

		}
		$option = get_option( 'serpr_account_json' );

		return $option;
	}

	public function set_custom_edit_columns( $columns ) {
		global $serpr_sites_cache, $serpr_active_site, $excluded_keywords;
		$serpr_sites_cache = get_transient( 'serpr_sites_cache' );
		if ( ! $serpr_sites_cache ) {
			$serpr_sites_cache = $this->get_console_pages_data();
			if ( ! $serpr_sites_cache ) {
				return $columns;
			}
			$lifetime = get_option( 'serpr_cache_lifetime' );
			set_transient( 'serpr_sites_cache', $serpr_sites_cache, $lifetime );
		}
		$serpr_active_site = get_option( 'serpr_active_site' );
		$serpr_active_site = str_replace( 'sc-domain:', '', $serpr_active_site );
		$serpr_active_site = preg_replace( '#^https?://#', '', $serpr_active_site );
		$serpr_active_site = preg_replace( '#^www\.(.+\.)#i', '$1', $serpr_active_site );
		$serpr_active_site = rtrim( $serpr_active_site, '/' );

		$columns['serp_rank'] = __( 'SERP Rank - Keyword -> Position', 'serp-rank' );

		//excluded keywords 
		$excluded_keywords = get_option( 'serpr_excluded_keywords' );
		if ($excluded_keywords){
			$columns['serp_rank_excluded_keywords'] = __( 'SERP Rank - Excluded keywords', 'serp-rank' );
		}
		return $columns;
	}
	public function keywords_page() {
		$serpr_sites_cache = get_transient( 'serpr_sites_cache' );
		$serpr_active_site = get_option( 'serpr_active_site' );

		if ( ! $serpr_sites_cache ) {
			$serpr_sites_cache = $this->get_console_pages_data();
			if ( $serpr_sites_cache ) {
				$lifetime = get_option( 'serpr_cache_lifetime' );
				set_transient( 'serpr_sites_cache', $serpr_sites_cache, $lifetime );
			}
		}
		if ( ! $serpr_active_site ) {
			echo '<h4>' . __( 'Please, set active site', 'serp-rank' ) . '</h4>';
		}

		$serpr_active_site = get_option( 'serpr_active_site' );
		$serpr_active_site = str_replace( 'sc-domain:', '', $serpr_active_site );
		$serpr_active_site = preg_replace( '#^https?://#', '', $serpr_active_site );
		$serpr_active_site = preg_replace( '#^www\.(.+\.)#i', '$1', $serpr_active_site );
		$serpr_active_site = rtrim( $serpr_active_site, '/' );
		$data              = array();
		$i                 = 1;
		foreach ( $serpr_sites_cache as $key => $value ) {
			if ( $value->keys ) {
				$url   = $value->keys[0];
				$query = $value->keys[1];
				$url   = preg_replace( '#^https?://#', '', $url );
				$url   = preg_replace( '#^www\.(.+\.)#i', '$1', $url );
				$url   = rtrim( $url, '/' );

				if ( strpos( $url, $serpr_active_site ) === 0 ) {
						// $url = str_replace($serpr_active_site, '', $url);
					if ( isset( $_REQUEST['s'] ) && '' != $_REQUEST['s'] ) {
						if ( isset( $_REQUEST['exact'] ) ) {
							if ( 'on' == $_REQUEST['exact'] && ( $query == $_REQUEST['s'] || trailingslashit( $value->keys[0] ) == trailingslashit( $_REQUEST['s'] ) ) ) {
								$data[] = array(
									'ID'          => $i++,
									'keyword'     => $query,
									'url'         => $value->keys[0],
									'position'    => intval( $value->position ),
									'impressions' => $value->impressions,
									'clicks'      => $value->clicks,
								);
							}
						} elseif ( false !== strpos( $query, $_REQUEST['s'] ) || false !== strpos( $value->keys[0], $_REQUEST['s'] ) ) {
							$data[] = array(
								'ID'       => $i++,
								'keyword'  => $query,
								'url'      => $value->keys[0],
								'position' => intval( $value->position ),
								'impressions' => $value->impressions,
								'clicks'      => $value->clicks,
							);
						}
					} else {
						$data[] = array(
							'ID'          => $i++,
							'keyword'     => $query,
							'url'         => $value->keys[0],
							'position'    => intval( $value->position ),
							'impressions' => $value->impressions,
							'clicks'      => $value->clicks,
						);
					}
				}
			}
		}
		if ( ! class_exists( 'Serp_Rank_Keywords_Table' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-serp-rank-keywords-list-table.php';
		}
		$list = new Serp_Rank_Keywords_Table();

		echo '<div class="wrap"><h2>' . __( 'Keyword Rank Tracker', 'serp-rank' ) . '</h2>';

		$list->set_data( $data );

		$list->prepare_items();
		$list->display_search_box( 'search', 'search_id' );
		$list->display();
		echo '</div>';
	}
	public function custom_columns( $column, $post_id ) {
		global $serpr_sites_cache, $serpr_active_site, $excluded_keywords;
		if ( ! $serpr_sites_cache ) {
			return;
		}
		if ( $column == 'serp_rank' || $column == 'serp_rank_excluded_keywords' ) {

			$permalink    = get_permalink( $post_id );
			$permalink    = str_replace( home_url(), '', $permalink );
			$permalink    = rtrim( $permalink, '/' );
			$rank         = '';
			$res          = array();
			$original_url = '';
			foreach ( $serpr_sites_cache as $key => $value ) {
				if ( $value->keys ) {
					$url = $value->keys[0];

					$query = $value->keys[1];
					$url   = preg_replace( '#^https?://#', '', $url );
					$url   = preg_replace( '#^www\.(.+\.)#i', '$1', $url );
					$url   = rtrim( $url, '/' );
					if ( strpos( $url, $serpr_active_site ) === 0 ) {
						$url = str_replace( $serpr_active_site, '', $url );
						if ( $permalink == $url ) {
							$original_url  = $value->keys[0];
							$res[ $query ] = intval( $value->position );
								// $rank .= '<li class="compact-list">'.$query.' -> '.intval($value->position).'</li>'."\n";
						}
					}
				}
			}
			if ( $res ) {
				arsort( $res );
				$res = array_slice( array_reverse( $res ), 0, 10 );
				echo '<ul class="serpr-keys-list">';
				$excluded_keywords_list = array_map('trim', explode("\n", $excluded_keywords));
				if ( $column == 'serp_rank' ) {
					foreach ( $res as $key => $value ) {
						if (in_array($key, $excluded_keywords_list)){
							unset($res[$key]);
						}
					}
					$num = count( $res );
					
					foreach ( $res as $key => $value ) {
						echo '<li>' . esc_html( $key ) . ' -> ' . esc_html( $value ) . '</li>';
					}
					if ( 10 < $num ) {
						echo '<li><a href="' . admin_url( 'admin.php?page=keyword-rank-tracker&s=' . urlencode( $original_url ) ) . '&exact=on">' . __( 'More Keywords', 'serp-rank' ) . '</a></li>';
					}
					
				}elseif ($column == 'serp_rank_excluded_keywords'){
					foreach ( $res as $key => $value ) {
						if (in_array($key, $excluded_keywords_list)){
							echo '<li>' . esc_html( $key ) . '</li>';
						}
					}
				}
				echo '</ul>';
			} else {
				echo '-';
			}
		}
	}
}
