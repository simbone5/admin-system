$(function()
{
	$('#date_link')
		.datePicker(
			{
				createButton:false,
				displayClose:true,
				closeOnSelect:false,
				selectMultiple:true
			}
		)
		.bind(
			'click',
			function()
			{
				$(this).dpDisplay();
				this.blur();
				return false;
			}
		)
		.bind(
			'dateSelected',
			function(e, selectedDate, $td, state)
			{
				var day = selectedDate.getDate();
				if(day<10)
					day  = "0"+day;
				var month = (selectedDate.getMonth()+1);
				if(month<10)
					month = "0"+month;
				var dateString = day+"/"+month+"/"+selectedDate.getFullYear();
				if(state){
					////////////////////////////
					// $td is the jQuery object wrapped onto the table cell clicked
					// If it is undefined then it's from the initial page load, not from a user select
					var appendString = "";
					if(typeof $td !="undefined"){
						var appendString = "new";
						$('#date_list').prepend("<a href='#' class='selectedDate' title='view'>"+dateString+" <span class='alert'>"+appendString+"</span><br/></a>");
					}
				}
				else{
					$("#date_list a:contains('"+dateString+"') > br").remove();
					$("#date_list a").remove(":contains('"+dateString+"')");
				}
			}
		)
		.dpSetStartDate('01/01/2000')
		.bind(
			'dpClosed',
			function(e, selectedDates)
			{
				
			}
		);

});
