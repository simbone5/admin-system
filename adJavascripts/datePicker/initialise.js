$(function()
{
	$('.date-pick')
		.datePicker(
			{
				createButton:false,
				displayClose:true,
				closeOnSelect:true,
				selectMultiple:false
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
				
			}
		)
		.dpSetStartDate('01/01/2000')
		.bind(
			'dpClosed',
			function(e, selectedDates)
			{
				updateCombos(selectedDates[0], this.id);
			}
		);

		
	var updateCombos = function (selectedDate, linkId)
	{
		var dateName = linkId.replace("_link", "");
		var selectedDate = new Date(selectedDate);
		$('#'+dateName+'_day option[value=' + selectedDate.getDate() + ']').attr('selected', 'selected');
		$('#'+dateName+'_month option[value=' + (selectedDate.getMonth()+1) + ']').attr('selected', 'selected');
		$('#'+dateName+'_year option[value=' + (selectedDate.getFullYear()) + ']').attr('selected', 'selected');
	}	
	
	$('select.day, select.month, select.year')
		.bind(
			'change',
			function()
			{
				var dateName = this.id.replace("_day", "").replace("_month", "").replace("_year", "");
				var d = new Date(
							$('#'+dateName+'_year').val(),
							$('#'+dateName+'_month').val()-1,
							$('#'+dateName+'_day').val()
						);
				$('#'+dateName+'_link').dpSetSelected(d.asString());
			}
		);
	
});