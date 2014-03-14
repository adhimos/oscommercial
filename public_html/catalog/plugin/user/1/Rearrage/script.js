<script>
var divNamesLeftSideSort = [];
var divNamesRightSideSort = [];
<?php
	$divsList = $_SESSION['plugins']['Rearrage']['plugin_data']['columnLeft'];
	if(isset($divsList)){
		foreach($divsList as $name){
			echo 'divNamesLeftSideSort.push("'.$name['name'].'");';
		}
		unset($divsList);	  
	}

	$divsList = $_SESSION['plugins']['Rearrage']['plugin_data']['columnRight'];
	if(isset($divsList)){
		foreach($divsList as $name){
			echo 'divNamesRightSideSort.push("'.$name['name'].'");';
		}
		unset($divsList);	  
	}

?>


(function($) {$.fn.reOrder = function(array) {
  	return this.each(function() {
		   
			for(var i = 0; i < array.length; i++){
				item = $(this).children('div').filter(function(index){	
					var textMinimizer = $(this).children(":first").clone().children().remove().end().text();	
					if(textMinimizer.length == 0){
						textMinimizer = $(this).children(":first").children(":first").text();
					}
					return textMinimizer == array[i];
					});
				array[i] = item.clone(true, true);
				item.remove();
			}
      			var unmovingItems = $(this).children('div').clone('true', 'true');  
			$(this).empty();
      			for(var i=0; i < array.length; i++){
        			$(this).append(array[i]);      
      			}

			if(unmovingItems.length > 0){
				for (var i = 0; i < unmovingItems.length; i++){
					$(this).append(unmovingItems[i]);      
				}
			}
    		
	});
}
})(jQuery);

if(divNamesLeftSideSort.length > 0){
	$('#columnLeft').reOrder(divNamesLeftSideSort);
}

if(divNamesRightSideSort.length > 0){
	$('#columnRight').reOrder(divNamesRightSideSort);
}


   $(document).ready(function(){ $("#columnLeft,#columnRight").sortable({
        axis: 'y',        
        update: function(event, ui) {
            	var array = $(this).children("div").clone();
		
		if (array.length > 0){
			var dataToSend =  {"plugin" : "Rearrage", "key" : $(this).attr('id'),  "data":[]}; 
	    		for(var i=0; i < array.length; i++) {
				var obj = array[i];
				var textMinimizer = $(obj).children(":first").clone().children().remove().end().text();	
				if(textMinimizer.length == 0){
					textMinimizer = $(obj).children(":first").children(":first").text();
				}
				dataToSend.data.push({"name" : textMinimizer});
			}			
			$.ajax({
				type: 'POST',
    				url: 'save_plugin_data_map.php',
    				data: {'plugin_data': JSON.stringify(dataToSend)}
  			});
		}		
	}
    });
});



</script>
