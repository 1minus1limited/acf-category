<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('gv_acf_field_category') ) :


class gv_acf_field_category extends acf_field {


	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function __construct( $settings ) {

		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/

		$this->name = 'category';


		$this->taxonomy_ids = [];


		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/

		$this->label = __('Category', 'cat');


		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/

		$this->category = 'choice';


		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/

		$this->defaults = array(
			'api_resource'	=> 'taxons',
		);


		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('category', 'error');
		*/

		$this->l10n = array(
			'error'	=> __('Error! Please enter a higher value', 'cat'),
		);


		/*
		*  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
		*/

		$this->settings = $settings;

		$this->taxons = [];

		// do not delete!
    	parent::__construct();

	}


	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field_settings( $field ) {

		/*
		*  acf_render_field_setting
		*
		*  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
		*  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
		*
		*  More than one setting can be added by copy/paste the above code.
		*  Please note that you must also have a matching $defaults value for the field name (font_size)
		*/

		acf_render_field_setting( $field, array(
			'label'			=> __('API Resource Name','cat'),
			'instructions'	=> __('Guitar Village Spree REST API Resource Name Eg: Taxon','cat'),
			'type'			=> 'text',
			'name'			=> 'api_resource',
		));

	}



	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field( $field ) {

		/*
		*  Review the data of $field.
		*  This will show what data is available
		*/

		// echo '<pre>';


		$args = array(
		 'post_parent' =>  $field['parent'],
		 'post_type' => 'acf-field',
		 'post_status' => 'any',
		 'orderby'   => 'ID',
		 'order' => 'ASC'
	 );
	 $children = get_children( $args );
	 $product_selected_ids = [];
	 foreach ($children as $value){
		   array_push($product_selected_ids , get_field($value->post_excerpt));
	 }

	 $product_selected_ids = array_filter($product_selected_ids);


		// echo '</pre>';

		// echo $field['api_resource'];
 		$site_options = new SiteOptions();
		// echo $site_options->field('webhook_endpoint');
		// echo $site_options->field('webhook_token');
		$ch = curl_init();
		$curlConfig = array(
		    CURLOPT_URL => $site_options->field('spree_endpoint') . $field['api_resource'] . "/?per_page=1&page=1&token=" . $site_options->field('webhook_token')
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt_array($ch, $curlConfig);
		$result = curl_exec($ch);
		curl_close($ch);
		$this->create_flat_taxon_array(json_decode($result, true)['taxons']);

		// print_r($this->taxons);


		/*
		*  Create a simple text input using the 'font_size' setting.
		*/

		?>

		<?php

		$selected = $field['value'];

		// echo  $site_options->field('spree_endpoint')  . "taxonomies/". $this->taxonomy_id . "/taxons/" . $selected ."/?token=" . $site_options->field('webhook_token');
		// die();


		if($selected && !isset($this->taxons[$selected])){


				$ch = curl_init();
				$curlConfig = array(
				    CURLOPT_URL => $site_options->field('spree_endpoint')  . "taxonomies/?token=" . $site_options->field('webhook_token')
				);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt_array($ch, $curlConfig);
				$result = curl_exec($ch);
				curl_close($ch);

				$taxonomies = json_decode($result, true)['taxonomies'];

			foreach ($taxonomies as $index => $taxonomy) {
				array_push($this->taxonomy_ids, $taxonomy['id']);
				$this->taxonomy_ids = array_unique($this->taxonomy_ids);
			}



	
			foreach ($this->taxonomy_ids as $index => $taxonomy_id) {
				# code...
				$ch = curl_init();
				$curlConfig = array(
				    CURLOPT_URL => $site_options->field('spree_endpoint')  . "taxonomies/". $taxonomy_id . "/taxons/" . $selected ."/?token=" . $site_options->field('webhook_token')
				);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt_array($ch, $curlConfig);
				$result = curl_exec($ch);
				$result = json_decode($result, true);
				curl_close($ch);
				
				if(isset($result['id'])){
					$this->taxons[$result['id']] = $result['pretty_name'];
					break;
				}
			}
		}

		if (($key = array_search($selected , $product_selected_ids)) !== false) {
    		unset($product_selected_ids[$key]);
		}

		$product_selected_ids_string = implode("-",$product_selected_ids);

		?>


		<div class="category_loader_overlay" ></div>
		<select size="5" data-url="<?= $site_options->field('spree_endpoint') . 'taxons/?per_page=1&token=' . $site_options->field('webhook_token') ?>" data-page="2" data-type="category"  name="<?php echo esc_attr($field['name']) ?>" data-product_ids="<?= $product_selected_ids_string ?>" class="<?php echo $field['wrapper']['class'] ?>" data-identifier="row_category_parent" data-category="<?= $selected ?>" onchange="update_associated_product_fields(this.value,'<?php echo $field['wrapper']['class'] ?>')">
			<option selected=""></option>
			<?php foreach ($this->taxons as $key => $value) { ?>
				<option <?php echo $key == $selected ? 'selected' : ''  ?> value="<?= $key ?>" > <?= $value ?> </option>
			<?php } ?>
		</select>
		<div class="loader" ></div>

		<?php // echo get_post_meta($post->ID, 'category_select_product_1', true) ?>
 <?php
	}


	/*
	* recursive loop function to read taxons
	*
	*/

	function create_flat_taxon_array($taxon_json){
		// print_r($taxon_json);





		foreach ($taxon_json as $value) {

			// echo $value['pretty_name'];
			// code...
			$this->taxons[$value['id']] = $value['pretty_name'];
			
			if(count($value['taxons']) > 0 ){

				 $this->create_flat_taxon_array($value['taxons']);
			}

		}

	}




	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
*/
	function input_admin_enqueue_scripts() {

		// vars
		$url = $this->settings['url'];
		$version = $this->settings['version'];

		// register & include JS
		wp_register_script('cat', "{$url}assets/js/input.js", array('acf-input'), $version);
		wp_enqueue_script('cat');


		// register & include CSS
		wp_register_style('cat', "{$url}assets/css/input.css", array('acf-input'), $version);
		wp_enqueue_style('cat');
	$site_options = new SiteOptions();
		// print_r($field);
		//echo "Sssss";
		// die();
		// echo "haleluya";
// echo  get_acf_fields_by_group($group->ID);
// die();

		 // get_acf_fields_by_group($group->ID);



		$endpointDetails = array(
        'ajax_url' => $site_options->field('spree_endpoint'),
				'ajax_token' => $site_options->field('webhook_token'),
				'categoryID' => get_field('category_select'),
				// 'product_1' => $field[]
    );
    wp_localize_script( 'cat', 'endpointDetails', $endpointDetails );

	}




	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function input_admin_head() {



	}

	*/


	/*
   	*  input_form_data()
   	*
   	*  This function is called once on the 'input' page between the head and footer
   	*  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and
   	*  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
   	*  seen on comments / user edit forms on the front end. This function will always be called, and includes
   	*  $args that related to the current screen such as $args['post_id']
   	*
   	*  @type	function
   	*  @date	6/03/2014
   	*  @since	5.0.0
   	*
   	*  @param	$args (array)
   	*  @return	n/a
   	*/

   	/*

   	function input_form_data( $args ) {



   	}

   	*/


	/*
	*  input_admin_footer()
	*
	*  This action is called in the admin_footer action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_footer)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function input_admin_footer() {



	}

	*/


	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function field_group_admin_enqueue_scripts() {

	}

	*/


	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add CSS and JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function field_group_admin_head() {

	}

	*/


	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

	/*

	function load_value( $value, $post_id, $field ) {

		return $value;

	}

	*/


	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is saved in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

	/*

	function update_value( $value, $post_id, $field ) {

		return $value;

	}

	*/


	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/

	/*

	function format_value( $value, $post_id, $field ) {

		// bail early if no value
		if( empty($value) ) {

			return $value;

		}


		// apply setting
		if( $field['font_size'] > 12 ) {

			// format the value
			// $value = 'something';

		}


		// return
		return $value;
	}

	*/


	/*
	*  validate_value()
	*
	*  This filter is used to perform validation on the value prior to saving.
	*  All values are validated regardless of the field's required setting. This allows you to validate and return
	*  messages to the user if the value is not correct
	*
	*  @type	filter
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$valid (boolean) validation status based on the value and the field's required setting
	*  @param	$value (mixed) the $_POST value
	*  @param	$field (array) the field array holding all the field options
	*  @param	$input (string) the corresponding input name for $_POST value
	*  @return	$valid
	*/

	/*

	function validate_value( $valid, $value, $field, $input ){

		// Basic usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = false;
		}


		// Advanced usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = __('The value is too little!','cat'),
		}


		// return
		return $valid;

	}

	*/


	/*
	*  delete_value()
	*
	*  This action is fired after a value has been deleted from the db.
	*  Please note that saving a blank value is treated as an update, not a delete
	*
	*  @type	action
	*  @date	6/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (mixed) the $post_id from which the value was deleted
	*  @param	$key (string) the $meta_key which the value was deleted
	*  @return	n/a
	*/

	/*

	function delete_value( $post_id, $key ) {



	}

	*/


	/*
	*  load_field()
	*
	*  This filter is applied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/

	/*

	function load_field( $field ) {

		return $field;

	}

	*/


	/*
	*  update_field()
	*
	*  This filter is applied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/

	/*

	function update_field( $field ) {

		return $field;

	}

	*/


	/*
	*  delete_field()
	*
	*  This action is fired after a field is deleted from the database
	*
	*  @type	action
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	n/a
	*/

	/*

	function delete_field( $field ) {



	}

	*/


}


// initialize
new gv_acf_field_category( $this->settings );


// class_exists check
endif;

?>
