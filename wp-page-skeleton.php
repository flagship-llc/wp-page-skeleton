<?php
/*
Plugin Name: Page Skeleton
Plugin URI: http://keita.flagship.cc/page-skeleton/
Description: Page Skeleton is a plugin that will recreate static page structures for large sites.
Version: 1.0.1
Author: Keitaroh Kobayashi, Flagship LLC
Author URI: http://keita.flagship.cc/
License: MIT
*/

if (!class_exists('Spyc')) {
  $spyc_path = dirname(__FILE__) . '/spyc/Spyc.php';
  if (!is_file($spyc_path)) {
    wp_die("Cannot find Spyc. Maybe git submodules aren't initialized.");
  } else {
    require_once($spyc_path);
  }
}

class WPSkeleton {

  public $enabled = false;
  public $pages_to_update = array();

  function init() {
    add_action('admin_init', array($this, 'admin_init'));
    add_action('admin_menu', array($this, 'admin_menu'));

    $this->file = get_stylesheet_directory() . '/skeleton.yml';
    if (file_exists($this->file)) {
      $this->enabled = true;
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

      if (!array_key_exists('template', $page_data)) { $page_data['template'] = 'default'; }

      $current_page = get_page_by_path($current_path);

      // If the page exists:
      if ($current_page != null) {
        // Since the page exists, we can embed the actual page into the array
        $page_data_array['page'] = $current_page;

        $new_page = $this->make_page_array($page_data, $parent);
        $new_page['ID'] = $current_page->ID;

        if ($this->page_needs_update($current_page, $new_page, $page_data['template'])) {
          $page_data_array['action'] = 'will update';

          if ($action == true) {

            $page_data_array['action'] = 'updated';

            // Update the page

            wp_update_post($new_page);

            if (array_key_exists('template', $page_data)) {
              update_post_meta($new_page['ID'], '_wp_page_template', $page_data['template']);
            }

          }

        } else {
          $page_data_array['action'] = 'none';
        }

      } else {
        // If the page doesn't exist, create page

        if ($action === false) {
          $page_data_array['action'] = 'will create';

          $current_page = isset($slugs) ? $slugs : array(sanitize_title($slug));

        } else {
          $page_data_array['action'] = 'created';

          $new_page = $this->make_page_array($page_data, $parent, $slug);

          $new_page_id = wp_insert_post($new_page);

          if (array_key_exists('template', $page_data)) {
            update_post_meta($new_page_id, '_wp_page_template', $page_data['template']);
          }

          $current_page = get_page($new_page_id);
          $page_data_array['page'] = $current_page;
        }
      }

      $page_data_array['data'] = $page_data;

      $this->pages_to_update[] = $page_data_array;

      // Recurse into child pages.
      if (array_key_exists('pages', $page_data)) {
        $this->sync($action, $page_data['pages'], $current_page);
      }
    }
  }

  function generate(&$error, $include_content = false, $write_to_file = false) {
    // Generate the YAML file.
    $error = false;

    $this->master_page_array = array();

    $this->recurse_pages($include_content);

    $this->master_page_array = $this->master_page_array['pages'];
    $skeleton = Spyc::YAMLDump($this->master_page_array);

    if ($write_to_file) {
      if (($f = @fopen($this->file, 'w')) === false) {
        $error = 'file permission';
      } else {
        fwrite($f, $skeleton);
        fclose($f);
      }
    }

    return $skeleton;
  }

  function admin_init() {
    wp_register_style('wp-page-skeleton-admin', plugins_url('admin_style.css', __FILE__));
  }

  function admin_menu() {

    $wp_page_skeleton = $this;

    $my_pages = array();

    $my_pages[] = add_menu_page(
      'Skeleton',
      'Skeleton',
      'edit_pages',
      'wp_page_skeleton',
      function() use ($wp_page_skeleton) {
        require(dirname(__FILE__) . '/admin_page.php');
      }
    );

    $my_pages[] = add_submenu_page(
      'wp_page_skeleton',
      'Generate Skeleton',
      'Generate Skeleton',
      'edit_pages',
      'wp_page_skeleton_generate',
      function() use ($wp_page_skeleton) {
        require(dirname(__FILE__) . '/admin_generate.php');
      }
    );

    foreach ($my_pages as $my_page) {
      add_action("admin_print_styles-{$my_page}", array($this, 'admin_styles'));
    }

  }

  function admin_styles() {
    wp_enqueue_style('wp-page-skeleton-admin');
  }

  private $master_page_array = array();

  private function recurse_pages($include_content = false, $parents = array(0), $slugs = array()) {
    $args = array(
      'hierarchical' => 0,
      'child_of' => end($parents),
      'parent' => end($parents)
    );
    $pages = get_pages($args);
    foreach ($pages as $page) {
      $parents_a = $parents;
      $slugs_a = $slugs;
      if (end($parents_a) == 0) {
        // Initial array.
        $parents_a = array();
        $slugs_a = array();
      }
      $parents_a[] = $page->ID;
      $slugs_a[] = urldecode($page->post_name);

      // $page_id_seq = implode('/', $slugs_a);
      // echo "{$page_id_seq} {$page->post_name}\n";

      $page_array = array(
        'title' => $page->post_title,
        'status' => $page->post_status,
        'menu_order' => $page->menu_order
      );

      if ($include_content === true) {
        $page_array['content'] = $page->post_content;
      }

      $template = get_post_meta($page->ID, '_wp_page_template', true);
      if ($template) {
        $page_array['template'] = $template;
      }

      // Insert this $page_array array into the master array
      $cursor = &$this->master_page_array;
      foreach ($slugs_a as $slug) {
        if (!array_key_exists('pages', $cursor)) {
          $cursor['pages'] = array();
        }
        if (!array_key_exists($slug, $cursor['pages'])) {
          $cursor['pages'][$slug] = array();
        }
        $cursor = &$cursor['pages'][$slug];
      }
      $cursor = $page_array;

      $this->recurse_pages($include_content, $parents_a, $slugs_a);
    }
  }

  private function page_needs_update($page, $page_data_array, $template = 'default') {
    if (is_integer($page)) {
      // This is actually a page ID that needs to be converted to an object.
      $page = get_page($page);
    }

    if (!is_object($page)) {
      // This page probably doesn't exist.
      return true;
    }

    if (isset($page_data_array['template'])) {
      $template = $page_data_array['template'];
    }

    foreach ($page_data_array as $key => $value) {
      if (isset($page->$key)) {
        if ($page->$key != $value) {
          return true;
        }
      }
    }

    // Check the template
    $now_template = get_post_meta($page->ID, '_wp_page_template', true);
    if ($now_template != $template) {
      return true;
    }

    return false;
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

    if (array_key_exists('menu_order', $page_data)) {
      $new_page['menu_order'] = $page_data['menu_order'];
    } else {
      $new_page['menu_order'] = 0;
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

