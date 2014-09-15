<?php
/*
Plugin Name: Stylish Login Pro
Plugin URI: http://www.rimazrauf.com
Description: Stylish Login Pro gives your WordPress login page a modern flat look, and you can Add your own Logo to its login page.

Version: 1.5
Author: Rimaz Rauf
Author URI: http://www.rimazrauf.com

License: GPLv2
*/

// Plugin version
if ( ! defined( 'ADD_LOGO_VERSION' ) ) {
	define( 'ADD_LOGO_VERSION', '1.5' );
}

if ( ! class_exists( 'WP_Stylish_Admin_Login' ) ) {
    class WP_Stylish_Admin_Login {
        /**
         * Construct the plugin object
         */
        public function __construct() {
            $plugin_options = get_option( 'wp_stylish_admin_login' );

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action( 'admin_init', array( $this, 'admin_init' ) );
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );

            if ( 'on' == $plugin_options['login'] ) {
            	add_action( 'login_enqueue_scripts', array( $this, 'login_enqueue_scripts' ) );
                add_filter( 'login_headertitle', array( $this, 'login_headertitle' ) );
                add_filter( 'login_headerurl', array( $this, 'login_headerurl' ) );
            }

        }

        public function admin_init() {
            register_setting( 'wp_stylish_admin_login', 'wp_stylish_admin_login', array( $this, 'wp_stylish_admin_login_validation' ) );

            load_plugin_textdomain( 'add-logo', null, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        }

    	/**
    	 * Validation
    	 *
    	 * @since 1.6
    	 */
    	public function wp_stylish_admin_login_validation( $input ) {
    		$input['login'] = ( empty( $input['login'] ) ) ? '' : 'on';
    		$input['admin'] = ( empty( $input['admin'] ) ) ? '' : 'on';
    		$input['image'] = esc_url( $input['image'] );

    		return $input;
    	}

        public function admin_menu() {
            add_options_page( __( 'Stylish Login', 'add-logo' ), __( 'Stylish Login', 'add-logo' ), 'manage_options', __FILE__, array( $this, 'add_logo_options_page' ) );
        }

        /**
         * Custom login logo URL
         *
         * This function is attached to the 'login_headerurl' filter hook.
         *
         * @since 1.6
         */
        public function login_headerurl() {
            return esc_url( home_url() );
        }

        /**
         * Custom login logo URL title
         *
         * This function is attached to the 'login_headertitle' filter hook.
         *
         * @since 1.6
         */
        public function login_headertitle() {
            return esc_attr( get_bloginfo( 'name' ) );
        }

        /**
         * Custom login screen
         *
         * This function is attached to the 'login_enqueue_scripts' filter hook.
         *
         * @since 1.6
         */
        function login_enqueue_scripts() {
            $plugin_options = get_option( 'wp_stylish_admin_login' );
        	if ( $image = $plugin_options['image' ] ) { ?>
<style>
body.login div#login h1 a {
    background-image: url(<?php echo esc_url( $image ); ?>);
    background-size: contain;
    width: 100%;
}
</style>
            <?php
            }
        }

        /**
         * Create the add logo to admin page
         *
         * This function is referenced in 'add_options_page()'.
         *
         * @since 1.6
         */
        public function add_logo_options_page() {
            if ( ! current_user_can( 'manage_options' ) )
                wp_die( __( 'You do not have sufficient permissions to access this page.', 'add-logo' ) );

            $plugin_options = get_option( 'wp_stylish_admin_login' );
            $image = ( $plugin_options['image'] ) ? '<img src="' . esc_url( $plugin_options['image'] ) . '" alt="" style="max-width: 100%;" />' : '';
            $display = ( $plugin_options['image'] ) ? '' : 'style="display: none;"';
        	?>
            <div class="wrap">
                <h2><?php _e( 'Add Logo to Admin', 'add-logo' ); ?></h2>
                <!-- Add Logo to Admin box begin-->
                <form method="post" action="options.php">
                    <?php settings_fields( 'wp_stylish_admin_login' ); ?>

                    <table id="add-logo-table" class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php _e( 'Logo Options', 'add-logo' ); ?></th>
                            <td>
                                <fieldset>
                                	<label for="add-logo-on-login">
                                	<input name="wp_stylish_admin_login[login]" id="add-logo-on-login" type="checkbox" <?php checked( esc_attr( $plugin_options['login'] ), 'on' ); ?>>
                                	<?php _e( 'Display logo on the login page', 'add-logo' ); ?></label>
                                	<br />
                                	<label for="add-logo-on-admin">
                                	<input name="wp_stylish_admin_login[admin]" id="add-logo-on-admin" type="checkbox" <?php checked( esc_attr( $plugin_options['admin'] ), 'on' ); ?>>
                                	<?php _e( 'Display logo on all admin pages', 'add-logo' ); ?></label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e( 'Upload Logo', 'add-logo' ); ?></th>
                            <td>
                                <input type="hidden" id="add-logo-image" name="wp_stylish_admin_login[image]" value="<?php echo esc_url( $plugin_options['image'] ); ?>" />
                                <div id="add-logo-image-container"><?php echo $image; ?></div>
                                <a href="#" class="select-image"><?php _e( 'Select image', 'add-logo' ); ?></a>&nbsp;&nbsp;&nbsp;<a href="#" class="delete-image" <?php echo $display; ?>><?php _e( 'Delete image', 'add-logo' ); ?></a>
                                <br />
                                <p class="description"><?php _e( 'Your logo should be no larger than 320px by 80px or else it will be resized on the login screen.', 'add-logo' ); ?></p>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(); ?>
                </form>
                <!-- Add Logo to Admin admin box end-->
            </div>
         <?php
         }

        /**
         * Set up the default options on activation
         *
         * This functions is referenced in 'register_activation_hook()'
         *
         * @since 1.6
         */
        public static function activate() {
            $default_option = array(
                'login' => 'on',
                'admin' => 'on',
                'image' => plugins_url( 'images/logo.png', __FILE__ )
            );

        	add_option( 'wp_stylish_admin_login', $default_option );
        }

        /**
         * Remove all options on deactivation
         *
         * This functions is referenced in 'register_deactivation_hook()'
         *
         * @since 1.6
         */
        public static function deactivate() {
        	delete_option( 'wp_stylish_admin_login' );
        }

        /**
         * Initialization of the plugin which creates the admin page
         *
         * This functions is attached to the 'admin_enqueue_scripts' action hook
         *
         * @since 1.6
         */
        public function admin_enqueue_scripts( $hook ) {
            $plugin_options = get_option( 'wp_stylish_admin_login' );

            if ( 'settings_page_stylish-admin-login/stylish-admin-login' == $hook ) {
                wp_enqueue_media();
                wp_enqueue_script( 'add_logo_to_admin', plugins_url( 'js/stylish-logo-select-image.js', __FILE__ ), array( 'jquery', 'media-upload', 'media-views' ), ADD_LOGO_VERSION, true );
            }

            if ( 'on' == $plugin_options['admin'] ) {
                wp_enqueue_script( 'add_logo_jquery', plugins_url( 'js/add-logo.js', __FILE__ ), array( 'jquery' ), ADD_LOGO_VERSION, true );
                wp_localize_script( 'add_logo_jquery', 'add_logo_image', esc_url( $plugin_options['image'] ) );
                wp_enqueue_style( 'add_logo_to_admin', plugins_url( 'css/add-logo.css', __FILE__ ), '', ADD_LOGO_VERSION );
            }
        }

    } // END class WP_Plugin_Template
}

