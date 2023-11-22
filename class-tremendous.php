<?php
/**
 * Privacy policy shortcode
 *
 * @category Wodrpress-Plugins
 * @package  WP-FoodTec
 * @author   FoodTec Solutions <info@foodtecsolutions.com>
 * @license  GPLv2 or later
 * @link     https://gitlab.foodtecsolutions.com/fts/wordpress/plugins/wp-foodtec
 * @since    1.0.0
 */

namespace WP_FoodTec\Includes\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The Tremendous shorcode class.
 */
class Tremendous {
    /**
     * Constructor
     */

    public function __construct() {
        add_shortcode( 'foodtec_tremendous', array( $this, 'tremendous_callback' ) );
        add_action( 'register_shortcode_ui', array( $this, 'shortcode_foodtec_tremendous' ) );

        add_action( 'wp_ajax_tremendous_request', array( $this, 'tremendous_request' ) );
        add_action( 'wp_ajax_nopriv_tremendous_request', array( $this, 'tremendous_request' ) );
    }

    /**
     * The Tremendous shortcode callback function.
     *
     * @return string The Privacy Policy HTML
     */
    public function tremendous_callback(): string {
		$isTremendous = get_option('foodtec_tremendous_activate', false);
		if(!$isTremendous){
			exit('Tremendous is not activated!');
		}

        $brand = ( new \WP_FoodTec\Includes\Libraries\Requests\Marketing\Brand )->request();

        if ( sizeof( $brand->plans ) === 0 ) {
            return '';
        }


        $params = array(
            'plan'         => $brand->plans[0],
            'register_url' => ( new \WP_FoodTec\Includes\Libraries\Html_Helpers )->get_register_url(),
            'recaptcha'     => ( new \WP_FoodTec\Includes\Libraries\Google_Recaptcha )->html( 'light' ),
            'nonce'         => wp_create_nonce( 'tremendous_request_nonce' ),
        );

		if ( ( new \WP_FoodTec\Includes\Libraries\Auth_Service )->is_logged_in() ) {
			$account = ( new \WP_FoodTec\Includes\Libraries\Requests\Marketing\Account )->request();

			if ($account) {
				$params['account'] = $account;
			}

		}

		$params['login_page'] = esc_url( get_page_link(absint( get_option( 'foodtec_login_page', '' ) )) );

        return ( new \WP_FoodTec\Includes\Libraries\Template )->load( 'tremendous.php', $params );



    }


    public function tremendous_request() {

        $ajax_options = array(
            'nonce'           => 'tremendous_request_nonce',
            'recaptcha'       => true,
            'ssl'             => true,
            'query_arguments' => array(
                'rewards_selection'            => array(
                    'sanitize_function' => 'sanitize_text_field',
                    'required'          => true,
                ),

            ),
        );

        $params = ( new \WP_FoodTec\Includes\Libraries\Ajax_Validator )->validate( $ajax_options );
		/**
		* If not logged in stop the process.
		*/
		if ( ( new \WP_FoodTec\Includes\Libraries\Auth_Service )->is_logged_in() ) {
			$account = (new \WP_FoodTec\Includes\Libraries\Requests\Marketing\Account)->request();

			if ($account) {
				$params['account'] = $account;
			}
		} else {
			exit('Please login to redeem a card');
		}

        $brand = ( new \WP_FoodTec\Includes\Libraries\Requests\Marketing\Brand )->request();

		$plans = $brand->plans[0];
		$chosenProduct = false;
		/**
		 * Deduct loyalty points
		 */
		foreach ($plans->thirdPartyAwards as $thirdPartyAward) {
			if ($thirdPartyAward->name == $params['rewards_selection']) {
				$chosenProduct = $thirdPartyAward;
				$marketing_url = get_option( 'foodtec_marketing_url', constant( 'MARKETING_URL' ) );

				$url = "$marketing_url/api/user/profile/loyalty/$thirdPartyAward->name/redeemThirdPartyAward";

				$response = ( new \WP_FoodTec\Includes\Libraries\Requests\Request( 'Bearer ' . $_SESSION[ 'FOODTEC-' . get_current_blog_id() ]['ACCESS_TOKEN'], 'Authorization' ) )->request( $url, null, 'POST' );

				if ( wp_remote_retrieve_response_code( $response ) !== 204 ) {
					exit(json_decode( wp_remote_retrieve_body( $response ), true )['message']);
				}
			}
		}
		/**
		 * Check for valid amount
		 */

		if (!$chosenProduct) {
			exit ('Please choose a valid amount!');
		}

		/**
		 * Build the request to Tremendous
		 */

		$product = get_option('foodtec_tremendous_products');
		$funding_source = get_option('foodtec_tremendous_funding_sources');
		$product = explode(',',$product);
        $data_to_send_api = json_encode(array(
            'payment' => array(
                'method' => 'balance',
                'funding_source_id' => $funding_source,
            ),
            'rewards' => array(
                array(
                    'value' => array(
                        'denomination' => $chosenProduct->displayName,
                    ),
                    'delivery' => array(
                        'method' => 'EMAIL',
                    ),
                    'recipient' => array(
                        'name'=> $params['account']->name,
                        'email'=> $params['account']->email,
                    ),
                    'products' => $product,
                ),
            ),

        ));

		$token = get_option('foodtec_tremendous_key');
        $url = get_option('foodtec_tremendous_url');
        $arguments = array(
            'method' => 'POST',
            'body' => $data_to_send_api,
            'headers' => array(
                'Authorization'=> "Bearer $token",
                'Content-Type' => 'application/json',
            ),
        );
		$response = wp_remote_post( $url,$arguments );
		$response = json_encode($response);



        exit($response);


    }

    /**
     * Registers the shortcode UI.
     *
     * @see https://github.com/wp-shortcake/Shortcake/blob/master/dev.php
     *
     * @return void
     */
    public function shortcode_foodtec_tremendous() {
        $fields = array(
            array(
                'label'   => esc_html__( 'Recaptcha Theme', 'wp-foodtec' ),
                'attr'    => 'theme',
                'type'    => 'select',
                'value'   => 'light',
                'options' => array(
                    array(
                        'value' => 'light',
                        'label' => esc_html__( 'Light', 'wp-foodtec' ),
                    ),
                    array(
                        'value' => 'dark',
                        'label' => esc_html__( 'Dark', 'wp-foodtec' ),
                    ),
                ),
            ),
        );
        $shortcode_ui_args = array(
            'label'         => esc_html__( 'Tremendous', 'wp-foodtec' ),
            'listItemImage' => 'dashicons-hidden',
            'attrs'         => $fields,
        );

        // @phan-suppress-next-line PhanUndeclaredFunction
        shortcode_ui_register_for_shortcode( 'foodtec_tremendous', $shortcode_ui_args );
    }
}


