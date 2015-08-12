<?php

class AF_Feedback_Form_Db
{


    /**
     *Create teble by forms data on install plugin
     */
    public function addTable ()
    {

        global $wpdb;
        $table_name = $wpdb->prefix . 'af_feedback_form';

        $wpdb->query( "CREATE TABLE IF NOT EXISTS $table_name (
                      `id` int(10) NOT NULL AUTO_INCREMENT,
                      `phone` int(10) DEFAULT NULL,
                      `username` varchar(255) DEFAULT NULL,
                      `email` varchar(255) DEFAULT NULL,
                      `start_date` int(10) DEFAULT NULL,
                      `end_date` int(10) DEFAULT NULL,
                      `submit_time` int(10) DEFAULT NULL,
                      PRIMARY KEY (`id`))" );
    }

    /**
     *Drop table on uninstall plugin
     */
    public function dropTable ()
    {

        global $wpdb;
        $table_name = $wpdb->prefix . 'af_feedback_form';
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" );

    }

    /**Add row with form data to db
     * @param array $formData
     * @return bool
     */
    public function addSendFormToDb ( array $formData )
    {
        global $wpdb;

        $toDb = array (
            'phone'       => esc_sql( $formData[ 'phone' ] ),
            'email'       => esc_sql( $formData[ 'email' ] ),
            'from_date'   => esc_sql( $formData[ 'from' ] ),
            'to_date'     => esc_sql( $formData[ 'to' ] ),
            'username'    => esc_sql( $formData[ 'name' ] ),
            'submit_time' => time(),
        );

        $table_name = $wpdb->prefix . 'af_feedback_form';
        if ($wpdb->insert( $table_name, $toDb ) == true) {
            return TRUE;
        }
        return false;
    }

    /**Get all submitted forms
     * @return mixed
     */
    public function loadSubmittedFormData ()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'af_feedback_form';

        $submittedForms = $wpdb->get_results ("SELECT * FROM $table_name", 'OBJECT_K');

        return $submittedForms;
    }

    /**Get one form data by ID
     * @param $formId
     * @return mixed
     */
    public function loadOneSubmitedFormData ( $formId ){
        global $wpdb;
        $table_name = $wpdb->prefix . 'af_feedback_form';

        $formData = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $formId");

        return $formData;


    }
}