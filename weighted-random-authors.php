<?php
/**
 Plugin Name: Weighted Random Authors
 Plugin URI: http://www.cmurrayconsulting.com/software/wordpress-weighted-random-authors/
 Description: A widget that selects a limited list of <strong>random authors, weighted towards authors with more posts</strong>. Configure numer of authors, title, display of post count, and author gravatar display.  
 Version: 1.1.1
 Author: Jacob M Goldman (C. Murray Consulting)
 Author URI: http://www.cmurrayconsulting.com

    Plugin: Copyright 2009 C. Murray Consulting  (email : jake@cmurrayconsulting.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class WeightedAuthorWidget extends WP_Widget
{
	function WeightedAuthorWidget() {
		$widget_ops = array('classname' => 'widget_weighted_authors', 'description' => __( "Displays authors in random order, weighted by number of posts.") );
		$this->WP_Widget('weightedauthors', __('Weighted Random Authors'), $widget_ops);
	}

    function widget($args, $instance) {
		extract($args);
      
		$title = apply_filters('widget_title', empty($instance['title']) ? 'Random Authors' : $instance['title']);
		$limit = (is_numeric($instance['limit']) && $instance['limit'] >= 0) ? intval($instance['limit']) : 5;
		$expand = (!isset($instance['expand'])) ? false : $instance['expand'];
		
		echo $before_widget;		
		echo $before_title.$title.$after_title;
		echo '<ul id="weighted_random_authors_list">';

      	//the magic
      	global $wpdb;
      	$sqllimit = ($instance['limit'] > 0 && !$expand) ? " LIMIT ".$limit : '';
      	$authors = $wpdb->get_results("SELECT post_author, (SELECT count(*) FROM $wpdb->posts wpp WHERE post_type = 'post' AND $wpdb->posts.post_author = wpp.post_author AND ".get_private_posts_cap_sql('post').") as weight FROM $wpdb->posts WHERE post_type = 'post' AND ".get_private_posts_cap_sql('post')." GROUP BY post_author ORDER BY weight*rand() DESC$sqllimit");
      	
      	$i = 0;
      	
      	foreach($authors as $author) {      		
      		echo '<li';
      		if ($expand && $limit > 0 && $i >= $limit) echo ' class="hidden_author" style="display: none;"';
      		echo '>';
      		
      		$author_info = get_userdata($author->post_author);
			$name = ($author_info->first_name && $author_info->last_name) ? $author_info->first_name.' '.$author_info->last_name : $author_info->display_name;
			
			if (isset($instance['gravatar']) && $instance['gravatar']) echo get_avatar($author_info->user_email,"16")." ";
      		echo '<a href="'.get_author_posts_url($author_info->ID).'" title="'.esc_attr(sprintf(__("Posts by %s"), $author_info->display_name)).'">'.$name.'</a>';
      		if (isset($instance['show_count']) && $instance['show_count']) echo " (".$author->weight.")"; 
      		
      		echo "</li>\n";
      		
      		$i++;
      	}
      	
      	if ($expand && $limit > 0 && $i > $limit) {
      		echo '<li class="expand"><a href="#" id="weighted_random_authors_expand_link">show all authors</a></li>';
  		}
      	      	
		echo "</ul>".$after_widget;
	}

	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['limit'] = intval($new_instance['limit']);
		$instance['expand'] = $new_instance['expand'];
		$instance['show_count'] = $new_instance['show_count'];
		$instance['gravatar'] = $new_instance['gravatar'];
		return $instance;
	}

	function form($instance){
		$instance = wp_parse_args((array) $instance, array('title' => 'Random Authors', 'limit' => 5, 'expand' => false, 'show_count' => false, 'gravatar' => false)); //defaults
		
		$title = htmlspecialchars($instance['title']);
		$limit = (is_numeric($instance['limit']) && intval($instance['limit']) >= 0) ? intval($instance['limit']) : 5;
		$expand_checked = ($instance['expand']) ? 'checked="checked" ' : '';
		$show_count_checked = ($instance['show_count']) ? 'checked="checked" ' : '';
		$gravatar_checked = ($instance['gravatar']) ? 'checked="checked" ' : '';
				
		echo '<p><label for="'.$this->get_field_name('title').'">'.__('Title:').' <input type="text" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" value="'.$title.'" /></label></p>';
		echo '<p><label for="'.$this->get_field_name('limit').'">'.__('Limit Number:').' <input type="text" id="'.$this->get_field_id('limit').'" name="'.$this->get_field_name('limit').'" value="'.$limit.'" size="3" /> <small>0 = all</small></label></p>';
		echo '<p><label for="'.$this->get_field_name('expand').'">'.__('Expand:').' <input type="checkbox" id="'.$this->get_field_id('expand').'" name="'.$this->get_field_name('expand').'" '.$expand_checked.'/> <small>link to show all authors</small></label></p>';
		echo '<p><label for="'.$this->get_field_name('show_count').'">'.__('Show Count:').' <input type="checkbox" id="'.$this->get_field_id('show_count').'" name="'.$this->get_field_name('show_count').'" '.$show_count_checked.'/></label></p>';
		echo '<p><label for="'.$this->get_field_name('gravatar').'">'.__('Show Avatar:').' <input type="checkbox" id="'.$this->get_field_id('gravatar').'" name="'.$this->get_field_name('gravatar').'" '.$gravatar_checked.'/></label></p>';
	}

}

function WeightedAuthorInit() { register_widget('WeightedAuthorWidget'); }
add_action('widgets_init', 'WeightedAuthorInit');

function WeightedAuthorHead() {
	if (is_active_widget(false,false,'weightedauthors')) {
		$path = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
	 	wp_enqueue_script('weighted_random_authors_script',$path.'expand.js',array('jquery'),'1.1',true);
	}
}
add_action('wp_head','WeightedAuthorHead',0);
?>