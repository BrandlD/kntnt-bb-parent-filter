<?php

/**
 * @wordpress-plugin
 * Plugin Name: Kntnt Parent Filter for Beaver Builder
 * Plugin URI:  https://github.com/Kntnt/kntnt-bb-parent-filter
 * Description: Extends Beaver Builder's loop settings with a parent filter.
 * Version:     1.0.0
 * Author:      Thomas Barregren
 * Author URI:  https://www.kntnt.com/
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: kntnt-bb-parent-filter
 * Domain Path: /languages
 */

namespace Kntnt\BB_Parent_Filter;

defined('WPINC') && new Plugin;

final class Plugin {

    private $step = 0;

    public function __construct() {
        add_action('plugins_loaded', [$this, 'run']);
    }

    public function run() {
        load_plugin_textdomain('kntnt-bb-parent-filter', false, basename(dirname(__FILE__)) . '/languages');
        add_filter('fl_builder_render_settings_field', [$this, 'builder_render_settings_field'], 10, 3);
        add_filter('fl_builder_loop_query_args', [$this, 'builder_loop_query_args']);
    }

    // Ugly hack to hook into bb-plugin/includes/ui-loop-settings.php just
    // after posts field has been rendered and adding a parents field if
    // the post type is page.
    public function builder_render_settings_field($field, $name, $settings) {
        if (0 == $this->step && 'posts_page' == $name) {
            $this->step = 1;
        }
        elseif (1 == $this->step) {
            $this->step = 2;
            \FLBuilder::render_settings_field('posts_parent', [
                'type'     => 'suggest',
                'action'   => 'fl_as_posts',
                'data'     => 'page',
                'label'    => __('Parent pages', 'kntnt-bb-parent-filter'),
                'help'     => __('Enter a list of parent pages.', 'kntnt-bb-parent-filter'),
                'matching' => true,
            ], $settings);
        }
        return $field;
    }

    public function builder_loop_query_args($args) {

        $settings = $args['settings'];

        if ( ! empty($settings->posts_parent_matching)) {
            $arg = $settings->posts_parent_matching ? 'post_parent__in' : 'post_parent__not_in';
            $parents = explode(',', $settings->posts_parent);
            $args[$arg] = $parents;
        }

        return $args;

    }

}
