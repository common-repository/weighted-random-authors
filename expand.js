jQuery('#weighted_random_authors_expand_link').toggle(
	function() { 
		jQuery('#weighted_random_authors_list .hidden_author').show();
		jQuery('#weighted_random_authors_expand_link').html('show fewer authors'); 
	},
	function() { 
		jQuery('#weighted_random_authors_list .hidden_author').hide();
		jQuery('#weighted_random_authors_expand_link').html('show all authors'); 
	}
);