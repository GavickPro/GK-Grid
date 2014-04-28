<?php

/*
Plugin Name: GK Grid
Plugin URI: http://www.gavick.com/
Description: Widget for the grid interface display
Version: 1.0
Author: GavickPro
Author URI: http://www.gavick.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*

Copyright 2013-2013 GavickPro (info@gavick.com)

this program is free software; you can redistribute it and/or modify
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

/*

Available actions:

Available filters:


*/

if ( !defined( 'WPINC' ) ) {
    die;
}

/**
 * i18n
 */
load_plugin_textdomain( 'gk-grid', false, dirname( dirname( plugin_basename( __FILE__) ) ).'/languages' );

/**
 * Register the GK Tabs Widget.
 *
 * Hooks into the widgets_init action.
 */
function gk_grid_register() {
	register_widget( 'GK_Grid_Widget' );
}

add_action( 'widgets_init', 'gk_grid_register' );
add_action( 'wp_loaded', array('GK_Grid_Widget', 'register_sidebars'));

class GK_Grid_Widget extends WP_Widget {
	
	private $config = array(
								'title' 				=> '',
								'selected_sidebar' 		=> '',
								'grid_manager'			=> '',
								'grid_margin'			=> '0',
								'grid_border'			=> 'none',
								'tablet_width'			=> '840',
								'mobile_width'			=> '600',
								'animation'				=> 'on',
								'anim_speed' 			=> 'fast',
								'anim_random'			=> 'on',
								'anim_type'	 			=> 'opacity',
								'amount_of_sidebars' 	=> '3',
								'style'					=> 'default-style',
								'cache_time'			=> 60
							);

	/**
	 *
	 * Constructor
	 *
	 * @return void
	 *
	 **/
	function __construct() {
		$this->WP_Widget(
			'gk_grid', 
			__( 'GK Grid', 'gk-grid' ), 
			array( 
				'classname' => 'widget_gk_grid', 
				'description' => __( 'Use this widget to show tabs created form the selected sidebar', 'gk-grid') 
			),
			array(
				'width' => 480, 
				'height' => 350
			)
		);
		
		$this->alt_option_name = 'gk_grid';
		//
		add_action('wp_enqueue_scripts', array($this, 'add_js'));
		add_action('wp_enqueue_scripts', array($this, 'add_css'));
		add_action('admin_enqueue_scripts', array($this, 'add_admin_js'));
		add_action('admin_enqueue_scripts', array($this, 'add_admin_css'));
		/**
 		 * install & uninstall
 		 */
		register_activation_hook( __FILE__, array( $this, 'install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'uninstall' ) );
	}
	
	/**
	 *
	 *
	 *
	 */
	function install() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
	}

	/**
	 *
	 *
	 *
	 */
	function uninstall() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		// remove the config option
		delete_option( 'widget_gk_grid' );
	}

	function add_js() {
		wp_register_script( 'gk-grid', plugins_url('gk-grid.js', __FILE__), array('jquery'), false, true);
		wp_enqueue_script('gk-grid');
	}

	function add_css() {
		wp_register_style( 'gk-grid', plugins_url('gk-grid.css', __FILE__), array(), false, 'all');
		wp_enqueue_style('gk-grid');

		$instances = get_option('widget_gk_grid');

		if(is_array($instances) || is_object($instances)) {
			foreach($instances as $id => $widget) {
				if(is_array($widget)) {
					wp_add_inline_style( 'gk-grid' , GK_Grid_Widget::additional_css($id, $widget) );
				}
			}	
		}
	}

