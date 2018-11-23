(function( $ ) {
	$(function() {

		function get_data(name){
		    name = name || 'misc';
		    return $('body').data(name);
		}

		function set_data(data, name){
		    data = data || '';
		    if(data != ''){
		        name = name || 'misc';
		        $('body').data(name, data);
		    }
		}

		Date.prototype.days=function(to){
		  return  Math.abs(Math.floor( to.getTime() / (3600*24*1000)) -  Math.floor( this.getTime() / (3600*24*1000)))
		}

		function dayDiff(startdate, enddate) {
		  return new Date(startdate).days(new Date(enddate));
		}

		function count_nights(scope, startdate, enddate){
			//console.log(startdate+' - '+enddate+' = '+dayDiff(startdate, enddate));
			//console.log(startdate+' < '+enddate);
			if(startdate.valueOf() < enddate.valueOf())
				$('[name="nights"]', scope).val(dayDiff(startdate, enddate));
			else
				$('[name="nights"]', scope).val('');
		}

		function formatDate(date) {
			var dd = date.getDate();
		    var mm = date.getMonth()+1; //January is 0!
		    var yyyy = date.getFullYear();

		    if(dd<10){
		        dd='0'+dd
		    } 
		    if(mm<10){
		        mm='0'+mm
		    } 

		    return mm + '/' + dd + '/' + yyyy;
		}

		function addDays(date, days) {
		    var result = new Date(date); //console.log(date.getDate()+' + '+days);
		    result.setDate(date.getDate() + days);
		    //console.log(result);
		    return result;
		}

		function getUrlParams(url) {
	        var params = {};
	        url.substring(1).replace(/[?&]+([^=&]+)=([^&]*)/gi,
	                function (str, key, value) {
	                     params[key] = value;
	                });
	        return params;
		}


		//console.log(formatDate(addDays(new Date('08/20/2015'), 5)));

		function checkInOut_datepicker(scope, el_checkin, el_checkout){
			//el_on_scope untuk mendeteksi elemen mana yg akan digunakan oleh datepicker
			el_checkin = el_checkin || '.checkin';
			el_checkout = el_checkout || '.checkout';
			
			//var scope = $(el_checkin).parents('form');

			var nowTemp = new Date();
			var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);


			var checkin_date_updated = now;
			var checkin = $(el_checkin, scope).fdatepicker({
				onRender: function (date) {
					return date.valueOf() < now.valueOf() ? 'disabled' : ''; 
				}
			}).on('changeDate', function (ev) {
				var scope_inside = $(this).parents('form');
				//console.log(checkout);
				checkin_date_updated = ev.date;
				checkin.date = checkin_date_updated
				if (ev.date.valueOf() > checkout.date.valueOf()) {
					var newDate = new Date(ev.date); 
					newDate.setDate(newDate.getDate() + 1); 
					$('.checkout', scope_inside).val(formatDate(newDate));
					//checkout.update(newDate);// ga bisa dipakai jika dalam satu halaman ada 2 form date range
				}
				//console.log('masuk sini checkin');
				//console.log(checkout.date);
				count_nights(scope_inside, ev.date,checkout.date);// update nights
				
				checkin.hide();

				var scope_inside = $(this).parents('form');
				$(el_checkout, scope_inside).focus();//[0].focus();

				//console.log('update checkin date '+checkin_date_updated+' sebelumnya '+checkin.date);
			}).data('datepicker');
			var checkout = $(el_checkout, scope).fdatepicker({
				onRender: function (date) {
					var next_fewdays = addDays(checkin.date, 30);//default 30
					//console.log('checkin : '+ checkin.date.valueOf() + ' , next_fewdays : '+next_fewdays.valueOf());
					//console.log(next_fewdays.valueOf());
					//|| ( date.valueOf() > next_fewdays.valueOf()
						//--var scope_inside = $(this).parents('form');
					//--var checkin_date_updated = $('.checkin', scope_inside).val();
					//checkin_date_updated = new Date(checkin_date_updated); 
					//console.log(checkin_date_updated);	
					//return date.valueOf() <= checkin.date.valueOf() ||  date.valueOf() > next_fewdays.valueOf() ? 'disabled' : '';
					return date.valueOf() <= checkin_date_updated ||  date.valueOf() > next_fewdays.valueOf() ? 'disabled' : '';
				}
			}).on('changeDate', function (ev) {
				var scope_inside = $(this).parents('form');
				//console.log('checkout : '+checkin.date+' '+ev.date);
				//console.log('masuk sini checkout');
				count_nights(scope_inside, checkin.date, ev.date);// update nights
				checkout.hide();
			}).data('datepicker');
		}

		if($('.nights').length > 0 ){
			$('.nights').change(function(){
				var scope = $(this).parents('form');
				var nights = Number($(this).val());
				var checkin_date = $('.checkin', scope).val();
				var checkout_date_new = '';
				if(checkin_date != ''){
					checkin_date = new Date(checkin_date);
					//console.log(formatDate(addDays(new Date('08/20/2015'),nights)));
					checkout_date_new = formatDate(addDays(checkin_date, nights));
				}
				$('.checkout', scope).val(checkout_date_new);
			});
		}

		$('.fdatepicker').fdatepicker({
			onRender: function (date) {
					var nowTemp = new Date();
					var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
					return date.valueOf() < now.valueOf() ? 'disabled' : ''; 
				}
		});

		$('.fdatepickertrip').fdatepicker({
			minView: 'year',
			format: 'yyyy-mm',
			startView: 'year'
		});

		$('.fdatepickerDOB').fdatepicker({
			format: 'yyyy-mm-dd',
			initialDate: new Date('02-12-1989'),
			disableDblClickSelection: true,
			leftArrow:'<<',
			rightArrow:'>>'
		});
		

		$('.special-offers-carousel').slick({
			dots: false,
			infinite: true,
			speed: 300,
			slidesToShow: 4, 
			slidesToScroll: 1,
			responsive: [
		    {
		      breakpoint: 1024,
		      settings: {
		        slidesToShow: 3,
		        slidesToScroll: 3,
		        infinite: true,
		        dots: true
		      }
		    },
		    {
		      breakpoint: 600,
		      settings: {
		        slidesToShow: 2,
		        slidesToScroll: 2
		      }
		    },
		    {
		      breakpoint: 480,
		      settings: {
		        slidesToShow: 1,
		        slidesToScroll: 1
		      }
		    }
		    // You can unslick at a given breakpoint now by adding:
		    // settings: "unslick"
		    // instead of a settings object
		  ]
		});

		$('.peek-gallery-preview').slick({
		  dots: true,
		  infinite: true,
		  speed: 300,
		  slidesToShow: 9, //make sure ini kurang lebih sama dengan yg nampak di peek nya
		  slidesToScroll: 3,
		  //centerMode: true,
		  variableWidth: true
		});

		$.each($('.checkin'), function(){
			var scope = $(this).parents('form');
			checkInOut_datepicker(scope);
		});
		
		//if($('.checkin2').length > 0)
		//	checkInOut_datepicker('.checkin2', '.checkout2');

		$('.has-form, .has-form input, .has-form select').click(function(e){
			e.stopPropagation();
		});

		$(document).on('click', '.autocomplete-suggestion, .autocomplete-no-suggestion', function(e){
			var active_dropdown_form = get_data('active_dropdown_form');
			$('.dropdown-form.'+active_dropdown_form).parents('li').addClass('hover');
		});

		$( "a.orbit-prev > span " ).replaceWith( "<span><i class='fa fa-chevron-left'></i></span>" );
		$( "a.orbit-next > span" ).replaceWith( "<span><i class='fa fa-chevron-right'></i></span>" );

		//room info
		$('.room-info').on('click', function(e){
			e.preventDefault();
			var $el = $(this);
			var $tr = $el.parents('tr');
			var $target = $('tr.'+$el.attr('tr-expand'));
			if($target.hasClass('hide')){
				$target.removeClass('hide');
				$tr.addClass('expanded');
				$el.find('i').removeClass('fa-caret-right').addClass('fa-caret-down');
			}else{
				$target.addClass('hide');
				$tr.removeClass('expanded');
				$el.find('i').removeClass('fa-caret-down').addClass('fa-caret-right');
			}
		});

		//add to cart
		$('.add-to-cart').bind('click', function(e){
			var post_type = $('.choose-room-type-rate-wrapper').attr('post-type');
			
	      	var $el = $(this);
	      	var scope = $el.parents('tr');
	      	var sku_id = $('[name="variation_id"]', scope).val();
	      	var data_default = {
	      		'post_type' : post_type,
			    'variation_id' : sku_id
	      	};

	      	switch(post_type){
	      		case 'hotel' :
	      			var num_of_rooms = $('[name="no_of_rooms"]', scope).val();
			      	var checkin = $('#nights_form [name="checkin"]').val();
			      	var checkout = $('#nights_form [name="checkout"]').val();
			      	var hotel_url = $('#nights_form [name="hotel_url"]').val();
			      	var data = $.extend(data_default, {
			      		'no_of_rooms' : num_of_rooms,
			      		'checkin' : checkin,
			      		'checkout' : checkout,
			      		'hotel_url' : hotel_url
			      	});
	      		break;
	      		case 'playground' :
	      			var no_of_people = $('[name="no_of_people"]', scope).val();//qty
			      	var playground_visit_date = $('#nights_form [name="playground_visit_date"]').val();
			      	var playground_url = $('#nights_form [name="playground_url"]').val();
			      	var data = $.extend(data_default, {
			      		'no_of_people' : no_of_people,
			      		'playground_visit_date' : playground_visit_date,
			      		'playground_url' : playground_url
			      	});
	      		break;
	      		case 'trip' :
	      			var no_of_people_t = $('[name="no_of_people_t"]', scope).val();//qty
			      	var trip_visit_date = $('#nights_form [name="trip_visit_date"]').val();
			      	var start_date = $('[name="start_date1"]').val();
			      	var end_date = $('[name="end_date"]').val();
			      	var trip_url = $('#nights_form [name="trip_url"]').val();
			      	var max = $('#nights_form [name="max"]').val();
			      	var data = $.extend(data_default, {
			      		'no_of_people_t' : no_of_people_t,
			      		'trip_visit_date' : trip_visit_date,
			      		'start_date' : start_date,
			      		'end_date' : end_date,
			      		'trip_url' : trip_url,
			      		'max_p' : max
			      	});
	      		break;
	      		case 'merchant' :
	      			var no_of_people = $('[name="no_of_people"]', scope).val();//qty
			      	var merchant_visit_date = $('[name="merchant_visit_date"]').val();
			      	var merchant_url = $('[name="merchant_url"]').val();
			      	var data = $.extend(data_default, {
			      		'no_of_people' : no_of_people,
			      		'merchant_visit_date' : merchant_visit_date,
			      		'merchant_url' : merchant_url
			      	});
	      		break;
	      	}
	      	
	      	$.get(site.base_url+'/add-to-cart', data, function( res ) { //console.log(res)
	      		if(res['redirect_url'] != undefined){
		      		window.location.href = res;
		      		//check valid checkin checkout date
		      		$("#nights_form").submit();
		      		return false;
		      	}
		      	/*
		      	if(res.success == 0){
	      			form_error(res.errors, scope);
	      			return false;
	      		}*/

		      	if(res['notification'] != undefined){
		      		$('.choose-room-type-rate-wrapper').prepend(res['notification']);
		      		setTimeout(function () {
		      		 $('.alert-box').fadeOut().remove(); 
		      		}, 5000);
		      	}
	      	});
	    });


	    //autocomplete find hotel
		//var nhlTeams = ['Anaheim Ducks', 'Atlanta Thrashers', 'Boston Bruins', 'Buffalo Sabres', 'Calgary Flames', 'Carolina Hurricanes', 'Chicago Blackhawks', 'Colorado Avalanche', 'Columbus Blue Jackets', 'Dallas Stars', 'Detroit Red Wings', 'Edmonton OIlers', 'Florida Panthers', 'Los Angeles Kings', 'Minnesota Wild', 'Montreal Canadiens', 'Nashville Predators', 'New Jersey Devils', 'New Rork Islanders', 'New York Rangers', 'Ottawa Senators', 'Philadelphia Flyers', 'Phoenix Coyotes', 'Pittsburgh Penguins', 'Saint Louis Blues', 'San Jose Sharks', 'Tampa Bay Lightning', 'Toronto Maple Leafs', 'Vancouver Canucks', 'Washington Capitals'];
	    //var nbaTeams = ['Atlanta Hawks', 'Boston Celtics', 'Charlotte Bobcats', 'Chicago Bulls', 'Cleveland Cavaliers', 'Dallas Mavericks', 'Denver Nuggets', 'Detroit Pistons', 'Golden State Warriors', 'Houston Rockets', 'Indiana Pacers', 'LA Clippers', 'LA Lakers', 'Memphis Grizzlies', 'Miami Heat', 'Milwaukee Bucks', 'Minnesota Timberwolves', 'New Jersey Nets', 'New Orleans Hornets', 'New York Knicks', 'Oklahoma City Thunder', 'Orlando Magic', 'Philadelphia Sixers', 'Phoenix Suns', 'Portland Trail Blazers', 'Sacramento Kings', 'San Antonio Spurs', 'Toronto Raptors', 'Utah Jazz', 'Washington Wizards'];
	    //var nhl = $.map(nhlTeams, function (team) { return { value: team, data: { category: 'NHL' }}; });
	    //var nba = $.map(nbaTeams, function (team) { return { value: team, data: { category: 'NBA' } }; });
	    //var teams = nhl.concat(nba);

	    // Initialize autocomplete with local lookup:
	    $('[name="destination"]').autocomplete({
	        serviceUrl: site.base_url+'/autocomplete/destination',
	        minChars: 2,
	        params : {
	        	post_type : 'hotel'
	        },
	        onSearchComplete: function (query, suggestions) {
	        	$('[name="search_by"]').val('');
	        	$('[name="search_slug"]').val('');
	        },
	        onSelect: function (suggestion) {
	            //$('#selection').html('You selected: ' + suggestion.value + ', ' + suggestion.data.category);
	            $('[name="search_by"]').val(suggestion.data.search_by);
	            $('[name="search_slug"]').val(suggestion.data.search_slug);
	        },
	        showNoSuggestionNotice: true,
	        noSuggestionNotice: 'Sorry, no matching results',
	        groupBy: 'category',
	        onHide: function (container) {
	        	set_data('hotel', 'active_dropdown_form');
	        }
	    });

	    $('[name="playground_destination"]').autocomplete({
	        serviceUrl: site.base_url+'/autocomplete/destination',
	        minChars: 2,
	        params : {
	        	post_type : 'playground'
	        },
	        onSearchComplete: function (query, suggestions) {
	        	$('[name="search_by"]').val('');
	        	$('[name="search_slug"]').val('');
	        },
	        onSelect: function (suggestion) {
	            //$('#selection').html('You selected: ' + suggestion.value + ', ' + suggestion.data.category);
	            $('[name="search_by"]').val(suggestion.data.search_by);
	            $('[name="search_slug"]').val(suggestion.data.search_slug);
	            $('.dropdown-form.playground').parents('li').addClass('hover');
	        },
	        showNoSuggestionNotice: true,
	        noSuggestionNotice: 'Sorry, no matching results',
	        groupBy: 'category',
	        onHide: function (container) {
	        	set_data('playground', 'active_dropdown_form');
	        }
	    });

	    $('[name="trip_destination"]').autocomplete({
	        serviceUrl: site.base_url+'/autocomplete/destination',
	        minChars: 2,
	        params : {
	        	post_type : 'trip'
	        },
	        onSearchComplete: function (query, suggestions) {
	        	$('[name="search_by"]').val('');
	        	$('[name="search_slug"]').val('');
	        },
	        onSelect: function (suggestion) {
	            //$('#selection').html('You selected: ' + suggestion.value + ', ' + suggestion.data.category);
	            $('[name="search_by"]').val(suggestion.data.search_by);
	            $('[name="search_slug"]').val(suggestion.data.search_slug);
	        },
	        showNoSuggestionNotice: true,
	        noSuggestionNotice: 'Sorry, no matching results',
	        groupBy: 'category',
	        onHide: function (container) {
	        	set_data('trip', 'active_dropdown_form');
	        }
	    });

	    $('[name="tourguide_name"]').autocomplete({
	        serviceUrl: site.base_url+'/autocomplete/destination/tourguide',
	        minChars: 2,
	        params : {
	        	post_type : 'trip'
	        },
	        onSearchComplete: function (query, suggestions) {
	        	$('[name="search_by"]').val('');
	        	$('[name="search_slug"]').val('');
	        },
	        onSelect: function (suggestion) {
	            //$('#selection').html('You selected: ' + suggestion.value + ', ' + suggestion.data.category);
	            $('[name="search_by"]').val(suggestion.data.search_by);
	            $('[name="search_slug"]').val(suggestion.data.search_slug);
	        },
	        showNoSuggestionNotice: true,
	        noSuggestionNotice: 'Sorry, no matching results',
	        groupBy: 'category',
	        onHide: function (container) {
	        	set_data('tourguide', 'active_dropdown_form');
	        }
	    });


	    $('[name="merchant_destination"]').autocomplete({
	        serviceUrl: site.base_url+'/autocomplete/destination',
	        minChars: 2,
	        params : {
	        	post_type : 'merchant'
	        },
	        onSearchComplete: function (query, suggestions) {
	        	$('[name="search_by"]').val('');
	        	$('[name="search_slug"]').val('');
	        },
	        onSelect: function (suggestion) {
	            //$('#selection').html('You selected: ' + suggestion.value + ', ' + suggestion.data.category);
	            $('[name="search_by"]').val(suggestion.data.search_by);
	            $('[name="search_slug"]').val(suggestion.data.search_slug);
	        },
	        showNoSuggestionNotice: true,
	        noSuggestionNotice: 'Sorry, no matching results',
	        groupBy: 'category',
	        onHide: function (container) {
	        	set_data('merchant', 'active_dropdown_form');
	        }
	    });

	    //filter
	    $('.sidebar_filter').click(function(){
	    	var stars = [];
	    	var regions = [];
	    	var categories = [];
	    	$('.sidebar_filter:checked').each(function(i, n){
	    		if($(n).attr('name').indexOf('chk_star') == 0)
	    			stars.push($(n).val());
	    		if($(n).attr('name').indexOf('chk_region')  == 0 )
	    			regions.push($(n).val());
	    		if($(n).attr('name').indexOf('chk_category')  == 0 )
	    			categories.push($(n).val());
	    		//console.log($(n).attr('name')+' - '+$(n).val());
	    	});
	    	//console.log(window.location.href);
	    	var params = getUrlParams(decodeURIComponent(window.location.href));
	    	

	    	var curr_url = window.location.href.split('?');
	    	var redirect_url = curr_url[0]; 
	    	//var url_0_segments = redirect_url.split('/');
	    	//var count_segments = url_0_segments.length;
	    	//if(url_0_segments[count_segments-1] == site.lang.city ) //make sure kalau ini search by city

	    	//var redirect_url_param = [];
	    	//if(curr_url[1] != '')
	    	//	redirect_url_param = curr_url[1].split('&');
	    	//redirect_url_param = $.extend([], getUrlParams(window.location.href));
	    	//console.log(redirect_url_param);

	    	//console.log(params); alert('tes');
	    	//paramsx = $.param(params);
	    	//console.log(paramsx);

	    	//if(params.checkin != undefined && params.checkin != '')
	    	//	params['checkin'] = params.checkin;
	    		//redirect_url_param.push('checkin='+params.checkin);

	    	//if(params.checkout != undefined && params.checkout != '')
	    	//	params['checkout'] = params.checkout;
	    		//redirect_url_param.push('checkout='+params.checkout);

	    	if(stars.length > 0)
	    		params['star'] = stars.join(',');
	    		//redirect_url_param.push('star='+stars.join(','));
	    	if(regions.length > 0)
	    		params['region'] = regions.join(',');
	    	if(categories.length > 0)
	    		params['category'] = categories.join(',');
	    		//redirect_url_param.push('region='+regions.join(','));
	    	//console.log(redirect_url_param);
	    	//alert($.param(redirect_url_param));
	    	var redirect_url_param = $.param(params);
	    	//window.location.href = redirect_url+'?'+redirect_url_param.join('&');
	    	window.location.href = redirect_url+'?'+redirect_url_param;
	    });

	    if ($('.alert-box').length >= 0) { 
	    	setTimeout(function () {
      		 	$('.alert-box').fadeOut().remove(); 
      		}, 5000);
	    }

	    //menu profile
	    $("#general-info-view").show();
		$("#profile-pic-view").hide();
		$("#id-info-view").hide();

	    $('a[id*="-trig"]').click(function(e){
			e.preventDefault();
			var id = $(this).attr('id');

			if (id == 'general-info-trig') 
				{
					$("#general-info-view").slideDown();
					$("#profile-pic-view").slideUp();
					$("#id-info-view").slideUp();
				}
			else if (id == 'profile-pic-trig') 
				{
					$("#general-info-view").slideUp();
					$("#profile-pic-view").slideDown();
					$("#id-info-view").slideUp();
				}
			else if (id == 'id-info-trig') 
				{
					$("#profile-pic-view").slideUp();
					$("#general-info-view").slideUp();
					$("#id-info-view").slideDown();
				};
			
		});


	    $('.horizontal-items .item img.squareClip').squareClip({ width : 130, height : 130 });
	    $('.peek-gallery-preview .slick-slide img.squareClip').squareClip({ width : 70, height : 70 });

	});
})(jQuery);