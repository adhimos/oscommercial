

<style>
  #draggable { width: 150px; height: 150px; padding: 0.5em; }
  </style>





<div id="draggable" style=" display: inline; cursor: move;">
<div class="ui-widget infoBoxContainer" id="customerAssistant" style="width: 362px;z-index:9999">
	<div class="ui-widget-header ui-corner-top infoBoxHeading" style="width: 356px;z-index:9999">
		Customer Assistant
		<span style="border:1px solid #FBF8EF; float: right"> <a href="<?php tep_assistant_disable_url(); ?>">X</a></span>
	</div>

	<div class="ui-widget-content infoBoxContents" style="font-size: 12px;"><b>Instructions</b></div>
	<div class="ui-widget-content infoBoxContents" id="message" style="width: 350px; text-align: left;">
		<?php echo $message; ?>
	<div>&nbsp;</div>
	Progress:
	<div id="progress" style="width:345px;height:25px;border:1px solid #ccc"></div>
	</div>

	
</div>

</div>


<script>
    var total = 345;
    var percent = <?php echo $progress; ?>;	
    var divWidth = percent/100 * total;
	

    var html1 = '<span style="position: absolute;left: 50%; padding-top:4px;">';
    var divHTML =  html1.concat(percent, '%</span><div style="width:', divWidth,'px;background-color:#CED8F6;hetight:20px;font-size:17px;">&nbsp;</div>');	
    $('#progress').html(divHTML);  

     $(function() {
    $( "#draggable" ).draggable();
    $( "#customerAssistant" ).addClass('shadow');
    $("#customerAssistant").css('position', 'absolute');
    $("#draggable").css('top', '<?php echo $top; ?>px'); 
    $("#draggable").css('left', '<?php echo $left; ?>px');
   
   
  });

  </script>
