$ = jQuery;
var lat_lng = [];
var markers = [];
var map;
	// $( document ).ready(function() {
		// $("#map img").css({'max-width':'none'});
	// });

	$( "#addnewresource" ).click(function() {
		$("#searchdiv").hide();
		$("#formdiv").show();
		$("#mapdiv").hide();
		$("#resultsdiv").hide();
		$("#approvediv").hide();
	});
	$( "#newsearch" ).click(function() {
		$("#resultsdiv").hide();
		$("#formdiv").hide();
		$("#mapdiv").hide();
		$("#searchdiv").show();
		$("#approvediv").hide();
	});	
	$( "#approveresource" ).click(function() {
		$("#searchdiv").hide();
		$("#formdiv").hide();
		$("#mapdiv").hide();
		$("#resultsdiv").hide();
		$("#approvediv").show();
	});

	$("#searchform").submit(function(e){
		
		e.preventDefault();
		$("#resultsdiv").show();
		$("#searchdiv").hide();
		var data = {
			action : "bi_resource_search",
			keyword : $("#keywordsearch").val(),			
			campus : $("#filterbycampus").val(),
			cat : $("#filterbycat").val()
		}
		
		
		$.ajax({

			url: MyAjax.ajaxurl,
			data: data,
			success: function(response) {
				lat_lng = [];
				$("#resultlist").empty();
				var count = 0;
				var html;			
				
				for(var i = 0; i < response.length ; i++) {
					count = count + 1;
					if (response[i].admin == "YES") {
						html = "<div id='div_" + response[i].id + "' style='padding:15px;border:solid 2px #a9a9a9;background-color:#E0EEEE;width:70%;max-width:70%;'><div style='float:right;'><button onclick='editResource(" + response[i].id + ")'>Edit</button> <button onclick='disapproveResource(" + response[i].id + ")'>Disapprove</button> <button onclick='deleteResource(" + response[i].id + ")'>Delete</button></div><strong style='font-size:14pt;'>" + response[i].title + "</strong><br /><strong>Description:</strong> " + response[i].desc + "<br /><strong>Contact Name:</strong> " + response[i].contactname + "<br /><strong>Campus:</strong> " + response[i].campus + "<br /><strong>Department:</strong> " + response[i].department + "<br /><strong>Phone:</strong> " + response[i].phone + "<br /><strong>Email:</strong> <a href='mailto:" + response[i].email + "'>" + response[i].email + "</a><br /><strong>Street Address:</strong> " + response[i].streetaddress + "<br /><strong>City:</strong> " + response[i].city + "<br /><strong>State:</strong> " + response[i].state + "<br /><strong>ZIP Code:</strong> " + response[i].zipcode + "<br /><strong>Link:</strong> <a href='" + response[i].link + "' target='_blank'>" + response[i].link + "<br /></div><br />";					
					} else {
						html = "<div id='div_" + response[i].id + "' style='padding:15px;border:solid 2px #a9a9a9;background-color:#E0EEEE;width:70%;max-width:70%;'><strong style='font-size:14pt;'>" + response[i].title + "</strong><br /><strong>Description:</strong> " + response[i].desc + "<br /><strong>Contact Name:</strong> " + response[i].contactname + "<br /><strong>Campus:</strong> " + response[i].campus + "<br /><strong>Department:</strong> " + response[i].department + "<br /><strong>Phone:</strong> " + response[i].phone + "<br /><strong>Email:</strong> <a href='mailto:" + response[i].email + "'>" + response[i].email + "</a><br /><strong>Street Address:</strong> " + response[i].streetaddress + "<br /><strong>City:</strong> " + response[i].city + "<br /><strong>State:</strong> " + response[i].state + "<br /><strong>ZIP Code:</strong> " + response[i].zipcode + "<br /><strong>Link:</strong> <a href='" + response[i].link + "' target='_blank'>" + response[i].link + "<br /></div><br />";					
					}					
					$("#resultlist").append(html);
					if (response[i].lat != "") {
						lat_lng.push({
							name: response[i].title,
							desc: response[i].desc,
							link: response[i].link,
							lat: parseFloat(response[i].lat),
							lng: parseFloat(response[i].lng)
						});
						
					}
				}
				$("#resultsfound").html( count + " results found");
				markers=[];
				//console.log(lat_lng);
			},
			error: function(xhr, status, error) {
				console.log(error);
			}			
		});
		
	});
	
	$("#resource_form").submit(function(e){
		// e.preventDefault();
		// $("#formdiv").hide();
	});

	
	function editResource(resourceid) {
		$("#resource_id").val(resourceid);
		$("#resultsdiv").hide();	
		$("#formdiv").show();
		$("#resource_form_title").html("Edit a Resource");
		
		var data = {
			action: 'bi_resource_edit',
			id: resourceid
		};		
		
		$.post(MyAjax.ajaxurl, data, function(response) {	
			for(var i = 0; i < response.length ; i++) {	
				//console.log(response[i].desc);
				
				$("#resource_name").val(response[i].title);
				$("#contact_name").val(response[i].contactname[0]);
				$("input[name=campus][value=" + response[i].campus[0] + "]").prop('checked', true);
				$("#department").val(response[i].department[0]);
				var cats = response[i].categories;
				for(var c = 0; c < cats.length ; c++) {					
					$("input[name='category[]'][value=" + cats[c].slug + "]").prop('checked', true);
				}
				$("#phone").val(response[i].phone[0]);
				$("#email").val(response[i].email[0]);
				$("#street_address").val(response[i].streetaddress[0]);
				$("#city").val(response[i].city[0]);
				$("#state").val(response[i].state[0]);
				$("#zip_code").val(response[i].zipcode[0]);
				$("#description").val(response[i].desc);
				$("#link").val(response[i].link[0]);
			}
		});			
	}
	
	function approveResource(resourceid) {
		var data = {
			action: 'bi_resource_approve',
			id: resourceid
		};		
		
		$.post(MyAjax.ajaxurl, data, function(response) {	
			for(var i = 0; i < response.length ; i++) {				
				alert("The '" + response[i].title + "' resource has been approved!");
				$("#" + response[i].id).hide();				
			}
		});	

	}	

	function deleteResource(resourceid) {
		var r = confirm("Are you sure you want to delete this resource?");
		if (r == true) {
				
			var data = {
				action: 'bi_resource_delete',
				id: resourceid
			};		
			
			$.post(MyAjax.ajaxurl, data, function(response) {	
				for(var i = 0; i < response.length ; i++) {				
					alert("The '" + response[i].title + "' resource has been deleted!");
					$("#div_" + response[i].id).hide();	
					$("#" + response[i].id).hide();						
				}
			});	
		} else {
			return false;
		}
	}	
	
	function disapproveResource(resourceid) {
				
			var data = {
				action: 'bi_resource_disapprove',
				id: resourceid
			};		
			
			$.post(MyAjax.ajaxurl, data, function(response) {	
				for(var i = 0; i < response.length ; i++) {				
					alert("The '" + response[i].title + "' resource has been disapproved!");
					$("#div_" + response[i].id).hide();
					$("#approvediv").append("<div id='" + response[i].id + "' style='padding:15px;border:solid 2px #a9a9a9;background-color:#FAF0E6;width:70%;max-width:70%;'><div style='float:right;'><button style='margin-left:5px;' onclick='approveResource(" + response[i].id + ")'>Approve</button></div><div style='float:right;'><button style='margin-left:5px;' onclick='deleteResource(" + response[i].id + ")'>Delete</button></div><strong style='font-size:14pt;'>" + response[i].title + "</strong><br /><strong>Description:</strong> " + response[i].desc + "<br /><strong>Contact Name:</strong> " + response[i].contactname + "<br /><strong>Campus:</strong> " + response[i].campus + "<br /><strong>Department:</strong> " + response[i].department + "<br /><strong>Phone:</strong> " + response[i].phone + "<br /><strong>Email:</strong> <a href='mailto:" + response[i].email + "'>" + response[i].email + "</a><br /><strong>Street Address:</strong> " + response[i].streetaddress + "<br /><strong>City:</strong> " + response[i].city + "<br /><strong>State:</strong> " + response[i].state + "<br /><strong>ZIP Code:</strong> " + response[i].zipcode + "<br /><strong>Link:</strong> <a href='" + response[i].link + "' target='_blank'>" + response[i].link + "</a><br /></div><br />");					
						
				}
			});	
		
	}	
	
	$( "#showmap" ).click(function(e) {
		e.preventDefault();
		$("#resultsdiv").hide();
		$("#mapdiv").show();		
		
		initialize();
		
		google.maps.event.trigger(map, 'resize');

		$("#map img").css({'max-width':'none !important'});

	});	

	function initialize() {		
			
            for (var i = 0; i < markers.length; i++) {
                markers[i].setMap(null);
            }
            markers.length = 0;		
		
			var mapCanvas = document.getElementById('map');
			var mapOptions = {
			  center: { lat: 38.5, lng: -92 },
			  zoom: 7,
			  mapTypeId: google.maps.MapTypeId.ROADMAP			 
			}
			map = new google.maps.Map(mapCanvas, mapOptions);
			
			markers = lat_lng;
			
			var infowindow = new google.maps.InfoWindow();

			// Loop through our array of markers & place each one on the map  
			for( i = 0; i < markers.length; i++ ) {
				
				var position = new google.maps.LatLng(markers[i].lat, markers[i].lng);	
				
				marker = new google.maps.Marker({
					position: position,
					map: map,
					title: markers[i].name
				});	
				
				google.maps.event.addListener(marker, 'click', (function(marker, i) {
				return function() {
				  infowindow.setContent("<h3>" + markers[i].name + "</h3><p>" + markers[i].desc + "</p><p><a href='" + markers[i].link + "' target='_blank'>" + markers[i].link + "</a></p>");
				  infowindow.open(map, marker);
				}
				})(marker, i));
			
			}			
			
	}
	

