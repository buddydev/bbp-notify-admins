=== bbPress Notify Admins ===
Contributors: buddydev,sbrajesh
Tags: bbpress, forum-notifications, admin notifications, notifications
Requires at least: 4.7.0
Tested up to: 4.9.7
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

bbPress Notify Admins plugin notifies all site admins when a new topic is created or a new reply is posted on the bbPress based forums. 

== Description ==
bbPress Notify Admin plugin notifies all the admins when a new topic is created or a new reply is posted on bbPress forum. It needs bbPress 2.2 or above.
We use this plugin internally at BuddyDev to provide timely support.

For more details, please visit [BuddyDev.com]( https://buddydev.com/bbpress/introducing-bbpress-notify-admins-plugin-notify-admins-on-new-topics-or-new-replies )

For support, please use [BuddyDev Forums]( https://buddydev.com/support/forums/ ).

== Installation ==

1. Visit Dashboard->Plugins->Add New ( Network Admin->Plugins->Add New )
1. Search for bbPress Notify Admin
1. Click Install and activate it
That's all.

Alternatively you can download the zip file, extract and upload. Then activate.

== Frequently Asked Questions ==

= Where is the setting? =
There is not settings. Just activate and that's all.


== Changelog ==
= 1.0.3 =
* Include the keymaster role in the list to be notified.
* Cleanup code.

= 1.0.2 =
* Fix type, notify method expects 'headers' not 'header'. Now, will allow sending mails to multiple users using the previous filter.

= 1.0.1 =
* Added filter to allow including extra emails to be notified

= 1.0.0 =
* Initial release
