<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UM_User_Locations
 */
class UM_User_Locations {


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @var array
	 */
	var $locales = [];


	/**
	 * @return UM_User_Locations
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_User_Locations constructor.
	 */
	function __construct() {
		$this->locales = array(
			'af'        => __( 'Afrikaans', 'um-user-locations' ),
			'sq'        => __( 'Albanian', 'um-user-locations' ),
			'am'        => __( 'Amharic', 'um-user-locations' ),
			'ar'        => __( 'Arabic', 'um-user-locations' ),
			'hy'        => __( 'Armenian', 'um-user-locations' ),
			'az'        => __( 'Azerbaijani', 'um-user-locations' ),
			'eu'        => __( 'Basque', 'um-user-locations' ),
			'be'        => __( 'Belarusian', 'um-user-locations' ),
			'bn'        => __( 'Bengali', 'um-user-locations' ),
			'bs'        => __( 'Bosnian', 'um-user-locations' ),
			'my'        => __( 'Burmese', 'um-user-locations' ),
			'ca'        => __( 'Catalan', 'um-user-locations' ),
			'zh'        => __( 'Chinese', 'um-user-locations' ),
			'zh-CN'     => __( 'Chinese (Simplified)', 'um-user-locations' ),
			'zh-HK'     => __( 'Chinese (Hong Kong)', 'um-user-locations' ),
			'zh-TW'     => __( 'Chinese (Traditional)', 'um-user-locations' ),
			'hr'        => __( 'Croatian', 'um-user-locations' ),
			'cs'        => __( 'Czech', 'um-user-locations' ),
			'da'        => __( 'Danish', 'um-user-locations' ),
			'nl'        => __( 'Dutch', 'um-user-locations' ),
			'en'        => __( 'English', 'um-user-locations' ),
			'en-AU'     => __( 'English (Australian)', 'um-user-locations' ),
			'en-GB'     => __( 'English (Great Britain)', 'um-user-locations' ),
			'et'        => __( 'Estonian', 'um-user-locations' ),
			'fa'        => __( 'Farsi', 'um-user-locations' ),
			'fi'        => __( 'Finnish', 'um-user-locations' ),
			'fil'       => __( 'Filipino', 'um-user-locations' ),
			'fr'        => __( 'French', 'um-user-locations' ),
			'fr-CA'     => __( 'French (Canada)', 'um-user-locations' ),
			'gl'        => __( 'Galician', 'um-user-locations' ),
			'ka'        => __( 'Georgian', 'um-user-locations' ),
			'de'        => __( 'German', 'um-user-locations' ),
			'el'        => __( 'Greek', 'um-user-locations' ),
			'gu'        => __( 'Gujarati', 'um-user-locations' ),
			'iw'        => __( 'Hebrew', 'um-user-locations' ),
			'hi'        => __( 'Hindi', 'um-user-locations' ),
			'hu'        => __( 'Hungarian', 'um-user-locations' ),
			'is'        => __( 'Icelandic', 'um-user-locations' ),
			'id'        => __( 'Indonesian', 'um-user-locations' ),
			'it'        => __( 'Italian', 'um-user-locations' ),
			'ja'        => __( 'Japanese', 'um-user-locations' ),
			'kn'        => __( 'Kannada', 'um-user-locations' ),
			'kk'        => __( 'Kazakh', 'um-user-locations' ),
			'km'        => __( 'Khmer', 'um-user-locations' ),
			'ko'        => __( 'Korean', 'um-user-locations' ),
			'ky'        => __( 'Kyrgyz', 'um-user-locations' ),
			'lo'        => __( 'Lao', 'um-user-locations' ),
			'lv'        => __( 'Latvian', 'um-user-locations' ),
			'lt'        => __( 'Lithuanian', 'um-user-locations' ),
			'mk'        => __( 'Macedonian', 'um-user-locations' ),
			'ms'        => __( 'Malay', 'um-user-locations' ),
			'ml'        => __( 'Malayalam', 'um-user-locations' ),
			'mr'        => __( 'Marathi', 'um-user-locations' ),
			'mn'        => __( 'Mongolian', 'um-user-locations' ),
			'ne'        => __( 'Nepali', 'um-user-locations' ),
			'no'        => __( 'Norwegian', 'um-user-locations' ),
			'pl'        => __( 'Polish', 'um-user-locations' ),
			'pt'        => __( 'Portuguese', 'um-user-locations' ),
			'pt-BR'     => __( 'Portuguese (Brazil)', 'um-user-locations' ),
			'pt-PT'     => __( 'Portuguese (Portugal)', 'um-user-locations' ),
			'pa'        => __( 'Punjabi', 'um-user-locations' ),
			'ro'        => __( 'Romanian', 'um-user-locations' ),
			'ru'        => __( 'Russian', 'um-user-locations' ),
			'sr'        => __( 'Serbian', 'um-user-locations' ),
			'si'        => __( 'Sinhalese', 'um-user-locations' ),
			'sk'        => __( 'Slovak', 'um-user-locations' ),
			'sl'        => __( 'Slovenian', 'um-user-locations' ),
			'es'        => __( 'Spanish', 'um-user-locations' ),
			'es-419'    => __( 'Spanish (Latin America)', 'um-user-locations' ),
			'sw'        => __( 'Swahili', 'um-user-locations' ),
			'sv'        => __( 'Swedish', 'um-user-locations' ),
			'ta'        => __( 'Tamil', 'um-user-locations' ),
			'te'        => __( 'Telugu', 'um-user-locations' ),
			'th'        => __( 'Thai', 'um-user-locations' ),
			'tr'        => __( 'Turkish', 'um-user-locations' ),
			'uk'        => __( 'Ukrainian', 'um-user-locations' ),
			'ur'        => __( 'Urdu', 'um-user-locations' ),
			'uz'        => __( 'Uzbek', 'um-user-locations' ),
			'vi'        => __( 'Vietnamese', 'um-user-locations' ),
			'zu'        => __( 'Zulu', 'um-user-locations' ),
		);

		add_filter( 'um_call_object_User_Locations', array( &$this, 'get_this' ) );

		if ( UM()->is_request( 'admin' ) ) {
			$this->admin();
		}

		$this->profile();
		$this->fields();
		$this->enqueue();
		$this->member_directory();
		$this->shortcodes();
	}


