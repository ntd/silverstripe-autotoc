<% loop Children %>
<li>
	<a href="#$Id">$Title</a><% if Children %>
	<ul class="nav navlist">
		<% include Menu %>
	</ul><% end_if %>
</li>
<% end_loop %>
