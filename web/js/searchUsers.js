function getUsers(searchKey) {
	var container = $('.searchResult');
	$(".ajaxLoading").fadeIn(300);
	$.ajax({
		url: 'searchGetUsers',
		type: 'POST',
		dataType: 'json',
		data: {value: searchKey},
		success: function(data) {
			container.mustache('user-search-result', data);	
		},
		complete: function() {
			$(".ajaxLoading").fadeOut(300);
		}
	});			
}

$(document).ready(function() {

	$("#userDivContainer").on('click', 'div.followButton', function(event) {
		var button = $(this);
		var id = button.data("user_id");
		var status = button.data("following");

		var loadingBlocker = $(this.parentNode.parentNode).find(".loadingBlocker");

		loadingBlocker.fadeIn(100);

		$.ajax({
			url: 'changeFollowStatus',
			type: 'POST',
			dataType: 'json',
			data: {id: id, status: status},
			success: function(data) {
				if(data.following) {
					$(button).mustache('user-is-followed');
					button.data("following", 'yes');
				} else {
					$(button).mustache('user-not-followed');
					button.data("following", 'no');
				}
			},
			complete: function() {
				loadingBlocker.fadeOut(100);
			}
		});	
	});

	$.Mustache.load('getMustacheTemplate/searchUsers')
		.done(function () {
			var searchKey = $.trim($("#search_value").val());
			if(searchKey) getUsers(searchKey);
		});
  var searchTimeout;
  $("#search_value").keyup(function() {
  	searchKey = this.value;

  	clearTimeout(searchTimeout);
  	searchTimeout = setTimeout(function() {
  		getUsers(searchKey);	
  	}, 100);

  });
});
