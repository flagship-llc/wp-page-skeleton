=== Page Skeleton ===
Contributors: keita_kobayashi
Tags: development, static page, structure, sync
Requires at least: 3.3.1
Tested up to: 3.5
Stable tag: 1.0
License: MIT
License URI: https://github.com/flagship-llc/wp-page-skeleton/blob/master/README.md

Page Skeleton is a plugin that will recreate static page structures for large sites.

== Description ==

Page Skeleton will keep all your static pages in sync between local, staging, and production environments. Page Skeleton reads a file called `skeleton.yml` in the theme directory, and recreates page structure, title, templates, and content from there. `skeleton.yml` is a simple YAML-formatted file with the purpose of being easy to read (and edit, if need be) by a human.

No free support is offered for Page Skeleton; contact for commercial support.

DISCLAIMER: This software is provided as-is, without warranty of any kind. Data loss may occur if you are not careful.

== Installation ==

1. Install the plugin
2. Activate!
3. Export your current page structure (make sure you add this to source control!)
4. Pull (checkout for you SVN users) the `skeleton.yml` file and press the "Sync pages from Skeleton" button.
5. All your static pages will be recreated!

== Frequently Asked Questions ==

= Will this break my site? =

It will if you aren't careful. Please make sure you're backing up your site regularly.

= I don't want to re-create my huge structure by hand. Is there an exporter? =

Yes! Use the "Generate Skeleton" option to make the `skeleton.yml` file.

== Changelog ==

= 1.0 =
* Initial release
