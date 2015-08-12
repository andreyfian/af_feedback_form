<?php
/*
Plugin Name: Andrey Filippov Feadback_Form
Description: Simple feedback form. Use shortcode [af_feedback_form]
Author: Andrey Filippov
*/
require_once( 'af_feedback_form.db_class.php' );

register_activation_hook( __FILE__, array ( 'AF_Feedback_Form_Class',
                                            'on_activation' ) );
register_deactivation_hook( __FILE__, array ( 'AF_Feedback_Form_Class',
                                              'on_deactivation' ) );
register_uninstall_hook( __FILE__, array ( 'AF_Feedback_Form_Class',
                                           'on_uninstall' ) );

add_action( 'plugins_loaded', array ( 'AF_Feedback_Form_Class',
                                      'init' ) );

class AF_Feedback_Form_Class
{
    protected static $instance;

    public static function init ()
    {
        is_null( self::$instance ) AND self::$instance = new self;
        return self::$instance;
    }

    /**
     *Install/activation hook
     */
    public static function on_activation ()
    {
        if ( current_user_can( 'activate_plugins' ) === FALSE )
            return;
        $plugin = isset( $_REQUEST[ 'plugin' ] ) ? $_REQUEST[ 'plugin' ] : '';
        check_admin_referer( "activate-plugin_{$plugin}" );

        //Add plugin options with default value to table 'options' if not exist

        $pluginOptions = array (

            'manager_email' => 'test@test.com',
            'email_subj'    => 'Form sumitted',
            'email_content' => 'Text area',
            'fail_from'     => 'invalid FROM area',
            'fail_to'       => 'invalid TO area',
            'fail_name'     => 'to short name',
            'fail_phone'    => 'to short phone',
            'fail_email'    => 'invalid emale',
            'form_send'     => 'Form submitted!',

        );

        $pluginOptions = serialize( $pluginOptions );

        add_option( 'af_feedback_form_options', $pluginOptions );

        //Add table with feedback form data

        $db = new AF_Feedback_Form_Db;

        $db->addTable();

    }

    /**
     *Deactivation hook
     */
    public static function on_deactivation ()
    {
        if ( current_user_can( 'activate_plugins' ) === FALSE )
            return;
        $plugin = isset( $_REQUEST[ 'plugin' ] ) ? $_REQUEST[ 'plugin' ] : '';
        check_admin_referer( "deactivate-plugin_{$plugin}" );

    }

    /**
     *Uninstall hook
     */
    public static function on_uninstall ()
    {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;
        check_admin_referer( 'bulk-plugins' );


        if ( __FILE__ != WP_UNINSTALL_PLUGIN )
            return;

        //Drop plugin options from table 'options'
        delete_option( 'af_feedback_form_options' );

        $db = new AF_Feedback_Form_Db();

        //And drop table with plugin data
        $db->dropTable();
    }

    /** Register action and shortcode
     *
     */
    public function __construct ()
    {


        add_shortcode( 'af_feedback_form', array ( $this, 'af_feedback_form_shortcode' ) );
        //AJAX frontend action
        add_action( 'wp_ajax_af_feedback_send', array ( $this, 'af_feedback_form_callback' ) );
        add_action( 'wp_ajax_nopriv_af_feedback_send', array ( $this, 'af_feedback_form_callback' ) );
        //AJAX admin actions
        add_action( 'wp_ajax_af_feedback_settings', array ( $this, 'af_feedback_settings' ) );
        add_action( 'wp_ajax_af_feedback_resend', array ( $this, 'af_feedback_resend' ) );
        //Add menu item
        add_action( 'admin_menu', array ( $this, 'af_feedback_form_admin_menu' ) );

    }

    /** Load plugin template when find shortcode
     *
     */
    public function af_feedback_form_shortcode ()
    {

        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-ui-tabs' );


        load_template( dirname( __FILE__ ) . '/frontend/template.php' );
    }

