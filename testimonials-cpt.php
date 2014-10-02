<?php
/**
 * Plugin Name: Genesis Testimonials CPT
 * Plugin URI: https://llamapress.com
 * Description: Use this plugin to add a Testimonial CPT to be used with the "testimonials" sortcode or a LlamaPress testimonial page template,
 *              this plugin can only be used with the Genesis framework.
 * Version: 1.0
 * Author: LlamaPress
 * Author URI: https://llamapress.com
 * License: GPL2
 */

/*  Copyright 2014  LlamaPress LTD  (email : info@llama-press.com)

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

//include plugins
include( plugin_dir_path( __FILE__ ) . 'inc/plugins/plugins.php');

/**
 * This class creates a custom post type lp-testimonials, this post type allows the user to create 
 * testimonial entries to display in the Testimonials page template.
 *
 * @since 1.0
 * @link https://llama-press.com
 */
class lpTestimonial {
    /**
    * Initiate functions and shortcode.
    *
    * @since 1.0
    * @link https://llama-press.com
    */
    public function __construct( ){
        
        /** Create testimonial custom post type */
        add_action( 'genesis_init', array( $this, 'testimonials_post_type' ) );
        
        /** Register department Taxonomy */
        add_action( 'genesis_init', array( $this, 'create_testimonials_tax' ) );
        
        /* Add testimonial_meta_boxes */
        add_action( 'do_meta_boxes', array( $this, 'testimonial_meta_boxes' ) );
        
        /* initiate save_testimonial_data function  */
        add_action('save_post', array( $this, 'save_testimonial_data' ) );
        
        /* Remove permalink section from testimonials edit post screen  */
        add_action('admin_print_styles-post.php', array( $this, 'posttype_admin_css' ) );
        
        /* add shortcode  */
        add_shortcode('testimonials', array( $this, 'testimonial_shortcode' ) ); 
        
        /* enque styles for shortcode */
        wp_enqueue_style( 'testimonials', plugins_url('genesis-testimonials-cpt/style.css') ); 
        
        /* create text domain */
        load_plugin_textdomain( 'lp', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
        
        /* Creates testimonial featured image size */
        add_image_size( 'lp-testimonial', 100, 100, TRUE );

    }

    /**
    * Creates lp-testimonials custom post type.
    * 
    * @since 1.0
    * @link https://llama-press.com
    */
    public function testimonials_post_type() {
        register_post_type( 'lp-testimonials',
            array(
                'labels' => array(
                    'name' => __( 'Testimonials', 'lp' ),
                    'singular_name' => __( 'Testimonial', 'lp' ),
                    'all_items' => __( 'All Testimonials', 'lp' ),
                    'add_new' => _x( 'Add new Testimonial', 'Testimonial', 'lp' ),
                    'add_new_item' => __( 'Add new Testimonial', 'lp' ),
                    'edit_item' => __( 'Edit Testimonial', 'lp' ),
                    'new_item' => __( 'New Testimonial', 'lp' ),
                    'view_item' => __( 'View Testimonial', 'lp' ),
                    'search_items' => __( 'Search Testimonials', 'lp' ),
                    'not_found' =>  __( 'No Testimonials found', 'lp' ),
                    'not_found_in_trash' => __( 'No Testimonials found in trash', 'lp' ), 
                    'parent_item_colon' => ''
                ),
                'exclude_from_search' => false,
                'has_archive' => false,
                'hierarchical' => true,
                'taxonomies'   => array( 'lp-sector' ),
                'public' => true,
                'menu_icon' => 'dashicons-smiley',
                'rewrite' => array( 'slug' => 'testimonials' ),
                'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
                'query_var'           => false,
            )
        );
    }

    /**
    * Create sectors taxanomy for lp-testimonials custom post type.
    * 
    * @see testimonials_post_type()
    * @since 1.0
    * @link https://llama-press.com
    */
    public function create_testimonials_tax() {
        register_taxonomy(
            'lp-sector',
            'lp-testimonials',
            array(
                'label' => __( 'Sectors' ),
                'hierarchical' => false,
                'show_admin_column' => true,
                'labels' => array('name' => _x( 'Sectors', 'Sectors', 'lp' ),
                                  'singular_name' => _x( 'Sector', 'Sector', 'lp' ),
                                  'search_items' => __( 'Search Sectors', 'lp' ),
                                  'popular_items'              => __( 'Popular Sectors', 'lp' ),
                                  'all_items'                  => __( 'All Sectors', 'lp' ),
                                  'parent_item'                => null,
                                  'parent_item_colon'          => null,
                                  'edit_item'                  => __( 'Edit Sector', 'lp' ),
                                  'update_item'                => __( 'Update Sector', 'lp' ),
                                  'add_new_item'               => __( 'Add New Sector', 'lp' ),
                                  'new_item_name'              => __( 'New Sector', 'lp' ),
                                  'separate_items_with_commas' => __( 'Separate sectors with commas', 'lp' ),
                                  'add_or_remove_items'        => __( 'Add or remove sectors' ),
                                  'choose_from_most_used'      => __( 'Choose from the most common sectors', 'lp' ),
                                  'not_found'                  => __( 'No sectors found.', 'lp' ),
                                  'menu_name'                  => __( 'Sectors', 'lp' ),)
            )
        );
    }
    
    /**
    * Registeres the custom meta box.
    * 
    * This custom meta box is for the testimonials company and position
    *
    * @see testimonial_metabox()
    * @since 1.0
    * @link https://llama-press.com
    */
    public function testimonial_meta_boxes() {
        add_meta_box(   'testimonial-details', __( 'Testimonial details', 'lp' ),  array( $this, 'testimonial_metabox' ), 'lp-testimonials', 'normal', 'high' );
    }
    
    /**
    * Cteahtes custom meta box HTML.
    *
    * @since 1.0
    * @link https://llama-press.com
    * @param array $post
    * @return mixed HTML.
    */
    public function testimonial_metabox($post) {
        // get the custom meta values
        $details = get_post_meta($post->ID, 'testimonial-details');

        //crete HTML and add custom values if they are set
        ?>
            <input type="hidden" name="testimonial_noncename" id="testimonial_noncename" value="<?php echo wp_create_nonce( 'testimonial'.$post->ID );?>" />

            <label for="testimonial-details[]" class="row-title"><?php _e( 'Testimonial company', 'lp' )?></label><br/>
            <input name="testimonial-details[]" type="text" value="<?php  echo $details[0][0]; ?>" /><br/>

            <label for="testimonial-details[]" class="row-title"><?php _e( 'Testimonial position', 'lp' )?></label><br/>
            <input name="testimonial-details[]" type="text" value="<?php  echo $details[0][1]; ?>" /><br/>

     <?php
    }

    /**
    * Saves the data from the custom meta box when the post is updated.
    * 
    * @see testimonials_post_type()
    * @since 1.0
    * @link https://llama-press.com
    * @param int $post_id Id of the post.
    * @return mixed
    */
    public function save_testimonial_data($post_id) {  
        // verify this came from the our screen and with proper authorization.
        if ( !wp_verify_nonce( $_POST['testimonial_noncename'], 'testimonial'.$post_id )) {
            return $post_id;
        }

        // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
            return $post_id;

        // Check permissions
        if ( !current_user_can( 'edit_post', $post_id ) )
            return $post_id;

        // We're authenticated: we need to find and save the data   
        $post = get_post($post_id);
        if ($post->post_type == 'lp-testimonials') { 
            if(isset($_POST['testimonial-details'])){
                $custom = $_POST['testimonial-details'];
                $old_meta = get_post_meta($post->ID, 'testimonial-details', true);
                // Update post meta
                if(!empty($old_meta)){
                    update_post_meta($post->ID, 'testimonial-details', $custom);
                } else {
                    add_post_meta($post->ID, 'testimonial-details', $custom, true);
                }
            }

        }   

        return $post_id;
    }
    
    /**
    * Remove permalink.
    * 
    * We dont need to display the permalink or the view post link on the edit screen so this function removes it.
    * 
    * @since 1.0
    * @link https://llama-press.com
    */
    public function posttype_admin_css() {
        global $post_type;
        if($post_type == 'lp-testimonials') {
            echo '<style type="text/css">#edit-slug-box, #view-post-btn, #post-preview, .updated #edit-slug-box, .preview{ display: none !important; }</style>';
        }
    }
     
    /**
    * Creates shortcode to display testimonials on any post or page.
    * 
    * @since 1.0
    * @link https://llama-press.com
    */
    public function testimonial_shortcode( $atts ) {
        
            $atts = shortcode_atts( array(
              'amount' => '',
              'orderby' => '',
              'order' => ''
            ), $atts );
            $amount = $atts['amount'];
            $orderby = $atts['orderby'];
            if( $orderby == "" ) $orderby = 'post_date';
            $order = $atts['order'];
            if( $order == "" ) $order = 'DESC';
            
            if( $amount != '' ){
                $args = array(
                    'post_type' => 'lp-testimonials',
                    'orderby'       => $orderby,
                    'order'         => $order,
                    'posts_per_page' => $amount
                );
            }
            else{
                $args = array(
                    'post_type' => 'lp-testimonials',
                    'orderby'       => $orderby,
                    'order'         => $order,
                );
            }

        $loop = new WP_Query( $args );
        if( $loop->have_posts() ){
            //loop through testimonial items
            while( $loop->have_posts() ): $loop->the_post();
                $details = get_post_meta( get_the_id(), 'testimonial-details' );
                $sector = wp_get_post_terms( get_the_id(), 'lp-sector' );
                $content .= "<div class='lp-testimonial'>";
                    $content .= "<div class='one-fourth first col lp-profile_pic'>";
                        if( has_post_thumbnail( ) ){
                            $content .= get_the_post_thumbnail( get_the_id(), 'lp-testimonial' );
                        }
                        else{
                            $content .= "<img src='" . plugins_url( 'img/thesheen.jpg' , __FILE__ ) . "' alt='sheen' />";
                        }
                        if( $sector ){
                            $content .= "<div class='sectors'>";
                            foreach ($sector as $sector){
                                $content .= $sector->name . '<br/>';
                            }
                            $content .= "</div>";
                        }
                    $content .= "</div>";
                    $content .= "<div class='three-fourths col'>";
                        $content .= "<h3>" . get_the_title() . "</h3>";
                        if($details[0][0]) $content .= "<strong>" . $details[0][0] . "</strong>";
                        if(!$details[0][1]) $content .= "<br/>";
                        if($details[0][0] && $details[0][1]) $content .= "<span> - </span>";
                        if($details[0][1]) $content .= "<strong>" . $details[0][1] . "</strong><br/>";
                        if( get_the_content() ){
                            $content .= apply_filters( 'the_content', get_the_content() );
                        }
                    $content .= "</div>";
                    $content .= "<div class='clearfix'></div>";
                $content .= "</div>";
            endwhile;
        }
        if($content)
        return $content;
    }
}

$testimonials = new lpTestimonial();

?>