	function add_admin_js() {
		wp_register_script( 'gk-jquery-spinner', plugins_url('jquery.spinner.js', __FILE__), array('jquery'), false, false);
		wp_enqueue_script('gk-jquery-spinner');
		wp_register_script( 'gk-jquery-sortable', plugins_url('jquery.sortable.js', __FILE__), array('jquery'), false, false);
		wp_enqueue_script('gk-jquery-sortable');
		wp_register_script( 'gk-grid', plugins_url('gk-grid-admin.js', __FILE__), array('jquery', 'gk-jquery-spinner'), false, false);
		wp_enqueue_script('gk-grid');
		$translations_array = array( 
			'GRID_NO_BLOCKS' => __( 'No blocks', 'gk-grid' ), 
			'LIST_SIZE' => __( 'Size: ', 'gk-grid' ),
			'LIST_POSITION' => __( 'Name: ', 'gk-grid' ),
			'LIST_DESKTOP_SIZE' => __( 'Desktop size: ', 'gk-grid' ),
			'LIST_TABLET_SIZE' => __( 'Tablet size: ', 'gk-grid' ),
			'LIST_MOBILE_SIZE' => __( 'Mobile size: ', 'gk-grid' ),
			'LIST_CANCEL' => __( 'Cancel', 'gk-grid' ),
			'LIST_ADD_BLOCK' => __( 'Add block', 'gk-grid' ),
			'LIST_SAVE_BLOCK' => __( 'Save block', 'gk-grid' ),
			'LIST_ERROR_DESKTOP' => __( 'The desktop width of block element cannot be bigger than 6', 'gk-grid' ),
			'LIST_ERROR_TABLET' => __( 'The tablet width of block element cannot be bigger than 4', 'gk-grid' ),
			'LIST_ERROR_MOBILE' => __( 'The mobile width of block element cannot be bigger than 2', 'gk-grid' )
		);
		wp_localize_script( 'gk-grid', 'GKGridManagerLang', $translations_array );
	}

	function add_admin_css() {
		wp_register_style( 'font-awesome', plugins_url('font-awesome/font-awesome.min.css', __FILE__), array(), false, 'all');
		wp_enqueue_style('font-awesome');
		wp_register_style('gk-grid', plugins_url('gk-grid-admin.css', __FILE__), array(), false, 'all');
		wp_enqueue_style('gk-grid');
	}

	static function register_sidebars() {
		$option = get_option('widget_gk_grid');
		$amount_of_sidebars = 0;
		if(is_array($option) && count($option) > 0) {
			foreach($option as $grid_widget_instance) {
				if($grid_widget_instance['amount_of_sidebars'] > $amount_of_sidebars) {
					$amount_of_sidebars = $grid_widget_instance['amount_of_sidebars'];
				}
			}
		} else {
			$amount_of_sidebars = 3;
		}
		// use the value for generating new sidebars
		for($i = 1; $i <= $amount_of_sidebars; $i++) {
			register_sidebar(
				array(
					'name'          => 'GK Grid ' . GK_Grid_Widget::roman_number($i),
					'id'            => 'gk-grid-sidebar-' . $i,
					'description'   => '',
			        'class'         => '',
					'before_widget' => '',
					'after_widget'  => '',
					'before_title'  => '',
					'after_title'   => ''
				)
			);
		}
	}


