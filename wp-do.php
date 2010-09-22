<?php
/*
Plugin Name: Wordpress Do
Plugin URI: http://www.heavyworks.net/projects/wp-do
Description: Do helps finding posts and actions from any page when logged in.
Version: 1.0.0
Author: Jan Seidl
Author URI: http://www.heavyworks.net
License: GPL2
*/
/*  Copyright 2010  Jan Seidl  (email : jan.seidl@heavyworks.net)

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

/* Includes */
include 'lib/do.class.php';

/* Callback Functions */
function wp_do_init() {

    $do = new WP_Do();
    $do->init();

    if (is_user_logged_in()) {
        add_action('wp_ajax_wp_do_action','wp_do_handle_ajax_call');
    }//end if

}//end wp_do_init() 

function wp_do_handle_ajax_call() {
    /* Run actions */
    $term = (isset($_GET['q']) && !empty($_GET['q'])) ? $_GET['q'] : null;
    $do = new WP_Do();
    $actions = $do->search($term);
    print $actions;
    exit;
}//end wp_do_handle_ajax_call

function wp_do_drawbox() {
    $do = new WP_Do();
    $do->drawBox();
}//end wp_do_drawbox

/* Actions */
add_action('init','wp_do_init');
add_action('wp_print_footer_scripts','wp_do_drawbox');

?>