if ( class_exists( 'WP_Stylish_Admin_Login' ) ) {
    /**
     * Installing the activation and deactivation hooks
     *
     * @since 1.6
     */
    register_activation_hook( __FILE__, array( 'WP_Stylish_Admin_Login', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'WP_Stylish_Admin_Login', 'deactivate' ) );

    // instantiate the plugin class
    $wp_stylish_admin_login = new WP_Stylish_Admin_Login();

    /**
     * Add settings link to plugin admin page
     *
     * @since 1.6
     */
    if ( isset( $wp_stylish_admin_login ) ) {
        function add_logo_plugin_settings_link( $links ) {
            $settings_link = '<a href="options-general.php?page=stylish-admin-login/stylish-admin-login.php">' . __( 'Settings', 'add-logo' ) . '</a>';
            array_unshift( $links, $settings_link );
            return $links;
        }

        $plugin = plugin_basename( __FILE__ );
        add_filter( "plugin_action_links_$plugin", 'add_logo_plugin_settings_link' );
    }
}

// Add a new logo to the login page
function wptutsplus_login_logo() { 
?>
    <style type="text/css">
		body  {
			background: rgba(228,200,213,1);
			background: -moz-radial-gradient(center, ellipse cover, rgba(228,200,213,1) 0%, rgba(143,101,159,1) 100%);
			background: -webkit-gradient(radial, center center, 0px, center center, 100%, , color-stop(0%, rgba(228,200,213,1)), color-stop(100%, rgba(143,101,159,1)));
			background: -webkit-radial-gradient(center, ellipse cover, rgba(228,200,213,1) 0%, rgba(143,101,159,1) 100%);
			background: -o-radial-gradient(center, ellipse cover, rgba(228,200,213,1) 0%, rgba(143,101,159,1) 100%);
			background: -ms-radial-gradient(center, ellipse cover, rgba(228,200,213,1) 0%, rgba(143,101,159,1) 100%);
			background: radial-gradient(ellipse at center, rgba(228,200,213,1) 0%, rgba(143,101,159,1) 100%);
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#e4c8d5', endColorstr='#8f659f', GradientType=1 );
		}
		#login_error, .login .message {
			padding: 10px;
			opacity: 0.49;
			-ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity = 49);
			/*-ms-filter must come before filter*/
			filter: alpha(opacity = 49);
			/*INNER ELEMENTS MUST NOT BREAK THIS ELEMENTS BOUNDARIES*/
			/*All filters must be placed together*/
		}
		.login #login h1 a {
			background-image: url( <?php echo plugins_url( 'images/logo-main.png', __FILE__ );?>);
			background-size: 300px auto;
			height: 70px;
			width: 300px;
		}
		.login #nav, .login #backtoblog {
			float:right;
			margin-bottom:-15px;
			margin-right:-20px;
			width:100%
		}
		.login #nav a, .login #backtoblog a {	
			float:right;
			font-size:1rem;
			color: #414242 !important;
		}
		.login #nav a:hover, .login #backtoblog a:hover {			
			color: #282828 !important;
			text-decoration:underline;
		}
		
		.login form .input, .login input[type="text"] {
			font-size:1.2rem !important;
			line-height:1.3em;
			background:#fff;
			margin-bottom:4%;
			border:1px solid #ccc;
			padding:4%;
			font-family:'Open Sans',sans-serif;
			font-size:95%;
			color:#555;
		}
	
		.login .button-primary {
			width:100%;
			height:45px !important;
			background:#3399cc;
			box-shadow:none !important;
			border-radius: 0;
			border:0;
			padding:4%;
			font-family:'Open Sans',sans-serif;
			font-size:100%;
			color:#fff;
			cursor:pointer;
			transition:background .3s;
			-webkit-transition:background .3s;
		}
		.login .button-primary:hover {
			background:#2288bb;
		}
		
		.login form .forgetmenot label {
			display:none;
		}
    </style>
<?php	
	}
add_action( 'login_enqueue_scripts', 'wptutsplus_login_logo' );