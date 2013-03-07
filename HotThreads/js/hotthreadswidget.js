/**
{licence}
*/
$(document).ready(function(){
	/**
	 * Updates the content of the Hot Threads Widget.
	 */
	function UpdateHotThreadsWidget() {
		var HotThreadsListURL = gdn.url('/plugin/hotthreads/getwidgetcontent');

		$.ajax({
			url: HotThreadsListURL,
			global: false,
			dataType: 'html',
			success: function(Data){
				var CurrentItems = $("#HotThreadsList_Items").html();
				// Update widget only if received data is different from the old one
				if(CurrentItems != Data) {
					// Hide current Widget content with a fade effect
					$("#HotThreadsList_Items").fadeOut('slow', function() {
						// Replace widget content and display it again, using the same fade effect
						$(this).html(Data);
						$("#HotThreadsList_Items").fadeIn('slow');
					});
				}

				// Set the auto refresh to occur again later
				setTimeout(UpdateHotThreadsWidget, gdn.definition('HotThreadsWidget_AutoUpdateDelay') * 1000);
			}
		});
	}

	// Hot Threads widget might not exist if there are no Hot Threads to display
	// and Admins chose to hide it when empty. In such case, there is no point in
	// trying to automatically update it
	var HotThreadsWidget = $('#HotThreadsList_Items');
	if(HotThreadsWidget) {
		// If Auto Update delay is greather than zero, it means it's enabled. In such
		// case, it starts the auto-refresh loop
		var AutoUpdateDelay = gdn.definition('HotThreadsWidget_AutoUpdateDelay');
		if(gdn.definition('HotThreadsWidget_AutoUpdateDelay') > 0) {
			setTimeout(UpdateHotThreadsWidget, gdn.definition('HotThreadsWidget_AutoUpdateDelay') * 1000);
		}
	}
});
