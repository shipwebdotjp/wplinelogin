<?php
/**
 * Plugin Name: WP LINE Login
 * Plugin URI: https://blog.shipweb.jp/wplinelogin/
 * Description: Add Login with LINE feature.
 * Version: 1.4.2
 * Author: shipweb
 * Author URI: https://blog.shipweb.jp/about
 * License: GPLv3
 * Text Domain: linelogin
 *
 * @package linelogin
 * @author shipweb
 * @license GPLv3
 */

/**
 * Copyright 2021 shipweb (email : shipwebdotjp@gmail.com)
 * https://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once plugin_dir_path( __FILE__ ) . 'include/setting.php';
require_once plugin_dir_path( __FILE__ ) . 'include/const.php';

/**
 * Line Login Class
 */
class linelogin {

	/**
	 * このプラグインのバージョン
	 */
	const VERSION = '1.4.2';

	/**
	 * このプラグインのID：Shipweb Line Login
	 */
	const PLUGIN_ID = 'sll';

	/**
	 * このプラグインの名前：Line Login
	 */
	const PLUGIN_NAME = 'linelogin';

	/**
	 * PREFIX
	 */
	const PLUGIN_PREFIX = self::PLUGIN_ID . '_';

	/**
	 * CredentialAction：設定
	 */
	const CREDENTIAL_ACTION__SETTINGS_FORM = self::PLUGIN_ID . '-nonce-action_settings-form';

	/**
	 * CredentialName：設定
	 */
	const CREDENTIAL_NAME__SETTINGS_FORM = self::PLUGIN_ID . '-nonce-name_settings-form';

	/**
	 * SESSIONのキー：STATE(TEMP)
	 */
	const SESSION_KEY__STATES = self::PLUGIN_PREFIX . 'oauth2_states';

	/**
	 * Cookieのキー：LINEID(TEMP)
	 */
	const COOKIE_KEY__LINEID = self::PLUGIN_PREFIX . 'unlinked_line_id';

	/**
	 * Cookieのキー：REDIRECT(TEMP)
	 */
	const COOKIE_KEY__REDIRECT_TO = self::PLUGIN_PREFIX . 'redirect_to';

	/**
	 * ユーザーメタキー：line
	 */
	const META_KEY__LINE = self::PLUGIN_PREFIX . 'lineid';

	/**
	 * ユーザーメタキー：line profile
	 */
	const META_KEY__LINEPROFILE = self::PLUGIN_PREFIX . 'lineprofile';

	/**
	 * ユーザーメタキー：line loginの各データ
	 */
	const META_KEY__LINELOGIN = self::PLUGIN_PREFIX . 'linelogin';

	/**
	 * ユーザーメタキー：WP LINE Connectとの連携用
	 */
	const META_KEY__LINECONNECT = 'line';

	/**
	 * パラメータキー：status
	 */
	const PARAMETER_KEY__STATUS = self::PLUGIN_PREFIX . 'status';

	/**
	 * パラメータキー：code
	 */
	const PARAMETER_KEY__CODE = self::PLUGIN_PREFIX . 'code';

	/**
	 * パラメータキー：next
	 */
	const PARAMETER_KEY__NEXT = self::PLUGIN_PREFIX . 'next';

	/**
	 * パラメータキー：UID
	 */
	const PARAMETER_KEY__UID = self::PLUGIN_PREFIX . 'uid';

	/**
	 * パラメータキー：MODE
	 */
	const PARAMETER_KEY__MODE = self::PLUGIN_PREFIX . 'mode';

	/**
	 * OPTIONSテーブルのキー：Setting
	 */
	const OPTION_KEY__SETTINGS = self::PLUGIN_PREFIX . 'settings';

	/**
	 * 画面のslug：トップ
	 */
	const SLUG__SETTINGS_FORM = self::PLUGIN_ID . '-settings-form';

	/**
	 * パラメーターのPREFIX
	 */
	const PARAMETER_PREFIX = self::PLUGIN_PREFIX;

	/**
	 * 一時入力値保持用のPREFIX
	 */
	const TRANSIENT_PREFIX = self::PLUGIN_PREFIX . 'temp-';

	/**
	 * 不正入力値エラー表示のPREFIX
	 */
	const INVALID_PREFIX = self::PLUGIN_PREFIX . 'invalid-';


	/**
	 * TRANSIENTキー(保存完了メッセージ)：設定
	 */
	const TRANSIENT_KEY__SAVE_SETTINGS = self::PLUGIN_PREFIX . 'save-settings';

	/**
	 * TRANSIENTのタイムリミット：5秒
	 */
	const TRANSIENT_TIME_LIMIT = 5;

	/**
	 * 通知タイプ：エラー
	 */
	const NOTICE_TYPE__ERROR = 'error';

	/**
	 * 通知タイプ：警告
	 */
	const NOTICE_TYPE__WARNING = 'warning';

	/**
	 * 通知タイプ：成功
	 */
	const NOTICE_TYPE__SUCCESS = 'success';

	/**
	 * 通知タイプ：情報
	 */
	const NOTICE_TYPE__INFO = 'info';

	/**
	 * 正規表現：ChannelAccessToken
	 */
	const REGEXP_CHANNEL_ACCESS_TOKEN = '/^[a-zA-Z0-9+\/=]{100,}$/';

	/**
	 * 正規表現：ChannelSecret
	 */
	const REGEXP_CHANNEL_SECRET = '/^[a-z0-9]{30,}$/';

	/**
	 * 正規表現：LINEユーザーID
	 */
	const REGEXP_LINE_USER_ID = '/^U[a-z0-9]{32}$/';

	/**
	 * 正規表現：ChannelSecret
	 */
	const ENDPOINTS = array(
		'login'    => 'login',
		'signup'   => 'register',
		'register' => 'register',
		'link'     => 'home',
	);

	const MODE_LOGIN  = 'login';
	const MODE_LINK   = 'link';
	const MODE_UNLINK = 'unlink';

	/**
	 * 設定データ
	 *
	 * @var array ini
	 */
	public $ini;

	/**
	 * Make instance
	 */
	public static function instance() {
		return new self();
	}


	/**
	 * HTMLのOPTIONタグを生成・取得
	 *
	 * @param array  $list List Item.
	 * @param mixed  $selected Selected Item.
	 * @param string $label Label.
	 */
	public static function make_html_select_options( $list, $selected, $label = null ) {
		$html = '';
		foreach ( $list as $key => $value ) {
			$html .= '<option class="level-0" value="' . esc_attr( $key ) . '"';
			if ( $key === $selected || ( is_array( $selected ) && in_array( $key, $selected ) ) ) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . ( is_null( $label ) ? esc_html( $value ) : esc_html( $value[ $label ] ) ) . '</option>';
		}
		return $html;
	}

	/**
	 * 通知タグを生成・取得
	 *
	 * @param string $message 通知するメッセージ.
	 * @param string $type 通知タイプ(error/warning/success/info).
	 * @retern string 通知タグ(HTML)
	 */
	public static function get_notice( $message, $type ) {
		return '<div class="' . esc_attr( 'notice notice-' . $type . ' is-dismissible' ) . '">' .
			'<p><strong>' . esc_html( $message ) . '</strong></p>' .
			'<button type="button" class="notice-dismiss">' .
			'<span class="screen-reader-text">Dismiss this notice.</span>' .
			'</button>' .
			'</div>';
	}

