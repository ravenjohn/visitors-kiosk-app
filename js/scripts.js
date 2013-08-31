/*jslint browser:true */
(function () {
    'use strict';

    /*jslint nomen: true*/
    var R = Function.prototype,
        $ = window.$,
        u = window._;
    /*jslint nomen: false*/

    R.empty = function (a) {
        return undefined === a || a === null || a === "" || a === "0" || a === 0 || (("object" === typeof a || "array" === typeof a) && a.length === 0);
    };

    R.loadTemplates = function () {
        if (R.empty($('#templates'))) {
            $.get("templates/templates.html", function (data) {
                $('body').append('<div id="#templates">' + data + "</div>");
				R.start();
            });
        }
    };
	
	function isLoggedIn(){
		return !(typeof $.cookie('access_token') === 'undefined' || $.cookie('access_token') == 'null');
	}

    R.start = function () {
		if(!isLoggedIn()){
			$('#main_content').html(u.template($('#login-template').html()));
			bindLogin();
		}
		else{
			$('#right_header_div').html(u.template($('#logout-template').html()));
			$('#main_content').append(u.template($('#menu-template').html()));
			$('#main_content').append(u.template($('#chart-template').html()));
			bindLogout();
			bindFirstFilter();
			addSelectYear();
			drawChart();
		}
    };

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
			$.getJSON(
				'/users/logout',
				{access_token : $.cookie('access_token')}
			).done(function(a){
					$.cookie('access_token', null);
					$('#right_header_div').html('');
					R.start();
			});
		});
	}
	
	function drawChart() {
		if(isLoggedIn()){
			reDrawChart();
		}
	}
	
	function reDrawChart(array) {
		function innerDraw(d){
			var data = google.visualization.arrayToDataTable(d),
				options = {
					vAxis: {minValue: 0}
				},
				chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
			chart.draw(data, options);
		}
		function innerDraw2(d){
			console.log(d);
			var data = google.visualization.arrayToDataTable(d),
				chart = new google.visualization.GeoChart(document.getElementById('map_div'));
			chart.draw(data, {});
		}
		if(typeof array === 'undefined'){
			var data = {
				grouping : $('#grouping').val(),
				year : $('#year').val(),
				month : $('#month').val(),
				category : $('#category').val(),
				access_token : $.cookie('access_token')
			};
			$.getJSON('/users/visitors/', data,
				function(data){innerDraw(data);}
			);
			$.getJSON('/users/visitors_by_country', data,
				function(data){innerDraw2(data);}
			);
		}
		else{
			innerDraw(array);
		}
	}
	
	function bindFirstFilter(){
		$('#grouping').change(function(e){
			if(this.value === 'daily')
			{
				addSelectMonth();
			}
			else if(this.value === 'monthly' || this.value === 'quarterly')
			{
				addSelectYear();
			}
			else if(this.value === 'yearly')
			{
				$('#additional_params').html('.');
			}
			reDrawChart();
		});
		$('#category').change(function(e){
			reDrawChart();
		});
	}
	
	function addSelectYear(){
		$('#additional_params').html('for the year <select id="year"></select>.');
		var myselect = document.getElementById("year"),
			year = new Date().getFullYear(),
			gen = function(max){do{myselect.add(new Option(year,year--),null);}while(--max>0);}(5);
		$('#year').change(function(e){reDrawChart();});
	}
	
	function addSelectMonth(){
		$('#additional_params').html('for the month of <select id="month"></select><select id="year"></select>.');
		var myselect = document.getElementById("month"),
			x,
			usrDate = new Date(),
			monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ],
			yourselect = document.getElementById("year"),
			year = usrDate.getFullYear(),
			gen = function(max){do{yourselect.add(new Option(year,year--),null);}while(--max>0);}(5);
			
		for(x=0; x<12; x++) {
			var opt = new Option(monthNames[x], x+1);
			if(x+1 == usrDate.getMonth() + 1)
				opt.selected = "selected";
			myselect.add(opt, null);
		}
		$('#month').change(function(e){reDrawChart();});
		$('#year').change(function(e){reDrawChart();});
	}

	google.load("visualization", "1", {packages:["corechart", "geochart"]});
	google.setOnLoadCallback(R.loadTemplates);
}());
