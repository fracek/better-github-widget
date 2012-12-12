<?php
/*
Plugin Name: Better GitHub Widget
Plugin URI: https://wordpress.org/extend/plugins/better-github-widget/
Description: Display your GitHub projects
Author: Francesco Ceccon
Version: 0.5.2
Author URI: http://francesco-cek.com
 */

$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( 'bg-wgt', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**
 * A better Github widget that displays a list of your most recent
 * active Github projects
 */
class Better_GitHub_Widget extends WP_Widget {

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
        $widget_ops = array('classname' => 'better-gh-widget', 'description' => __('Display your GitHub projects','bg-wgt'));
        parent::__construct(
	 		'better-gh-widget', // Base ID
			'Better GitHub Widget', // Name
            $widget_ops
		);
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

        echo $before_widget;
        echo $before_title . $title . $after_title;

        // Octocat image
        echo '<img width="128px" alt="GitHub Octocat" src="' . plugins_url('octocat.png', __FILE__) . '"';
        echo ' style="display: block; margin: 0px auto;" />';

        // username @ GitHub
        echo '<p style="text-align: center; ">';
        echo '<a href="http://github.com/' . $username . '/" >';
        echo $username . '</a> @ GitHub</p>';

        // the list of repos
        echo '<ul id="gh-repos">';
        echo '<li id="gh-loading">' . __('Status updating...','bg-wgt') . '</li>';
        echo '</ul>';
        echo '<script src="' . plugins_url('github.js', __FILE__) . '" type="text/javascript"> </script>';
?>
<script type="text/javascript">
        github.showRepos({
            user: '<?php echo $username; ?>',
            count: <?php echo $count; ?>,
            skip_forks: <?php echo $skip_forks; ?>,
        });
  </script>
<?php
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
        $instance = wp_parse_args( (array) $instance, array( 'username' => '',
            'count' => '', 'title' => 'GitHub'));
        $username = strip_tags($instance['username']);
        $count = strip_tags($instance['count']);
        $title = strip_tags($instance['title']);
        $skip_forks = strip_tags($instance['skip_forks']);
        $checked = ( $skip_forks ) ? 'checked="checked"' : '';

        echo '<p><label for="'. $this->get_field_id('title') . '">' . __('Title','bg-wgt') . ':';
        echo '<input class="widefat" id="' . $this->get_field_id('title') . '" ';
        echo 'name="' . $this->get_field_name('title') . '" type="text" ';
        echo 'value="' . esc_attr($title) . '" title="' . __('Title of the widget as it appears on the page','bg-wgt') . '" />';
        echo '</label></p>';

        echo '<p><label for="'. $this->get_field_id('username') . '">' . __('Username','bg-wgt') . ':';
        echo '<input class="widefat" id="' . $this->get_field_id('username') . '" ';
        echo 'name="' . $this->get_field_name('username') . '" type="text" ';
        echo 'value="' . esc_attr($username) . '" title="' . __('Your Github username','bg-wgt') . '"/>';
        echo '</label></p>';

        echo '<p><label for="' . $this->get_field_id('count') . '">' . __('Number of projects to show','bg-wgt') . ':';
        echo '<input class="widefat" id="' . $this->get_field_id('count') . '" ';
        echo 'name="' . $this->get_field_name('count') . '" type="number" ';
        echo 'value="' . esc_attr($count) . '" title="0 for all." />';
        echo '<br><small>' . __('Set to 0 to display all your projects','bg-wgt') . '</small>';
        echo '</label></p>';

        echo '<p><label for="' . $this->get_field_id('skip_forks') . '">' .  __('Show Forked Repositories:','bg-wgt') . ' </label>';
        echo '<input type="checkbox" name="' . $this->get_field_name('skip_forks') . '" value="1" ' . $checked . '/>'; 
        echo '</p>';
    }

} // class Better_GitHub_Widget
add_action( 'widgets_init', create_function( '', 'register_widget( "better_github_widget" );' ) );
?>
