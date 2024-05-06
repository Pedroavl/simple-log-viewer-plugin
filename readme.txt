=== Simple Log Viewer ===
Contributors: pedroasa
Tags: erros, logs, debug
Donate link: https://pedroavelar.com.br/
Requires at least: 5.4
Tested up to: 6.4.2
Requires PHP: 7.4
Stable tag: 1.0.3.1
License: GPL-3.0-or-later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A simple plugin to log errors in real time in a metabox in the admin panel.

== Description ==
Um simples plugin para registrar erros em tempo real em uma metabox no painel administrativo.

== Installation ==
1. Upload \"simple-log-viewer.php\" to the \"/wp-content/plugins/\" directory.
2. Activate the plugin through the \"Plugins\" menu in WordPress.
3. Activate WP_DEBUG in wp-config.php


== Frequently Asked Questions ==
**Is the plugin free?**
Yes, it always will be, we are open to the open source community.
**How do I contribute to plugin improvements?**
Send an email to pedro.emanuel.avl@gmail.com with subject contribute to Simple Log Viewer containing your github user in the subject, after acceptance create an issue, perform a fork, after adding the new feature make a pull request , for approval.



== Screenshots ==
1. View logs in metabox
2. Settings page for enable WP_DEBUG and clear logs


== Changelog ==
**Security path version**
1.0.3.1 - Fix security issue, permission public acess endpoint errors changed for proteged route
**Path Version**
1.0.3 - Bug fix: the option to activate WP_DEBUG was forcing activation even if the checkbox was unchecked  
1.0.2 - Correction in the directory structure to save log directory for uploads
1.0.1 - internationalization and support WP_DEBUG enable in pannel settings
**Version Beta**
1.0.0 Pre release
**Develop version**
0.0.1 Initial release
