<?php

/**
 * Linelogin Const Class
 *
 * Const Class
 *
 * @category Components
 * @package  Const
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/
 */

class lineloginConst {
	/**
	 * 設定項目
	 */
	public static array $settings_option;

	public static function initialize() {

		self::$settings_option = array(
			'channel' => array(
				'prefix' => '1',
				'name'   => __( 'Channel', linelogin::PLUGIN_NAME ),
				'fields' => array(
					'login_channel_id'            => array(
						'type'     => 'text',
						'label'    => __( 'Login channel ID', linelogin::PLUGIN_NAME ),
						'required' => true,
						'default'  => '',
						'hint'     => __( 'This number is displayed on the Basic Information page of the LINE Login channel.', linelogin::PLUGIN_NAME ),
						'regex'    => '/^[0-9]+$/',
						'size'     => 20,
					),
					'login_channel_secret'        => array(
						'type'     => 'text',
						'label'    => __( 'Login channel secret', linelogin::PLUGIN_NAME ),
						'required' => true,
						'default'  => '',
						'hint'     => __( 'This alphanumeric is displayed on the Basic Information page of the LINE Login channel.', linelogin::PLUGIN_NAME ),
						'regex'    => '/^[a-z0-9]{30,}$/',
						'size'     => 33,
					),
					'messagingapi_channel_secret' => array(
						'type'     => 'text',
						'label'    => __( 'Messaging API Channel secret', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => '',
						'hint'     => __( 'When link to LINE Connect, enter the secret of the LINE Messaging API channel.', linelogin::PLUGIN_NAME ),
						'regex'    => '/^[a-z0-9]{30,}$/',
						'size'     => 33,
					),
					'encrypt_password'            => array(
						'type'     => 'text',
						'label'    => __( 'Encryption Secret', linelogin::PLUGIN_NAME ),
						'required' => true,
						'default'  => 'PleaseChangeHere',
						'hint'     => __( 'Secret used for cookie encryption in alphanumeric characters.', linelogin::PLUGIN_NAME ),
						'regex'    => '/^[0-9a-zA-Z]+$/',
					),
				),
			),
			'page'    => array(
				'prefix' => '2',
				'name'   => __( 'Page', linelogin::PLUGIN_NAME ),
				'fields' => array(
					'login_url'    => array(
						'type'     => 'text',
						'label'    => __( 'Login page', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => 'login',
						'hint'     => __( 'Enter login page slug', linelogin::PLUGIN_NAME ),
					),
					'register_url' => array(
						'type'     => 'text',
						'label'    => __( 'Sign up page', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => 'register',
						'hint'     => __( 'Enter sign up page slug', linelogin::PLUGIN_NAME ),
					),
					'home_url'     => array(
						'type'     => 'text',
						'label'    => __( 'User home', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => 'user',
						'hint'     => __( 'Slug of the page to be displayed after LINE login. If left blank, Will be redirected to the site home page.', linelogin::PLUGIN_NAME ),
					),
					'user_url'     => array(
						'type'     => 'text',
						'label'    => __( 'User account', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => 'account',
						'hint'     => __( 'Slug of the page to be displayed after the LINE linking has been completed. This is the page that generally displays the user\'s LINE linking status.', linelogin::PLUGIN_NAME ),
					),
					'message_url'  => array(
						'type'     => 'text',
						'label'    => __( 'Line message', linelogin::PLUGIN_NAME ),
						'required' => true,
						'default'  => 'linemessage',
						'hint'     => __( 'This page is for displaying messages. Please include the short code [line_login_message].', linelogin::PLUGIN_NAME ),
					),
					'callback_url' => array(
						'type'     => 'text',
						'label'    => __( 'Line login', linelogin::PLUGIN_NAME ),
						'required' => true,
						'default'  => 'linelogin',
						'hint'     => __( 'This page is used for LINE login. Please create a page with this slug. No content is required.', linelogin::PLUGIN_NAME ),
					),
				),
			),
			'message' => array(
				'prefix' => '3',
				'name'   => __( 'Message', linelogin::PLUGIN_NAME ),
				'fields' => array(
					'login_label'               => array(
						'type'     => 'text',
						'label'    => __( 'LINE login link label', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'LINE login', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Label for LINE login link.', linelogin::PLUGIN_NAME ),
					),
					'normal_login_label'        => array(
						'type'     => 'text',
						'label'    => __( 'Normal login link label', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'Go to login page', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Label for the link to the normal login page.', linelogin::PLUGIN_NAME ),
					),
					'register_label'            => array(
						'type'     => 'text',
						'label'    => __( 'Sign up label', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'Go to sign up page', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Label for the link to the sign up page.', linelogin::PLUGIN_NAME ),
					),
					'home_label'                => array(
						'type'     => 'text',
						'label'    => __( 'User home label', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'Home', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Label for the link to the home page.', linelogin::PLUGIN_NAME ),
					),
					'user_label'                => array(
						'type'     => 'text',
						'label'    => __( 'Account home label', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'Go to user account page', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Label for the link to the user account page.', linelogin::PLUGIN_NAME ),
					),
					'access_denied_message'     => array(
						'type'     => 'text',
						'label'    => __( 'Login canceled message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'LINE login was canceled.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when LINE login is canceled after redirecting to LINE login screen.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'auth_error_message'        => array(
						'type'     => 'text',
						'label'    => __( 'Authentication error message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'An error occurred during LINE authentication.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when an error occurs during LINE authentication.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'invalid_state_message'     => array(
						'type'     => 'text',
						'label'    => __( 'Linking error message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'An error occurred during LINE linking.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when an error occurs during LINE linking.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'userdetails_error_message' => array(
						'type'     => 'text',
						'label'    => __( 'User information retrieval failure message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'An error occurred during LINE authentication. Please try again later.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when an error occurs while retrieving user information after LINE authentication.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'duplicate_error_message'   => array(
						'type'     => 'text',
						'label'    => __( 'LINE account duplication message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'This LINE account is already linked to another user. Please unlink it and try again.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when a LINE account is already linked to another user.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'overwrite_error_message'   => array(
						'type'     => 'text',
						'label'    => __( 'WordPress account duplication message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'Another LINE account is already linked to this WordPress account. Please unlink it and try again.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when a WordPress account is already linked to another LINE account when using direct link.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'already_linked_message'    => array(
						'type'     => 'text',
						'label'    => __( 'Already linked message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'This LINE account is already linked.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when attempting to link an already linked LINE account.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'goto_login_message'        => array(
						'type'     => 'text',
						'label'    => __( 'Not logged in (Login link) message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'This LINE account is not yet linked. Linking will occur when you log in with your site username or email address and password.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when a not yet linked LINE account logs in using the "login" mode.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'goto_regist_message'       => array(
						'type'     => 'text',
						'label'    => __( 'Not logged in (Sign up link) message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'This LINE account is not yet linked. Linking will occur when you sign up on this site.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when a not yet linked LINE account logs in using the "sign up" mode.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'link_complete_message'     => array(
						'type'     => 'text',
						'label'    => __( 'Linking complete message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'LINE linking is complete.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when LINE linking is successfully completed.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'register_complete_message' => array(
						'type'     => 'text',
						'label'    => __( 'Register complete message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'Create Account and LINE linking is complete.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when auto created account and LINE linking is successfully completed.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'unlink_confirm_message'    => array(
						'type'     => 'text',
						'label'    => __( 'Unlinking confirmation message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'Are you sure you want us to unlink your account? Because of the automatically created account, you may not retain your password. In that case, you will not be able to log into your account again.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when unlinking automatically created account.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'unlink_complete_message'   => array(
						'type'     => 'text',
						'label'    => __( 'Unlinking complete message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'LINE unlinking is complete.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when LINE unlinking is successfully completed.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'unlink_incomplete_message' => array(
						'type'     => 'text',
						'label'    => __( 'Unlinking error message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'Your account is automatically created and cannot be unlinked. To unlink, please delete your account.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when LINE unlinking is failed due to user account was automatically created.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'delete_confirm_message'    => array(
						'type'     => 'text',
						'label'    => __( 'Delete confirmation message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'Are you sure you want us to delete your account? This action is irreversible.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when deleting automatically created account.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'delete_complete_message'   => array(
						'type'     => 'text',
						'label'    => __( 'Delete complete message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'Your account has been deleted.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when deleting automatically created account is completed.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'link_changed_message'      => array(
						'type'     => 'text',
						'label'    => __( 'Linked account change message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'The linked LINE account has been changed.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when the linked LINE account is changed to a different LINE account.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'nofriend_error_message'    => array(
						'type'     => 'text',
						'label'    => __( 'Not a friend message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'To link LINE, you need to be friends with the official LINE account.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when a user who can only link friends attempts to link LINE, but they are not friends with the official LINE account.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
					'link_expired_message'      => array(
						'type'     => 'text',
						'label'    => __( 'Link expired message', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => __( 'This link has expired.', linelogin::PLUGIN_NAME ),
						'hint'     => __( 'Message displayed when the link has expired.', linelogin::PLUGIN_NAME ),
						'size'     => 60,
					),
				),
			),
			'other'   => array(
				'prefix' => '4',
				'name'   => __( 'Other', linelogin::PLUGIN_NAME ),
				'fields' => array(
					'logging'                    => array(
						'type'     => 'select',
						'label'    => __( 'Logging', linelogin::PLUGIN_NAME ),
						'required' => true,
						'list'     => array(
							'on'  => __( 'Enable', linelogin::PLUGIN_NAME ),
							'off' => __( 'Disable', linelogin::PLUGIN_NAME ),
						),
						'default'  => 'off',
						'hint'     => __( 'Setting to record logs for LINE login.', linelogin::PLUGIN_NAME ),
					),
					'log_file'                   => array(
						'type'     => 'text',
						'label'    => __( 'Log file path', linelogin::PLUGIN_NAME ),
						'required' => false,
						'default'  => '/var/log/linelogin.log',
						'hint'     => __( 'Path to the log file.', linelogin::PLUGIN_NAME ),
						'size'     => 40,
					),
					'auto_create_account'        => array(
						'type'     => 'select',
						'label'    => __( 'Auto create wp account', linelogin::PLUGIN_NAME ),
						'required' => true,
						'list'     => array(
							'on'  => __( 'Enable', linelogin::PLUGIN_NAME ),
							'off' => __( 'Disable', linelogin::PLUGIN_NAME ),
						),
						'default'  => 'off',
						'hint'     => __( 'If user log in with an unlinked LINE account, whether or not to automatically creat a WordPress account.', linelogin::PLUGIN_NAME ),
					),
					'unlink_autocreated_account' => array(
						'type'     => 'select',
						'label'    => __( 'Unlink autocreated account', linelogin::PLUGIN_NAME ),
						'required' => true,
						'list'     => array(
							'on'     => __( 'Just unlink , remain account', linelogin::PLUGIN_NAME ),
							'delete' => __( 'Delete Account', linelogin::PLUGIN_NAME ),
							'off'    => __( 'Disable unlinking', linelogin::PLUGIN_NAME ),
						),
						'default'  => 'off',
						'hint'     => __( 'Behavior when unlinking automatically created accounts.', linelogin::PLUGIN_NAME ),
					),
					'use_email_as_key'           => array(
						'type'     => 'select',
						'label'    => __( 'Use email as key', linelogin::PLUGIN_NAME ),
						'required' => true,
						'list'     => array(
							'on'  => __( 'Enable', linelogin::PLUGIN_NAME ),
							'off' => __( 'Disable', linelogin::PLUGIN_NAME ),
						),
						'default'  => 'off',
						'hint'     => __( 'If the email addresses match, they are assumed to be the same user and linking.', linelogin::PLUGIN_NAME ),
					),
					'directlink'                 => array(
						'type'     => 'select',
						'label'    => __( 'Use user-specific login link', linelogin::PLUGIN_NAME ),
						'required' => true,
						'list'     => array(
							'on'  => __( 'Enable', linelogin::PLUGIN_NAME ),
							'off' => __( 'Disable', linelogin::PLUGIN_NAME ),
						),
						'default'  => 'off',
						'hint'     => __( 'Setting to issue individual user-specific LINE login links for linking without logging in.', linelogin::PLUGIN_NAME ),
					),
					'bot_prompt'                 => array(
						'type'     => 'select',
						'label'    => __( 'Display friend add option', linelogin::PLUGIN_NAME ),
						'required' => true,
						'list'     => array(
							'off'        => __( 'Do not display', linelogin::PLUGIN_NAME ),
							'normal'     => __( 'Display on authorization screen', linelogin::PLUGIN_NAME ),
							'aggressive' => __( 'Display after authorization screen', linelogin::PLUGIN_NAME ),
						),
						'default'  => 'off',
						'hint'     => __( 'Setting to display options for adding the LINE official account as a friend during user login.', linelogin::PLUGIN_NAME ),
					),
					'initial_amr_display'        => array(
						'type'     => 'select',
						'label'    => __( 'Default login method', linelogin::PLUGIN_NAME ),
						'required' => true,
						'list'     => array(
							'off'    => __( 'Email and password', linelogin::PLUGIN_NAME ),
							'lineqr' => __( 'QR code', linelogin::PLUGIN_NAME ),
						),
						'default'  => 'off',
						'hint'     => __( 'Setting for the initial login method to display when automatic login is not possible.', linelogin::PLUGIN_NAME ),
					),
					'isFriendonly'               => array(
						'type'     => 'select',
						'label'    => __( 'Allow linking for friends only', linelogin::PLUGIN_NAME ),
						'required' => true,
						'list'     => array(
							'off' => __( 'No', linelogin::PLUGIN_NAME ),
							'on'  => __( 'Yes', linelogin::PLUGIN_NAME ),
						),
						'default'  => 'off',
						'hint'     => __( 'Setting to allow LINE linking only for friends of the official account.', linelogin::PLUGIN_NAME ),
					),
				),
			),
		);
	}
}
