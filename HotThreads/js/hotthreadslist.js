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
				$("#HotThreadsList_Items").replaceWith(Data);
				setTimeout(UpdateHotThreadsWidget, gdn.definition('HotThreadsWidget.AutoUpdateDelay') * 1000);
			}
		});
	}

	// If Auto Update delay is greather than zero, it means it's enabled. In such
	// case, it starts the auto-refresh loop
	if(gdn.definition('HotThreadsWidget.AutoUpdateDelay') > 0) {
		setTimeout(UpdateHotThreadsWidget, gdn.definition('HotThreadsWidget.AutoUpdateDelay') * 1000);
	}
});
