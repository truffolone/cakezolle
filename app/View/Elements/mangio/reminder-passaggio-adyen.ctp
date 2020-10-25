<style>
	#submenu .btn.reminder-adyen, .navbar-right > .reminder-adyen {
		border-bottom: 5px solid #ff6b01;
	}
	
	#submenu .btn.reminder-adyen:hover, .navbar-right .reminder-adyen:hover {
	  color: #fff;
	  background: #ffe200; 
	}
	
	.reminder-adyen {
		color: #ff8103 !important;
	}
</style>
<li class="reminder-adyen">
	<a href="#" class="reminder-passaggio-adyen" onClick="openAdyenRemainder()">
		<i class="reminder-adyen fa fa-credit-card fa-2x icon-animated-bell"></i>
	</a>
</li> 


<script>

	function openAdyenRemainder() {
		$('#reminder-adyen-modal').modal('show');
	}

</script>
