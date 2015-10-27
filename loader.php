<?php
/*
Plugin Name: MU Broader Impacts Resources
Version: 1.0
Author: Michael C. Barbaro (CARES)
Description: This plugin creates a custom post type, taxonomies, form and map for Broader Impact Resources.
*/

	
add_action( 'init', 'register_cpt_bi_resource' );

function register_cpt_bi_resource() {

    $labels = array( 
        'name' => _x( 'BI Resources', 'bi_resource' ),
        'singular_name' => _x( 'BI Resource', 'bi_resource' ),
        'add_new' => _x( 'Add New', 'bi_resource' ),
        'all_items' => _x( 'BI Resources', 'bi_resource' ),
        'add_new_item' => _x( 'Add New BI Resource', 'bi_resource' ),
        'edit_item' => _x( 'Edit BI Resource', 'bi_resource' ),
        'new_item' => _x( 'New BI Resource', 'bi_resource' ),
        'view_item' => _x( 'View BI Resource', 'bi_resource' ),
        'search_items' => _x( 'Search BI Resources', 'bi_resource' ),
        'not_found' => _x( 'No bi resources found', 'bi_resource' ),
        'not_found_in_trash' => _x( 'No bi resources found in Trash', 'bi_resource' ),
        'parent_item_colon' => _x( 'Parent BI Resource:', 'bi_resource' ),
        'menu_name' => _x( 'BI Resources', 'bi_resource' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true
    );

    register_post_type( 'bi_resource', $args );
}

add_action( 'init', 'register_taxonomy_bi_categories' );

function register_taxonomy_bi_categories() {

    $labels = array( 
        'name' => _x( 'BI Categories', 'bi_categories' ),
        'singular_name' => _x( 'BI Category', 'bi_categories' ),
        'search_items' => _x( 'Search BI Categories', 'bi_categories' ),
        'popular_items' => _x( 'Popular BI Categories', 'bi_categories' ),
        'all_items' => _x( 'All BI Categories', 'bi_categories' ),
        'parent_item' => _x( 'Parent BI Category', 'bi_categories' ),
        'parent_item_colon' => _x( 'Parent BI Category:', 'bi_categories' ),
        'edit_item' => _x( 'Edit BI Category', 'bi_categories' ),
        'update_item' => _x( 'Update BI Category', 'bi_categories' ),
        'add_new_item' => _x( 'Add New BI Category', 'bi_categories' ),
        'new_item_name' => _x( 'New BI Category', 'bi_categories' ),
        'separate_items_with_commas' => _x( 'Separate bi categories with commas', 'bi_categories' ),
        'add_or_remove_items' => _x( 'Add or remove bi categories', 'bi_categories' ),
        'choose_from_most_used' => _x( 'Choose from the most used bi categories', 'bi_categories' ),
        'menu_name' => _x( 'BI Categories', 'bi_categories' ),
    );

    $args = array( 
        'labels' => $labels,
        'public' => true,
        'show_in_nav_menus' => true,
        'show_ui' => true,
        'show_tagcloud' => true,
        'show_admin_column' => false,
        'hierarchical' => true,

        'rewrite' => true,
        'query_var' => true
    );

    register_taxonomy( 'bi_categories', array('bi_resource'), $args );
}

function bi_resource_search_scripts() {
	wp_enqueue_script( 'bi_resource_search', plugin_dir_url(__FILE__) . '/js/bi-resource-search.js', array('jquery'), '1.0.0', true ); 
	wp_localize_script( 'bi_resource_search', 'MyAjax', array('ajaxurl' => admin_url('admin-ajax.php')));	
}

add_action( 'wp_ajax_bi_resource_search', 'bi_resource_search_callback' );
add_action( 'wp_ajax_nopriv_bi_resource_search', 'bi_resource_search_callback' );

function bi_resource_search_callback() {

	$bi_admins = array(
		"barbarom@missouri.edu",
		"barbaroe@missouri.edu",
		"vassmers@missouri.edu",
		"renoes@missouri.edu"
	);
	$current_user = wp_get_current_user();
	$current_email = $current_user->user_email;
	$allowed = false;
	if (in_array($current_email,$bi_admins)) {
		$allowed = true;
	}
	

	do_action("init");
	header('Content-type: application/json');
	
	$args = array(
		'post_type' => 'bi_resource',
		'posts_per_page' => -1,
		'post_status' => 'publish'
	);

	$keywordsearch = "";
	if (isset($_GET['keyword'])) {
		$keywordsearch = sanitize_text_field( $_GET['keyword'] );
		$args['s'] = $keywordsearch;
	}

	$filterbycampus = "";
	if (isset($_GET['campus'])) {
		if ( $_GET['campus'] != "" ) {
			$filterbycampus = sanitize_text_field( $_GET['campus'] );
			$args['meta_query'] = array(array('key' => 'campus','value' => $filterbycampus,'compare' => '='),);				
		}
	}	
	
	$filterbycat = "";
	if (isset($_GET['cat'])) {
		if ($_GET['cat'] != "") {
			$filterbycat = sanitize_text_field( $_GET['cat'] );
			$args['tax_query'] = array(array('taxonomy' => 'bi_categories','field' => 'slug','terms' => $filterbycat ),);			
		}
	}	
	
	//Check to see if user is admin. This will determine whether the user can edit a resource.
	//********CHANGE THIS TO SUIT THE ROLE OF SOMEONE WHO CAN EDIT RESOURCES, IF NEEDED*********	
	$admin_check="";
	if ($allowed) {
		$admin_check = "YES";
	} else {
		$admin_check = "NO";
	}
	
	$result = array();	
	
	
	$resource_query = new WP_Query( $args );
	
	while ( $resource_query->have_posts() ) {
		$resource_query->the_post();
		$postid = get_the_ID();
		$result[] = array(
			"id" => $postid,
			"title" => get_the_title(),
			"permalink" => get_permalink(),
			"desc" => get_the_content(),
			"contactname" => get_post_meta( $postid, 'contact_name' ),
			"campus" => get_post_meta( $postid, 'campus' ),
			"department" => get_post_meta( $postid, 'department' ),
			"phone" => get_post_meta( $postid, 'phone' ),
			"email" => get_post_meta( $postid, 'email' ),
			"streetaddress" => get_post_meta( $postid, 'street_address' ),
			"city" => get_post_meta( $postid, 'city' ),
			"state" => get_post_meta( $postid, 'state' ),
			"zipcode" => get_post_meta( $postid, 'zip_code' ),
			"link" => get_post_meta( $postid, 'link' ),
			"lat" => get_post_meta( $postid, 'bi_resource_lat' ),
			"lng" => get_post_meta( $postid, 'bi_resource_long' ),
			"admin" => $admin_check
		);
		
	}
	echo json_encode($result);	
	/* Restore original Post Data */
	wp_reset_postdata();
	wp_die();
}

function bi_google_maps_script() {
	
		wp_register_script( 'bi-google-maps', 'https://maps.googleapis.com/maps/api/js', array(), '1.0.0', true );
		//wp_register_script( 'bi-google-map-js', plugins_url('js/bi-google-map.js', __FILE__), array('bi-google-maps','jquery'));
	
}
add_action('wp_enqueue_scripts', 'bi_google_maps_script');

function bi_form_creation() {	  
	
	$bi_admins = array(
		"barbarom@missouri.edu",
		"barbaroe@missouri.edu",
		"vassmers@missouri.edu",
		"renoes@missouri.edu"
	);
	$current_user = wp_get_current_user();
	$current_email = $current_user->user_email;
	$allowed = false;
	if (in_array($current_email,$bi_admins)) {
		$allowed = true;
	}
	
	wp_enqueue_script( 'bi-google-maps' );
	//wp_enqueue_script( 'bi-google-map-js' );
	
	bi_resource_search_scripts();
	bi_resource_terms_create();
	ob_start();	

?>
	<div id="buttonsdiv" style="border-bottom:solid 1px #e0e0e0;height:40px;">
		<div style="float:left;"><button id="newsearch">New Search</button></div>
		<div style="float:right;margin-left:15px;"><button id="addnewresource">Add a Resource</button></div>
		<?php
			//********CHANGE THIS TO SUIT THE ROLE OF SOMEONE WHO CAN APPROVE RESOURCES, IF NEEDED*********
			if ($allowed) {
		?>
			<div style="float:right;margin-left:15px;"><button id="approveresource">Approve Resource(s)</button></div>
			<form id="recalc_form" name="recalc_form" action="" method="post" onsubmit="return alert('Map Coordinates have been re-calculated!')">
				<div style="float:right;"><input type="submit" id="runlatlongcalc" name="runlatlongcalc" value="Re-calculate Map Coordinates" /></div>
			</form>		
		<?php
			}
		?>		
	</div>
	
	<div id="searchdiv">
		<h2>Search Resources</h2>
		<form id="searchform" action="" method="get">
			<input type="text" id="keywordsearch" name="keywordsearch" placeholder="Search keywords" style="width:400px" /><br /><br />
			Filter by category:<br />
			<select id="filterbycat" name="filterbycat">
				<option selected value="">All categories</option>
				<option value="a-h">A/H</option>
				<option value="education">Education</option>
				<option value="evaluation">Evaluation</option>
				<option value="research">Research</option>
				<option value="k12">K12</option>
				<option value="diversity">Diversity</option>
				<option value="workforce-development">Workforce Development</option>
			</select><br /><br />
			Filter by campus:<br />
			<select id="filterbycampus" name="filterbycampus">
				<option selected value="">All campuses</option>
				<option value="MU">MU</option>
				<option value="UMSL">UMSL</option>
				<option value="MU S/T">MU S/T</option>
				<option value="UMKC">UMKC</option>
			</select><br /><br />
			<button type="submit">Search</button>
		</form>		
	</div>
	<div id="resultsdiv" style="display:none">
		<h2>Resource Results</h2>
		<div id="resultsfound"></div>		
		<div id="showmapdiv"><a id="showmap" href="javascript:void(0)">Map Results</a></div>
		<br />
		<ul id="resultlist"></ul>
	</div>
	<div id="mapdiv" style="display:none">
		<h2>Resource Locations</h2>
		<div id="map" style="width:950px;height:600px;"></div>
	</div>
	<div id="approvediv" style="display:none">
		<h2>Approve Resource(s)</h2>
		<?php		
			$approval_args = array(
				'post_type' => 'bi_resource',
				'posts_per_page' => -1,
				'post_status' => 'draft'
			);			
			$approval_query = new WP_Query( $approval_args );
			while ( $approval_query->have_posts() ) {
				$approval_query->the_post();
				$approval_postid = get_the_ID();
				$approval_title = get_the_title();
				$desc = get_the_content();
				$contactname = get_post_meta( $approval_postid, 'contact_name', true );
				$campus = get_post_meta( $approval_postid, 'campus', true );
				$department = get_post_meta( $approval_postid, 'department', true );
				$phone = get_post_meta( $approval_postid, 'phone', true );
				$email = get_post_meta( $approval_postid, 'email', true );
				$streetaddress = get_post_meta( $approval_postid, 'street_address', true );
				$city = get_post_meta( $approval_postid, 'city', true );
				$state = get_post_meta( $approval_postid, 'state', true );
				$zipcode = get_post_meta( $approval_postid, 'zip_code', true );
				$link = get_post_meta( $approval_postid, 'link', true );		
				echo "<div id='" . $approval_postid . "' style='padding:15px;border:solid 2px #a9a9a9;background-color:#FAF0E6;width:70%;max-width:70%;'><div style='float:right;'><button style='margin-left:5px;' onclick='approveResource(" . $approval_postid . ")'>Approve</button></div><div style='float:right;'><button style='margin-left:5px;' onclick='deleteResource(" . $approval_postid . ")'>Delete</button></div><strong style='font-size:14pt;'>" . $approval_title . "</strong><br /><strong>Description:</strong> " . $desc . "<br /><strong>Contact Name:</strong> " . $contactname . "<br /><strong>Campus:</strong> " . $campus . "<br /><strong>Department:</strong> " . $department . "<br /><strong>Phone:</strong> " . $phone . "<br /><strong>Email:</strong> <a href='mailto:" . $email . "'>" . $email . "</a><br /><strong>Street Address:</strong> " . $streetaddress . "<br /><strong>City:</strong> " . $city . "<br /><strong>State:</strong> " . $state . "<br /><strong>ZIP Code:</strong> " . $zipcode . "<br /><strong>Link:</strong> <a href='" . $link . "' target='_blank'>" . $link . "</a><br /></div><br />";					
				
			}	
		?>
	</div>
	<div id="formdiv" style="display:none">
		<h2 id="resource_form_title">Add a Resource</h2>
		<form id="resource_form" name="resource_form" action="" method="post" onsubmit="return alert('Thank you! The resource was submitted successfully!')">
			<input type="hidden" id="resource_id" name="resource_id" value="" />
		
			<strong>Resource Name:</strong><br /><input type="text" id="resource_name" name="resource_name" style="width:400px" /><br/><br/>
			<strong>Contact Name:</strong><br /><input type="text" id="contact_name" name="contact_name" style="width:400px" /><br /><br/>
			<strong>Campus:</strong><br />
				<input type="radio" name="campus" value="MU" />MU<br />
				<input type="radio" name="campus" value="UMSL" />UMSL<br />
				<input type="radio" name="campus" value="MU S/T" />MU S/T<br />
				<input type="radio" name="campus" value="UMKC" />UMKC<br /><br />
			<strong>Department:</strong><br /><input type="text" name="department" style="width:400px" /><br /><br/>
			<strong>Categories:</strong><br />
				<input type="checkbox" name="category[]" value="a-h" />A/H<br />
				<input type="checkbox" name="category[]" value="education" />Education<br />
				<input type="checkbox" name="category[]" value="evaluation" />Evaluation<br />
				<input type="checkbox" name="category[]" value="research" />Research<br />
				<input type="checkbox" name="category[]" value="k12" />K12<br />
				<input type="checkbox" name="category[]" value="diversity" />Diversity<br />
				<input type="checkbox" name="category[]" value="workforce-development" />Workforce Development		
			<br /><br />
			<strong>Phone:</strong><br /><input type="text" id="phone" name="phone" style="width:400px" /><br /><br/>
			<strong>Email:</strong><br /><input type="text" id="email" name="email" style="width:400px" /><br /><br/>
			<strong>Street Address:</strong><br /><input type="text" id="street_address" name="street_address" style="width:400px" /><br /><br/>
			<strong>City:</strong><br /><input type="text" id="city" name="city" style="width:400px" /><br /><br/>
			<strong>State:</strong><br />
			<select id="state" name="state">
				<option value="" selected>---Select---</option>		
				<option value="AL">AL</option>
				<option value="AK">AK</option>
				<option value="AZ">AZ</option>
				<option value="AR">AR</option>
				<option value="CA">CA</option>
				<option value="CO">CO</option>
				<option value="CT">CT</option>
				<option value="DE">DE</option>
				<option value="DC">DC</option>
				<option value="FL">FL</option>
				<option value="GA">GA</option>
				<option value="HI">HI</option>
				<option value="ID">ID</option>
				<option value="IL">IL</option>
				<option value="IN">IN</option>
				<option value="IA">IA</option>
				<option value="KS">KS</option>
				<option value="KY">KY</option>
				<option value="LA">LA</option>
				<option value="ME">ME</option>
				<option value="MD">MD</option>
				<option value="MA">MA</option>
				<option value="MI">MI</option>
				<option value="MN">MN</option>
				<option value="MS">MS</option>
				<option value="MO">MO</option>
				<option value="MT">MT</option>
				<option value="NE">NE</option>
				<option value="NV">NV</option>
				<option value="NH">NH</option>
				<option value="NJ">NJ</option>
				<option value="NM">NM</option>
				<option value="NY">NY</option>
				<option value="NC">NC</option>
				<option value="ND">ND</option>
				<option value="OH">OH</option>
				<option value="OK">OK</option>
				<option value="OR">OR</option>
				<option value="PA">PA</option>
				<option value="RI">RI</option>
				<option value="SC">SC</option>
				<option value="SD">SD</option>
				<option value="TN">TN</option>
				<option value="TX">TX</option>
				<option value="UT">UT</option>
				<option value="VT">VT</option>
				<option value="VA">VA</option>
				<option value="WA">WA</option>
				<option value="WV">WV</option>
				<option value="WI">WI</option>
				<option value="WY">WY</option>		
			</select>
			<br /><br/>
			<strong>ZIP Code:</strong><br /><input type="text" id="zip_code" name="zip_code" style="width:100px" /><br /><br/>
			<strong>Description:</strong><br /><textarea id="description" name="description" rows="4" cols="50"></textarea><br /><br/>
			<strong>Link:</strong><br /><input type="text" id="link" name="link" style="width:400px" /><br /><br/>
			<br /><br/><input type="submit" id="submit_resource_form" name="submit_resource_form" value="Submit" /><br /><br/>
		</form>
	</div>
	<style>
		#map img {max-width: none !important;}
	</style>	

<?php
	if ($_POST["submit_resource_form"]) {
		?>
			<div id="form_response"></div>
			

		<?php
			if (!empty($_POST['resource_name'])) {
				if (empty($_POST['resource_id'])) {	
						$post = array();
						//********CHANGE THIS TO SUIT THE ROLE OF SOMEONE WHO CAN APPROVE RESOURCES, IF NEEDED*********
						if ($allowed) {
							$post = array(
									'post_content' => $_POST['description'],
									'post_title' => $_POST['resource_name'],
									'post_status' => 'publish',
									'post_type' => 'bi_resource'
							);
						} else {
							$post = array(
									'post_content' => $_POST['description'],
									'post_title' => $_POST['resource_name'],
									'post_status' => 'draft',
									'post_type' => 'bi_resource'
							);						
						}
						$newresourceid = wp_insert_post( $post );

						if (!empty($_POST['contact_name'])) {
							add_post_meta($newresourceid, 'contact_name', $_POST['contact_name']);
						}
						if (!empty($_POST['campus'])) {
							add_post_meta($newresourceid, 'campus', $_POST['campus']);
						}		
						if (!empty($_POST['department'])) {
							add_post_meta($newresourceid, 'department', $_POST['department']);
						}
						if (!empty($_POST['phone'])) {
							add_post_meta($newresourceid, 'phone', $_POST['phone']);
						}
						if (!empty($_POST['email'])) {
							add_post_meta($newresourceid, 'email', $_POST['email']);
						}
						if (!empty($_POST['street_address'])) {
							add_post_meta($newresourceid, 'street_address', $_POST['street_address']);
						}
						if (!empty($_POST['city'])) {
							add_post_meta($newresourceid, 'city', $_POST['city']);
						}
						if (!empty($_POST['state'])) {
							add_post_meta($newresourceid, 'state', $_POST['state']);
						}
						if (!empty($_POST['zip_code'])) {
							add_post_meta($newresourceid, 'zip_code', $_POST['zip_code']);
						}
						if (!empty($_POST['link'])) {
							add_post_meta($newresourceid, 'link', $_POST['link']);
						}
						
						if(!empty($_POST['category'])) {					
							wp_set_object_terms( $newresourceid, $_POST['category'], 'bi_categories' );				
						}	
						
						//Set latitude and longitude 
						$strAddress = $_POST['street_address'] . " " . $_POST['city'] . " " . $_POST['state'] . " " . $_POST['zip_code'];
						if (strlen($strAddress) > 3) {
							$geo = bi_resource_geocode($strAddress);
							
							if (!empty($geo)) {
								$latitude = $geo['latitude'];
								$longitude = $geo['longitude'];

								add_post_meta($newresourceid, 'bi_resource_lat', $latitude);
								add_post_meta($newresourceid, 'bi_resource_long', $longitude);								
							}
						}
						//Email admins that a new resource has been created and needs approval. Only send if created by non-admin
						if (!$allowed) {
							//Add the appropriate emails here for those who are going to approve resources.
							$to = array(
								'barbarom@missouri.edu',
								'barbaroe@missouri.edu',
								'vassmers@missouri.edu',
								'renoes@missouri.edu'
							);
							$mailcontent = "The following resource needs your approval:<br /><br /><strong>" . $_POST['resource_name'] . "</strong>";							
							wp_mail( $to, 'A New Broader Impacts Resource Needs Approval', $mailcontent );
						}
					} else {
						$update_post_id = $_POST['resource_id'];
						$post = array(
								'ID' => $update_post_id,
								'post_content' => $_POST['description'],
								'post_title' => $_POST['resource_name']						
						);
							
						wp_update_post( $post );					

						if (!empty($_POST['contact_name'])) {
							update_post_meta($update_post_id, 'contact_name', $_POST['contact_name']);
						}
						if (!empty($_POST['campus'])) {
							update_post_meta($update_post_id, 'campus', $_POST['campus']);
						}		
						if (!empty($_POST['department'])) {
							update_post_meta($update_post_id, 'department', $_POST['department']);
						}
						if (!empty($_POST['phone'])) {
							update_post_meta($update_post_id, 'phone', $_POST['phone']);
						}
						if (!empty($_POST['email'])) {
							update_post_meta($update_post_id, 'email', $_POST['email']);
						}
						if (!empty($_POST['street_address'])) {
							update_post_meta($update_post_id, 'street_address', $_POST['street_address']);
						}
						if (!empty($_POST['city'])) {
							update_post_meta($update_post_id, 'city', $_POST['city']);
						}
						if (!empty($_POST['state'])) {
							update_post_meta($update_post_id, 'state', $_POST['state']);
						}
						if (!empty($_POST['zip_code'])) {
							update_post_meta($update_post_id, 'zip_code', $_POST['zip_code']);
						}
						if (!empty($_POST['link'])) {
							update_post_meta($update_post_id, 'link', $_POST['link']);
						}
						
						if(!empty($_POST['category'])) {					
							wp_set_object_terms( $update_post_id, $_POST['category'], 'bi_categories' );				
						}
						
						//Set latitude and longitude 
						$strAddress = $_POST['street_address'] . " " . $_POST['city'] . " " . $_POST['state'] . " " . $_POST['zip_code'];
						if (strlen($strAddress) > 3) {
							$geo = bi_resource_geocode($strAddress);
							
							if (!empty($geo)) {
								$latitude = $geo['latitude'];
								$longitude = $geo['longitude'];
								//var_dump($geo['latitude']);
								update_post_meta($update_post_id, 'bi_resource_lat', $latitude);
								update_post_meta($update_post_id, 'bi_resource_long', $longitude);								
							}
						}						
					}
			}
	}
	
	if ($_POST["runlatlongcalc"]) {
	
		$result = array();

		$args=array(
		  'post_type' => 'bi_resource',
		  'post_status' => 'publish',
		  'posts_per_page' => -1
		);
		$my_query = new WP_Query($args);
		if( $my_query->have_posts() ) {
		  while ($my_query->have_posts()) : $my_query->the_post(); 
			$postid = get_the_ID();
			$lat = get_post_meta($postid,'bi_resource_lat',true);
			$street = get_post_meta($postid,'street_address',true);
			$city = get_post_meta($postid,'city',true);
			$state = get_post_meta($postid,'state',true);
			$zip = get_post_meta($postid,'zip_code',true);
			
			

			
			
			if (empty($lat)) {
				//Set latitude and longitude 
				$strAddress = $street . " " . $city . " " . $state . " " . $zip;				
				
				if (strlen($strAddress) > 3) {		
				
					$geo = bi_resource_geocode($strAddress);					
					//var_dump($geo);
					if (!empty($geo)) {					
					
						$latitude = $geo['latitude'];
						$longitude = $geo['longitude'];

						update_post_meta($postid, 'bi_resource_lat', $latitude);
						update_post_meta($postid, 'bi_resource_long', $longitude);	
					
					} 
				}		
			}
		  endwhile;
		}
		wp_reset_query();	

	}
	
	return ob_get_clean();
}
add_shortcode('bi_resource_form', 'bi_form_creation');

// function to geocode address, it will return false if unable to geocode address
function bi_resource_geocode($address){ 

    //$url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address2}&key=AIzaSyDzsrDVBlOfzDeyAGpJO35qdOEFKIgT9ZA"; 

	
   $address = str_replace (" ", "+", urlencode($address));
   $details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$address."&sensor=false";
 
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $details_url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $response = json_decode(curl_exec($ch), true);
 
   // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
   if ($response['status'] != 'OK') {
    return null;
   }
 
   //print_r($response);
   $geometry = $response['results'][0]['geometry'];
 
    $longitude = $geometry['location']['lng'];
    $latitude = $geometry['location']['lat'];
 
    $array = array(
        'latitude' => $geometry['location']['lat'],
        'longitude' => $geometry['location']['lng'],
        'location_type' => $geometry['location_type'],
    );
 
    return $array;	

}


function bi_resource_terms_create() {
	//If the terms)categories) DO NOT exist (==0) then create them.
	if (term_exists('A & H','bi_categories') == 0) {
		wp_insert_term('A & H','bi_categories');
	}
	if (term_exists('Education','bi_categories') == 0) {
		wp_insert_term('Education','bi_categories');
	}
	if (term_exists('Evaluation','bi_categories') == 0) {
		wp_insert_term('Evaluation','bi_categories');
	}
	if (term_exists('Research','bi_categories') == 0) {
		wp_insert_term('Research','bi_categories');
	}
	if (term_exists('K12','bi_categories') == 0) {
		wp_insert_term('K12','bi_categories');
	}
	if (term_exists('Diversity','bi_categories') == 0) {
		wp_insert_term('Diversity','bi_categories');
	}
	if (term_exists('Workforce Development','bi_categories') == 0) {
		wp_insert_term('Workforce Development','bi_categories');
	}	
}



add_action( 'wp_ajax_bi_resource_edit', 'bi_resource_edit_callback' );
add_action( 'wp_ajax_nopriv_bi_resource_edit', 'bi_resource_edit_callback' );

function bi_resource_edit_callback() {
	do_action("init");
	header('Content-type: application/json');
	
	$result = array();	
	
	$queried_post = get_post($_POST['id']);
	$postid = $_POST['id'];
	
	$resource_terms = wp_get_object_terms( $postid,  'bi_categories' );
	// if ( ! empty( $resource_terms ) ) {
		// if ( ! is_wp_error( $resource_terms ) ) {
			// foreach( $resource_terms as $term ) {
				// echo '<li><a href="' . get_term_link( $term->slug, 'bi_categories' ) . '">' . esc_html( $term->name ) . '</a></li>'; 
			// }
		// }
	// }	
	
	$result[] = array(
		"id" => $postid,
		"title" => $queried_post->post_title,
		"desc" => $queried_post->post_content,
		"contactname" => get_post_meta( $postid, 'contact_name' ),
		"campus" => get_post_meta( $postid, 'campus' ),
		"department" => get_post_meta( $postid, 'department' ),
		"phone" => get_post_meta( $postid, 'phone' ),
		"email" => get_post_meta( $postid, 'email' ),
		"streetaddress" => get_post_meta( $postid, 'street_address' ),
		"city" => get_post_meta( $postid, 'city' ),
		"state" => get_post_meta( $postid, 'state' ),
		"zipcode" => get_post_meta( $postid, 'zip_code' ),
		"link" => get_post_meta( $postid, 'link' ),
		"categories" => $resource_terms
	);
		
	
	echo json_encode($result);	
	wp_die();	

}

add_action( 'wp_ajax_bi_resource_approve', 'bi_resource_approve_callback' );
add_action( 'wp_ajax_nopriv_bi_resource_approve', 'bi_resource_approve_callback' );

function bi_resource_approve_callback() {
	do_action("init");
	header('Content-type: application/json');
	
	$result = array();

	$post_id = $_POST['id'];	
	wp_publish_post( $post_id );
	
	$queried_post = get_post($_POST['id']);	
	
	$result[] = array(
		"id" => $post_id,
		"title" => $queried_post->post_title,
		"desc" => $queried_post->post_content,
		"contactname" => get_post_meta( $post_id, 'contact_name' ),
		"campus" => get_post_meta( $post_id, 'campus' ),
		"department" => get_post_meta( $post_id, 'department' ),
		"phone" => get_post_meta( $post_id, 'phone' ),
		"email" => get_post_meta( $post_id, 'email' ),
		"streetaddress" => get_post_meta( $post_id, 'street_address' ),
		"city" => get_post_meta( $post_id, 'city' ),
		"state" => get_post_meta( $post_id, 'state' ),
		"zipcode" => get_post_meta( $post_id, 'zip_code' ),
		"link" => get_post_meta( $post_id, 'link' )
	);
	
	echo json_encode($result);	
	wp_die();
}

add_action( 'wp_ajax_bi_resource_delete', 'bi_resource_delete_callback' );
add_action( 'wp_ajax_nopriv_bi_resource_delete', 'bi_resource_delete_callback' );

function bi_resource_delete_callback() {
	do_action("init");
	header('Content-type: application/json');
	
	$result = array();

	$post_id = $_POST['id'];	
	
	$queried_post = get_post($_POST['id']);	
	wp_delete_post( $post_id );
	
	$result[] = array(
		"id" => $post_id,
		"title" => $queried_post->post_title
	);
	
	echo json_encode($result);	
	wp_die();
}

add_action( 'wp_ajax_bi_resource_disapprove', 'bi_resource_disapprove_callback' );
add_action( 'wp_ajax_nopriv_bi_resource_disapprove', 'bi_resource_disapprove_callback' );

function bi_resource_disapprove_callback() {
	do_action("init");
	header('Content-type: application/json');
	
	$result = array();

	$post_id = $_POST['id'];	
	
	$queried_post = get_post($_POST['id']);	

	$my_post = array(
      'ID'           => $post_id,      
      'post_status' => 'draft'
	);

	// Update the post into the database
	  wp_update_post( $my_post );
	
	$result[] = array(
		"title" => $queried_post->post_title,
		"id" => $post_id,
		"desc" => $queried_post->post_content,
		"contactname" => get_post_meta( $post_id, 'contact_name' ),
		"campus" => get_post_meta( $post_id, 'campus' ),
		"department" => get_post_meta( $post_id, 'department' ),
		"phone" => get_post_meta( $post_id, 'phone' ),
		"email" => get_post_meta( $post_id, 'email' ),
		"streetaddress" => get_post_meta( $post_id, 'street_address' ),
		"city" => get_post_meta( $post_id, 'city' ),
		"state" => get_post_meta( $post_id, 'state' ),
		"zipcode" => get_post_meta( $post_id, 'zip_code' ),
		"link" => get_post_meta( $post_id, 'link' )
	);
	
	echo json_encode($result);	
	wp_die();
}



	

	


?>