/*jslint browser:true */
(function () {
    'use strict';

    /*jslint nomen: true*/
    var R = Function.prototype,
        $ = window.$,
        u = window._;
    /*jslint nomen: false*/

    R.opts = {
        live : false,
        manga_id : null,
        manga_name : null,
        chapter : null
    };

    R.chapters = [];

    R.empty = function (a) {
        return undefined === a || a === null || a === "" || a === "0" || a === 0 || (("object" === typeof a || "array" === typeof a) && a.length === 0);
    };

    R.setOptions = function () {
        var options = window.location.hash.split("/");
        if (options.length > 1) {
            R.opts.manga_name = options[1];
            if (options.length > 2) {
                if (!R.empty(options[2]) || options[2] === "0") {
                    R.opts.chapter = options[2];
                }
            }
        }
    };

    R.loadTemplates = function () {
        if (R.empty($('#templates'))) {
            $.get("templates/templates.html", function (data) {
                $('body').append('<div id="#templates">' + data + "</div>");
				R.start();
            });
        }
    };

    R.start = function () {
		if(typeof $.cookie('access_token') === 'undefined' || $.cookie('access_token') == 'null'){
			$('#main_content').html(u.template($('#login-template').html()));
			bindLogin();
		}
		else{
			homepage();
		}
    };

	function homepage(){
		$('#right_header_div').html(u.template($('#logout-template').html()));
		bindLogout();
	}

	function bindLogin(){
		$('#login_div').submit(function (e){
			e.preventDefault();
			$.ajax({
				url : $(e.currentTarget).attr('action'),
				method : $(e.currentTarget).attr('method'),
				type : 'JSON',
				data : $($(e.target)[0]).serialize()
			}).done(function(a){
				$.cookie('access_token', a.access_token);
				$('#main_content').html('');
				R.start();
			}).fail(function(a){
				alert(a.responseJSON.error);
			});
		});
	}
	
	function bindLogout(){
		$('#logout').click(function(e){
			e.preventDefault();
			$.getJSON('/users/logout', {access_token : $.cookie('access_token')},
				function(a){
					$.cookie('access_token', null);
					$('#right_header_div').html('');
					R.start();
				}
			);
		});
	}

    R.loadTemplates();
}());
