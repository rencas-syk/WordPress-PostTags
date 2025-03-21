<?php
/**
 * 
 * Plugin Name: Post Tags
 * Description: Adding Tags to Posts
 * Version: 1.0
 * Author: rencas
 * Text Domain: wp-post-tags
 */


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Check if class exists to avoid plugin conflicts
if ( !class_exists('PostTags') ) {

    class PostTags {
        public function __construct() {

            // Add the admin submenu
            add_action('admin_menu', array( $this,'add_admin_menu') );
            // Add the AJAX action (only for logged in users)
            add_action('wp_ajax_save_post_tags', array($this,'save_post_tags'));
            // Enqueue the AJAX request script
            add_action('admin_enqueue_scripts', [$this, 'enqueue_savetags_script']);
        }

        public function enqueue_savetags_script() {
            wp_enqueue_script('wp-post-tags-savetags-js', plugin_dir_url(__FILE__) . 'js/savetags.js', [], null, true);

            wp_localize_script('wp-post-tags-savetags-js', 'ajax_object', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('save_post_tags_nonce') // required by WP for security check
            ]);
        }
        
        public function add_admin_menu() {
            add_submenu_page(
                parent_slug: 'edit.php', // admin menu slug
                page_title: __("Post Tags"), 
                menu_title: __('Post Tags'),
                capability: 'manage_options',
                menu_slug: 'pt-options',
                callback: array( $this,'pt_admin_page'),
            );
        }

        // AJAX request handler
        public function save_post_tags() {
            // Check if necessary parameters exist

            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'save_post_tags_nonce')) {
                wp_send_json_error(["message" => "Security check failed"]);
                wp_die();
            }
            if (!isset($_POST['post_id']) || !isset($_POST['post_tags'])) {
                wp_send_json_error(["message" => "Invalid request"]);
            }

            // Sanitize inputs
            $post_id = intval($_POST['post_id']);
            $post_tags = sanitize_text_field($_POST['post_tags']);

            // Verify if the post exists
            if (get_post($post_id) === null) {
                wp_send_json_error(["message" => "Post not found"]);
            }

            // Update post meta (ensure the user is authorized)
            if (!current_user_can('edit_post', $post_id)) {
                wp_send_json_error(["message" => "Permission denied"]);
                wp_die();
            }
        
            // Update post meta
            $updated = update_post_meta($post_id, '_post_tags', $post_tags);
        
            if ($updated !== false) {
                wp_send_json_success(["message" => "Tags updated successfully"]);
            } else {
                wp_send_json_error(["message" => "Failed to update tags"]);
            }
        
            wp_die(); // WP: always call this function after sending the response
        }
        
        // Function to display the admin page
        // displays a table with all posts, their IDs and their tags
        // each row has an input field to edit the tags and a button to save them
        // IDs and Title are not editable
        public function pt_admin_page() {
            ?>
            <div class="wrap">
                <div id="post-tags-message"></div>
                <h1>Simple Tagging System</h1>
                <h2>Existing Tags</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Post ID</th>
                            <th>Title</th>
                            <th>Tags</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $args = ['post_type' => 'post', 'posts_per_page' => -1]; // -1: all posts
                        $posts = get_posts($args);
                        foreach ($posts as $post) {
                            $tags = get_post_meta($post->ID, '_post_tags', true);
                            echo "<tr>
                                    <td>{$post->ID}</td>
                                    <td>{$post->post_title}</td>
                                    <td><input type='text' name='post_tags' value={$tags}>
                                    <input type='submit' value=" . __('Save') . " class='button button-primary'
                                    onclick='saveTags(this.parentNode.parentNode)'></td> 
                                  </tr>"; // onclick: call the enqueued saveTags function from savetags.js with the current row
                        }
                        ?>
                    </tbody>
                </table>
                
            </div>
            <?php
        }
    }

    new PostTags;
}
