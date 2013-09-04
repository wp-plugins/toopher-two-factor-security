=== Toopher Two-Factor Security (BETA) ===
Contributors: Toopher, Inc.
Tags: security, two-factor, password, login, otp, authentication
Requires at least: 3.5.1
Tested up to: 3.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Toopher's Location-based Two-Factor Authentication protects your website from unauthorized logins.

== Description ==

Prevent unauthorized access to your Wordpress site with [Toopher's](https://www.toopher.com)
innovative location-based two-factor authentication solution.

First, the bad news: your password is probably lousy.  Passwords that are easy to remember are
insecure almost by definition, and passwords that are hard to remember end up getting written
down or forgotten.  Security-conscious administrators have known for decades that one of the
best ways to address the problem is [Multi-Factor Authentication](http://en.wikipedia.org/wiki/Multi-factor_authentication),
but most websites today still rely solely on passwords for a simple reason:  **Typical
Multi-Factor Authentication is expensive, and your users will hate you for making them use it.**

At Toopher, Inc., we want to fix this by building the most secure and usable Multi-Factor
authentication solution available, and making it available at the Internet's favorite
price-point: **FREE**.

Just install the plugin, register for API credentials at the
[Toopher Developer's Portal](https://dev.toopher.com), and you'll be ready to add
Two-Factor authentication for up to ten users.

== Installation ==

1. Register for your free "Requester Credentials" at the [Toopher Developer Portal](https://dev.toopher.com)
1. From the WordPress dashboard, install and activate the Toopher Plugin
1. In the "Toopher Authentication" settings menu [Settings -> Toopher Authentication],
enter your assigned Toopher API Key and Secret from the first step.  The default Toopher
API URL is [https://api.toopher.com/v1](https://api.toopher.com/v1)

That's it!  Your users can now individually enable Toopher protection on their account
on their user profile page.

== Frequently Asked Questions ==

== Screenshots ==

1. The Toopher Pairing screen, located in the User Profile admin view

== Changelog ==

= 1.3 (BETA) =

* Fix issue with XMLRPC authentication routine polling indefinitely in some cases.

= 1.2 (BETA) =

* Fix issue with Toopher user-specific options defaulting to incorrect values
* Add uninstall routine
* Add link to settings page on plugin list

= 1.1 =

* Fix terminal naming issue

= 1.0 =

* Initial release
