=== Flywheel Migrations ===
Contributors: getflywheel, blogvault, akshatc
Tags: flywheel, migration
Requires at least: 4.0
Tested up to: 5.8
Requires PHP: 5.4.0
Stable tag: 4.62
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Ready to migrate to Flywheel? We’re super excited to take care of your site and handle all of the hassles of hosting, including security, performance, and updates. Let’s get you migrated over and on your way to creating and managing WordPress sites with ease, so you can keep focusing on doing your best work!

== Description ==

Ready to migrate to Flywheel? We’re super excited to take care of your site and handle all of the hassles of hosting, including security, performance, and updates. Let’s get you migrated over and on your way to creating and managing WordPress sites with ease, so you can keep focusing on doing your best work!

The robots inside this plugin do all of the heavy lifting for you! With clearly defined fields and email notifications, you don't have to watch the progress bar tick by. All you need to kick off a migration is a few Flywheel specific fields and you're on your way.

Not sure where to start? Visit our [help doc](https://getflywheel.com/wordpress-support/migrating-your-site-from-another-host-to-flywheel/) to walk you through step by step instructions.

Change your mind? [Let us migrate your site to Flywheel for free!](https://www.getflywheel.com/migration)

By using this plugin you are agreeing to our [Terms of Service](https://blogvault.net/tos/)

[youtube https://www.youtube.com/watch?v=SyDhfrg6NBc]

== FREQUENTLY ASKED QUESTIONS ==

=Can I still use this plugin if I don’t have a Flywheel account?=
No, but creating a Flywheel account is simple (and free)! [Create an account](app.getflywheel.com).

=What information will the plugin ask for?=
You’ll need to provide your email address, Flywheel dashboard URL, Flywheel temporary URL, and Flywheel privacy mode credentials.  Please visit the migration page in your site dashboard to find this information, and check out this [migration help doc](https://getflywheel.com/wordpress-support/migrating-your-site-from-another-host-to-flywheel/) for more details.

=Is Multisite Network supported with this plugin?=
Yes, this plugin has been developed to be capable of moving full Multisite Networks! To move a full network, make sure to activate the plugin.

=Can I migrate a site into or out of a Multisite Network using this plugin?=
No. This plugin is designed to migrate only a single WordPress install as is. This plugin does not support single WordPress installs being migrated into Multisite Networks. It also does not support migrating a sub-site out of a Multisite Network.

=How long does it take to migrate a website?=
Sites come in all shapes and sizes. Because of this, migrations can range anywhere from 30 minutes to several hours.

=What happens if I run into an error after the migration is complete?=
No matter what happens, we’re always here to help! Once your migration is complete, we suggest clicking through your site for quality assurance. If anything is amiss, reach out to our [technical onboarding team](https://www.getflywheel.com/migration).

=Other than running the plugin, is there anything else I need to do?=
Once the migration completes, you will still need to [take your site live](https://www.getflywheel.com/migration) by updating your DNS to point to our servers. If you have any custom configurations on your current server, our [support team](https://www.getflywheel.com/migration) can assist you in setting those up on your Flywheel server.

=Do I need to leave the window open while the migration is processing?=
Nope! Sit back and relax while robots move your site. Once your migration is complete, we’ll send a completion email to your inbox.


== INSTALLATION ==

If you have WordPress 2.7 or above you can install Flywheel Migrations from within WordPress

1. Navigate to the plugins page in the WordPress dashboard of the site you want to migrate
2. Select “Add New”
3. Search for “Flywheel Migrations”
4. Find the plugin and select “Install”
5. Once the plugin is installed you can activate it and begin migrating your site!

== CHANGELOG ==
= 4.62 =
* MultiTable Sync in single callback functionality added.
* Improved host info
* Fixed services data fetch bug
* Fixed account listing bug in wp-admin

= 4.58 =
* Better Handling of error message from Server on signup
* Added Support for Multi Table Callbacks

= 4.35 =
* Improved scanfiles and filelist api

= 4.31 =
* Fetching Mysql Version
* Robust data fetch APIs
* Core plugin changes
* Sanitizing incoming params

= 4.25 =
* Updated links and added a step-by-step migration video under Description section

= 4.1 =
* First release of Flywheel Plugin