	// function to generate the module CSS code
	static function additional_css($id, $config) {
		// prepare the helper variables
		$prefix = '#gk-grid-gk_grid-'.str_replace('_multiwidget', '', $id).' .gk-grid-element.gk-grid-';
		$grid_data = json_decode(str_replace('&quot;', '"', $config['grid_manager']));
		$output_desktop = '';
		$output_tablet = '';
		$output_mobile = '';
		// get the grid settings
		if (isset($grid_data->blocks)) {
			$block_data = $grid_data->blocks;
			$mod_height_desktop = $grid_data->heights->desktop;
			$mod_height_tablet = $grid_data->heights->tablet;
			$mod_height_mobile = $grid_data->heights->mobile;
			// define the blocks border
			$output_desktop .= '#gk-grid-gk_grid-'.$id.' .gk-grid-element { border: ' . $config['grid_border'] . '!important; }' . "\n" . '.gk-grid .gk-img-desktop { display: block; } .gk-grid .gk-img-tablet, .gk-grid .gk-img-mobile { display: none; } ' . "\n" ;
			// define the blocks size and position
			for($i = 0; $i < count($block_data); $i++) {
				$el = $block_data[$i];
				$output_desktop .= $prefix . str_replace(array('[', ']', ' '), array('_', '', '-'), $el->ID) . ' { height: '.($el->SIZE_D_H * (100.0 / $mod_height_desktop)).'%; width: '.($el->SIZE_D_W * (100.0 / 6)).'%; left: '.($el->POS_D_X * (100.0 / 6)).'%; top: '.($el->POS_D_Y * (100.0 / $mod_height_desktop)).'%; }' . "\n";
				$output_tablet .= $prefix . str_replace(array('[', ']', ' '), array('_', '', '-'), $el->ID) . ' { height: '.($el->SIZE_T_H * (100.0 / $mod_height_tablet)).'%; width: '.($el->SIZE_T_W * (100.0 / 4)).'%; left: '.($el->POS_T_X * (100.0 / 4)).'%; top: '.($el->POS_T_Y * (100.0 / $mod_height_tablet)).'%; }' . "\n";
				$output_mobile .= $prefix . str_replace(array('[', ']', ' '), array('_', '', '-'), $el->ID) . ' { height: '.($el->SIZE_M_H * (100.0 / $mod_height_mobile)).'%; width: '.($el->SIZE_M_W * (100.0 / 2)).'%; left: '.($el->POS_M_X * (100.0 / 2)).'%; top: '.($el->POS_M_Y * (100.0 / $mod_height_mobile)).'%; }' . "\n";
			}
		}	
		// output the final CSS code
		return $output_desktop . '@media (max-width: '.$config['tablet_width'].'px) { ' . "\n" . '.gk-grid .gk-img-tablet { display: block; } .gk-grid .gk-img-desktop, .gk-grid .gk-img-mobile { display: none; } ' . "\n" . $output_tablet . '} ' . "\n" . '@media (max-width: '.$config['mobile_width'].'px) { ' . "\n" . '.gk-grid .gk-img-mobile { display: block; } .gk-grid .gk-img-desktop, .gk-grid .gk-img-tablet { display: none; } ' . "\n"  . $output_mobile . '} ';
	}