	/**
	 * エラータグを生成・取得
	 *
	 * @param string $message 通知するメッセージ.
	 * @param string $type 通知タイプ(error/warning/success/info).
	 * @retern string
	 */
	public static function get_error_bar( $message, $type ) {
		return '<div class="error">' . esc_html( $message ) . '</div>';
	}

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		add_action(
			'init',
			function () {
				// オプションの読み込み.
				$this->ini = $this->get_all_options();

				// ログイン時、LINEアカウント連携.
				add_action( 'wp_login', array( $this, 'redirect_account_link' ), 10, 2 );
				// 新規登録時、LINEアカウント連携.
				add_action( 'user_register', array( $this, 'register_account_link' ), 10, 2 );
				// LINEログインURLにアクセスされたときLINEログイン画面へリダイレクト.
				add_action( 'template_redirect', array( $this, 'redirect_to_line' ), 10, 2 );
				// ログインボタンショートコードのフック.
				add_shortcode( 'line_login_link', array( $this, 'login_link_shortcode_handler_function' ) );
				// メッセージ表示ショートコードのフック.
				add_shortcode( 'line_login_message', array( $this, 'login_message_shortcode_handler_function' ) );
				// 管理画面を表示中、且つ、ログイン済、且つ、特権管理者or管理者の場合.
				if ( is_admin() && is_user_logged_in() && ( is_super_admin() || current_user_can( 'manage_options' ) ) ) {
					// 管理画面のトップメニューページを追加.
					add_action( 'admin_menu', array( 'lineloginSetting', 'set_plugin_menu' ) );
					// 管理画面各ページの最初、ページがレンダリングされる前に実行するアクションに、
					// 初期設定を保存する関数をフック.
					add_action( 'admin_init', array( 'lineloginSetting', 'save_settings' ) );
				}

				// ユーザープロフィールにLINEユーザーIDを追加.
				add_action( 'edit_user_profile', array( $this, 'register_line_user_id_profilebox' ) );
				add_action( 'show_user_profile', array( $this, 'register_line_user_id_profilebox' ) );
				// ユーザープロフィールにLINEユーザーIDを保存.
				add_action( 'profile_update', array( $this, 'update_line_user_id_profilebox' ) );
				// ユーザーのMETAにLINEユーザーIDとプロフィール情報を登録(LINE Connect連携用).
				add_action( 'line_login_update_user_meta', array( $this, 'line_login_update_user_meta' ), 10, 3 );
				// ユーザーのMETAからLINEユーザーIDとプロフィール情報を削除(LINE Connect連携用).
				add_action( 'line_login_delete_user_meta', array( $this, 'line_login_delete_user_meta' ), 10, 2 );
			}
		);
		// テキストドメイン呼出し.
		load_plugin_textdomain( self::PLUGIN_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		lineloginConst::initialize();
	}

	/**
	 * 複合化：AES 256
	 *
	 * @param string $edata 暗号化してBASE64にした文字列.
	 * @param string $password 複合化のパスワード.
	 * @return string 複合化された文字列
	 */
	public static function decrypt( $edata, $password ) {
		$data    = base64_decode( $edata );
		$salt    = substr( $data, 0, 16 );
		$ct      = substr( $data, 16 );
		$rounds  = 3; // depends on key length.
		$data00  = $password . $salt;
		$hash    = array();
		$hash[0] = hash( 'sha256', $data00, true );
		$result  = $hash[0];
		for ( $i = 1; $i < $rounds; $i++ ) {
			$hash[ $i ] = hash( 'sha256', $hash[ $i - 1 ] . $data00, true );
			$result    .= $hash[ $i ];
		}
		$key = substr( $result, 0, 32 );
		$iv  = substr( $result, 32, 16 );
		return openssl_decrypt( $ct, 'AES-256-CBC', $key, 0, $iv );
	}

	/**
	 * 暗号化: AES 256
	 *
	 * @param string $data encrypted data.
	 * @param string $password Password.
	 * @return string base64 encrypted data
	 */
	public static function encrypt( $data, $password ) {
		// Set a random salt.
		$salt          = openssl_random_pseudo_bytes( 16 );
		$salted        = '';
		$dx            = '';
		$salted_length = strlen( $salted );
		// Salt the key(32) and iv(16) = 48.
		while ( $salted_length < 48 ) {
			$dx            = hash( 'sha256', $dx . $password . $salt, true );
			$salted       .= $dx;
			$salted_length = strlen( $salted );
		}
		$key            = substr( $salted, 0, 32 );
		$iv             = substr( $salted, 32, 16 );
		$encrypted_data = openssl_encrypt( $data, 'AES-256-CBC', $key, 0, $iv );
		return base64_encode( $salt . $encrypted_data );
	}

	/**
	 * 登録されているオプション情報を全て返す
	 */
	public static function get_all_options() {
		$options = get_option( self::OPTION_KEY__SETTINGS ); // オプションを取得.
		foreach ( lineloginConst::$settings_option as $tab_name => $tab_details ) {
			// flatten.
			foreach ( $tab_details['fields'] as $option_key => $option_details ) {
				if ( ! isset( $options[ $option_key ] ) ) {
					$options[ $option_key ] = $option_details['default'];
				}
			}
		}
		return $options;
	}

	/**
	 * 登録されているオプションの値を返す
	 *
	 * @param string $option_name Option Name.
	 */
	public static function get_option( $option_name ) {
		$options = get_option( self::OPTION_KEY__SETTINGS ); // オプションを取得.
		if ( isset( $options[ $option_name ] ) ) {
			return $options[ $option_name ];
		}
		foreach ( lineloginConst::$settings_option as $tab_name => $tab_details ) {
			// flatten.
			foreach ( $tab_details['fields'] as $option_key => $option_details ) {
				if ( $option_name === $option_key ) {
					return $option_details['default'];
				}
			}
		}
		return null;
	}

