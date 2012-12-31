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
  public $pages_to_update = array();

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
      // - Since we're at the root element, we'll reset the pages to update array.
      $this->pages_to_update = array();
    }
    foreach ($pages_array as $slug => $page_data) {
      // Check if the page exists (use slug as key) - store page into $current_page
      $current_path = '';
      if ($parent === false) {
        $current_path = sanitize_title($slug);
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

      $current_path = "/$current_path/";

      $page_data_array = array(
        'slug' => $current_path
      );

      $current_page = get_page_by_path($current_path);

      // If the page exists:
      if ($current_page != null) {
        $page_data_array['action'] = 'update';

        // Since the page exists, we can embed the actual page into the array
        $page_data_array['page'] = $current_page;

        if ($action === false) {

          // No action update

        } else {

          // Update the page
          $new_page = $this->make_page_array($page_data, $parent);
          $new_page['ID'] = $current_page->ID;
          wp_update_post($new_page);

          if (array_key_exists('template', $page_data)) {
            update_post_meta($new_page['ID'], '_wp_page_template', $page_data['template']);
          }

        }

      } else {
        // If the page doesn't exist, create page
        $page_data_array['action'] = 'create';

        if ($action === false) {

          $current_page = isset($slugs) ? $slugs : array(sanitize_title($slug));

        } else {

          $new_page = $this->make_page_array($page_data, $parent, $slug);

          $new_page_id = wp_insert_post($new_page);

          if (array_key_exists('template', $page_data)) {
            update_post_meta($new_page_id, '_wp_page_template', $page_data['template']);
          }

          $current_page = get_page($new_page_id);
          $page_data_array['page'] = $current_page;
        }
      }

      // Recurse into child pages.
      $this->pages_to_update[] = $page_data_array;

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

  private function make_page_array($page_data, $parent, $slug = null) {
    $new_page = array();

    if ($slug != null) {
      $new_page['post_name'] = $slug;
    }

    if (array_key_exists('title', $page_data)) {
      $new_page['post_title'] = $page_data['title'];
    }

    if (array_key_exists('content', $page_data)) {
      $new_page['post_content'] = $page_data['content'];
    }

    if (array_key_exists('status', $page_data)) {
      $new_page['post_status'] = $page_data['status'];
    } else {
      $new_page['post_status'] = 'publish';
    }

    if ($parent !== false) {
      $new_page['post_parent'] = $parent->ID;
    }

    $new_page['post_type'] = 'page';

    return $new_page;
  }
}

$wp_page_skeleton = new WPSkeleton();
add_action('init', array($wp_page_skeleton, 'init'));

