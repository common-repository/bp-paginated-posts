<?php
/*
Plugin Name: BP Paginated Posts
Description: BuddyPress paginated site wide posts widget
Author: Stephanie Wells
Author URI: http://blog.strategy11.com 
Plugin URI: http://blog.strategy11.com/buddypress-site-wide-paginated-posts-plugin/ 
Version: 2.3
*/

/* Register widgets for blogs component */
function register_page_bpposts_widget() {
	add_action('widgets_init', create_function('', 'return register_widget("BP_Paginated_Posts_Widget");') );
}
add_action( 'plugins_loaded', 'register_page_bpposts_widget' );

class BP_Paginated_Posts_Widget extends WP_Widget {
	function bp_paginated_posts_widget() {
		parent::WP_Widget( false, $name = __( 'Paginated Site Wide Posts', 'buddypress' ) );
	}

	function widget($args, $instance) {
		global $bp;
		
	    extract( $args );
		
		echo $before_widget;
		
		if (!empty($instance['title']))
		    echo $before_title . $instance['title'] . $after_title; 
		
		$instance['next_link'] = ( empty( $instance['next_link'] ) || !$instance['next_link'] ) ? '&raquo;' : $instance['next_link'];	
		$instance['prev_link'] = ( empty( $instance['prev_link'] ) || !$instance['prev_link'] ) ? '&laquo;' : $instance['prev_link'];
        $instance['max_posts'] = is_numeric( $instance['max_posts'] ) ? $instance['max_posts'] : 10;
		$instance['total_posts'] = is_numeric( $instance['total_posts'] ) ? $instance['total_posts'] : 200;	
		$instance['featured_posts'] = is_numeric( $instance['featured_posts'] ) ? $instance['featured_posts'] : 1;
		$instance['untitled_post'] = ( empty( $instance['untitled_post'] ) || !$instance['untitled_post'] ) ? 'Untitled' : $instance['untitled_post'];	
			
        $page = ( ( !empty( $_GET['swpage'] ) && intval($_GET['swpage']) > 0 ) ? intval($_GET['swpage']) : 1 );

        $displaylimit = ($page * $instance['max_posts']);
        $display = (($page-1) * $instance['max_posts']);
        
        $posts = bp_blogs_get_latest_posts( null, $instance['total_posts'] );
		$page_count = ceil(count($posts) / $instance['max_posts']);    
		$last_post = $display + $instance['max_posts'];
		$page_links = '';
		
		if (count($posts) > 0){
		
        $nextitems = false;
        if ($page < $page_count)
            $nextitems = true;
            
        $previousitems = false;
        if ( $page > 1 )
        	$previousitems = true; 
		?>
		
        <div class="pagination">
            <div class="pag-count">
				Viewing post <?php echo $display+1 ?> to <?php echo ($last_post < count($posts)) ? $last_post : count($posts) ?> (of <?php echo count($posts) ?> posts) &nbsp;
            </div>
            <div class="pagination-links"> &nbsp;
            <?php   
                if ($page_count > 1){    
                    if ($previousitems)
                    	$page_links .= '<a href="' . add_query_arg( 'swpage', ($page-1) ) . '" class="prev page-numbers">' . $instance['prev_link'] . '</a>';

                    
                    // First page is always displayed
                     if($page==1)
                         $page_links .= "<span class='page-numbers current'>1</span>";
                     else
                         $page_links .= ' <a href="' . add_query_arg( 'swpage', (1) ) . '" class="page-numbers">1</a>';

                     // If the current page is more than 1 space away from the first page then we put some dots in here
                     if($page >= 4 && $page_count > 8)
                        $page_links .= "<span class='page-numbers dots'>...</span>";

                     // display the current page icon and 1 page beneath and above it
                     $low_page = (($page >= 4 && $page_count > 8) ?($page-1):2);
                     $high_page = ((($page + 1) < ($page_count-1) && $page_count > 8)?($page+1):($page_count-1));
                     for($i = $low_page; $i <= $high_page; $i++){
                         if($page==$i)
                             $page_links .= "<span class='page-numbers current'>$i</span>";
                         else
                             $page_links .= ' <a href="' . add_query_arg( 'swpage', ($i) ) . '" class="page-numbers">' . $i .'</a> ';
                     }

                     // If the current page is more than 1 away from the last page then show ellipsis
                     if($page < ($page_count - 2) && $page_count > 8)
                         $page_links .= "<span class='page-numbers dots'>...</span>";

                     // Display the last page icon
                     if($page == $page_count)
                         $page_links .= "<span class='page-numbers current'>$page_count</span>";
                     else
                         $page_links .= ' <a href="' . add_query_arg( 'swpage', ($page_count) ) . '" class="page-numbers">' . $page_count . '</a>';


                    if ($nextitems)
                    	$page_links .= '<a href="' . add_query_arg( 'swpage', ($page+1) ) . '" class="next page-numbers">' . $instance['next_link'] .'</a>';
                }
                echo $page_links;
                
            ?>
            </div>
        </div>

        <ul id="recent-posts" class="item-list">
    	<?php
        while($display < $displaylimit) {
        	if ( array_key_exists( $display, $posts ) ) { ?>
    			<?php $post = $posts[$display]; ?>
				<li class="post">
					<div class="item-avatar">
						<a href="<?php echo $permalink = bp_post_get_permalink( $post, $post->blog_id ) ?>" title="<?php echo apply_filters( 'the_title', $post->post_title ) ?>"><?php echo bp_core_fetch_avatar( array( 'item_id' => $post->post_author, 'type' => 'thumb' ) ) ?></a>
					</div>

					<div class="item">
						<h4 class="item-title"><a href="<?php echo $permalink ?>" title="<?php echo apply_filters( 'the_title', $post->post_title ) ?>"><?php $title = apply_filters( 'the_title', $post->post_title ); echo ($title == '')?($instance['untitled_post']):($title); ?></a></h4>
						
						<div class="item-meta"><p class="date"><?php echo date(get_option('date_format'), strtotime(apply_filters( 'post_date', $post->post_date ))); ?> <em><?php printf( __( 'by %s from the blog <a href="%s">%s</a>', 'buddypress' ), bp_core_get_userlink( $post->post_author ), get_blog_option( $post->blog_id, 'siteurl' ), get_blog_option( $post->blog_id, 'blogname' ) ) ?></em></p></div>
						<?php if ( $instance['featured_posts'] > $display ) : ?>
							<div class="item-content"><?php echo bp_create_excerpt($post->post_content) ?></div>
						<?php endif; ?>

						<p class="postmetadata"<span class="comments"><a href="<?php echo $permalink ?>#comments"><?php echo apply_filters( 'the_title', $post->comment_count ) ?> Comments »</a></span></p>
					</div>
				</li>
<?php		} else
                $nextitems = FALSE;

        	$display++;
        }?>
        </ul>
        <div class="pagination">
            <div class="pagination-links"> &nbsp;
                <?php echo $page_links; ?>
            </div>
        </div>
<?php	}else echo '<p>No Posts to Display</p>';
        echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['max_posts'] = strip_tags( $new_instance['max_posts'] );
		$instance['total_posts'] = strip_tags( $new_instance['total_posts'] );
		$instance['featured_posts'] = strip_tags( $new_instance['featured_posts'] );
        $instance['next_link'] = $new_instance['next_link'];
        $instance['prev_link'] = $new_instance['prev_link'];
        $instance['untitled_post'] = $new_instance['untitled_post'];
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array('title' => 'Site Wide Posts', 'max_posts' => 10, 'total_posts' => 200, 'next_link' => '&raquo;', 'prev_link' => '&laquo;', 'featured_posts' => 1, 'untitled_post' => 'Untitled' ) );
		$max_posts = strip_tags( $instance['max_posts'] );
		$total_posts = strip_tags( $instance['total_posts'] );
		$featured_posts = strip_tags( $instance['featured_posts'] );
		?>