    /** Ajax callback. Validate user input data and add string to DB
     *
     */
    function af_feedback_form_callback ()
    {
        ob_clean();
echo '<p>sent!</p>'; wp_die();
        $pluginOption = get_option( 'af_feedback_form_options' );

        $pluginOption = unserialize( $pluginOption );


        parse_str( $_POST[ 'data' ], $userSendData );

        foreach ( $userSendData as $key => $value ) {

            $userSendData[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );

        }

        if ( ! $userSendData[ 'from' ] ) {

            echo $pluginOption[ 'fail_from' ];

        } elseif ( ! $userSendData[ 'to' ] ) {

            echo $pluginOption[ 'fail_to' ];

        } elseif ( strlen( $userSendData[ 'name' ] ) < 1 ) {

            echo $pluginOption[ 'fail_name' ];

        } elseif ( strlen( $userSendData[ 'phone' ] ) < 1 ) {

            echo $pluginOption[ 'fail_phone' ];

        } elseif ( ! preg_match( '/^([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})$/', $userSendData[ 'email' ] ) ) {

            echo $pluginOption[ 'fail_email' ];

        } else {
            $db = new AF_Feedback_Form_Db();

            if ($db->addSendFormToDb( $userSendData )  === TRUE ) {

                $this->sendEmailOnSubmit( TRUE, TRUE, $userSendData );

                echo $pluginOption[ 'form_send' ];

                wp_die();
            }

        }

        wp_die();
    }

    /**
     *AJAX action - plugin settings save
     */
    function af_feedback_settings ()
    {
        $this->checkUser();

        parse_str( $_POST[ 'data' ], $savedSettings );

        $pluginOption = get_option( 'af_feedback_form_options' );

        $pluginOption = unserialize( $pluginOption );

        $pluginOption = array_merge( $pluginOption, $savedSettings );

        $pluginOption = serialize( $pluginOption );


        if ( update_option( 'af_feedback_form_options', $pluginOption ) === TRUE ) {
            echo 'Settings update';
        } else {
            echo 'Sory, some trouble';
        }


        wp_die();
    }

    /**
     *AJAX action - resend confirm email from adminpanel
     */
    function af_feedback_resend () {

        $this->checkUser();

        $formId = sanitize_text_field( $_POST[ 'data' ] );

        $db = new AF_Feedback_Form_Db();

        $formData = $db->loadOneSubmitedFormData($formId);

        $sendData = array(
            'from' => $formData->from_date,
            'to' => $formData->to_date,
            'name' => $formData->username,
            'phone' => $formData->phone,
            'email' => $formData->email,
        );

        $this->sendEmailOnSubmit(FALSE, TRUE, $sendData);

        echo 'ok';

        wp_die();
    }

    /**
     *Add menu item to admin menu
     */
    function af_feedback_form_admin_menu ()
    {
        add_options_page( 'AF_Feadback_Form config', 'AF_Feadback_Form', 'manage_options', 'af_form_option_slug', array ( $this,
                                                                                                                          'af_feedback_form_settings_page' ) );
    }

    /**
     *Plugin settings page load
     */
    function  af_feedback_form_settings_page ()
    {
        $this->checkUser();

        $submittedForms = new AF_Feedback_Form_Db();
        $submittedForms = $submittedForms->loadSubmittedFormData();

        $pluginOption = get_option( 'af_feedback_form_options' );
        $pluginOption = unserialize( $pluginOption );

        extract( $submittedForms );
        extract( $pluginOption );


        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-ui-tabs' );


        require( dirname( __FILE__ ) . '/backend/template.php' );
    }


    /**Send email to user or/and manager
     * @param bool $manager
     * @param bool $user
     * @param array $userSendData
     */
    private function sendEmailOnSubmit ( $manager = FALSE, $user = FALSE, array $userSendData )
    {
        $pluginOption = get_option( 'af_feedback_form_options' );

        $pluginOption = unserialize( $pluginOption );

        $html = "From:" . $userSendData[ 'from' ];
        $html .= "To:" . $userSendData[ 'to' ];
        $html .= "Name:" . $userSendData[ 'name' ];
        $html .= "Phone:" . $userSendData[ 'phone' ];
        $html .= "Email:" . $userSendData[ 'email' ];


        if ( $manager === TRUE ) {

            $mesageToManager = 'You have new submitted form  ' . $html . ' on ' . date( ' l jS \of F Y h:i:s A ', time() );

            wp_mail( $pluginOption[ 'manager_email' ], 'New form submitted', $mesageToManager );


        }
        if ( $user === TRUE ) {

            wp_mail( $userSendData[ 'email' ], $pluginOption [ 'email_subj' ], $pluginOption [ 'email_content' ] . $html );

        }
    }

    private function checkUser () {
        if( ! current_user_can('manage_options') ){
            wp_die();
        }
    }

}