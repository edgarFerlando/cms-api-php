(function($){
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

	function remove_data(name){
	    name = name || 'misc';
	    var body = $('body')[0];
	    $.removeData(body, name);
	}

	$.fn.cloneForm = function(options) {
		var defaults = {
			btn 		: '',
			scope 		: '',
			tpl 		: '',
			container	: '',
			container_before : '<div class="item">',
			container_after : '</div>',
			btn_remove	: '<a href="#" class="remove"><i class="fa fa-minus-circle"></i></a>'
		};
		var options = $.extend(defaults, options);

		return this.each(function() {
			var btn = options.btn;
			var scope = options.scope == ''?btn+'-scope': options.scope;
			var tpl = options.tpl == ''?btn+'-tpl': options.tpl;
			var container = options.container == ''?btn+'-container': options.container;

			$(scope).addClass('cloneForm');
			if(get_data(btn) == undefined)
				set_data({idx : 0}, btn);//create idx

			function cloneform_recheck_remove_btn(){
				//pastikan jika elemen sisa satu, maka button remove di hide
				var num_items = $(container+' .item').length;
				if(num_items == 1)
					$(container+' .item .remove').addClass('hide');
				else
					$(container+' .item .remove').removeClass('hide');
			}

			$(this).click(function(e){
				var data = get_data(btn); 
				var idx = data.idx;
				//get tpl
				var tpl_html = $(tpl).html();
				var keyword = new RegExp('__idx__', "g");
                tpl_html = tpl_html.replace( keyword, idx );

                $(container).append(options.container_before+tpl_html+options.btn_remove+options.container_after);

				set_data({idx : idx+1}, btn);

				cloneform_recheck_remove_btn();
				e.preventDefault();
			});

			//remove item
			$(document).on('click', container+' .item .remove', function(e){
                e.preventDefault();
                $(this).parents('.item').remove();
                cloneform_recheck_remove_btn();
            });

			//init
			if($(options.container+' '+options.btn+'-item').length < 1){
				$(this).trigger('click');
			}
		});
	};



	$.fn.squareClip = function(options) {
		var defaults = {
			width 		: 100,
			height   	: 100
		};
		var options = $.extend(defaults, options);

        //this.filter('[data-clipPath]').each(function(i) {
        this.each(function(i) {
            //get IMG attributes
            //var maskPath = '130,0 0,160 0,485 270,645 560,485 560,160';
            //var maskCircleRadius = '0';
            var maskSrc = $(this).attr('src');
            var maskWidth = options.width;
            var maskHeight = options.width;
            //var maskWidth2 = 130;
            //var maskHeight2 = 130;
            var maskAlt = $(this).attr('alt');
            var maskTitle = $(this).attr('title');
            var uniqueID = i;

            //build SVG from our IMG attributes & path coords.
            var svg = $('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" class="svgMask" width="'+maskWidth+'" height="'+maskHeight+'"><defs><pattern id="maskID'+uniqueID+'" height="100%" width="100%" patternContentUnits="objectBoundingBox" viewBox="0 0 1 1" preserveAspectRatio="xMidYMid slice"><image preserveAspectRatio="xMidYMid slice" height="1" width="1" xlink:href="'+maskSrc+'"/></pattern></defs><rect width="'+maskWidth+'" height="'+maskHeight+'" fill="url(#maskID'+uniqueID+')" /></svg>');

            //swap original IMG with SVG
            $(this).replaceWith(svg);

            //clean up
            delete maskPath, maskSrc, maskWidth, maskHeight, maskAlt, maskTitle, uniqueID, svg;

        });

        return this;

    };

})(jQuery);