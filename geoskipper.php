<?php

/*
  Plugin Name: GeoSkipper
  Plugin URI: http://wordpress.org/extend/plugins/geoskipper/
  Description: Allows you to easily install the Geoskipper plugin on your site and control which pages it shows up on.
  Version: 0.1.1
  Author: Tony Primerano
  Author URI: http://tonycode.com/blog
  License: GPL2

  Copyright 2011 GeoSkipper info@geoskipper.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/* Returns true if GeoSkipper code should be placed on the current page */

function display_geoskipper($post_id) {
  global $post;

  if (is_home() && get_option('geoskipper_show_on_blog_listing') == "on") {
    // We're on the homepage
    return true;
  }
  if (!is_home() && is_front_page() && get_option('geoskipper_show_on_static_homepage') == "on") {
    // We're on a static homepage
    return true;
  }

  if (is_page() || is_single()) {
    if (get_post_meta($post->ID, 'geoskipper-on', true) == "on") {
      return true;
    }
  }

  if (get_option('geoskipper_show_on_all_pages') == "on") {
    return true;
  }

  return false;
}

/* Places GeoSkipper snippet in footer.  Called from wp_footer action. */

function insert_geoskipper_script($post_id) {
  $id = get_option('geoskipper_site_id');
  if ($id) {
    if (display_geoskipper($post_id)) {
      echo '<script src="http://widget.geoskipper.com/geo/' . $id . '" type="text/javascript"></script>';
    }
  }
}

/* Builds GeoSkipper Settings Page */

function geoskipper_settings() {

  if ($_POST["action"] == "update") {
    update_option("geoskipper_site_id", $_POST["geoskipper_site_id"]);
    update_option("geoskipper_show_on_blog_listing", $_POST["geoskipper_show_on_blog_listing"]);
    update_option("geoskipper_show_on_static_homepage", $_POST["geoskipper_show_on_static_homepage"]);
    update_option("geoskipper_show_on_all_pages", $_POST["geoskipper_show_on_all_pages"]);
    $message = '<div id="message" class="updated fade"><p><strong>Options Saved</strong></p></div>';
  }

  $options['site_id'] = get_option('geoskipper_site_id');
  $options['blog_on'] = '';
  $options['all_on'] = '';
  $options['static_on'] = '';
  if (get_option('geoskipper_show_on_blog_listing') == "on") {
    $options['blog_on'] = 'checked="checked"';
  }
  if (get_option('geoskipper_show_on_static_homepage') == "on") {
    $options['static_on'] = 'checked="checked"';
  }
  if (get_option('geoskipper_show_on_all_pages') == "on") {
    $options['all_on'] = 'checked="checked"';
  }

  echo '
	<div class="wrap">
		' . $message . '
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>GeoSkipper Settings</h2>
		<form method="post" action="">
		<input type="hidden" name="action" value="update" />
		<h3>GeoSkipper Settings</h3>
        <label for="geoskipper_site_id">Enter Site Id</label>
		<input name="geoskipper_site_id" type="text" id="geoskipper_site_id" value="' . $options['site_id'] . '" /> 
		<br />
        <label for="geoskipper_show_on_blog_listing">Show Widget on Blog Listing</label>
		<input name="geoskipper_show_on_blog_listing" type="checkbox" id="geoskipper_show_on_blog_listing" ' . $options['blog_on'] . ' />
		<br />
        <label for="geoskipper_show_on_static_homepage">Show Widget on Static Homepage (when applicable)</label>
		<input name="geoskipper_show_on_static_homepage" type="checkbox" id="geoskipper_show_on_static_homepage" ' . $options['static_on'] . ' />
		<br />
        <label for="geoskipper_show_on_all_pages">Show Widget on all Pages</label>
		<input name="geoskipper_show_on_all_pages" type="checkbox" id="geoskipper_show_on_all_pages" ' . $options['all_on'] . ' />
		<br /><br />
		<input type="submit" class="button-primary" value="Save Changes" />
		</form>
        <p> 
        You can enable GeoSkipper on invididual pages and posts when adding or editing them.
        </p>
        <p>
        Checking <strong>"Show Widget on all Pages"</strong> overrides any post/page level settings as well as the
        <strong>"Show Widget on Blog Listing"</strong> and
        <strong>"Show Widget on Static Homepage"</strong> options.
        </p>
        <p>Questions?  Visit the <a href="http://wordpress.org/extend/plugins/geoskipper/">plugin site</a> or our <a href="http://geoskipper.zendesk.com/home">help page</a></p>
	</div>';
}

/* Registers our settings page via admin_menu action callback */

function geoskipper_admin_menu() {
  add_options_page('GeoSkipper', 'GeoSkipper', 'edit_posts', 'geo-skipper-admin', 'geoskipper_settings');
}

/* Adds a box to the main column on the Post and Page edit screens.
 * Called from admin_init action */

function geoskipper_add_custom_box() {
  add_meta_box(
          'geoskipper-settings-id',
          'GeoSkipper&nbsp;Controls&nbsp;&nbsp;<a href="http://wordpress.org/extend/plugins/geoskipper/">Help</a>',
          'geoskipper_custom_box',
          'post', 'side'
  );
  add_meta_box(
          'geoskipper-settings-id',
          'GeoSkipper&nbsp;Controls&nbsp;&nbsp;<a href="http://wordpress.org/extend/plugins/geoskipper/">Help</a>',
          'geoskipper_custom_box',
          'page', 'side'
  );
}

/* Prints the box content on page/post page */

function geoskipper_custom_box($post) {
  // Use nonce for verification
  wp_nonce_field(plugin_basename(__FILE__), 'geoskipper_noncename');
  $checked = '';
  if (get_post_meta($post->ID, 'geoskipper-on', true) == "on") {
    $checked = 'checked="checked"';
  }
  // The actual fields for data entry
  echo '<label for="geoskipper-display">';
  echo "Show Geoskipper Widget on this " . ucfirst($post->post_type);
  echo '</label> ';
  echo '<input name="geoskipper-display" type="checkbox" id="geoskipper-display" ' . $checked . ' />';
  
}

/* When the post is saved, saves our custom data
 * Called from save_post action  */

function geoskipper_save_postdata($post_id) {
  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if (!wp_verify_nonce($_POST['geoskipper_noncename'], plugin_basename(__FILE__)))
    return $post_id;

  // verify if this is an auto save routine.
  // If it is our form has not been submitted, so we dont want to do anything
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    return $post_id;


  // Check permissions
  if ('page' == $_POST['post_type']) {
    if (!current_user_can('edit_page', $post_id))
      return $post_id;
  } else {
    if (!current_user_can('edit_post', $post_id))
      return $post_id;
  }

  // OK, we're authenticated: we need to find and save the data

  $mydata = $_POST['geoskipper-display'];

  update_post_meta($post_id, 'geoskipper-on', $mydata);

  return $mydata;
}

// Put widget in footer
add_action('wp_footer', 'insert_geoskipper_script');
// Add menu to setup widget
add_action('admin_menu', 'geoskipper_admin_menu');
// Menu on post and page edit sections
add_action('admin_init', 'geoskipper_add_custom_box', 1);
// Process page/post settings
add_action('save_post', 'geoskipper_save_postdata');
?>