	/**
	 * LINEログイン開始
	 */
	public function redirect_to_line() {
		$isline_page = is_page( rtrim( $this->ini['callback_url'], '/' ) );

		if ( $isline_page ) {
			if ( isset( $_SERVER['QUERY_STRING'] ) ) {
				// Because of query string will be sanitize when using values in $req_vars.
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				parse_str( wp_unslash( $_SERVER['QUERY_STRING'] ), $req_vars );
			}
			if ( isset( $req_vars[ self::PARAMETER_KEY__MODE ] ) && self::MODE_UNLINK === $req_vars[ self::PARAMETER_KEY__MODE ] ) {
				// 連係解除リンク.
				if ( is_user_logged_in() ) {
					// check nonce of unlink_account.
					if ( false === wp_verify_nonce( $req_vars['_wpnonce'], 'unlink_account' ) ) {
						$redirect_to = add_query_arg(
							array(
								self::PARAMETER_KEY__STATUS => 'error',
								self::PARAMETER_KEY__CODE => 'link_expired',
								self::PARAMETER_KEY__NEXT => self::MODE_LINK,
							),
							self::get_url( 'message' )
						);
						wp_safe_redirect( $redirect_to );
						exit();
					}

					// check usermetta this user account was created by linelogin.
					$line_login_data = get_user_meta( get_current_user_id(), self::META_KEY__LINELOGIN, true );
					if ( ! empty( $line_login_data ) && isset( $line_login_data['createdbylinelogin'] ) && $line_login_data['createdbylinelogin'] ) {
						if ( 'on' === $this->ini['unlink_autocreated_account'] || 'delete' === $this->ini['unlink_autocreated_account'] ) {
							self::delete_user_meta( get_current_user_id() );
							if ( 'delete' === $this->ini['unlink_autocreated_account'] ) {
								require_once ABSPATH . 'wp-admin/includes/user.php';
								wp_delete_user( get_current_user_id() );
								$redirect_to = add_query_arg(
									array(
										self::PARAMETER_KEY__STATUS => 'info',
										self::PARAMETER_KEY__CODE   => 'delete_complete',
										self::PARAMETER_KEY__NEXT   => self::MODE_LINK,
									),
									self::get_url( 'message' )
								);
								wp_safe_redirect( $redirect_to );
								exit();
							}
						} else {
							// if account was created by linelogin, user connot unlink.
							$redirect_to = add_query_arg(
								array(
									self::PARAMETER_KEY__STATUS => 'error',
									self::PARAMETER_KEY__CODE   => 'unlink_incomplete',
									self::PARAMETER_KEY__NEXT   => self::MODE_LINK,
								),
								self::get_url( 'message' )
							);
							wp_safe_redirect( $redirect_to );
							exit();
						}
					}
					self::delete_user_meta( get_current_user_id() );
					$redirect_to = add_query_arg(
						array(
							self::PARAMETER_KEY__STATUS => 'info',
							self::PARAMETER_KEY__CODE   => 'unlink_complete',
							self::PARAMETER_KEY__NEXT   => self::MODE_LINK,
						),
						self::get_url( 'message' )
					);
					wp_safe_redirect( $redirect_to );
					exit();
				}
			}

			if ( session_status() !== PHP_SESSION_ACTIVE ) {
				session_start();    // セッション開始.
			}
			// OAuth2のクライアントライブラリ読み込み.
			require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
			// LINEのOAuth2クライアントインスタンス作成.
			$provider = new Osapon\OAuth2\Client\Provider\Line(
				array(
					'clientId'     => $this->ini['login_channel_id'],
					'clientSecret' => $this->ini['login_channel_secret'],
					'redirectUri'  => self::get_url( 'callback' ),
				)
			);

			if ( ! empty( $req_vars['error'] ) ) {
				$req_vars['error'] = sanitize_text_field( $req_vars['error'] );
				// エラーが発生した場合.
				$redirect_to = add_query_arg(
					array(
						self::PARAMETER_KEY__STATUS => 'error',
						self::PARAMETER_KEY__CODE   => ! empty( $this->ini[ $req_vars['error'] . '_message' ] ) ? $req_vars['error'] : 'auth_error',
						self::PARAMETER_KEY__NEXT   => self::MODE_LINK,
					),
					self::get_url( 'message' )
				);
				self::logging( 'error: auth_error: ' . esc_html( $req_vars['error'] ) . ' : ' . esc_html( isset( $req_vars['error_description'] ) ? wp_unslash( $req_vars['error_description'] ) : '' ) );
				wp_safe_redirect( $redirect_to );
				exit;
			} elseif ( empty( $req_vars['code'] ) ) {
				// codeのないリクエスト=ログイン開始
				// 認可要求時のオプションパラメーター.
				$scopes = array(
					'openid',
					'profile',
				);
				if ( 'on' === $this->ini['auto_create_account'] || 'on' === $this->ini['use_email_as_key'] ) {
					$scopes[] = 'email';
				}
				$option = array(
					'scope' => $scopes,
				);
				if ( 'off' !== $this->ini['bot_prompt'] ) {
					$option['bot_prompt'] = $this->ini['bot_prompt'];
				}
				if ( 'off' !== $this->ini['initial_amr_display'] ) {
					$option['initial_amr_display'] = $this->ini['initial_amr_display'];
				}
				$auth_url                              = $provider->getAuthorizationUrl( $option );
				$_SESSION[ self::SESSION_KEY__STATES ] = $provider->getState();   // Stateをセッションに保持.
				$_SESSION['lastpage']                  = isset( $req_vars[ self::PARAMETER_KEY__MODE ] ) ? sanitize_text_field( $req_vars[ self::PARAMETER_KEY__MODE ] ) : self::MODE_LOGIN;                   // リンク元をセッションに保持.
				if ( 'on' === $this->ini['directlink'] && isset( $req_vars[ self::PARAMETER_KEY__UID ] ) ) {
					$sll_user_login = self::decrypt( sanitize_text_field( $req_vars[ self::PARAMETER_KEY__UID ] ), $this->ini['encrypt_password'] );
					if ( $sll_user_login ) {
						$_SESSION[ self::PARAMETER_KEY__UID ] = $sll_user_login;
					}
					self::logging( 'from login link user: ' . esc_html( $sll_user_login ) );
				}
				$http_referer = isset( $_SERVER['HTTP_REFERER'] ) ? wp_unslash( $_SERVER['HTTP_REFERER'] ) : '';
				if ( ! empty( $http_referer ) && strpos( $http_referer, '?' ) !== false ) {
					parse_str( substr( $http_referer, strpos( $http_referer, '?' ) + 1 ), $ref_vars );
					self::logging( 'HTTP_REFERER: ' . esc_html( substr( $http_referer, strpos( $http_referer, '?' ) + 1 ) ) );
				}

				if ( isset( $ref_vars['redirect_to'] ) ) {
					// リファラーにリダイレクト先がある場合リダイレクト先をCookieに格納.
					setcookie( self::COOKIE_KEY__REDIRECT_TO, esc_url_raw( $ref_vars['redirect_to'] ), time() + 60 * 60, '/', '', false, true );   // Cookieにセット.
				} elseif ( isset( $req_vars['redirect_to'] ) ) {
					// クエリストリングにリダイレクト先がある場合リダイレクト先をCookieに格納.
					setcookie( self::COOKIE_KEY__REDIRECT_TO, esc_url_raw( $req_vars['redirect_to'] ), time() + 60 * 60, '/', '', false, true );   // Cookieにセット.
				} else {
					setcookie( self::COOKIE_KEY__REDIRECT_TO, '', time() - 3600, '/' ); // COKIE削除.
				}
				self::logging( 'auth: ' . esc_url( $auth_url ) );
				header( 'Location: ' . $auth_url ); // LINE認証URLへリダイレクトさせる.
				exit();
			} elseif ( empty( $req_vars['state'] ) || empty( $_SESSION[ self::SESSION_KEY__STATES ] ) || ( $req_vars['state'] !== $_SESSION[ self::SESSION_KEY__STATES ] ) ) { // stateがないか、stateが異なる場合はエラー.
				unset( $_SESSION[ self::SESSION_KEY__STATES ] );    // セッションのstateを削除.
				$redirect_to = add_query_arg(
					array(
						self::PARAMETER_KEY__STATUS => 'error',
						self::PARAMETER_KEY__CODE   => 'invalid_state',
						self::PARAMETER_KEY__NEXT   => self::MODE_LINK,
					),
					self::get_url( 'message' )
				);
				self::logging( 'error: invalid_state' );
				wp_safe_redirect( $redirect_to );
				exit();
			} else {
				unset( $_SESSION[ self::SESSION_KEY__STATES ] );    // State削除
				// アクセストークンの取得.
				$token = $provider->getAccessToken(
					'authorization_code',
					array(
						'code' => sanitize_text_field( $req_vars['code'] ),
					)
				);
				self::logging( 'token: ' . $token );
				try {
					// ユーザープロフィールを取得.

					$client              = new GuzzleHttp\Client(
						array(
							// Base URI is used with relative requests.
							'base_uri' => 'https://api.line.me',
							// You can set any number of default request options.
							'timeout'  => 15.0,
						)
					);
					$access_token_values = $token->getValues();

					$path           = '/oauth2/v2.1/verify';
					$headers        = array(
						'Content-Type' => 'application/x-www-form-urlencoded',
					);
					$request_params = array(
						'id_token'  => $access_token_values['id_token'],
						'client_id' => $this->ini['login_channel_id'],
					);
					$response       = $client->request(
						'POST',
						$path,
						array(
							'allow_redirects' => true,
							'headers'         => $headers,
							'form_params'     => $request_params,
						)
					);
					$body           = $response->getBody();
					$string_body    = (string) $body;
					$json_body      = json_decode( $string_body );

					$owner_details = $json_body;
				} catch ( Exception $e ) {
					$redirect_to = add_query_arg(
						array(
							self::PARAMETER_KEY__STATUS => 'error',
							self::PARAMETER_KEY__CODE   => 'userdetails_error',
							self::PARAMETER_KEY__NEXT   => self::MODE_LINK,
						),
						self::get_url( 'message' )
					);
					self::logging( 'error: userdetails_error. ' . esc_html( print_r( $e, true ) ) );
					wp_safe_redirect( $redirect_to );
					exit();
				}

				$line_user_id = $owner_details->sub; // LINEユーザーIDを取得
				// メタ情報からLINEユーザーIDでユーザー検索.
				$user_query = new WP_User_Query(
					array(
						'meta_key'   => self::META_KEY__LINE,
						'meta_value' => $line_user_id,
					)
				);
				$users      = $user_query->get_results();
				if ( ! empty( $users ) ) {
					// LINE IDがすでにWPユーザーと連携されている場合.
					$user    = $users[0]; // ユーザーの一人目.
					$user_id = (int) $user->ID; // IDを取得.

					if ( ! is_user_logged_in() ) {
						// 未ログインの場合はそのユーザーでログイン
						// ログイン処理.
						self::do_user_login( $user_id, $user );
						if ( isset( $_COOKIE[ self::COOKIE_KEY__REDIRECT_TO ] ) ) {
							$redirect_to = sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_KEY__REDIRECT_TO ] ) );
							setcookie( self::COOKIE_KEY__REDIRECT_TO, '', time() - 3600, '/' ); // COKIE削除.
						} else {
							$redirect_to = self::get_url( 'home' );
							self::logging( 'logged in redirect_to: ' . $redirect_to );
						}
					} elseif ( get_current_user_id() !== $user_id ) {
						// ログイン済みの場合
						// LINE連携されているユーザーがログイン中のユーザーでない場合
						// 重複して連携はできないのでエラー表示.
						$redirect_to = add_query_arg(
							array(
								self::PARAMETER_KEY__STATUS => 'error',
								self::PARAMETER_KEY__CODE => 'duplicate_error',
								self::PARAMETER_KEY__NEXT => self::MODE_LINK,
							),
							self::get_url( 'message' )
						);
					} else {
						// LINE連携されているユーザーがログイン中のユーザーの場合.
						$redirect_to = add_query_arg(
							array(
								self::PARAMETER_KEY__STATUS => 'error',
								self::PARAMETER_KEY__CODE => 'already_linked',
								self::PARAMETER_KEY__NEXT => self::MODE_LINK,
							),
							self::get_url( 'message' )
						);
					}
					wp_safe_redirect( $redirect_to );
					exit();
				} else {
					// ユーザーの連携ステータスを取得.
					$opts        = array(
						'http' =>
						array(
							'header' => 'Authorization: Bearer ' . $token,
						),
					);
					$context     = stream_context_create( $opts );
					$result      = file_get_contents( 'https://api.line.me/friendship/v1/status', false, $context );
					$friend_flag = json_decode( $result, true );
					self::logging( 'friendstatus: ' . esc_html( $result ) );
					$line_user_data = array(
						'id'          => $line_user_id,
						'displayName' => $owner_details->name,
						'isFriend'    => $friend_flag['friendFlag'],
					);
					if ( isset( $owner_details->picture ) ) {
						$line_user_data['pictureUrl'] = $owner_details->picture;
					}
					if ( isset( $owner_details->email ) ) {
						$line_user_data['email'] = $owner_details->email;
					}
					if ( 'on' === $this->ini['isFriendonly'] && false === $friend_flag['friendFlag'] ) {
						// 友だちのみ連携可能な設定で、友達でない場合エラー.
						$redirect_to = add_query_arg(
							array(
								self::PARAMETER_KEY__STATUS => 'info',
								self::PARAMETER_KEY__CODE => 'nofriend_error',
								self::PARAMETER_KEY__NEXT => self::MODE_LINK,
							),
							self::get_url( 'message' )
						);
						wp_safe_redirect( $redirect_to );
						exit();
					}
					// 連携されていない場合は連携する
					// WordPressユーザーのメタ情報にLINEユーザーIDを追加.
					if ( ! is_user_logged_in() ) {
						if ( isset( $_SESSION[ self::PARAMETER_KEY__UID ] ) ) {
							// セッションに紐づけるユーザーログインが入っていれば連携させる（固有ログインLINKからログイン）.
							$user_query = new WP_User_Query(
								array(
									'search'         => sanitize_text_field( wp_unslash( $_SESSION[ self::PARAMETER_KEY__UID ] ) ),
									'search_columns' => array( 'user_login' ),
								)
							);
							$users      = $user_query->get_results();
							if ( ! empty( $users ) ) {
								unset( $_SESSION[ self::PARAMETER_KEY__UID ] );
								$user                  = $users[0]; // ユーザーの一人目.
								$user_id               = $user->ID; // IDを取得.
								$assigned_line_user_id = get_user_meta( $user_id, self::META_KEY__LINE, true );
								if ( $assigned_line_user_id ) {
									// 既に別のLINE連携されていたら
									// LINE連携されているLINEユーザーIDがログインしたLINEアカウントのLINEユーザーIDでない場合
									// 上書きして連携はできないのでエラー表示.
									$redirect_to = add_query_arg(
										array(
											self::PARAMETER_KEY__STATUS => 'error',
											self::PARAMETER_KEY__CODE => 'overwrite_error',
											self::PARAMETER_KEY__NEXT => self::MODE_LINK,
										),
										self::get_url( 'message' )
									);
									self::logging( 'overwrite_error: user_id=' . get_current_user_id() );
								} else {
									// ユーザーメタにLINE IDをセット.
									self::update_user_meta( $user_id, $line_user_data );
									self::do_user_login( $user_id, $user );
									$redirect_to = add_query_arg(
										array(
											self::PARAMETER_KEY__STATUS => 'info',
											self::PARAMETER_KEY__CODE => 'link_complete',
											self::PARAMETER_KEY__NEXT => self::MODE_LINK,
										),
										self::get_url( 'message' )
									);
								}
								wp_safe_redirect( $redirect_to );
								exit();
							}
						}
						if ( 'on' === $this->ini['use_email_as_key'] && isset( $line_user_data['email'] ) ) {
							// Search User by email.
							$user_query = new WP_User_Query(
								array(
									'search'         => $line_user_data['email'],
									'search_columns' => array( 'user_email' ),
								)
							);
							$users      = $user_query->get_results();
							if ( ! empty( $users ) ) {
								$user                  = $users[0]; // ユーザーの一人目.
								$user_id               = $user->ID; // IDを取得.
								$assigned_line_user_id = get_user_meta( $user_id, self::META_KEY__LINE, true );
								$code                  = 'link_complete';
								if ( $assigned_line_user_id ) {
									// 既に別のLINE連携されていたら
									// LINE連携されているLINEユーザーIDがログインしたLINEアカウントのLINEユーザーIDでない場合
									// 上書きして連携はできないのでエラー表示.
									$redirect_to = add_query_arg(
										array(
											self::PARAMETER_KEY__STATUS => 'error',
											self::PARAMETER_KEY__CODE => 'overwrite_error',
											self::PARAMETER_KEY__NEXT => self::MODE_LINK,
										),
										self::get_url( 'message' )
									);
									self::logging( 'overwrite_error: user_id=' . get_current_user_id() );
								} else {
									// ユーザーメタにLINE IDをセット.
									self::update_user_meta( $user_id, $line_user_data );
									self::do_user_login( $user_id, $user );
									$redirect_to = add_query_arg(
										array(
											self::PARAMETER_KEY__STATUS => 'info',
											self::PARAMETER_KEY__CODE => 'link_complete',
											self::PARAMETER_KEY__NEXT => self::MODE_LINK,
										),
										self::get_url( 'message' )
									);
								}
								wp_safe_redirect( $redirect_to );
								exit();
							}
						}
						if ( 'on' === $this->ini['auto_create_account'] ) {
							// LINE Login Only Mode -> Create User & Login & Link.
							$user_name     = self::make_user_name( $line_user_data['id'], $line_user_data['email'] ?? null );
							$user_password = wp_generate_password( 12, false );
							$display_name  = $line_user_data['displayName'];
							$userdata      = array(
								'user_login'   => $user_name,
								'user_pass'    => $user_password,
								'display_name' => $display_name,
							);
							if ( isset( $line_user_data['email'] ) ) {
								$userdata['user_email'] = $line_user_data['email'];
							}
							$userdata = apply_filters( 'wp_pre_insert_user_data', $userdata, false, null, $userdata );
							$user_id  = wp_insert_user( $userdata );

							// ユーザー登録が成功した場合.
							if ( ! is_wp_error( $user_id ) ) {
								self::logging( 'User auto created: ID=' . $user_id );
								self::update_user_meta( $user_id, $line_user_data );    // WPユーザーとLINE ID連携.
								$line_login_data = array(
									'createdbylinelogin' => true,
								);
								update_user_meta( $user_id, self::META_KEY__LINELOGIN, $line_login_data ); // ユーザーメタにLINE Login関連データをセット.
								do_action( 'user_register', $user_id, $userdata );
								$user = get_user_by( 'id', $user_id );
								self::do_user_login( $user_id, $user );   // ログイン処理.
								$redirect_to = add_query_arg(
									array(
										self::PARAMETER_KEY__STATUS => 'info',
										self::PARAMETER_KEY__CODE => 'register_complete',
										self::PARAMETER_KEY__NEXT => self::MODE_LINK,
									),
									self::get_url( 'message' )
								);
							} else {
								self::logging( 'User auto create failed.' . $user_id->get_error_message() );
								$redirect_to = add_query_arg(
									array(
										self::PARAMETER_KEY__STATUS => 'error',
										self::PARAMETER_KEY__CODE => 'goto_regist',
										self::PARAMETER_KEY__NEXT => self::MODE_LINK,
									),
									self::get_url( 'message' )
								);
							}
						} else {
							// 未ログインの場合はcookieにLINE IDを登録してからログインページ／登録ページへリダイレクト.
							$encrypted_line_user_data = self::encrypt( json_encode( $line_user_data ), $this->ini['encrypt_password'] );   // LINEユーザーIDの暗号化.
							setcookie( self::COOKIE_KEY__LINEID, $encrypted_line_user_data, time() + 60 * 60, '/', '', false, true );   // Cookieにセット.
							$next_code = isset( $_SESSION['lastpage'] ) && self::MODE_LOGIN === $_SESSION['lastpage'] ? 'goto_login' : 'goto_regist';
							$next_slug = isset( $_SESSION['lastpage'] ) && isset( self::ENDPOINTS[ $_SESSION['lastpage'] ] ) ? self::ENDPOINTS[ sanitize_text_field( wp_unslash( $_SESSION['lastpage'] ) ) ] : self::MODE_LOGIN;

							$redirect_to = add_query_arg(
								array(
									self::PARAMETER_KEY__STATUS => 'info',
									self::PARAMETER_KEY__CODE => $next_code,
									self::PARAMETER_KEY__NEXT => $next_slug,
								),
								self::get_url( $next_slug )
							);
						}

						wp_safe_redirect( $redirect_to );
						exit();
					} else {
						// ログイン済みの場合はログイン中のユーザーと関連付ける.
						$user_id               = get_current_user_id();
						$assigned_line_user_id = get_user_meta( $user_id, self::META_KEY__LINE, true );
						$code                  = 'link_complete';
						if ( $assigned_line_user_id ) {
							// 既に別のLINE連携されていたら.
							$code = 'link_changed';
						}
						// ユーザーメタにLINE IDをセット.
						self::update_user_meta( $user_id, $line_user_data );
						$redirect_to = add_query_arg(
							array(
								self::PARAMETER_KEY__STATUS => 'info',
								self::PARAMETER_KEY__CODE => $code,
								self::PARAMETER_KEY__NEXT => self::MODE_LINK,
							),
							self::get_url( 'message' )
						);
						wp_safe_redirect( $redirect_to );
						exit();
					}
				}
			}
			exit();
		}
	}

	/**
	 * ログイン時にLINEログイン経由で未連携の場合は連携させる
	 *
	 * @param string  $user_login ユーザー名.
	 * @param WP_User $current_user ログイン中のユーザー.
	 */
	public function redirect_account_link( $user_login, $current_user ) {
		self::check_is_user_has_unlinked_line_id( $current_user->ID );
	}

	/**
	 *
	 * 新規登録時にLINEログイン経由で未連携の場合は連携させる
	 *
	 * @param int   $user_id ユーザーID.
	 * @param array $userdata ユーザー情報.
	 */
	public function register_account_link( $user_id, $userdata ) {
		self::check_is_user_has_unlinked_line_id( $user_id );
	}

	/**
	 * CookieにLINE IDが保存されているかチェックし、保存されていれば連携
	 *
	 * @param int $user_id ユーザーID.
	 */
	public function check_is_user_has_unlinked_line_id( $user_id ) {
		if ( isset( $_COOKIE[ self::COOKIE_KEY__LINEID ] ) ) {
			// COOKIEにLINE ID KEYがセットされていたら.
			$encrypted_line_user_id = sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_KEY__LINEID ] ) ); // COKIEからLINE IDを取得.
			$line_user_data         = json_decode( self::decrypt( $encrypted_line_user_id, $this->ini['encrypt_password'] ), true );   // 暗号化されているLINE IDを復号.
			$line_user_id           = $line_user_data['id'];
			$user_query             = new WP_User_Query(
				array(
					'meta_key'   => self::META_KEY__LINE,
					'meta_value' => $line_user_id,
				)
			);
			$users                  = $user_query->get_results();
			if ( ! empty( $users ) ) {
				// LINE IDがすでにWPユーザーと連携されている場合.
				$user    = $users[0]; // ユーザーの一人目.
				$user_id = (int) $user->ID; // IDを取得.
				if ( get_current_user_id() !== $user_id ) {
					// LINE連携されているユーザーがログイン中のユーザーでない場合
					// 重複して連携はできないのでエラー表示.
					$redirect_to = add_query_arg(
						array(
							self::PARAMETER_KEY__STATUS => 'error',
							self::PARAMETER_KEY__CODE   => 'duplicate_error',
							self::PARAMETER_KEY__NEXT   => self::MODE_LINK,
						),
						self::get_url( 'message' )
					);
					self::logging( 'duplicate_error: user_id=' . get_current_user_id() );
				} else {
					// LINE連携されているユーザーがログイン中のユーザーの場合.
					setcookie( self::COOKIE_KEY__LINEID, '', time() - 3600, '/' ); // COKIE削除.
					$redirect_to = add_query_arg(
						array(
							self::PARAMETER_KEY__STATUS => 'error',
							self::PARAMETER_KEY__CODE   => 'already_linked',
							self::PARAMETER_KEY__NEXT   => self::MODE_LINK,
						),
						self::get_url( 'message' )
					);
					self::logging( 'already_linked: user_id=' . get_current_user_id() );
				}
			} else {
				$assigned_line_user_id = get_user_meta( $user_id, self::META_KEY__LINE, true );
				$code                  = 'link_complete';
				if ( $assigned_line_user_id ) {
					// 既に別のLINEアカウントに連携されていたら.
					$code = 'link_changed';
				}
				self::update_user_meta( $user_id, $line_user_data );
				setcookie( self::COOKIE_KEY__LINEID, '', time() - 3600, '/' ); // COKIE削除.
				if ( isset( $_COOKIE[ self::COOKIE_KEY__REDIRECT_TO ] ) ) {
					$redirect_to = sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_KEY__REDIRECT_TO ] ) );
					setcookie( self::COOKIE_KEY__REDIRECT_TO, '', time() - 3600, '/' ); // COKIE削除.
				} else {
					$redirect_to = add_query_arg(
						array(
							self::PARAMETER_KEY__STATUS => 'info',
							self::PARAMETER_KEY__CODE   => $code,
							self::PARAMETER_KEY__NEXT   => self::MODE_LINK,
						),
						self::get_url( 'message' )
					);
				}
			}
			wp_safe_redirect( $redirect_to );
			exit();
		}
	}

	/**
	 * 連携状態表示ショートコード実行
	 *
	 * @param array  $atts ショートコード属性.
	 * @param string $content ショートコードに内包される文字列.
	 * @param string $tag ショートコードのタグ名.
	 */
	public function login_link_shortcode_handler_function( $atts, $content = null, $tag = '' ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'login_label'     => $this->ini['login_label'],
				'unlinked_label'  => __( 'Unlinked to LINE', 'linelogin' ),
				'linked_label'    => __( 'Linked to LINE', 'linelogin' ),
				'unlinked_button' => __( 'Link', 'linelogin' ),
				'linked_button'   => __( 'Unlink', 'linelogin' ),
				'delete_button'   => __( 'Delete', 'linelogin' ),
			)
		);
		if ( isset( $_SERVER['QUERY_STRING'] ) ) {
			parse_str( sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ), $req_vars );
		}
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id(); // 現在のユーザーを取得.
			if ( 0 !== $user_id ) {
				$line_user_id = get_user_meta( $user_id, self::META_KEY__LINE, true );
				if ( $line_user_id ) {
					$url              = add_query_arg(
						array(
							self::PARAMETER_KEY__MODE => self::MODE_UNLINK,
						),
						self::get_url( 'callback' )
					);
					$unlink_nonce_url = wp_nonce_url(
						$url,
						'unlink_account',
						'_wpnonce'
					);
					$output           = $atts['linked_label'] ? "<span class='line-login-label linked'>" . esc_html( $atts['linked_label'] ) . '</span>' : '';
					$line_login_data  = get_user_meta( $user_id, self::META_KEY__LINELOGIN, true );
					if ( ! empty( $line_login_data ) && isset( $line_login_data['createdbylinelogin'] ) && $line_login_data['createdbylinelogin'] ) {
						if ( 'on' === $this->ini['unlink_autocreated_account'] ) {
							$url_text = sprintf(
								'<a class="line-login-link linked" href="%s" onclick="return confirm( \'%s\' );">%s</a>',
								esc_url( $unlink_nonce_url ),
								esc_js( $this->ini['unlink_confirm_message'] ),
								esc_html( $atts['linked_button'] )
							);
						} elseif ( 'delete' === $this->ini['unlink_autocreated_account'] ) {
							$url_text = sprintf(
								'<a class="line-login-link linked delete" href="%s" onclick="return confirm( \'%s\' );">%s</a>',
								esc_url( $unlink_nonce_url ),
								esc_js( $this->ini['delete_confirm_message'] ),
								esc_html( $atts['delete_button'] )
							);
						} elseif ( 'off' === $this->ini['unlink_autocreated_account'] ) {
							$url_text = sprintf( '<a class="line-login-link linked disabled" tabindex="-1">%s</a>', esc_html( $atts['linked_button'] ) );
						}
					} else {
						$url_text = "<a href='" . esc_url( $unlink_nonce_url ) . "' class='line-login-link linked'>" . esc_html( $atts['linked_button'] ) . '</a>';
					}
					$output .= $url_text;
				} else {
					$url     = add_query_arg(
						array(
							self::PARAMETER_KEY__MODE => self::MODE_LINK,
						),
						self::get_url( 'callback' )
					);
					$output  = $atts['unlinked_label'] ? "<span class='line-login-label unlinked'>" . esc_html( $atts['unlinked_label'] ) . '</span>' : '';
					$output .= "<a href='" . esc_url( $url ) . "' class='line-login-link unlinked'>" . esc_html( $atts['unlinked_button'] ) . '</a>';
				}
			}
		} else {
			$next_slug = isset( $req_vars[ self::PARAMETER_KEY__NEXT ] ) && self::ENDPOINTS[ $req_vars[ self::PARAMETER_KEY__NEXT ] ] ? $this->ini[ self::ENDPOINTS[ $req_vars[ self::PARAMETER_KEY__NEXT ] ] . '_url' ] : '';
			$url       = add_query_arg(
				array(
					self::PARAMETER_KEY__MODE => self::MODE_LOGIN,
				),
				self::get_url( 'callback' )
			);
			if ( '' === $next_slug || ! is_page( rtrim( $next_slug, '/' ) ) ) {
				$output = "<a href='" . esc_url( $url ) . "' class='line-login-link login'>" . esc_html( $atts['login_label'] ) . '</a>';
			} else {
				$output = '';
			}
		}

		return $output;
	}

	/**
	 * 各種メッセージ表示ショートコード実行
	 *
	 * @param array  $atts ショートコード属性.
	 * @param string $content ショートコードに内包される文字列.
	 * @param string $tag ショートコードのタグ名.
	 */
	public function login_message_shortcode_handler_function( $atts, $content = null, $tag = '' ) {
		if ( isset( $_SERVER['QUERY_STRING'] ) ) {
			parse_str( sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ), $req_vars );
		}
		// status を error か info に絞る.
		$status = isset( $req_vars[ self::PARAMETER_KEY__STATUS ] ) && in_array( $req_vars[ self::PARAMETER_KEY__STATUS ], array( 'error', 'info' ), true ) ? $req_vars[ self::PARAMETER_KEY__STATUS ] : '';
		// 表示するメッセージコード.
		$code = isset( $req_vars[ self::PARAMETER_KEY__CODE ] ) && $req_vars[ self::PARAMETER_KEY__CODE ] ? $req_vars[ self::PARAMETER_KEY__CODE ] : '';
		// 次の移動先タイプ.
		$next_type = isset( $req_vars[ self::PARAMETER_KEY__NEXT ] ) && isset( self::ENDPOINTS[ $req_vars[ self::PARAMETER_KEY__NEXT ] ] ) ? self::ENDPOINTS[ $req_vars[ self::PARAMETER_KEY__NEXT ] ] : '';
		// 次の移動先スラッグ.
		$next_slug = $next_type && isset( $this->ini[ $next_type . '_url' ] ) ? $this->ini[ $next_type . '_url' ] : '';
		// 次の移動先リンクラベル.
		$next_label = isset( $req_vars[ self::PARAMETER_KEY__NEXT ] ) && self::ENDPOINTS[ $req_vars[ self::PARAMETER_KEY__NEXT ] ] ? $this->ini[ self::ENDPOINTS[ $req_vars[ self::PARAMETER_KEY__NEXT ] ] . '_label' ] : '';

		if ( $code ) {
			$output  = "<div class='line-login-message {$status}'>" . ( $this->ini[ $code . '_message' ] ? esc_html( $this->ini[ $code . '_message' ] ) : '' ) . '</div>';
			$output .= $next_slug && ! is_page( rtrim( $next_slug, '/' ) ) ? "<div class='line-login-nexturl'><a href='" . esc_url( self::get_url( $next_type ) ) . "'>" . esc_html( $next_label ) . '</a></div>' : '';
			return $output;
		}
		return;
	}

	/**
	 * ユーザーのメタデータにLINE IDをセット
	 *
	 * @param int   $user_id ユーザーID.
	 * @param array $line_profile_data LINE Profile Data.
	 */
	public function update_user_meta( $user_id, $line_profile_data ) {
		$line_user_id = $line_profile_data['id'];
		update_user_meta( $user_id, self::META_KEY__LINE, $line_user_id ); // ユーザーメタにLINE IDをセット.
		update_user_meta( $user_id, self::META_KEY__LINEPROFILE, $line_profile_data ); // ユーザーメタにLINE IDをセット.
		if ( ! empty( $this->ini['messagingapi_channel_secret'] ) && $line_profile_data['isFriend'] ) {
			$line_user_data = get_user_meta( $user_id, self::META_KEY__LINECONNECT, true );
			if ( empty( $line_user_data ) ) {
				$line_user_data = array();
			}
			$secret_prefix                    = substr( $this->ini['messagingapi_channel_secret'], 0, 4 );
			$line_user_data[ $secret_prefix ] = array(
				'id'          => $line_user_id,
				'displayName' => $line_profile_data['displayName'],
				'pictureUrl'  => $line_profile_data['pictureUrl'],
			);
			update_user_meta( $user_id, self::META_KEY__LINECONNECT, $line_user_data );
			// リッチメニューをセット.
			do_action( 'line_link_richmenu', $user_id );
		}
		self::logging( 'update_user_meta: user_id=' . $user_id . ' line_user_id:' . $line_user_id );
	}

	/**
	 * ユーザーのメタデータにLINE IDをセット(Line Connect連携)
	 *
	 * @param int    $user_id ユーザーID.
	 * @param array  $line_profile_data LINE Profile Data.
	 * @param string $secret_prefix チャネルシークレットの先頭4文字.
	 */
	public function line_login_update_user_meta( $user_id, $line_profile_data, $secret_prefix ) {
		if ( substr( $this->ini['messagingapi_channel_secret'], 0, 4 ) === $secret_prefix ) {
			$line_user_id = $line_profile_data['id'];
			update_user_meta( $user_id, self::META_KEY__LINE, $line_user_id ); // ユーザーメタにLINE IDをセット.
			update_user_meta( $user_id, self::META_KEY__LINEPROFILE, $line_profile_data ); // ユーザーメタにプロフィール情報をセット.
			self::logging( 'update_user_meta: user_id=' . $user_id . ' line_user_id:' . $line_user_id );
		}
	}

	/**
	 * ユーザーのメタデータからLINE IDを削除
	 *
	 * @param int $user_id ユーザーID.
	 */
	public function delete_user_meta( $user_id ) {
		delete_user_meta( $user_id, self::META_KEY__LINE );
		delete_user_meta( $user_id, self::META_KEY__LINEPROFILE );

		if ( ! empty( $this->ini['messagingapi_channel_secret'] ) ) {
			$secret_prefix  = substr( $this->ini['messagingapi_channel_secret'], 0, 4 );
			$user_meta_line = get_user_meta( $user_id, self::META_KEY__LINECONNECT, true );
			if ( $user_meta_line && $user_meta_line[ $secret_prefix ] ) {
				do_action( 'line_unlink_richmenu', $user_id, $secret_prefix );
				unset( $user_meta_line[ $secret_prefix ] );
				if ( empty( $user_meta_line ) ) {
					// ほかに連携しているチャネルがなければメタデータ削除.
					delete_user_meta( $user_id, self::META_KEY__LINECONNECT );
				} else {
					// ほかに連携しているチャネルがあれば残りのチャネルが入ったメタデータを更新.
					update_user_meta( $user_id, self::META_KEY__LINECONNECT, $user_meta_line );
				}
			}
		}
		self::logging( 'delete_user_meta: user_id=' . $user_id );
	}

	/**
	 * ユーザーのメタデータからLINE IDを削除(LINE Connect連携用).
	 *
	 * @param int    $user_id ユーザーID.
	 * @param string $secret_prefix チャネルシークレットの先頭4文字.
	 */
	public function line_login_delete_user_meta( $user_id, $secret_prefix ) {
		if ( substr( $this->ini['messagingapi_channel_secret'], 0, 4 ) === $secret_prefix ) {
			delete_user_meta( $user_id, self::META_KEY__LINE );
			delete_user_meta( $user_id, self::META_KEY__LINEPROFILE );
		}
	}

	/**
	 * ログ出力
	 *
	 * @param string $text ログ内容.
	 */
	public function logging( $text ) {
		if ( 'on' === $this->ini['logging'] ) {
			$logtext = gmdate( '[d/M/Y:H:i:s O] ' ) . $text . ' ' . ( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' ) . "\n";
			error_log( $logtext, 3, $this->ini['log_file'] );
		}
	}
	/**
	 * Create a login username from LINE USER ID.
	 *
	 * This function takes a LINE USER ID as input and generates a login username.
	 * It extracts a substring from the user ID and checks for the existence of the username.
	 * If the username already exists, it iteratively modifies the substring until a unique username is found.
	 *
	 * @param string $user_id The LINE USER ID to create a login username from.
	 * @return string The generated login username.
	 */
	public function make_user_name( $user_id, $email ) {
		if ( isset( $email ) ) {
			// get before @ from email.
			$name = explode( '@', $email );
			$name = $name[0];
			// delete exclude alphanumeric.
			$name = preg_replace( '/[^a-zA-Z0-9]/', '', $name );
			// check username exists.
			if ( ! username_exists( $name ) ) {
				return $name;
			}
		} else {
			$name = '';
		}

		$user_name = $name . substr( $user_id, 2, 2 );
		$offset    = 1;
		while ( $user_exists = username_exists( $user_name ) ) {
			$user_name = $name . substr( $user_id, 2 + $offset, 2 );
			++$offset;
		}

		return $user_name;
	}

	/**
	 * Perform user login process.
	 *
	 * This function handles the login process for a user by performing the following steps:
	 * 1. Clears the authentication cookie.
	 * 2. Sets the current user based on the provided user ID.
	 * 3. Sets the authentication cookie for the user.
	 * 4. Triggers the 'wp_login' action hook with the user login and user object.
	 *
	 * @param int     $user_id The user ID to perform the login for.
	 * @param WP_User $user    The user object containing user information.
	 * @return void
	 */
	public function do_user_login( $user_id, $user ) {
		wp_clear_auth_cookie();
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );
		do_action( 'wp_login', $user->user_login, $user );
	}

	/**
	 * プロフィール画面にLINEユーザーID追加
	 *
	 * @param WP_User $user Target WP User.
	 */
	public function register_line_user_id_profilebox( $user ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		if ( is_object( $user ) ) {
			$line_user_id    = get_user_meta( $user->ID, self::META_KEY__LINE, true );
			$line_profile    = get_user_meta( $user->ID, self::META_KEY__LINEPROFILE, true );
			$line_login_data = get_user_meta( $user->ID, self::META_KEY__LINELOGIN, true );
		} else {
			$line_user_id = null;
		}
		$lineloginlink = add_query_arg(
			array(
				self::PARAMETER_KEY__UID => urlencode( self::encrypt( $user->user_login, $this->ini['encrypt_password'] ) ),
			),
			self::get_url( 'callback' )
		)
		?>
		<h3><?php echo esc_html( __( 'LINE Login Connect', 'linelogin' ) ); ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="lineid"><?php echo esc_html( __( 'LINE User ID', 'linelogin' ) ); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="lineid" value="<?php echo esc_attr( $line_user_id ); ?>" id="lineid" /><br />
					<span class="description"><?php echo esc_html( __( '33 alphanumeric characters starting with "U".', 'linelogin' ) ); ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="linedisplayName"><?php echo esc_html( __( 'LINE Display Name', 'linelogin' ) ); ?></label></th>
				<td>
					<?php echo ! empty( $line_profile['displayName'] ) ? esc_html( $line_profile['displayName'] ) : ''; ?>
				</td>
			<tr>
				<th><label for="linepictureUrl"><?php echo esc_html( __( 'LINE Profile Picture', 'linelogin' ) ); ?></label></th>
				<td>
					<?php echo ! empty( $line_profile['pictureUrl'] ) ? "<img src='" . esc_url( $line_profile['pictureUrl'] ) . "' width=200>" : ''; ?>
				</td>
			</tr>
			<tr>
				<th><label for="lineemail"><?php echo esc_html( __( 'LINE Mailaddress', 'linelogin' ) ); ?></label></th>
				<td>
					<?php echo ! empty( $line_profile['email'] ) ? esc_html( $line_profile['email'] ) : ''; ?>
				</td>
			</tr>
			<tr>
				<th><label for="lineisFriend"><?php echo esc_html( __( 'LINE Friend', 'linelogin' ) ); ?></label></th>
				<td>
					<?php echo ( ! empty( $line_profile['isFriend'] ) ? esc_html( __( 'Yes', 'linelogin' ) ) : esc_html( __( 'No', 'linelogin' ) ) ); ?>
				</td>
			</tr>
			<?php if ( 'on' === $this->ini['auto_create_account'] ) { ?>
			<tr>
				<th><label for="lineCreatedByLineLogin"><?php echo esc_html( __( 'Auto Created Account', 'linelogin' ) ); ?></label></th>
				<td>
					<?php echo ( ! empty( $line_login_data['createdbylinelogin'] ) ? esc_html( __( 'Yes', 'linelogin' ) ) : esc_html( __( 'No', 'linelogin' ) ) ); ?>
				</td>
			</tr>
			<?php } ?>
			<?php if ( 'on' === $this->ini['directlink'] ) { ?>
				<tr>
					<th><?php echo esc_html( __( 'LINE Login Link', 'linelogin' ) ); ?></th>
					<td>
						<input type="text" class="regular-text" name="lineloginlink" value="<?php echo esc_attr( $lineloginlink ); ?>" id="lineloginlink" />
						<button type="button" class="button secondary" onclick="document.getElementById('lineloginlink').select();document.execCommand('copy');"><?php echo esc_html( __( 'Copy', 'linelogin' ) ); ?></button>
						<br />
						<span class="description"><?php echo esc_html( __( 'User can link to LINE by logging in via this link.', 'linelogin' ) ); ?></span>
					</td>
				</tr>
			<?php } ?>
		</table>
		<?php
	}
	/**
	 * プロフィール画面にからPOSTされたLINEユーザーIDを保存
	 *
	 * @param int $user_id User ID.
	 */
	public function update_line_user_id_profilebox( $user_id ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		// Because of nonce was verified by WordPress core. so don't have to check nocne again in this action hook.
		// @codingStandardsIgnoreLine
		$line_user_id = isset( $_POST['lineid'] ) ? sanitize_text_field( wp_unslash( $_POST['lineid'] ) ) : '';
		if ( preg_match( self::REGEXP_LINE_USER_ID, $line_user_id ) ) {
			update_user_meta( $user_id, self::META_KEY__LINE, $line_user_id );
		} elseif ( empty( $line_user_id ) ) {
			delete_user_meta( $user_id, self::META_KEY__LINE );
		}
	}

	/**
	 * URLを返す
	 *
	 * @param string $type URL種別.
	 */
	public function get_url( $type ) {
		if ( isset( $this->ini[ $type . '_url' ] ) ) {
			if ( rtrim( $this->ini[ $type . '_url' ], '/' ) ) {
				$target_page = get_page_by_path( $this->ini[ $type . '_url' ] );
				if ( $target_page ) {
					$permalink = get_permalink( $target_page );
					return $permalink;
				} else {
					return home_url();
				}
			} else {
				return home_url();
			}
		}
		return false;
	}
}

$GLOBALS['linelogin'] = new linelogin();
