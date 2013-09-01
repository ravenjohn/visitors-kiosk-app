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
			$('#main_content').html(u.template($('#menu-template').html()));
			$('#analytics, #visitors').click(
				function(e){
					var hash = $(this).attr('href'),
						vals = hash.split('/');
					if(vals.length > 1)
						eval(vals[1]+'();');
					else analytics();
				}
			);
			bindLogout();
			var hash = location.hash,
				vals = hash.split('/');
			if(vals.length > 1)
				eval(vals[1]+'();');
			else analytics();
		}
    };
	
	function analytics(){
		$('.active').removeClass('active');
		$('#analytics').parent().addClass('active');
		$('#content').html(u.template($('#chart-template').html()));
		bindFirstFilter();
		addSelectYear();
		drawChart();
	}
	
	function visitors(){
		$('.nav li.active').removeClass('active');
		$('#visitors').parent().addClass('active');
		$('#content').html(u.template($('#visitor-template').html()));
		$.getJSON('/users/visitor_details',{fields : 'id,name,affiliation,country,category,contact,date_created'},
			function (data){
				location.hash = '#/visitors';
				$('#visitors_table').html('');
				for(var i in data.records){
					$('#visitors_table').append(u.template($('#visitor-row-template').html(), data['records'][i]));
				}
				paginate(data.total, data.count);
			}
		);
		
		$('#search').keyup(function(){
			$.getJSON('/users/visitor_details',{fields : 'id,name,affiliation,country,category,contact,date_created', search_key : $(this).val()},
				function (data){
					location.hash = '#/visitors/1';
					$('#visitors_table').html('');
					for(var i in data.records){
						$('#visitors_table').append(u.template($('#visitor-row-template').html(), data['records'][i]));
					}
					paginate(data.total, data.count);
				}
			);
		});
	}
	
	function paginate(total_count, b){
		$('#pagination').html('');
		if(total_count > 0)
		{
			var page = parseInt(location.hash.split('/')[2], 10),
				j,
				totalPage = Math.ceil(total_count/10);
			if(isNaN(page)) page = 1;
			if(page > 2) j = page - 2;
			else j = 1;
			if((page >=  totalPage - 1) && totalPage > 3) j -= 2;
			else if((page >= totalPage - 2) && totalPage > 3) j--;
			$('#pagination').append('<li><a href="#/visitors/1" class="page" data-page="1"><</a></li>');
			for(var i=0; (j < page + 3 || j <= 5) && j <= totalPage;i+=10, j++){
				var _class = '';
				if(j == page){
					_class = 'active';
				}
				$('#pagination').append('<li class="'+_class+'"><a href="#/visitors/'+j+'" class="page" data-page="'+j+'">'+j+'</a></li>');
			}
			$('#pagination').append('<li><a href="#/visitors/' + totalPage + ' " class="page" data-page="'+totalPage+'">></a></li>');
		}
			
		$('.page').click(function(e){
			$.getJSON('/users/visitor_details',{fields : 'id,name,affiliation,country,category,contact,date_created', page : $(this).data('page'), search_key : $('#search').val()},
				function (data){
					$('#visitors_table').html('');
					for(var i in data.records){
						$('#visitors_table').append(u.template($('#visitor-row-template').html(), data['records'][i]));
					}
					paginate(data.total, data.count);
				}
			);
		});
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
				$('#login_div').parent().append(
					'<div class="alert alert-dismissable alert-danger" style="width: 300px; margin: 0 auto;">	\
						<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>	\
						<strong>Warning!</strong> ' + a.responseJSON.error + '	\
					</div>	\
				');
				setTimeout(function(){
					$('.alert-danger').fadeOut();
				}, 3000);
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
			).fail(function(){
				$.cookie('access_token', null);
				$('#right_header_div').html('');
				R.start();
			});
			$.getJSON('/users/visitors_by_country', data,
				function(data){innerDraw2(data);}
			).fail(function(){
				$.cookie('access_token', null);
				$('#right_header_div').html('');
				R.start();
			});
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