	/**
	 *
	 * Outputs the HTML code of this widget.
	 *
	 * @param array An array of standard parameters for widgets in this theme
	 * @param array An array of settings for this widget instance
	 * @return void
	 *
	 **/
	function widget($args, $instance) {		
		global $wp_registered_widgets;
		global $wp_registered_sidebars;
		
		if(!isset($args['widget_id'])) {
			$args['widget_id'] = null;
		}

		//
		extract($args, EXTR_SKIP);
		//
		foreach($this->config as $key => $value) {
			if($key == 'title') {
				$this->config['title'] = apply_filters('widget_title', !isset($instance['title']) ? $this->config['title'] : $instance['title'], $instance, $this->id_base);
			} else {
				$this->config[$key] = !isset($instance[$key]) ? $this->config[$key] : $instance[$key];
			}
		}
		// parse the grid settings
		$this->config['grid_manager'] = json_decode(str_replace('&quot;', '"', $this->config['grid_manager']));

		// get the cache content
		$cache_content = get_transient(md5($this->id));
		// prepare a variable for the cached data
		$cache_output = '';
		// check if the recursive problem doesn't appear
		$all_sidebars = get_option('sidebars_widgets');
		$recursive_flag = false;

		foreach($all_sidebars as $sidebar_name => $single_sidebar) {
			if(
				is_array($single_sidebar) && 
				in_array($args['widget_id'], $single_sidebar) && 
				$sidebar_name == $this->config['selected_sidebar']
			) {
				$recursive_flag = true;
			}
		}
		//
		if(!$recursive_flag) {
			if ($this->config['selected_sidebar'] !== '') {
				echo $before_widget;
				if($this->config['title'] != '') {
					echo $before_title;
					echo $this->config['title'];
					echo $after_title;
				}
				// generating wrapper with params in the data-* attributes
				echo '<div 
						id="gk-grid-'.$this->id.'"
						class="gk-grid" 
						data-animation="'.$this->config['animation'].'" 
						data-random="'.$this->config['anim_random'].'" 
						data-speed="'.$this->config['anim_speed'].'" 
						data-type="'.$this->config['anim_type'].'"
					>';
				echo '<div class="gk-grid-wrap" style="margin: '.$this->config['grid_margin'].'">';
				// creating the tabs
				$sidebars = wp_get_sidebars_widgets();
				$widget_code = array();
				
				$cache_time =  $this->config['cache_time'];
				if($cache_content && $cache_time > 0) {
					$widget_code = $cache_content;
				} else {
					if(isset($sidebars[$this->config['selected_sidebar']])) {
						foreach($sidebars[$this->config['selected_sidebar']] as $widget) {
							if(isset($wp_registered_widgets[$widget])) {
								$selected_sidebar = $wp_registered_sidebars[$this->config['selected_sidebar']];
								// get the widget params and merge with sidebar data, widget ID and name
								$params = array_merge(
									array( 
										array_merge( 
											$selected_sidebar, 
											array(
												'widget_id' => $widget, 
												'widget_name' => $wp_registered_widgets[$widget]['name']
											) 
										) 
									),
									
									(array) $wp_registered_widgets[$widget]['params']
								);
								
								// apply params
								$params = apply_filters( 'dynamic_sidebar_params', $params );
								// modify params
								$params[0]['before_widget'] = '<div id="'.$widget.'" class="box '.$wp_registered_widgets[$widget]['classname'].'">';
								$params[0]['after_widget'] = '</div>';
								$params[0]['before_title'] = '{BLOCK_TITLE}';
								$params[0]['after_title'] = '{/BLOCK_TITLE}';
								// get the widget callback function
								$callback = $wp_registered_widgets[$widget]['callback'];
								// generate
								ob_start();
								do_action('dynamic_sidebar', $wp_registered_widgets[$widget]);
								// use the widget callback function if exists
								if ( is_callable($callback) ) {
									call_user_func_array($callback, $params);
								}
								// get the widget code
								array_push($widget_code, ob_get_contents());
								ob_end_clean();
							}
						}
					} else {
						echo 'Selected sidebar is blank';
					}
					// store the results
					if($cache_time > 0) {
						$cache_output = $widget_code;
						$this->config['cache_time'] = ($this->config['cache_time'] == '' || !is_numeric($this->config['cache_time'])) ? 60 : (int) $this->config['cache_time'];
						set_transient(md5($this->id) , $cache_output, $this->config['cache_time'] * 60);
					} else {
						delete_transient(md5($this->id));
					}
				}				
				// generate the content
				for($i = 0; $i < count($this->config['grid_manager']->blocks); $i++) {
					echo '<div class="gk-grid-element gk-grid-'. str_replace(array('[', ']', ' '), array('_', '', '-'), $this->config['grid_manager']->blocks[$i]->ID) . (($this->config['animation'] == 'off') ? ' active' : '').'">';
					echo preg_replace('@{BLOCK_TITLE}(.*?){/BLOCK_TITLE}@mis', '', $widget_code[$i]);
					echo '</div>';
				}
				// generate the necessary images
				if(isset($this->config['grid_manager']->heights->desktop) && $this->config['grid_manager']->heights->desktop > 0) {
					echo '<img class="gk-img-desktop" src="data:image/png;base64,'. $this->generate_blank_image(60, 10 * $this->config['grid_manager']->heights->desktop) .'" alt="" />';
					echo '<img class="gk-img-tablet" src="data:image/png;base64,'. $this->generate_blank_image(40, 10 * $this->config['grid_manager']->heights->tablet) .'" alt="" />';
					echo '<img class="gk-img-mobile" src="data:image/png;base64,'. $this->generate_blank_image(20, 10 * $this->config['grid_manager']->heights->mobile) .'" alt="" />';
				}
				// close the wrappers
				echo '</div>';
				echo '</div>';
				// 
				echo $after_widget;
			} else {
				echo $before_widget;
				if($this->config['title'] != '') {
					echo $before_title;
					echo $this->config['title'];
					echo $after_title;
				}
				//
				echo '<p class="gk-grid-error"><strong>&times;</strong>'.__('You didn\'t selected any blocks source :(', 'gk-grid').'</p>';
				// 
				echo $after_widget;
			}
		} else {
			echo $before_widget;
			if($this->config['title'] != '') {
				echo $before_title;
				echo $this->config['title'];
				echo $after_title;
			}
			//
			echo '<p class="gk-grid-error"><strong>&infin;</strong>'.__('It seems that you want to do something very bad ;)', 'gk-grid') . '<br /><small>' . __('Tip: recursion is very dangerous - please change the source of tabs for this widget.', 'gk-grid').'</small></p>';
			// 
			echo $after_widget;
		}
	}

