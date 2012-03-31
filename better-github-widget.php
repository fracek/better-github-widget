<?php
/*
Plugin Name: Better GitHub Widget
Plugin URI: http://github.com/fracek/better-github-widget
Description: Display your GitHub projects
Author: Francesco Ceccon
Version: 0.5
Author URI: http://francesco-cek.com
 */

/**
 * Adds Foo_Widget widget.
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
        $widget_ops = array('classname' => 'better-gh-widget', 'description' => __('Display your GitHub projects'));
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
        $title = 'GitHub';

        echo $before_widget;
        echo $before_title . $title . $after_title;

        // Octocat image
        echo '<img width="128px" src="' . plugins_url('octocat.png', __FILE__) . '"';
       echo ' style="display: block; margin: 0px auto;" />';

        // username @ GitHub
        echo '<p style="text-align: center; ">';
        echo '<a href="http://github.com/' . $username . '/" />';
        echo $username . '</a> @ GitHub</p>';

        // the list of repos
        echo '<ul id="gh-repos">';
        echo '<li id="gh-loading">Status updating...</li>';
        echo '</ul>';
        echo '<script src="' . plugins_url('github.js', __FILE__) . '" type="text/javascript"> </script>';
?>
<script type="text/javascript">
        github.showRepos({
            user: '<?php echo $username; ?>',
            count: <?php echo $count; ?>,
            skip_forks: true,
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
        $instance = wp_parse_args( (array) $instance, array( 'username' => '', 'count' => ''));
        $username = strip_tags($instance['username']);
        $count = strip_tags($instance['count']);
        
        echo '<p><label for="'. $this->get_field_id('username') . '">' . __('Username') . ':';
        echo '<input class="widefat" id="' . $this->get_field_id('username') . '" ';
        echo 'name="' . $this->get_field_name('username') . '" type="text" ';
        echo 'value="' . attribute_escape($username) . '" />';
        echo '</label></p>';

        echo '<p><label for="' . $this->get_field_id('count') . '">' . __('Number of projects to show') . ':';
        echo '<input class="widefat" id="' . $this->get_field_id('count') . '" ';
        echo 'name="' . $this->get_field_name('count') . '" type="number" ';
        echo 'value="' . attribute_escape($count) . '" />';
        echo '<br><small>' . __('Set to 0 to display all your projects</small>');
        echo '</label></p>';
    }

} // class Foo_Widget
add_action( 'widgets_init', create_function( '', 'register_widget( "better_github_widget" );' ) );
?>
