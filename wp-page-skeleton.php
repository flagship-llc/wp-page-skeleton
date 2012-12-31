<?php
/*
Plugin Name: Page Skeleton
Plugin URI: http://keita.flagship.cc/page-skeleton/
Description: Reads a file called "skeleton.yml" in your theme folder to keep your static pages in sync.
Version: 0.0.1
Author: Keitaroh Kobayashi, Flagship LLC
Author URI: http://keita.flagship.cc/
License: MIT
*/

$spyc_path = dirname(__FILE__) . '/spyc/Spyc.php';
if (!is_file($spyc_path)) {
  wp_die("Cannot find Spyc. Maybe git submodules aren't initialized.");
} else {
  require_once($spyc_path);
}

class WPSkeleton {

  public $enabled = false;

  function init() {
    add_action('admin_init', array($this, 'admin_init'));
    add_action('admin_menu', array($this, 'admin_menu'));

    $file = get_stylesheet_directory() . '/skeleton.yml';
    if (file_exists($file)) {
      $this->enabled = true;
      $this->file = $file;
    }
  }

  function load_configuration() {
    return Spyc::YAMLLoad($this->file);
  }

  function sync($action = true, $pages_array = false, $parent = false) {
    if ($pages_array === false) {
      // Use the root element.
      $pages_array = $this->load_configuration();
    }
    foreach ($pages_array as $slug => $page_data) {
      // Check if the page exists (use slug as key) - store page into $current_page
      $current_path = '';
      if ($parent === false) {
        $current_path .= sanitize_title($slug);
      } else {
        // We have a parent.
        
        if (is_object($parent)) {

          if (count($parent->ancestors) > 0) {
            $slugs = array();
            foreach ($parent->ancestors as $anc_id) {
              $slugs[] = get_page($anc_id)->post_name;
            }
            $slugs = array_reverse($slugs);
            $slugs[] = $parent->post_name;
            $slugs[] = sanitize_title($slug);
          } else {
            $slugs = array($parent->post_name, sanitize_title($slug));
          }

        } else {
          $slugs = $parent;
          $slugs[] = sanitize_title($slug);
        }

        $current_path = implode('/', $slugs);
      }
      if ($current_path == '') {
        // Nothing has changed. Empty slugs are not good.
        continue;
      }

      echo "Slug: $current_path\n";
      $current_page = get_page_by_path($current_path);

      // If the page exists:
      if ($current_page != null) {

        if ($action === false) {

          echo " - Marked for update.\n";

        } else {

          echo " - Marked for update (not implemented yet).\n";

        }

      } else {
        // If the page doesn't exist, create page
        if ($action === false) {

          echo " - Marked for creation.\n";
          $current_page = isset($slugs) ? $slugs : array(sanitize_title($slug));

        } else {

          $new_page = array(
            'post_name' => $slug
          );

          if (array_key_exists('title', $page_data)) {
            $new_page['post_title'] = $page_data['title'];
          }

          if (array_key_exists('content', $page_data)) {
            $new_page['post_content'] = $page_data['content'];
          }

          if (array_key_exists('status', $page_data)) {
            $new_page['post_status'] = $page_data['post_status'];
          } else {
            $new_page['post_status'] = 'publish';
          }

          if ($parent !== false) {
            $new_page['post_parent'] = $parent->ID;
          }

          $new_page['post_type'] = 'page';

          $new_page_id = wp_insert_post($new_page);

          if (array_key_exists('template', $page_data)) {
            update_post_meta($new_page_id, '_wp_page_template', $page_data['template']);
          }

          echo " - Created.\n";

          $current_page = get_page($new_page_id);
        }
      }

      // Recurse into child pages.
      if (array_key_exists('pages', $page_data)) {
        $this->sync($action, $page_data['pages'], $current_page);
      }
    }
  }

  function admin_init() {

  }

  function admin_menu() {

    $wp_page_skeleton = $this;

    add_menu_page(
      'Skeleton',
      'Skeleton',
      'edit_pages',
      'wp_page_skeleton',
      function() use ($wp_page_skeleton) {
        require(dirname(__FILE__) . '/admin_page.php');
      }
    );

  }
}

$wp_page_skeleton = new WPSkeleton();
add_action('init', array($wp_page_skeleton, 'init'));

