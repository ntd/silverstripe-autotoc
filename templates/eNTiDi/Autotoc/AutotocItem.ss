<li>
	<a href="#$Id.ATT">$Title</a><% if $Children %>
	<ul class="nav navlist"><% loop $Children %>
		<% include AutotocItem %><% end_loop %>
	</ul><% end_if %>
</li>
