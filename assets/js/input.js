function update_associated_product_fields(value,class_name){
	loadProducts(value,class_name);
}

function loadProducts(value,class_name , product_ids_string = null ){

	jQuery("."+class_name+"_product").find('select').find('option').remove();
	if(!product_ids_string){
		product_ids_string = "";
	}

		jQuery.ajax({
			url: endpointDetails.ajax_url+'taxons/products?id='+value+'&per_page=1000&token='+endpointDetails.ajax_token,
			type: 'GET',
			datatype: 'jsonp',
			success: function(json){
				if(json.products.length == 0 ){
					jQuery("."+class_name+"_product").find('select').append('<option value=""></option>');
				} else {


						product_ids = 	product_ids_string.split("-");

								jQuery("."+class_name+"_product").each( function( index, obj ) {
							jQuery.each(json.products, function (key, data) {
									if(product_ids.length && data.id == product_ids[index] ){
										jQuery(obj).find('select').append('<option  selected value="'+data.id+'" >'+data.name+'</option>');

									}else{
										jQuery(obj).find('select').append('<option   value="'+data.id+'" >'+data.name+'</option>');

									}

								});


									// .val(product_ids[index]).change();
							});



				}



			// select_option(endpointDetails.product_1, "category_select_product_1");
			// select_option(endpointDetails.product_2, "category_select_product_2");
			// select_option(endpointDetails.product_3, "category_select_product_3");
			// select_option(endpointDetails.product_4, "category_select_product_4");
			}
		});
}




(function($){

	/**
	*  initialize_field
	*
	*  This function will initialize the $field.
	*
	*  @date	30/11/17
	*  @since	5.6.5
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function initialize_field( $field ) {

		//$field.doStuff();

	}


	if( typeof acf.add_action !== 'undefined' ) {

		/*
		*  ready & append (ACF5)
		*
		*  These two events are called when a field element is ready for initizliation.
		*  - ready: on page load similar to $(document).ready()
		*  - append: on new DOM elements appended via repeater field or other AJAX calls
		*
		*  @param	n/a
		*  @return	n/a
		*/

		acf.add_action('ready_field/type=category', initialize_field);
		acf.add_action('append_field/type=category', initialize_field);


	} else {

		/*
		*  acf/setup_fields (ACF4)
		*
		*  These single event is called when a field element is ready for initizliation.
		*
		*  @param	event		an event object. This can be ignored
		*  @param	element		An element which contains the new HTML
		*  @return	n/a
		*/

		$(document).on('acf/setup_fields', function(e, postbox){

			// find all relevant fields
			$(postbox).find('.field[data-field_type="category"]').each(function(){

				// initialize
				initialize_field( $(this) );

			});

		});

	}

	$(document).on('change','#selected_category',function(){
		loadProducts($(this).val());

	});


	$(document).on('ready',function(){

		$('*[data-identifier="row_category_parent"]').each(function(){
		  var	category_id = $(this).data('category');
			var product_ids = $(this).data('product_ids');
			var class_name = $(this).attr('class');

			// $product_selected_ids_string
			loadProducts(category_id,class_name,product_ids);
		})





			// var p1 = $(".acf-field[data-name='category_select_product_1'] select option[value='"+endpointDetails.product_1+"']");
			// // console.log(select_option());
			// // console.log(select_option(endpointDetails.product_1));
			// var p2 = $('.acf-field[data-name="category_select_product_2').find("select option[value='"+endpointDetails.product_2+"']");
			// p1.attr("selected","selected");
			// var p3 = $('.acf-field[data-name="category_select_product_3').find("select option[value='"+endpointDetails.product_3+"']");
			// p1.attr("selected","selected");
			// var p4 = $('.acf-field[data-name="category_select_product_4').find("select option[value='"+endpointDetails.product_4+"']");
			// p1.attr("selected","selected");
	});

	// Default selected option
	function select_option(key, field_name) {
  	 return $('.acf-field[data-name="'+field_name+'"] select option[value='+key+']').attr("selected","selected");
	}



})(jQuery);
