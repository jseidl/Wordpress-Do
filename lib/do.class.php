<?php

class WP_Do {

    /* Actions Array */
    private $actions = array(
        /* Dashboard */
        array('name'=>'go to dashboard',                'url' => 'index.php'),
        array('name'=>'view updates',                   'url' => 'update-core.php'),
        /* Posts */
        array('name'=>'write post',                     'url' => 'post-new.php'),
        array('name'=>'manage posts',                   'url' => 'edit.php'),
        array('name'=>'manage tags',                    'url' => 'edit-tags.php?taxonomy=post_tag'),
        array('name'=>'manage categories',              'url' => 'edit-tags.php?taxonomy=category'),
        /* Media */
        array('name'=>'go to media library',            'url' => 'upload.php'),
        array('name'=>'add new media to library',       'url' => 'media-new.php'),
        /* Links */
        array('name'=>'manage links',                   'url' => 'link-manager.php'),
        array('name'=>'add new link',                   'url' => 'link-add.php'),
        array('name'=>'manage link categories',         'url' => 'edit-link-categories.php'),
        /* Pages */
        array('name'=>'write page',                     'url' => 'page-new.php'),
        array('name'=>'manage pages',                   'url' => 'edit.php?post_type=page'),
        /* Comments */
        array('name'=>'manage comments',                'url' => 'edit-comments.php'),
        array('name'=>'manage pending comments',        'url' => 'edit-comments.php?comment_status=moderated'),
        array('name'=>'manage spammed comments',        'url' => 'edit-comments.php?comment_status=spam'),
        /* Appearance */
        array('name'=>'manage themes',                  'url' => 'themes.php'),
        array('name'=>'manage widgets',                 'url' => 'widgets.php'),
        array('name'=>'manage navigation menus',        'url' => 'nav-menus.php'),
        /* Plugins */
        array('name'=>'manage plugins',                 'url' => 'plugins.php'),
        array('name'=>'add new plugin',                 'url' => 'plugin-install.php'),
        array('name'=>'plugin editor',                  'url' => 'plugin-editor.php'),
        /* Users */
        array('name'=>'manage users',                   'url' => 'users.php'),
        array('name'=>'add new user',                   'url' => 'user-new.php'),
        array('name'=>'edit my profile',                'url' => 'profile.php'),
        /* Settings */
        array('name'=>'edit general settings',          'url' => 'options-general.php'),
        array('name'=>'edit reading settings',          'url' => 'options-writing.php'),
        array('name'=>'edit writing settings',          'url' => 'options-reading.php'),
        array('name'=>'edit permalink settings',        'url' => 'options-permalink.php'),
        array('name'=>'edit discusison settings',       'url' => 'options-discussion.php'),
        array('name'=>'edit media settings',            'url' => 'options-media.php'),
        array('name'=>'edit privacy settings',          'url' => 'options-privacy.php'),
        array('name'=>'edit miscelaneous settings',     'url' => 'options-misc.php')
    );

    public function __construct() {

        // Constants
        define('WPDO_PATH',   WP_PLUGIN_DIR.'/wp-do/');
        define('WPDO_DIR',    WP_PLUGIN_URL.'/wp-do/');
        define('WPDO_JSDIR',  WPDO_DIR.'js/');
        define('WPDO_CSSDIR', WPDO_DIR.'css/');

        define('WPDO_TYPE_ACTION','action');
        define('WPDO_TYPE_POST','action');
        define('WPDO_TYPE_PAGE','page');

        // Dependency check
        if (function_exists('json_encode')) if (!class_exists('Services_JSON')) include WPDO_PATH.'/lib/PEAR/JSON.php';

    }//end __construct()

    public function init() {
        
        if (is_user_logged_in()) {

            wp_enqueue_script('jquery');
            // Hotkeys
            // Registering new one because one bundled with WP is
            // rather obsolete
            wp_deregister_script('jquery-hotkeys');
            wp_register_script('jquery-hotkeys', WPDO_JSDIR.'jquery-plugins/jquery.hotkeys.js', 'jquery', null, true);
            wp_enqueue_script('jquery-hotkeys');

            // Autocomplete
            wp_enqueue_script('jquery-autocomplete', WPDO_JSDIR.'jquery-plugins/jquery.autocomplete.min.js', 'jquery', null, true);

            // Monolithic WP Do Script
            wp_enqueue_script('wp_do', WPDO_JSDIR.'wp_do.js', null, null, true);
            // CSS
            wp_enqueue_style('wp_do',WPDO_CSSDIR.'wp_do.css');

        }//end if

    }//end init()

    public function drawBox() {

        if (is_user_logged_in()) {

        $boxContent = file_get_contents(WPDO_PATH.'/res/box.html');

        // Add box content
        $boxContent = str_replace('%action_label%', _('What do you want to do?'), $boxContent);
        $boxContent = str_replace('%action_url%', admin_url('admin-ajax.php'), $boxContent);
        $boxContent = str_replace('%window_note%', _('Press <kbd>ESCAPE</kbd> to close this box'), $boxContent);

        print $boxContent;

        }//end if

    }//end drawBox()

    public function search($term) {

        $matches = array();

        if (empty($term)) return $this->_encodeJSON($matches);

        $term = $this->_sanitizeTerm($term);

        // Grab Actions
        foreach ($this->actions as $action) {

            if (strpos($action['name'], $term) !== false) $matches[] = $this->_buildFullURL($this->_setType($action, WPDO_TYPE_ACTION));

        }//end foreach

        // Grab Posts
        global $wpdb;
        $posts = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type NOT IN ('revision','page') AND post_title LIKE '%{$term}%'");
        foreach ($posts as $post) {
            $matches[] = $this->_buildFullURL(array(
                'name' => _('Edit post: ') . $post->post_title,
                'type' => WPDO_TYPE_POST,
                'url' =>  "post.php?action=edit&post={$post->ID}"
            ));
        }//end foreach

        // Grab Pages
        global $wpdb;
        $posts = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type NOT IN ('revision','post') AND post_title LIKE '%{$term}%'");
        foreach ($posts as $post) {
            $matches[] = $this->_buildFullURL(array(
                'name' => _('Edit page: ') . $post->post_title,
                'type' => WPDO_TYPE_POST,
                'url' => "post.php?action=edit&post={$post->ID}"
            ));
        }//end foreach
        return $this->_encodeJSON($matches);

    }//end search()

    private function _setType($item, $type) {
        $item['type'] = $type;
        return $item;
    }//end _setType

    private function _buildFullURL($item) {
        $item['url'] = admin_url($item['url']);
        return $item;
    }//end _buildFullURL()

    private function _encodeJSON($value) {

        if (function_exists('json_encode')) return json_encode($value);

        $json = new Services_JSON();
        return $json->encode($value);

    }//end _encodeJSON()

    private function _sanitizeTerm($term) {

        return strtolower(strip_tags($term));

    }//end _sanitizeTerm()

}//end :: wp_Do
?>
