<?php
/*
Plugin Name: Izweb Custom Login
Plugin URI: https://github.com/nhiha60591/izweb-custom-member/
Description: Custom Register/ Login
Version: 1.0.1
Author: Izweb Team
Author URI: https://github.com/nhiha60591
Text Domain: izweb-custom-member
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if( !class_exists( 'Izweb_Custom_Login' ) ){
    class Izweb_Custom_Login{
        function __construct(){
            add_action( 'tml_new_user_registered', array( $this, 'add_option_subscription' ), 10, 2 );
            add_action( 'init', array( $this, 'show_mes' ) );
            $pending_role = get_role( 'pending' );
            if(!$pending_role){
                add_role( 'pending','Pending User', array('read'=> true));
            }
            $this->plugin_defines();
            $this->plugin_includes();
            add_action( 'woocommerce_order_status_changed', array( $this, 'change_user_role' ), 10, 3 );
        }
        function plugin_defines(){
            define( '__COMPLETE_REGISTER__', __('Register successfully. You must purchase a membership product to use this site.') );
        }
        function plugin_includes(){

        }
        function plugin_front_script(){

        }
        function add_option_subscription( $user_id, $user_pass ){
            update_user_meta( $user_id, 'izweb-subscription', 'no' );
            $user = new WP_User( $user_id );
            $user->set_role( 'pending' );
            wp_redirect( add_query_arg( array('izweb_action'=>'register-complete'), get_permalink( woocommerce_get_page_id( 'shop' ) ) ) );
            exit();
        }

        function show_mes(  ){
            if( isset( $_REQUEST['izweb_action']) && $_REQUEST['izweb_action'] == 'register-complete')
                wc_add_notice( __COMPLETE_REGISTER__ );

        }
        function change_user_role( $order_id, $old_status, $new_status){
            if( $new_status == 'completed' ){
                $order = new WC_Order( $order_id );
                if( WC_Subscriptions_Order::order_contains_subscription( $order ) ){
                    $user = new WP_User( $order->get_user_id() );
                    if( in_array( 'pending', $user->roles )){
                        $user->remove_role('pending');
                        $user->add_role( 'subscriber' );
                    }
                }
            }
        }

    }
    new Izweb_Custom_Login();
}