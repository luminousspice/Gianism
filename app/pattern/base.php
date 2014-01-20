<?php

namespace Gianism\Pattern;

use Gianism\Option;

/**
 * Base class
 *
 * @package Gianism
 * @author Takahashi Fumiki
 * @since 2.0
 * @property-read string $dir
 * @property-read string $url
 */
abstract class Base
{

    /**
     * Name
     *
     * Used for option key, nonce, etc.
     *
     * @var string
     */
    protected $name = 'wp_gianism';


    /**
     * Version number
     *
     * @var string
     */
    protected $version = \GIANISM_VERSION;


    /**
     * i18n Domain
     *
     * @var string
     */
    protected $domain = \GIANISM_DOMAIN;

    /**
     * Message post type
     *
     * @var string
     */
    public $message_post_type = 'gianism_message';

    /**
     * Alias of __
     *
     * @param string $string
     * @return string
     */
    public function _($string){
        return __($string, $this->domain);
    }

    /**
     * Alias of _e
     *
     * @param string $string
     */
    public function e($string){
        _e($string, $this->domain);
    }

    /**
     * Return $_GET
     *
     * @param string $key
     * @return mixed
     */
    public function get($key){
        if(isset($_GET[$key])){
            return $_GET[$key];
        }else{
            return null;
        }
    }

    /**
     * Return $_POST
     *
     * @param string $key
     * @return mixed
     */
    public function post($key){
        if(isset($_POST[$key])){
            return $_POST[$key];
        }else{
            return null;
        }
    }

    /**
     * return $_REQUEST
     *
     * @param string $key
     * @return mixed
     */
    public function request($key){
        if(isset($_REQUEST[$key])){
            return $_REQUEST[$key];
        }else{
            return null;
        }
    }


    /**
     * Return nonce name with prefix
     *
     * @param string $action
     * @return string
     */
    public function nonce_action($action){
        return $this->name."_".$action;
    }

    /**
     * Alias of wp_nonce_field
     *
     * @param string $action
     * @param bool $referrer Default false.
     */
    public function nonce_field($action, $referrer = false){
        wp_nonce_field($this->nonce_action($action), "_{$this->name}_nonce", $referrer);
    }

    /**
     * Check nonce
     *
     * @param string $action
     * @param string $referrer
     * @return boolean
     */
    public function verify_nonce($action, $referrer = ''){
        if($referrer){
            return ( (wp_verify_nonce($this->request("_{$this->name}_nonce"), $this->nonce_action($action)) && $referrer == $this->request("_wp_http_referer")) );
        }else{
            return wp_verify_nonce($this->request("_{$this->name}_nonce"), $this->nonce_action($action));
        }
    }

    /**
     * Returns if login is forced to use SSL.
     *
     * To override it, use filter `gianism_force_ssl_login`
     *
     * @return boolean
     */
    public function is_ssl_required(){
        $is_ssl = (defined('FORCE_SSL_LOGIN') && FORCE_SSL_LOGIN) || (defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN);
        /**
         * Login action must be under SSL or not.
         *
         * @param bool $is_ssl
         */
        return apply_filters('gianism_force_ssl_login', $is_ssl);
    }

    /**
     * Determin if sevice is enabled.
     *
     * @param string $service If not specified, one of the services are enabled, return true.
     * @return bool
     */
    public function is_enabled($service = ''){
        /** @var \Gianism\Option $option */
        $option = Option::get_instance();
        switch( strtolower($service) ){
            case 'facebook':
                return $option->fb_enabled;
                break;
            case 'twitter':
                return $option->tw_enabled;
                break;
            case 'google':
                return $option->ggl_enabled;
                break;
            case 'yahoo':
                return $option->yahoo_enabled;
                break;
            case 'mixi':
                return $option->mixi_enabled;
                break;
            case '':
                return ( $option->fb_enabled || $option->tw_enabled || $option->ggl_enabled || $option->yahoo_enabled || $option->mixi_enabled );
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Add message to show
     *
     * @param string $string
     * @param bool $error
     */
    protected function add_message($string, $error = false){
        $key = $error ? 'error' : 'updated';
        if( session_id() ){
            if( !isset($_SESSION[$this->name]) ){
                $_SESSION[$this->name] = array();
            }
            if( !isset($_SESSION[$this->name][$key]) ){
                $_SESSION[$this->name][$key] = array();
            }
            $_SESSION[$this->name][$key][] = $string;
        }
    }

    /**
     * Getter
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name){
        switch($name){
            case 'url':
                return plugin_dir_url(dirname(dirname(__FILE__)));
                break;
            case 'dir':
                return plugin_dir_path(dirname(dirname(__FILE__)));
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * Retrieve user meta's owner ID
     *
     * @global \wpdb $wpdb
     * @param string $key
     * @param string $value
     * @return int User ID. If not exists, return 0
     */
    public function get_meta_owner($key, $value){
        /** @var \wpdb $wpdb */
        global $wpdb;
        $query = <<<EOS
            SELECT user_id FROM {$wpdb->usermeta}
            WHERE meta_key = %s AND meta_value = %s
EOS;
        return (int)$wpdb->get_var($wpdb->prepare($query, $key, $value));
    }

    /**
     * Get service instance
     *
     * @param string $service
     * @return null|\Gianism\Service\Common
     */
    public function get_service_instance($service){
        $service = strtolower($service);
        $class_name = 'Gianism\\Service\\'.ucfirst($service);
        if( class_exists($class_name) ){
            $reflection = new \ReflectionMethod($class_name, 'get_instance');
            if( $reflection->isPublic() && $reflection->isStatic() ){
                return $class_name::get_instance();
            }
        }
        return null;
    }

    /**
     * Short hand for wp_die
     *
     * @param string $message
     * @param int $status_code
     * @param bool $return
     */
    protected function wp_die($message, $status_code = 500, $return = true){
        wp_die($message, get_status_header_desc($status_code).' | '.get_bloginfo('name'), array(
            'response' => intval($status_code),
            'back_link' => (boolean) $return,
        ));
    }

    /**
     * Write session
     *
     * @param string $key
     * @param mixed $value
     */
    protected function session_write($key, $value){
        if( isset($_SESSION[$this->name]) ){
            $_SESSION[$this->name][$key] = $value;
        }
    }

    /**
     * Get session
     *
     * After get, session key will be deleted.
     *
     * @param string $key
     * @return bool
     */
    protected function session_get($key){
        if( isset($_SESSION[$this->name][$key]) ){
            $value = $_SESSION[$this->name][$key];
            $this->session_delete($key);
            return $value;
        }
        return false;
    }

    /**
     * Delete session
     *
     * @param string $key
     */
    protected function session_delete($key){
        if( isset($_SESSION[$this->name][$key]) ){
            unset($_SESSION[$this->name][$key]);
        }
    }
}