	/**
	 * @return $this
	 */
	function get_this() {
		return $this;
	}


	/**
	 * @return um_ext\um_user_locations\core\Member_Directory()
	 */
	function member_directory() {
		if ( empty( UM()->classes['um_user_locations_member_directory'] ) ) {
			UM()->classes['um_user_locations_member_directory'] = new um_ext\um_user_locations\core\Member_Directory();
		}
		return UM()->classes['um_user_locations_member_directory'];
	}


	/**
	 * @return um_ext\um_user_locations\core\Shortcodes()
	 */
	function shortcodes() {
		if ( empty( UM()->classes['um_user_locations_shortcodes'] ) ) {
			UM()->classes['um_user_locations_shortcodes'] = new um_ext\um_user_locations\core\Shortcodes();
		}
		return UM()->classes['um_user_locations_shortcodes'];
	}


	/**
	 * @return um_ext\um_user_locations\core\Enqueue()
	 */
	function enqueue() {
		if ( empty( UM()->classes['um_user_locations_enqueue'] ) ) {
			UM()->classes['um_user_locations_enqueue'] = new um_ext\um_user_locations\core\Enqueue();
		}
		return UM()->classes['um_user_locations_enqueue'];
	}


	/**
	 * @return um_ext\um_user_locations\core\Fields()
	 */
	function fields() {
		if ( empty( UM()->classes['um_user_locations_fields'] ) ) {
			UM()->classes['um_user_locations_fields'] = new um_ext\um_user_locations\core\Fields();
		}
		return UM()->classes['um_user_locations_fields'];
	}


	/**
	 * @return um_ext\um_user_locations\core\Profile()
	 */
	function profile() {
		if ( empty( UM()->classes['um_user_locations_profile'] ) ) {
			UM()->classes['um_user_locations_profile'] = new um_ext\um_user_locations\core\Profile();
		}
		return UM()->classes['um_user_locations_profile'];
	}


	/**
	 * @return um_ext\um_user_locations\core\Profile()
	 */
	function admin() {
		if ( empty( UM()->classes['um_user_locations_admin'] ) ) {
			UM()->classes['um_user_locations_admin'] = new um_ext\um_user_locations\core\Admin();
		}
		return UM()->classes['um_user_locations_admin'];
	}


	/**
	 * @return array
	 */
	function get_locale() {
		$default_locale = UM()->options()->get( 'um_google_lang_as_default' );
		if ( $default_locale ) {
			$locale = get_locale();
			$locales = array_keys( $this->locales );
			if ( ! in_array( $locale, $locales ) ) {
				$locale = str_replace( '_', '-', $locale );
				if ( ! in_array( $locale, $locales ) ) {
					$locale = explode( '-', $locale );
					if ( isset( $locale[1] ) ) {
						$locale = $locale[1];
					}
				}
			}
		} else {
			$locale = UM()->options()->get( 'um_google_lang' );
		}

		return $locale;
	}


	/**
	 * @param array $user1 User1 location (lat, lng)
	 * @param array $user2 User2 location (lat, lng)
	 * @param string $unit ( km || miles )
	 *
	 * @return string
	 */
	public function calculate_distance( $user1, $user2, $unit ) {
		$theta = $user1[1] - $user2[1];
		$miles = ( sin( deg2rad( $user1[0] ) ) * sin( deg2rad( $user2[0] ) ) ) + ( cos( deg2rad( $user1[0] ) ) * cos( deg2rad( $user2[0] ) ) * cos( deg2rad( $theta ) ) );
		$miles = acos( $miles );
		$miles = rad2deg( $miles );
		$miles = $miles * 60 * 1.1515;

		if ( 'km' === $unit ) {
			// translators: %s is the number of kilometers.
			$distance = sprintf( __( '%s km', 'um-user-locations' ), round( $miles * 1.609344, 2 ) );
		} else {
			// translators: %s is the number of miles.
			$distance = sprintf( __( '%s miles', 'um-user-locations' ), round( $miles, 2 ) );
		}

		return $distance;
	}
}


if ( ! function_exists( 'um_init_user_locations' ) ) {


	/**
	 * Create class copy
	 */
	function um_init_user_locations() {
		if ( function_exists( 'UM' ) ) {
			UM()->set_class( 'User_Locations', true );
		}
	}
}
add_action( 'plugins_loaded', 'um_init_user_locations', -10, 1 );
