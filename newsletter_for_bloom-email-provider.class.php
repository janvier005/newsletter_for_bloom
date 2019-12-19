<?php

if ( !defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Bloom Provider
 */
class Nlplugin_ET_Core_API_Email_Provider extends \ET_Core_API_Email_Provider {

	public static $PLUGIN_REQUIRED;

	/**
	 * @inheritDoc
	 */
	public $custom_fields_scope = 'account';

	/**
	 * @inheritDoc
	 */
	public $uses_oauth = false;

	private $plugin;

	public function _set_http_timeout($timeout) {
		return 60;
	}

	public function __construct($owner = '', $account_name = '', $api_key = '' ) {
		$this->plugin = Nlplugin_Plugin::instantiate();

		$this->name = Nlplugin_Plugin::get_menu_branding();
		$this->slug = Nlplugin_Plugin::get_branding();

		//$this->http->expects_json = true;

		parent::__construct( $owner, $account_name, $api_key );

		if ( null === self::$PLUGIN_REQUIRED ) {
			self::$PLUGIN_REQUIRED = esc_html__( 'MailPoet plugin is either not installed or not activated.', 'et_core' );
		}
	}

	protected function _fetch_custom_fields( $list_id = '', $list = array() ) {
		static $processed = null;

		if ( is_null( $processed ) ) {
			$processed = array();

			$fields = [
			  0 => [
			    'id' => 'email',
			    'name' => 'Email',
			    'type' => 'text',
			    'params' => [
			      'required' => '1',
			    ],
			  ],
			  1 => [
			    'id' => 'first_name',
			    'name' => 'First name',
			    'type' => 'text',
			    'params' => [
			      'required' => '',
			    ],
			  ],
			  2 => [
			    'id' => 'last_name',
			    'name' => 'Last name',
			    'type' => 'text',
			    'params' => [
			      'required' => '',
			    ],
			  ]
			];

			foreach ( $fields as $field ) {
				$field_id   = $field['id'];
				$field_name = $field['name'];

				if ( in_array( $field_id, array( 'email', 'first_name', 'last_name' ) ) ) {
					continue;
				}

				$processed[ $field_id ] = array(
					'field_id' => $field_id,
					'name'     => $field_name,
					'type'     => 'any',
				);
			}
		}

		return $processed;
	}

	protected function _process_custom_fields( $args ) {
		if ( ! isset( $args['custom_fields'] ) ) {
			return $args;
		}

		$fields = $args['custom_fields'];

		unset( $args['custom_fields'] );

		foreach ( $fields as $field_id => $value ) {
			if ( is_array( $value ) && $value ) {
				// This is a multiple choice field (eg. checkbox, radio, select)
				$value = array_keys( $value );

				if ( count( $value ) > 1 ) {
					$value = implode( ',', $value );
				} else {
					$value = array_pop( $value );
				}
			}

			self::$_->array_set( $args, $field_id, $value );
		}

		return $args;
	}

	/**
	 * @inheritDoc
	 */
	public function get_account_fields() {
		return array();
	}

	/**
	 * @inheritDoc
	 */
	public function get_data_keymap($keymap = array()) {
		$keymap = array(
			'list'       => array(
				'list_id' => 'id',
				'name'    => 'name'
			),
			'subscriber' => array(
				'name'          => 'first_name',
				'last_name'     => 'last_name',
				'email'         => 'email'
			),
		);

		return parent::get_data_keymap( $keymap );
	}

	/**
	 * @inheritDoc
	 */
	public function fetch_subscriber_lists() {

		$newsletter = \Newsletter::instance();

		$data = $newsletter->get_lists();

		if(empty($data)){
			$data = [
			  0 => [
			    'id' => '1',
			    'name' => 'No List',
			    'type' => 'default',
			    'description' => 'Empty lists list.',
			    'created_at' => '0000-00-00 00:00:00',
			    'updated_at' => '0000-00-00 00:00:00',
			    'deleted_at' => NULL
			  ]
			];
		}

		if ( ! empty( $data ) ) {
			$this->data['lists'] = $this->_process_subscriber_lists( $data );
		}

		$this->data['is_authorized'] = true;

		$this->save_data();

		return 'success';
	}

	/**
	 * @inheritDoc
	 */
	public function subscribe($args, $url = '') {

		$array = array();
		$array['name'] = '';

		if(isset($args['email'])){
			$array['email'] = $args['email'];
		}
		if(isset($args['first_name'])){
			$array['name'] = $args['first_name'];
		}
		if(isset($args['last_name'])){
			$array['name'] = $array['name'].' '.$args['last_name'];
		}
		if(isset($args['list_id'])){
			$array['lists'] = array( $args['list_id']);
		}
		if(isset($args['custom_fields'])){
			$array['profile'] = $args['custom_fields'];
		}

		$result = \TNP::subscribe($array);

		return 'success';
	}
}
