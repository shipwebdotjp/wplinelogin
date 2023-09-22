[日本語版](README-ja.md)
# WP LINE Login
This plugin is for adding the ability to log in with your LINE account to WordPress.
## Features
* Feature to log in to WordPress using your LINE account. Add social login functionality to Wordpress.
* Can work with both new and existing users
* Can integrate with LINE Connect

## Specification

### Linking new users
LINE Login → Sign up as a new WP user → Complete linking.

#### About User Registration

- The LINE Login plugin does not include user registration functionality. Therefore, please consider using membership site building plugins such as [Ultimate Member](https://ja.wordpress.org/plugins/ultimate-member/) or [Simple Membership](https://ja.wordpress.org/plugins/simple-membership/).
- If you plan to handle user registration on your own, please make sure to call the [user_register action hook](https://developer.wordpress.org/reference/hooks/user_register/) after the user registration process is completed.

### Linking Existing Users

- LINE Login → WordPress Login → Link Account
- If you are already logged in, you can link your account by going to the linking menu and selecting LINE Login → Link Account.
- You can also link your account by using the user-specific login link → LINE Login → Link Account.

### Installation Instructions

Since this plugin is not available in the WordPress plugin directory, you will need to download the ZIP file and upload it from your WordPress admin panel.

- Download the "wplinelogin.zip" file from the latest release in the Assets section on [GitHub/wplinelogin](https://github.com/shipwebdotjp/wplinelogin/releases/).
- Log in to your WordPress admin panel, go to the "Plugins" menu, and select "Add New."
- Click on "Upload Plugin."
- Click on "Choose File" and select the ZIP file you downloaded earlier. Then click "Install Now."
- After the installation is complete, activate "WP LINE Login" from the list of plugins in the admin panel.

## Initial Setup

### Creating Pages

#### Creating a Page for LINE Login

Please create a static page with the slug `linelogin`. The content of this page can be empty.

#### Creating a Page for LINE Login Messages

Create another page with the slug `linemessage`, and insert the shortcode `[line_login_message]` into its content.

### Configuration in LINE Developers

#### Creating a LINE Login Channel

1. Firstly, create a LINE Login channel in [LINE Developers](https://developers.line.biz/).
2. Make sure to note down the Channel ID and Channel Secret.

#### Setting the Callback URL

In LINE Developers, open the "LINE Login" settings, and add the permalink of the "linelogin" page you created earlier to the "Callback URL" field.

If your permalink structure is set to "Post name" (%postname%), it should look like this:

```
WordPress Site URL/linelogin/
```

For example: https://example.com/linelogin/

If your permalink structure is set to "Plain," it will look like this: `http://example.com/?page_id=1`.

### LINE Login Plugin Configuration in WordPress

1. Navigate to the "Settings" → "LINE Login" menu to open the LINE Login settings page.
2. Enter the Channel ID and Channel Secret that you previously noted into their respective input fields in the "Channel" tab.
3. Change the Encryption Secret to a suitable alphanumeric value.
4. Open the "Page Settings" tab and configure the slugs for each page according to your website.
5. Save your settings.

## Usage

You can add LINE Login links to your site, and you can also display LINE Login links using shortcodes.

### LINE Login Shortcode

```
[line_login_link]
```

This shortcode serves two purposes:

1. Displaying a link for LINE Login.
2. Displaying the linking status between the WP user and their LINE account, as well as the link for linking/unlinking.

The content displayed depends on the user's login status. If the user is not logged in, the login link is displayed. If the user is logged in, it will display the linking status and the link for linking/unlinking.

The shortcode display flow looks like this:

![Shortcode Flow](https://blog.shipweb.jp/wp-content/uploads/2022/01/%E3%83%AD%E3%82%B0%E3%82%A4%E3%83%B3%E3%83%9C%E3%82%BF%E3%83%B3.png)

Pages where it's recommended to display the shortcode:

- Login page (below the login form)
- User profile editing page

#### LINE Login Link URLs

You can perform LINE Login and linking without using the shortcode by appending query parameters to the permalink of the "linelogin" page.

There are four types of parameters available to initiate LINE Login, each serving a different purpose:

##### Login

If a user logs in with an unlinked LINE account through this link, they will be redirected to the WordPress login page.

- ?sll_mode=login (Default)

##### Signup (New Registration)

If a user logs in with an unlinked LINE account through this link, they will be redirected to the sign up page.

- ?sll_mode=signup
- ?sll_mode=register

##### LINE Linking/Unlinking

This is used for users who are already logged in to initiate or unlink their LINE account. After linking or unlinking, they will be redirected to the message page.

- ?sll_mode=link
- ?sll_mode=unlink

### Customizing Text Using Parameters

You can customize the text for links, linking status messages, and button labels using parameters in the shortcode.

#### login_label

Label for the login link.

#### unlinked_label

Status message when LINE is not linked. Default is "Unlinked to LINE"

#### unlinked_button

Label for the button that appears when LINE is not linked. Default is "Link."

#### linked_label

Status message when LINE is linked. Default is "Linked to LINE."

#### linked_button

Label for the button that appears when LINE is linked. Default is "Unlink."

#### Example

```
[line_login_link login_label="LINE Login" unlinked_label="You are not linked." linked_label="You are already linked." unlinked_button="LINK" linked_button="UNLINK"]
```

#### Output HTML example

```
//login link
<a href='https://example.com/linelogin/' class='line-login-link login'>[login_label]</a>

//Display status: Unlinked to LINE
<span class='line-login-label unlinked'>[unlinked_label]</span>
<a href='https://example.com/linelink/' class='line-login-link unlinked'>[unlinked_button]</a>

//Display status: Linked to LINE
<span class='line-login-label linked'>[linked_label]</span>
<a href='https://example.com/lineunlink/' class='line-login-link linked'>[linked_button]</a>
```

### LINE Linking Completion Message Shortcode

The following shortcode is used to display messages upon the completion of LINE linking and to prompt users to log in with their WordPress accounts if they are not linked to LINE:

```
[line_login_message]
```

In addition to adding this shortcode to the LINE Login Message page (linemessage) you created initially, adding it to the following pages will make it easier for users to understand when they need to log in with their WordPress accounts:

- Login page
- New user sign up(registration) page

## Login Flowchart

![Login Flowchart](https://blog.shipweb.jp/wp-content/uploads/2022/01/LINE%E3%83%AD%E3%82%B0%E3%82%A4%E3%83%B3%E3%83%95%E3%83%AD%E3%83%BC-569x1024.png)

## Login Redirection

When you initiate LINE Login from a login page with the `redirect_to` parameter in the URL, it will redirect to the URL specified in `redirect_to` after LINE Login is completed.

The flow goes as follows: Access a page that requires login while not logged in → Redirect to the login page → Perform LINE Login → Redirect to the original access destination URL.

## Manual Linking by Administrators

If you know the LINE User ID (a 33-character alphanumeric string starting with "U," not the LINE username), you can manually link a specific WordPress user to that LINE User ID.

To do this, open the information editing page for the WordPress user you want to link from the user list page in the admin panel. In the LINE Login Linking section, enter the LINE User ID of the LINE account you want to link and save it.

You can obtain the LINE User ID from your LINE Developers account if it's your own ID. For other cases, you can obtain it using the Messaging API's webhook or, for premium or authenticated accounts, from the user list retrieval endpoint.

## Direct Linking URL

To use this feature, you must have the setting **Use user-specific login link: Enabled** in other settings.

You can obtain a user-specific LINE Login link from the user information editing page in the admin panel (/wp-admin/user-edit.php). You can send this link individually to users via LINE messages or other means. Users can click the link to establish a connection without entering their WordPress ID/password.

When a user performs LINE Login through this LINE Login link, even if they are not logged in to WordPress, they will be linked to the logged-in LINE User.

- If a LINE account that is already linked to another WordPress user logs in:
  → The WordPress user linked to the LINE account will not change.
- Link expiration:
  → None
- Deactivation method for links:
  → None (You can deactivate them in bulk by setting them to "Not in use")

## Screenshots

### Mobile

#### Login Link Display Example

![](https://blog.shipweb.jp/wp-content/uploads/2023/01/image-4.png)

#### Login Page

If you are logged in to the LINE app on your smartphone, you will be directed to the authorization screen, so the login page is not displayed (it briefly switches to the LINE app and shows that you are logged in).

#### Authorization Screen

![](https://blog.shipweb.jp/wp-content/uploads/2023/01/image-2.png)

### PC

#### Login Page

If your browser's cookies contain previously logged-in LINE account information, you will see a single sign-on screen to log in with that account. If your browser does not have those cookies, you will see the login screen for entering your email and password.

##### No Cookies (Login with Email and Password)

![](https://blog.shipweb.jp/wp-content/uploads/2023/01/image.png)

##### With Cookies (Single Sign-On)

![](https://blog.shipweb.jp/wp-content/uploads/2023/01/image-1.png)

#### Authorization Screen

![](https://blog.shipweb.jp/wp-content/uploads/2023/01/image-3.png)

## Customization

You can configure settings through the LINE Login settings page.

It's possible to change the content of messages and the URLs of various pages.

For more complex customizations, we offer paid services. Please [contact us here](https://blog.shipweb.jp/contact) if you have specific customization requirements.

## System Requirements

- WordPress 4.9.13 or higher

## Author

- Ship

## Acknowledgments

- To all those who have used this plugin.

## License

- GPLv3