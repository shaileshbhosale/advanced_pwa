CONTENTS
---------------------
   
 * About
 * Pre-Requisites
 * Installation
 * Configuration
 * Troubleshooting
 * Maintainers

About
-----

- This module will turn your website into a progressive web app
- User can choose to subscribe or not to subscribe to push notifications
- Notifications will be sent to subscribed users on publish of specific content
   types. Site admin can choose such content types using this module
- Site admin can send generic notifications to all subscribed users
- Site admin can set background color and theme color of progressive web app
- Site admin can set public / private keys that will be used to sign Push API
   requests
- Site admin can view subscribed users (User id and subscription endpoint)


Pre-Requisites
--------------

- php version 7.0 or higher
- minishlink/web-push library version 4.0
- Site domain should be SSL enabled. Push notifications only works on SSL
   enabled domains

Installation
------------

1. Install 'Advanced Progressive web app' module
   - Install using composer: 'composer require drupal/advanced_pwa'
      (recommended)
      or
   - Download and install 'Advanced Progressive web app' module
   - The above mentioned 'web-push' library dependency will be automatically
      installed if you are installing the module using composer
2. If you did not install the module using composer, install the 'web-push'
    library manually:
   - Run 'composer require minishlink/web-push:^4.0' from your sites root
      folder
   Please note that the current module works only with web-push library
      version 4.0

Configuration
-------------

Once you install the module, a link 'Advanced pwa Settings' will appear on
   '/admin/config' page under 'System'.

1. Go to '/admin/config/system/advanced_pwa' to configure manifest related
     settings
   a. Enable 'Enable push notifications' to enable push notification feature.
       Disabling the push notifications will ensure that no user will be able
        to receive push notifications
   b. 'Short name' will be name of the app that will appear on users home
        screen
   c. Enter Name of the app
   d. 'General App Icon' will be the icon that will appear on users home
        screen, along with app name
   e. 'Background Color' will be shown when the user opens the website from
        their homescreen
   f. 'Theme Color' is used to create a consistent experience in the browser
2. Go to '/admin/config/advanced_pwa/config' to set up public / private keys
     and upload app notification icon
   Note: Click on 'Generate keys' before uploading notification icon.
3. 'Push Notification Subscription'
     ('/admin/config/advanced_pwa/config-subscription') config page will allow
        you to select content types. Push notification will be sent to
          subscribed users whenever content of the selected content type
           is published
4. 'Push Notification Subscription List'
     (/admin/config/advanced_pwa/subscription-list) page will show list of
       subscribed users
5. You can send generic notification message from
    'Broadcast Push Notification' (/admin/config/advanced_pwa/config-broadcast)
      page

Troubleshooting
---------------

1. The current module works only with web-push library version 4.0
2. Web-push library 4.0 needs php version 7.0 or higher
