<script type="text/javascript">
var divNames = [];
<?php
	$divsList = $_SESSION['plugins']['Remover']['plugin_data'];
	if(isset($divsList)){
		foreach($divsList as $name){
			echo 'divNames.push("'.$name.'");';
		}
	}

?>
if (divNames.length > 0){
$('.ui-widget-header:not(#headerMenu)').filter(function(index){	
	var text = $(this).clone().children().remove().end().text();	
	if(text.length == 0){
		text = $(this).children(":first").text();
	}
	return jQuery.inArray(text, divNames) > -1;
}).parent().remove();
}


var closeButton = $("<span  class='close_button' style='float:right; cursor:Pointer; border:1px solid white; padding-left:1px; padding-right: 1px;' title='Close'>X</span>");
$('.ui-widget-header', '#columnLeft,#columnRight').append(closeButton);
$('.close_button').click(function(){	
	$(this).parent().parent().remove();
	var text = $(this).parent().clone().children().remove().end().text();	
	if(text.length == 0){
		text = $(this).parent().children(":first").text();
	}
		
		var data = {"plugin" : "Remover", "data": text};
		$.ajax({
			type: 'POST',
    			url: 'save_plugin_data.php',
    			data: {'plugin_data': JSON.stringify(data)}
  		});
	
});
</script>
