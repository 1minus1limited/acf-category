function update_associated_product_fields(value,class_name){
	loadProducts(value,class_name);
}

function loadProducts(value,class_name , product_ids_string = null ){
	var selected_set = false;
	jQuery("."+class_name).attr('data-category', value);
	jQuery("."+class_name+"_product").find('select').attr('size', '5');
	jQuery("."+class_name+"_product").find('select').data('page', '2');
	jQuery("."+class_name+"_product").attr('data-category', value);
	jQuery("."+class_name+"_product").find('select').find('option').remove();
	if(!product_ids_string){
		product_ids_string = "";
	}

		jQuery.ajax({
			url: endpointDetails.ajax_url+'taxons/products?id='+value+'&per_page=10&page=1&token='+endpointDetails.ajax_token,
			type: 'GET',
			datatype: 'jsonp',
			success: function(json){


				if(json.products.length == 0 ){
					jQuery("."+class_name+"_product").find('select').append('<option value=""></option>');
				} else {


						product_ids = 	product_ids_string.split("-");

						jQuery("."+class_name+"_product").each( function( index, obj ) {
							selected_set = false
							jQuery.each(json.products, function (key, data) {
								if(product_ids.length && data.id == product_ids[index] ){
									selected_set = true;
									jQuery(obj).find('select').append('<option  selected value="'+data.id+'" >'+data.name+'</option>');
								}else{
									jQuery(obj).find('select').append('<option   value="'+data.id+'" >'+data.name+'</option>');
								}
							});

							if(selected_set == false && product_ids_string != ""){
								jQuery.ajax({
									url: endpointDetails.ajax_url + 'products/' + product_ids[index] + '?token='+endpointDetails.ajax_token,
									type: 'GET',
									datatype: 'jsonp',
									success: function(res){
										if(res.id){
											jQuery(obj).find('select').append('<option  selected value="'+res.id+'" >'+res.name+'</option>');
										}
									}
								});
							}
						});



				}
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

	  $('.category_loader_overlay').hide();

      $('.loader').hide();

		$('*[data-identifier="row_category_parent"]').each(function(){
		    var	category_id = $(this).data('category');
			var product_ids = $(this).data('product_ids');
			var class_name = $(this).attr('class');
			jQuery("."+class_name+"_product").find('select').data('page',"2");
			jQuery("."+class_name+"_product").find('select').data('type',"product");



				jQuery("."+class_name+"_product").find('select').on('scroll', function(e){
				    var sel = $(this);
				    category_id = $("."+class_name).attr('data-category');


			      	var data_url = endpointDetails.ajax_url+'taxons/products?id='+category_id+'&per_page=10&token='+endpointDetails.ajax_token;
			      	var data_offset =$(this).data('page');	
			      	var data_type = $(this).data('type');	
			  		var elem = $(e.currentTarget);
	
				    if (elem[0].scrollHeight - elem.scrollTop() <= elem.outerHeight()) 
    				{
				    
				   
				        sel.siblings('.category_loader_overlay').show();

				      	sel.siblings('.loader').show();

						console.log(data_offset);

				      	data_url = data_url + "&page=" +String(data_offset);
						sel.data('page',parseInt(data_offset) + 1);

						jQuery.ajax({
							url: data_url,
							type: 'GET',
							datatype: 'jsonp',
							success: function(json){

								

						if(json.products.length > 0 ) {
							jQuery.each(json.products, function (key, data) {
								sel.append('<option   value="'+data.id+'" >'+data.name+'</option>');
							});
						}
								
				      			sel.siblings('.category_loader_overlay').hide();

				      			sel.siblings('.loader').hide();


							}
						});


				      }
				    
				  });

			// $product_selected_ids_string
			loadProducts(category_id,class_name,product_ids);






		});


	  $('*[data-identifier="row_category_parent"]').on('scroll', function(){
	    var sel = $(this);
	    var lasto = sel.find('option:last');
	    var s = sel.position().top + sel.height();
	    var o = lasto.height() + lasto.position().top - 1;
      	var data_url = $(this).data('url');
      	var data_offset = $(this).data('page');	
      	var data_type = $(this).data('type');	
	    
	      if(o < s){
      		console.log(data_offset);

	        sel.siblings('.category_loader_overlay').show();

	      	sel.siblings('.loader').show();



	      	data_url = data_url + "&page=" +data_offset;
			sel.data('page',parseInt(data_offset) + 1);

			jQuery.ajax({
				url: data_url,
				type: 'GET',
				datatype: 'jsonp',
				success: function(json){

					

						if(json.taxons.length > 0 ) {
							create_cat_flat_taxon_array(json.taxons,sel);

							// jQuery.each(json.taxons, function (key, data) {
							// 	sel.append('<option   value="'+data.id+'" >'+data.name+'</option>');
							// });
						}
					
	      			sel.siblings('.category_loader_overlay').hide();

	      			sel.siblings('.loader').hide();


				}
			});


	      }
	    
	  });


	function create_cat_flat_taxon_array(taxon_json,sel){
		// print_r($taxon_json);





		$.each(taxon_json,function(index,value) {

			// echo $value['pretty_name'];
			// code...
			// taxons[value['id']] = value['pretty_name'];
			sel.append('<option   value="'+value.id+'" >'+value.pretty_name+'</option>');
			// console.log(value);
			if(value.taxons.length > 0 ){
				create_cat_flat_taxon_array(value.taxons,sel);
			}

		});

	}



	});

	// Default selected option
	function select_option(key, field_name) {
  	 return $('.acf-field[data-name="'+field_name+'"] select option[value='+key+']').attr("selected","selected");
	}



})(jQuery);
