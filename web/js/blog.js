$(document).ready(function() {
	$.Mustache.load('getMustacheTemplate/blog');

	$("#blogMessages").on('click', '.post .footer .repostButton', function(){
		repost(this);
	});

	$("#blogMessages").on('click', '.post .footer .deleteButton', function(){
		deletePost(this);
	});

	$("#blogMessages").on('click', '.post .footer .showConv', function(){
		getReplies(this);
	});
	$("#blogMessages").on('click', '.post .footer .hideConv', function(){
		clearReplies(this);
	});
	$("#blogMessages").on('click', '.post .footer .openReply', function(){
		openReplyForm(this);
	});
	$("#blogMessages").on('click', '.post .replyForm .closeReply', function(){
		closeReplyForm(this);
	});

	$("#blogMessages").on('click', '.post .replyForm .replyButton', function(){
		reply(this);
	});

	$("#postButton").click(makePost);
});
function openReplyForm(button) {
  button = $(button);
	button.hide();
	var container = button.closest(".wrapper");
	var form = container.find(".replyForm");

	form.slideDown(100);
}
function closeReplyForm(button) {
	button = $(button);

	var form = button.closest(".replyForm");

	var replyButton = button.closest(".wrapper").find(".footer .reply").show();

	form.slideUp(100);
}
function makePost() {
	var body = $.trim($("#postBody").val());
	if(body === '') {

	} else {
		this.disabled = true;
		$.ajax({
			url: 'makePost',
			type: 'POST',
			dataType: 'json',
			data: {body: body},
			success: function(data) {
				
			},
			complete: function() {
				location.reload(true);
			}
		});
  }		
}
function clearReplies(button, toggleButtons) {
	var post = $(button).parents("div.post").last();

	post.find(".post").remove();

	if(!toggleButtons) {
		post.find(".hideConv").hide();
		post.find(".showConv").show();
	}
}

function reply(button) {
	var post = $(button).parents("div.post");
	var form = $(button).closest(".replyForm");
	var original_id = post.data("original_id");
	var textArea = form.find(".postBody");
	var body = $.trim(textArea.val());

	if(body === '') {

	} else {
		button.disabled = true;
		$.ajax({
			url: 'replyToPost',
			type: 'POST',
			dataType: 'json',
			data: {body: body, original_id: original_id},
			success: function(data) {
				closeReplyForm(button);

				textArea.val('');

				getReplies(button);
			},
			complete: function() {
				button.disabled = false;
			}
		});
	}
}

function repost(button) {

	var post = $(button).parents("div.post").last();
	var id = post.data("original_id");

	if(confirm("")) {
		$(button).hide();
		$.ajax({
			url: 'rePost',
			type: 'POST',
			dataType: 'json',
			data: {original_id: id},
			success: function(data) {
				location.reload(true);
			},
			complete: function() {

			}
		});
	}

}
function deletePost(button) {
	var post = $(button).closest("div.post");
	var post_id = post.data("post_id");

	if(confirm("")) {
		$.ajax({
			url: 'deletePost',
			type: 'POST',
			dataType: 'json',
			data: {id: post_id},
			success: function(data) {
				post.remove();
			},
			complete: function() {

			}
		});
	}
}

function getReplies(button) {
	var post = $(button).parents("div.post").last();
	var wrapper = post.find(".wrapper");

	var original_id = post.data("original_id");
	var post_id = post.data("post_id");

	var userId = $("#user_id").val();

	$.ajax({
		url: 'getConv/'+original_id,
		type: 'GET',
		dataType: 'json',
		success: function(data) {
			var method = 'after';
			var cont = wrapper;
			var cl = 'simple';

			post.find(".hideConv").show();
			post.find(".showConv").hide();

			clearReplies(button, true);

			$.each(data.replies, function(index, value) {
				if(value.id == post_id) {
					method = 'prepend';
					cont = post;
				} else {
					if(value.id == original_id) {
						cl = 'hasReplies';
					} else {
						cl = 'simple';
					}
					if(userId == value.userId) {
						value.owner = true;
					}
					value.cl = cl;
					value.original_id = original_id;
					cont.mustache('blog-post-conv', value, {method: method});
				}
			});
		},
		complete: function() {
			//$(".ajaxLoading").fadeOut(300);
		}
	});
}