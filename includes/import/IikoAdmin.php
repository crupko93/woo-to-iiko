<?php
/**
 * Страница импорта товаров в админке
 */

if ( ! defined( 'ABSPATH' ) ) {
  return;
}

class IikoAdmin {

	/**
	 * The single instance of IikoImport.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

    public $settings = null;
    public $_version;
	public $base = 'iiko_';
    public $_token;
    public $_locale;
    public $file;
    public $dir;
    public $assets_dir;
    public $assets_url;
    public $script_suffix;

	public function __construct (  $file = '', $version = '0.0.3', $_token = 'iiko', $_locale = 'iiko' ) {
        $this->_version = $version;
        $this->_token = $_token;
        $this->_locale = $_locale;
        // Load plugin environment variables
        $this->file = $file; // wp-content\plugins\woo-to-iiko\iiko.php
        $this->dir = dirname( $this->file );
        $this->assets_dir = trailingslashit( $this->dir ) . 'assets';
        $this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );


        // Register plugin settings
        add_action( 'admin_init' , array( $this, 'register_settings' ) );
        // Add settings page to menu
        add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );
        // Add settings link to plugins page
        add_filter( 'plugin_action_links_' . plugin_basename( $this->file ) , array( $this, 'add_settings_link' ), 10, 1 );
        add_filter( 'plugin_row_meta', array( $this, 'iiko_row_meta' ), 10, 4);


        register_activation_hook( $this->file, array( $this, 'install' ) );

        // Load API for generic admin functions
        if ( is_admin() ) {
          new IIKO_Admin_Api();
        }
        // Handle localisation
        $this->load_plugin_textdomain();
        add_action( 'init', array( $this, 'load_localisation' ), 0 );
	}

    /**
    * Load plugin localisation
    */
    public function load_localisation () {
        load_plugin_textdomain( $this->_locale, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
    } // End load_localisation ()

    /**
    * Load plugin textdomain
    * @return  void
    */
    public function load_plugin_textdomain () {
        $domain = $this->_locale;
        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
        load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
    }
    /**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item (){
    add_submenu_page( 'woocommerce', __('Iiko import','iiko'), __('Iiko import', 'iiko'), 'manage_options', $this->_token, array( $this, 'settings_page' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
        $settings_link = '<a href="admin.php?page=wc-settings&tab=iiko">' . __( 'Settings', 'iiko' ) . '</a>';
        $import_link = '<a href="admin.php?page=' . $this->_token .'">' . __( 'Import', 'iiko' ) . '</a>';
  		array_push( $links, $import_link );
        array_unshift($links, $settings_link);
  		return $links;
	}

    public function iiko_row_meta( $meta, $plugin_file ){
        if($plugin_file !== plugin_basename( $this->file ))
          return $meta;

          if(empty(check_license())) {
            $meta[] = '<a href="/wp-admin/options-general.php?page=iiko" style="color: red;">' .
            __( 'Activate license', 'iiko' ) . '</a>';
        }
        return $meta;
    }


	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {
				if ( $current_section && $current_section != $section )
				  continue;
				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->_token . '_settings' );
				foreach ( $data['fields'] as $field ) {
					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}
					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->admin, 'display_field' ), $this->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section )
				  break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
     * Load settings page content
     */
    public function settings_page () {

        $iiko = IikoApi::get_settings();// Получаем настройки из опций
        if(!$iiko['pass'] or !$iiko['login']or !$iiko['serv']){
          ?>
          <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p><?=__('Please set Connection settings.', 'iiko')?>  <a href="/wp-admin/admin.php?page=wc-settings&tab=iiko" class="button-primary"><?php _e('Set settings', 'iiko') ?></a></p>
          </div>
          <?php
        } elseif( false === set_organization()){
            ?>
          <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p><?=__('Настройки соединения заданы не правильно.', 'iiko')?>  <a href="/wp-admin/admin.php?page=wc-settings&tab=iiko" class="button-primary"><?php _e('Set settings', 'iiko') ?></a></p>
          </div>
            <?php
        }else {
          do_action('add_form_import', $iiko, false); // Подключение шаблона формы для импорта
          /** add_form_import(); */
        }
    }

  /**
   * Main IikoImport Instance
   *
   * @return IikoAdmin|object
   */
  public static function instance ( $file = '', $version = '', $_token = '', $_locale = '' ) {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self( $file, $version, $_token, $_locale );
    }
    return self::$_instance;
  } // End instance ()

  /**
   * Singleton rules
   */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	}
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	}

  /**
   * Installation. Runs on activation.
   */
  public function install () {
    $this->_log_version_number();
  } // End install ()
  private function _log_version_number () {
    update_option( $this->_token . '_version', $this->_version, false );
  } // End _log_version_number ()
}