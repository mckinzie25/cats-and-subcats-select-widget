<?php
/*
Plugin Name:  Cats and Subcats Select
Description:  Allows user to browse posts by categories and subcategories.  A select box will appear for users to select a top-level category; based on the category selected, a second select box will be populated with subcategories to select.
*/

class Cats_And_Subcats_Select_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			// Base ID of widget
			'cats_and_subcats_select_widget',

			// Widget name for UI
			__('Cats and Subcats Select Widget', 'cats_and_subcats_select_widget'),

			// Widget description
			array( 'description' => __( 'Allows user to browse posts by categories and subcategories.', 'cats_and_subcats_select_widget_domain' ), )
		);

		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );

	}

	public function register_plugin_styles() {
		wp_enqueue_style( 'cats-and-subcats-select-widget', plugin_dir_url(__FILE__) . 'cats-and-subcats-select-widget.css' );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', 'Categories & Subcategories' );

		echo $args['before_widget'];

		if ( ! empty( $title ) )

			//Provides metadata for selected category.
			if ( is_category( ) ) {
				$cat = get_query_var( 'cat' );
				$selectedcat = get_category( $cat );
			}

			//Sets id for top-level category select box.
			$dropdown_cat_id = "{$this->id_base}-cats-dropdown-{$this->number}";

			echo $args['before_title'] . $title . $args['after_title'];

			//Sets label only visible to screenreaders for top-level category select box.
			echo '<label class="screen-reader-text" for="' . esc_attr( $dropdown_cat_id ) . '">Select Categories</label>';

			//Sets params for top-level category select box.
			$cat_args['show_option_none'] = __( 'Select Category' );
			$cat_args['hide_empty'] = FALSE;
			$cat_args['hierarchical'] = TRUE;
			$cat_args['depth'] = 1;
			$cat_args['id'] = $dropdown_cat_id;
			$cat_args['name'] = "{$this->id_base}-cats";

			//If the user has selected a subcategory already, then the parent cat should appear
			//in the parent select box.  Checks to see if this is a parent cat.
			if( $selectedcat->parent != 0 ) {
				$cat_args['selected'] = $selectedcat->parent;
			}

			wp_dropdown_categories( apply_filters( 'widget_categories_dropdown_args', $cat_args ) );

			echo '<div class = "cats-and-subcats-select-widget-cat-select">';

			//Sets id for child-level category select box.
			$dropdown_subcat_id = "{$this->id_base}-subcats-dropdown-{$this->number}";

			echo '</div>';

			//Prevents subcategory dropdown from showing unless user has already
			//selected a top-level category.
			if( isset( $cat ) ) {
				$cat_args = array();

				//Arguments to show child (sub) categories for page category.
				$cat_args['id'] = $dropdown_subcat_id;
				$cat_args['name'] = "{$this->id_base}-subcats";

				//If the user has selected a category that is not a top-level (parent)
				//category, then show all sibling categories--categories that share the
				//same parent.  Otherwise, show all subcategories of selected category.
				if( $selectedcat->parent != 0 ) {
					$cat_args['child_of'] = $selectedcat->parent;

					//Provides metadata for parent category of selected category, so we
					//can set value for $cat_args['show_option_none'].
					$parentcat = get_category( $selectedcat->parent );

					$cat_args['show_option_none'] = __( 'Select Subcategory in ' . $parentcat->cat_name );
				}
				else {
					$cat_args['child_of'] = $selectedcat->cat_ID;

					$cat_args['show_option_none'] = __( 'Select Subcategory in ' . $selectedcat->cat_name );

					//Checks to see if current term has subcategories or not; if not, then
					//prevents subcategory dropdown from appearing.
					$subcat_arr = get_terms( 'category', array( 'child_of' => $selectedcat->cat_ID ) );
				}

				if( isset( $subcat_arr ) && count( $subcat_arr ) == 0 ) {
					echo '<p class = "cats-and-subcats-select-widget-nosubcats">This category contains no subcategories</p>';
				}
				else {
					//Sets label only visible to screenreaders for top-level category select box.
					echo '<label class="screen-reader-text" for="' . esc_attr( $dropdown_subcat_id ) . '">Select Subcategories</label>';

					echo '<div class = "cats-and-subcats-select-widget-subcat-select">';

					wp_dropdown_categories( apply_filters( 'widget_categories_dropdown_args', $cat_args ) );

					echo '</div>';
				}
			}
			?>

<script type='text/javascript'>
/* <![CDATA[ */
(function() {
	//Takes user to appropriate cat page on category/subcategory selection.
	function onCatChange() {
		if ( this.options[ this.selectedIndex ].value > 0 ) {
			location.href = "<?php echo home_url(); ?>/?cat=" + this.options[ this.selectedIndex ].value;
		}
	}

	//If user selects top-level category
	var dropdown_cat = document.getElementById( "<?php echo esc_js( $dropdown_cat_id ); ?>" );
	dropdown_cat.onchange = onCatChange;

	//If user selects top-level category
	var dropdown_subcat = document.getElementById( "<?php echo esc_js( $dropdown_subcat_id ); ?>" );
	dropdown_subcat.onchange = onCatChange;

})();
/* ]]> */
</script>
<?php

		echo $args['after_widget'];
	}

	public function form( $instance ) {
	}

	public function update( $new_instance, $old_instance ) {
		return $instance;
	}
}

// Register and load the widget
function cats_and_subcats_select_load_widget() {
	register_widget( 'cats_and_subcats_select_widget' );
}
add_action( 'widgets_init', 'cats_and_subcats_select_load_widget' );