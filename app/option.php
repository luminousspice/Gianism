<?php

namespace Gianism;

/**
 * Class Option
 *
 * @package Gianism
 * @author Takahashi Fumiki
 *
 * @property-read bool $fb_enabled
 * @property-read bool $facebook_enabled
 * @property-read string $fb_app_id
 * @property-read string $fb_app_secret
 * @property-read int $fb_fan_gate
 * @property-read bool $tw_enabled
 * @property-read bool $twitter_enabled
 * @property-read string $tw_screen_name
 * @property-read string $tw_consumer_key
 * @property-read string $tw_consumer_secret
 * @property-read string $tw_access_token
 * @property-read string $tw_access_token_secret
 * @property-read bool $ggl_enabled
 * @property-read bool $google_enabled
 * @property-read string $ggl_consumer_key
 * @property-read string $ggl_consumer_secret
 * @property-read bool $yahoo_enabled
 * @property-read string $yahoo_application_id
 * @property-read string $yahoo_consumer_secret
 * @property-read bool $mixi_enabled
 * @property-read string $mixi_consumer_key
 * @property-read string $mixi_consumer_secret
 * @property-read string $mixi_access_token
 * @property-read string $mixi_refresh_token
 * @property-read bool $show_button_on_login
 * @property-read int $button_type
 */
class Option extends Pattern\Singleton
{

    /**
     * @const UPDATED_ACTION Action name which fires on updating option
     */
    const UPDATED_ACTION = 'gianism_option_updated';

    /**
     * オプションのキー名
     *
     * @var string
     */
    protected $key = '';

    /**
     * オプション
     *
     * @var array
     */
    public $values = array();

    /**
     * オプション初期値
     *
     * @var array
     */
    protected $default_option = array(
        'fb_enabled' => 0,
        'fb_app_id' => '',
        'fb_app_secret' => '',
        'fb_fan_gate' => 0,
        'tw_enabled' => 0,
        "tw_screen_name" => "",
        "tw_consumer_key" => "",
        "tw_consumer_secret" => "",
        "tw_access_token" => "",
        "tw_access_token_secret" => "",
        "ggl_enabled" => 0,
        "ggl_consumer_key" => "",
        "ggl_consumer_secret" => "",
        'yahoo_enabled' => 0,
        'yahoo_application_id' => '',
        'yahoo_consumer_secret' => '',
        "mixi_enabled" => 0,
        "mixi_consumer_key" => "",
        "mixi_consumer_secret" => "",
        "mixi_access_token" => "",
        "mixi_refresh_token" => "",
        'show_button_on_login' => true,
        'button_type' => 0,
    );


    /**
     * Constructor
     *
     * @param array $argument
     */
    protected function __construct(array $argument = array()){
        $this->key = $this->name.'_option';
        $saved_option = get_option($this->key);
        foreach($this->default_option as $key => $value){
            if(!isset($saved_option[$key])){
                $this->values[$key] = $value;
            }else{
                $this->values[$key] = $saved_option[$key];
            }
        }
    }

    /**
     * Save options with post data
     */
    public function update(){
        $this->values = wp_parse_args(array(
            'fb_enabled' => ($this->post('fb_enabled') == 1) ? 1 : 0,
            'fb_app_id' => (string)$this->post('fb_app_id'),
            'fb_app_secret' => (string)$this->post('fb_app_secret'),
            'fb_fan_gate' => (int)$this->post('fb_fan_gate'),
            'tw_screen_name' => (string)$this->post('tw_screen_name'),
            'tw_enabled' => (string)($this->post('tw_enabled') == 1) ? 1 : 0,
            "tw_consumer_key" => (string)$this->post('tw_consumer_key'),
            "tw_consumer_secret" => (string)$this->post('tw_consumer_secret'),
            "tw_access_token" => (string)$this->post('tw_access_token'),
            "tw_access_token_secret" => (string)$this->post('tw_access_token_secret'),
            'ggl_enabled' => ($this->post('ggl_enabled') == 1) ? 1 : 0,
            "ggl_consumer_key" => (string)$this->post('ggl_consumer_key'),
            "ggl_consumer_secret" => (string)$this->post('ggl_consumer_secret'),
            "yahoo_enabled" => ($this->post('yahoo_enabled') == 1) ? 1 : 0,
            "yahoo_application_id" => (string)$this->post('yahoo_application_id'),
            "yahoo_consumer_secret" => (string)$this->post('yahoo_consumer_secret'),
            "mixi_enabled" => ($this->post('mixi_enabled') == 1) ? 1 : 0,
            "mixi_consumer_key" => (string)$this->post('mixi_consumer_key'),
            "mixi_consumer_secret" => (string)$this->post('mixi_consumer_secret'),
            'show_button_on_login' => (boolean)$this->post('show_button_on_login'),
            'button_type' => (int)$this->post('button_type'),
        ), $this->values);
        if(update_option($this->key, $this->values)){
            $this->add_message($this->_('Option updated.'));
            do_action(self::UPDATED_ACTION, $this->values);
        }else{
            $this->add_message($this->_('Option failed to update.'), true);
        }
    }

    /**
     * Detect if show login buttons
     *
     * @param string $context
     * @return mixed|void
     */
    public function show_button_on_login( $context = 'login' ){
        /**
         * Display Case-by-case filter
         *
         * @param bool $display Whether to display
         * @param string $context 'login', 'register', etc.
         */
        return apply_filters('gianism_show_button_on_login', $this->show_button_on_login, $context);
    }

    /**
     * Return button types
     *
     * @return array
     */
    public function button_types(){
        return array(
            $this->_('Medium'),
            $this->_('Large'),
        );
    }

    /**
     * Returns if option is wrong
     *
     * @param string $name
     * @return bool
     */
    public function has_invalid_option($name){
        switch($name){
            case 'google_redirect':
                $option = get_option($this->key, array());
                return isset($saved_option['ggl_redirect_uri']) && !empty($saved_option['ggl_redirect_uri']);
                break;
            default:
                return false;
                break;
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
            case 'facebook_enabled':
                return $this->fb_enabled;
                break;
            case 'twitter_enabled':
                return $this->tw_enabled;
                break;
            case 'google_enabled':
                return $this->ggl_enabled;
                break;
            default:
                if( isset($this->values[$name]) ){
                    return $this->values[$name];
                }else{
                    return parent::__get($name);
                }
                break;
        }
    }
}