        <p><label><?php _e('Title:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo attribute_escape( $instance['title'] ); ?>" /></label></p>
        
		<p><label><?php _e('Posts per Page:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_posts' ); ?>" name="<?php echo $this->get_field_name( 'max_posts' ); ?>" type="text" value="<?php echo attribute_escape( $max_posts ); ?>" style="width: 30%" /></label></p>
		
		<p><label><?php _e('Total Posts:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'total_posts' ); ?>" name="<?php echo $this->get_field_name( 'total_posts' ); ?>" type="text" value="<?php echo attribute_escape( $total_posts ); ?>" style="width: 30%" /></label></p>
		
		<p><label><?php _e('Number of Posts with Excerpt:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'featured_posts' ); ?>" name="<?php echo $this->get_field_name( 'featured_posts' ); ?>" type="text" value="<?php echo attribute_escape( $featured_posts ); ?>" style="width: 30%" /></label>
		</p>
		
		<p><label><?php _e('Untitled Post Label:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'untitled_post' ); ?>" name="<?php echo $this->get_field_name( 'untitled_post' ); ?>" type="text" value="<?php echo attribute_escape( $instance['untitled_post'] ); ?>" /></label></p>
		
		<p><label><?php _e('Next Link:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'next_link' ); ?>" name="<?php echo $this->get_field_name( 'next_link' ); ?>" type="text" value="<?php echo attribute_escape( $instance['next_link'] ); ?>" /></label></p>
		
		<p><label><?php _e('Previous Link:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'prev_link' ); ?>" name="<?php echo $this->get_field_name( 'prev_link' ); ?>" type="text" value="<?php echo attribute_escape( $instance['prev_link'] ); ?>" /></label></p>
		
	<?php
	}
}

?>