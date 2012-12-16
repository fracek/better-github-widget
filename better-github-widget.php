<?php
/*
Plugin Name: Better GitHub Widget
Plugin URI: https://wordpress.org/extend/plugins/better-github-widget/
Description: Display your GitHub projects
Author: Francesco Ceccon
Version: 0.6.1
Author URI: http://francesco-cek.com
 
Text Domain:   better-github-widget
Domain Path:   /languages/
 */

$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( 'better-github-widget', false,
    dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**
 * A better Github widget that displays a list of your most recent
 * active Github projects
 */
class Better_GitHub_Widget extends WP_Widget {

    private $sections = array(
        'Repositories',
        'Activity'
    );

    /**
     * PHP 4 constructor
     */
    function Better_GitHub_Widget() {
        Better_GitHub_Widget::__construct();
    }

    /**
     * PHP 5 constructor
     */
    function __construct() {
        $widget_ops = array('classname' => 'better-gh-widget',
            'description' => __('Display your GitHub projects','better-github-widget'));
        parent::__construct(
            'better-gh-widget', // Base ID
            'Better GitHub Widget', // Name
            $widget_ops
        );
        add_action('wp_ajax_bgw_update_order', array(&$this, 'update_order')); 
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        extract($args);
        $username = $instance['username'];
        $count = $instance['count'];
        $title = $instance['title'];
        $skip_forks = ($instance['skip_forks']) ? 'false' : 'true';
        $show_octocat = $instance['show_octocat'];

        echo $before_widget;
        echo $before_title . $title . $after_title;

        // Octocat image
        if ($show_octocat) {
            echo '<img width="128px" alt="GitHub Octocat" src="' . plugins_url('octocat.png', __FILE__) . '"';
            echo ' style="display: block; margin: 0px auto;" />';
        }

        // username @ GitHub
        echo '<p style="text-align: center; ">';
        echo '<a href="http://github.com/' . $username . '/" >';
        echo $username . '</a> @ GitHub</p>';

        $this->display_repos($username, $count, $skip_forks);
        echo $after_widget;
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['username'] = strip_tags($new_instance['username']);
        $instance['count'] = strip_tags($new_instance['count']);
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['skip_forks'] = strip_tags($new_instance['skip_forks']);
        $instance['show_octocat'] = strip_tags($new_instance['show_octocat']);
        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        //  Assigns values
        $defaults = array(
            'username' => '',
            'count' => '0',
            'title' => 'GitHub',
            'skip_forks' => 'false',
            'show_octocat' => 'true',
            'sections_order' => $this->sections
        );
        $instance = wp_parse_args( (array) $instance, $defaults);
        $username = strip_tags($instance['username']);
        $count = strip_tags($instance['count']);
        $title = strip_tags($instance['title']);
        $skip_forks = strip_tags($instance['skip_forks']);
        $skip_forks_checked = ($skip_forks) ? 'checked="checked"' : '';
        $show_octocat = strip_tags($instance['show_octocat']);
        $show_octocat_checked = ($show_octocat) ? 'checked="checked"' : '';
        $sections = $instance['sections_order'];

        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('section-order', plugins_url('js/section-order.js', __FILE__));
        wp_localize_script('section-order', 'SectionOrder', array(
            'nonce' => wp_create_nonce('section-order-nonce')
        ));
        wp_enqueue_style('section-order-style', plugins_url('css/section-order.css', __FILE__));

        // Title
        echo '<p><label for="'. $this->get_field_id('title') . '">' .
            __('Title','better-github-widget') . ':';
        echo '<input class="widefat" id="' . $this->get_field_id('title') . '" ';
        echo 'name="' . $this->get_field_name('title') . '" type="text" ';
        echo 'value="' . esc_attr($title) . '" title="' .
            __('Title of the widget as it appears on the page','better-github-widget') . '" />';
        echo '</label></p>';

        // Username
        echo '<p><label for="'. $this->get_field_id('username') . '">' .
            __('Username','better-github-widget') . ':';
        echo '<input class="widefat" id="' . $this->get_field_id('username') . '" ';
        echo 'name="' . $this->get_field_name('username') . '" type="text" ';
        echo 'value="' . esc_attr($username) . '" title="' .
            __('Your Github username','better-github-widget') . '"/>';
        echo '</label></p>';

        // Repo Count
        echo '<p><label for="' . $this->get_field_id('count') . '">' .
            __('Number of projects to show','better-github-widget') . ':';
        echo '<input class="widefat" id="' . $this->get_field_id('count') . '" ';
        echo 'name="' . $this->get_field_name('count') . '" type="number" ';
        echo 'value="' . esc_attr($count) . '" title="0 for all." />';
        echo '<br><small>' . __('Set to 0 to display all your projects','better-github-widget') . '</small>';
        echo '</label></p>';

        // Skip Forks
        echo '<p><label for="' . $this->get_field_id('skip_forks') . '">' .
            __('Show Forked Repositories:','better-github-widget') . ' </label>';
        echo '<input type="checkbox" name="' . $this->get_field_name('skip_forks') .
            '" value="1" ' . $skip_forks_checked . '/>'; 
        echo '</p>';

        // Show Octocat
        echo '<p><label for="' . $this->get_field_id('show_octocat') . '">' .
            __('Show Octocat:','better-github-widget') . ' </label>';
        echo '<input type="checkbox" name="' . $this->get_field_name('show_octocat') .
            '" value="1" ' . $show_octocat_checked . '/>'; 
        echo '</p>';

        echo '<p><label for="' . '">' . __('Diplay order:','better-github-widget') .
            '</label>';
        echo '<div class="bgw-sections-order"><table class="wp-list-table widefat">';
        echo '<thead><tr>';
        echo '<th>Section</th><th>Display</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        foreach($sections as $k => $section) {
            echo '<tr id="section_'.$k.'" class="list_item"><td>'.$section.'</td><td>' .
                '<input type="checkbox" checked="checked"></input>' .
                '</td></tr>';
        }
        echo '</tbody>';
        echo '</table></div>';
    }

    /**
     * Outputs the html content of the repository list
     *
     * @param string $username the user username
     * @param string $count how many repositories to show
     * @param string $skip_forks don't show forks?
     */
    private function display_repos($username, $count, $skip_forks) {
        // the list of repos
        echo '<ul id="gh-repos">';
        echo '<li id="gh-loading">' . __('Status updating...','better-github-widget') . '</li>';
        echo '</ul>';
        echo '<script src="' . plugins_url('js/github.js', __FILE__) . '" type="text/javascript"> </script>';
?>
<script type="text/javascript">
        github.showRepos({
            user: '<?php echo $username; ?>',
            count: <?php echo $count; ?>,
            skip_forks: <?php echo $skip_forks; ?>,
        });
  </script>
<?php
    }

    public function update_order() {
        if (!is_admin()) die('Not an admin');
        if (!isset($_REQUEST['nonce']) ||
            !wp_verify_nonce($_REQUEST['nonce'], 'section-order-nonce')) 
            die('Invalid Nonce');
        $sections = $this->sections;
        $new_order = $_POST['section'];
        $new_sections = array();

        foreach($new_order as $v) {
            if (isset($sections[$v])) {
                $new_sections[$v] = $sections[$v];
            }
        }
        // FIXME: save the new order
        die();
    }

} // class Better_GitHub_Widget
add_action( 'widgets_init', create_function( '', 'register_widget( "better_github_widget" );' ) );
?>