	/**
	 *
	 * Used in the back-end to update the module options
	 *
	 * @param array new instance of the widget settings
	 * @param array old instance of the widget settings
	 * @return updated instance of the widget settings
	 *
	 **/
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		if(count($new_instance) > 0) {
			foreach($new_instance as $key => $option) {
				$instance[$key] = esc_attr(strip_tags($new_instance[$key]));
			}
		}

		$this->refresh_cache();

		$alloptions = wp_cache_get('alloptions', 'options');
		if(isset($alloptions['gk_grid'])) {
			delete_option( 'gk_grid' );
		}

		return $instance;
	}

	/**
	 *
	 * Refreshes the widget cache data
	 *
	 * @return void
	 *
	 **/

	function refresh_cache() {
		wp_cache_delete( 'gk_grid', 'widget' );

		if(is_array(get_option('widget_gk_grid'))) {
		    $ids = array_keys(get_option('widget_gk_grid'));
		    for($i = 0; $i < count($ids); $i++) {
		        if(is_numeric($ids[$i])) {
		            delete_transient(md5('gk_grid-' . $ids[$i]));
		        }
		    }
	    } else {
	    	delete_transient(md5('gk_grid-' . $this->id));
	    }
	}

	/**
	 *
	 * Outputs the HTML code of the widget in the back-end
	 *
	 * @param array instance of the widget settings
	 * @return void - HTML output
	 *
	 **/
	function form($instance) {
		global $wp_registered_sidebars;

		foreach($this->config as $key => $value) {
			$this->config[$key] = isset($instance[$key]) ? esc_attr($instance[$key]) : $this->config[$key];
		}
	
	?>
		<div class="gk-grid-ui">
			<p class="gk-cols2">
				<span>
					<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" title="<?php _e('Specify the title of the widget - leave blank to avoid this element.', 'gk-grid'); ?>"><?php _e( 'Title:', 'gk-grid' ); ?></label>
					<input class="gk-title" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $this->config['title'] ); ?>" />
				</span>

				<span>	
					<label for="<?php echo esc_attr( $this->get_field_id( 'selected_sidebar' ) ); ?>" title="<?php _e('Specify the sidebar which will be source of the grid blocks. The widget will receive the widget title as a grid name and content of the widget as a grid content.', 'gk-grid'); ?>"><?php _e( 'Source:', 'gk-grid' ); ?></label>
				
					<select id="<?php echo esc_attr( $this->get_field_id( 'selected_sidebar' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'selected_sidebar' ) ); ?>">
						<option value=""<?php selected('', $this->config['selected_sidebar']); ?>><?php _e('None', 'gk-grid'); ?></option>
						<?php foreach(array_keys($wp_registered_sidebars) as $sidebar) : ?>
						<option value="<?php echo $sidebar; ?>"<?php selected($sidebar, $this->config['selected_sidebar']); ?>><?php echo $wp_registered_sidebars[$sidebar]["name"]; ?></option>
						<?php endforeach; ?>
					</select>
				</span>
			</p>

			<div class="gk_grid_manager">
				<div class="gk_grid_blocks">
					<div class="gk_grid_header">
						<h3><?php _e('Grid Blocks', 'gk-grid'); ?></h3>
						<button class="gk_grid_add gk_grid_btn"><i class="icon-plus"></i></button>
						<div class="gk_grid_form_add">
							<div>
								<p><label><?php _e('Name: ', 'gk-grid'); ?></label> <span><input type="text" size="14" class="gk_grid_form_add_position" /></span></p>
								<p><label><?php _e('Desktop size:', 'gk-grid'); ?></label> <span><input type="number" size="1" min="1" max="6" value="1" class="gk_grid_form_add_desktop_w" /> &times; <input type="number" size="1" min="1" max="9" value="1" class="gk_grid_form_add_desktop_h" /></span></p>
								<p><label><?php _e('Tablet size:', 'gk-grid'); ?></label> <span><input type="number" size="1" min="1" max="4" value="1" class="gk_grid_form_add_tablet_w" /> &times; <input type="number" size="1" min="1" max="9" value="1" class="gk_grid_form_add_tablet_h" /></span></p>
								<p><label><?php _e('Mobile size:', 'gk-grid'); ?></label> <span><input type="number" size="1" min="1" max="2" value="1" class="gk_grid_form_add_mobile_w" /> &times; <input type="number" size="1" min="1" max="9" value="1" class="gk_grid_form_add_mobile_h" /></span></p>
							
								<p><button class="gk_grid_form_add_cancel gk_grid_btn"><?php _e('Cancel', 'gk-grid'); ?></button><button class="gk_grid_form_add_save gk_grid_btn"><?php _e('Add block', 'gk-grid'); ?></button></p>
							</div>
						</div>
					</div>
					
					<div class="gk_grid_content">
						<ul class="gk_grid_blocks_list loading"></ul>
					</div>
				</div>
				<div class="gk_grid_preview">
					<div class="gk_grid_header">
						<h3><?php _e('Preview', 'gk-grid'); ?></h3>
					</div>
					
					<div class="gk_grid_content">					
						<div class="gk_grid_desktop_preview loading"></div>
						
						<h4><i class="icon-desktop"></i></h4>
						
						<div class="gk_grid_mobile_preview_wrap">
							<div>
								<div class="gk_grid_tablet_preview loading"></div>
								
								<h4><i class="icon-tablet"></i></h4>
							</div>
							
							<div>
								<div class="gk_grid_mobile_preview loading"></div>
								
								<h4><i class="icon-mobile-phone"></i></h4>
							</div>
						</div>
					</div>
				</div>
			</div>
			<textarea name="<?php echo esc_attr( $this->get_field_name( 'grid_manager' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'grid_manager' ) ); ?>" rows="20" cols="50"><?php echo esc_attr($this->config['grid_manager']); ?></textarea>
			
			<p class="gk-cols2">
				<span>
					<label for="<?php echo esc_attr( $this->get_field_id( 'grid_margin' ) ); ?>" title="<?php _e('You can specify the margin of the grid wrapper as a CSS margin value.', 'gk-grid'); ?>"><?php _e( 'Grid margin:', 'gk-grid' ); ?></label>
					<input class="gk-inline-wide" id="<?php echo esc_attr( $this->get_field_id( 'grid_margin' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'grid_margin' ) ); ?>" type="text" value="<?php echo esc_attr( $this->config['grid_margin'] ); ?>" />
				</span>

				<span>
					<label for="<?php echo esc_attr( $this->get_field_id( 'grid_border' ) ); ?>" title="<?php _e('You can specify the border of the grid blocks as a CSS border value.', 'gk-grid'); ?>"><?php _e( 'Grid border:', 'gk-grid' ); ?></label>
					<input class="gk-inline-wide" id="<?php echo esc_attr( $this->get_field_id( 'grid_border' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'grid_border' ) ); ?>" type="text" value="<?php echo esc_attr( $this->config['grid_border'] ); ?>" />
				</span>
			</p>

			<p class="gk-cols2">
				<span>
					<label for="<?php echo esc_attr( $this->get_field_id( 'tablet_width' ) ); ?>" title="<?php _e('You can specify the start width for the tablet layout.', 'gk-grid'); ?>"><?php _e( 'Tablet width: ', 'gk-grid' ); ?></label>

					<input class="gk-inline" id="<?php echo esc_attr( $this->get_field_id( 'tablet_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tablet_width' ) ); ?>" type="text" value="<?php echo esc_attr( $this->config['tablet_width'] ); ?>" />px
				</span>

				<span>
					<label for="<?php echo esc_attr( $this->get_field_id( 'mobile_width' ) ); ?>" title="<?php _e('You can specify the start width for the mobile layout.', 'gk-grid'); ?>"><?php _e( 'Mobile width: ', 'gk-grid' ); ?></label>
					<input class="gk-inline" id="<?php echo esc_attr( $this->get_field_id( 'mobile_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'mobile_width' ) ); ?>" type="text" value="<?php echo esc_attr( $this->config['mobile_width'] ); ?>" />px
				</span>
			</p>
			
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'animation' ) ); ?>"><?php _e('Animation', 'gk-grid'); ?></label>
				
				<select id="<?php echo esc_attr( $this->get_field_id('animation')); ?>" name="<?php echo esc_attr( $this->get_field_name('animation')); ?>">
					<option value="on"<?php selected($this->config['animation'], 'on'); ?>>
						<?php _e('On', 'gk-grid'); ?>
					</option>
					<option value="off"<?php selected($this->config['animation'], 'off'); ?>>
						<?php _e('Off', 'gk-grid'); ?>
					</option>
				</select>

				<select id="<?php echo esc_attr( $this->get_field_id('anim_speed')); ?>" name="<?php echo esc_attr( $this->get_field_name('anim_speed')); ?>">
					<option value="fast"<?php selected($this->config['anim_speed'], 'fast'); ?>>
						<?php _e('Fast', 'gk-grid'); ?>
					</option>
					<option value="normal"<?php selected($this->config['anim_speed'], 'normal'); ?>>
						<?php _e('Normal', 'gk-grid'); ?>
					</option>
					<option value="slow"<?php selected($this->config['anim_speed'], 'slow'); ?>>
						<?php _e('Slow', 'gk-grid'); ?>
					</option>
				</select>
				
				<select id="<?php echo esc_attr( $this->get_field_id('anim_random')); ?>" name="<?php echo esc_attr( $this->get_field_name('anim_random')); ?>">
					<option value="on"<?php selected($this->config['anim_random'], 'on'); ?>>
						<?php _e('Random animation', 'gk-grid'); ?>
					</option>
					<option value="off"<?php selected($this->config['anim_random'], 'off'); ?>>
						<?php _e('Linear animation', 'gk-grid'); ?>
					</option>
				</select>
				
				<select id="<?php echo esc_attr( $this->get_field_id('anim_type')); ?>" name="<?php echo esc_attr( $this->get_field_name('anim_type')); ?>">
					<option value="opacity"<?php selected($this->config['anim_type'], 'opacity'); ?>><?php _e('Opacity', 'gk-grid'); ?></option>
					<option value="scale"<?php selected($this->config['anim_type'], 'scale'); ?>><?php _e('Scale', 'gk-grid'); ?></option>
					<option value="rotate"<?php selected($this->config['anim_type'], 'rotate'); ?>><?php _e('Rotate', 'gk-grid'); ?></option>
					<option value="rotate3d"<?php selected($this->config['anim_type'], 'rotate3d'); ?>><?php _e('Rotate 3D', 'gk-grid'); ?></option>
					<option value="top"<?php selected($this->config['anim_type'], 'top'); ?>><?php _e('Top', 'gk-grid'); ?></option>
					<option value="right"<?php selected($this->config['anim_type'], 'right'); ?>><?php _e('Right', 'gk-grid'); ?></option>
					<option value="bottom"<?php selected($this->config['anim_type'], 'bottom'); ?>><?php _e('Bottom', 'gk-grid'); ?></option>
					<option value="left"<?php selected($this->config['anim_type'], 'left'); ?>><?php _e('Left', 'gk-grid'); ?></option>
				</select>
			</p>

			<p class="gk-cols2">
				<span>
					<label for="<?php echo esc_attr( $this->get_field_id( 'amount_of_sidebars' ) ); ?>" title="<?php _e('You can specify how many sidebars will be added by this widget. These sidebars are very useful, because they won\'t be displayed in your theme.', 'gk-grid'); ?>"><?php _e( 'Amount of sidebars:', 'gk-grid' ); ?></label>
					<input class="gk-small" id="<?php echo esc_attr( $this->get_field_id( 'amount_of_sidebars' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'amount_of_sidebars' ) ); ?>" type="number" min="0" max="100" value="<?php echo esc_attr( $this->config['amount_of_sidebars'] ); ?>" />
				</span>
				<span>
					<label for="<?php echo esc_attr( $this->get_field_id( 'cache_time' ) ); ?>" title="<?php _e('You can enable the widget cache. You can specify the cache time (in minutes)', 'gk-grid'); ?>"><?php _e( 'Cache time: ', 'gk-grid' ); ?></label>
					<input class="gk-small" id="<?php echo esc_attr( $this->get_field_id( 'cache_time' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'cache_time' ) ); ?>" type="number" min="0" max="10000" value="<?php echo esc_attr( $this->config['cache_time'] ); ?>" /> (min)
				</span>
			</p>
		</div>

		<script type="text/javascript">
			setTimeout(function() {
				jQuery('.gk_grid_manager').each(function(i, el) {
					el = jQuery(el);
					var id = el.parent().parent().parent().find('.widget-id').val();
					
					if(id.indexOf('gk_grid-__i__') === -1) {
						var selected = jQuery("div[id$='"+id+"']");
						if(!selected.hasClass('activated')) {
							selected.addClass('activated');
							
							var gridManager = GKGridManager();
							gridManager.init(selected);	
							
							selected.find('.widget-control-save').click(function() {
								selected.removeClass('activated');
							});
						}
					}
				});
			}, 1500);
		</script>
	<?php
	}

	// function to generate blank transparent PNG images
	public function generate_blank_image($width, $height){ 
		$image = imagecreatetruecolor($width, $height);
		imagesavealpha($image, true);
		$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
		imagefill($image, 0, 0, $transparent);
		// cache the output
		ob_start();
		imagepng($image);
		$img =  ob_get_contents();
		ob_end_clean();
		// return the string
		return base64_encode($img);
	}

	static function roman_number($num) {
    	$n = intval($num);
    	$result = '';
 
    
    	$roman_numerals = array(
            'M'  => 1000,
            'CM' => 900,
            'D'  => 500,
            'CD' => 400,
            'C'  => 100,
            'XC' => 90,
            'L'  => 50,
            'XL' => 40,
            'X'  => 10,
            'IX' => 9,
            'V'  => 5,
            'IV' => 4,
            'I'  => 1
        );
 
	    foreach ($roman_numerals as $roman => $number) {
	        $matches = intval($n / $number);
	        $result .= str_repeat($roman, $matches);
	        $n = $n % $number;
	    }

	    return $result;
    }
}

// EOF
