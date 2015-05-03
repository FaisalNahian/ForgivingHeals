(function (Views, Models, $, Backbone) {
	Views.MobileSingleStory 	= Backbone.View.extend({
		el : 'body.single-story',
		events : {
			'submit form#insert_reaction'     	: 'insertAnswer',
			'click 	a.btn-post-reactions' 		: 'showFormReply',
			'click  a#close_reply_form'  		: 'cancelFormReply',
			'change select#move_to_order' 		: 'orderReactions'
		},
		initialize: function(){
			var story = new Models.Post(currentStory);
			this.blockUi	=	new AE.Views.BlockUi();
			this.story 	= 	new Views.PostListItem({
				el: $("#story_content"),
				model: story
			});

			$('.reaction-item').each(function(index){
				var element = $(this);
				if ( reactionsData ) {
					var model	= new Models.Post(reactionsData[index]);
					var reaction	=	new Views.PostListItem({
						el: element,
						model: model
					});
				}
					
			});
			$('.share-social').popover({ html : true});	
		},
		orderReactions: function(event){
			event.preventDefault();
			var target = $(event.currentTarget);
			if(target.val())
				window.location.href = target.val();
		},
		showFormReply: function(event){
			event.preventDefault();
			var target = $(event.currentTarget);
			target.fadeOut('normal', function() {
				$('form#insert_reaction').slideDown().find("textarea").focus();
				$('html, body').animate({ scrollTop: 60000 }, 'slow');
			});

		},
		cancelFormReply: function(event){
			event.preventDefault();
			var target = $(event.currentTarget);
			target.fadeIn('normal', function() {
				$('form.form-post-reactions').slideUp();
				$('.btn-post-reactions').fadeIn();
			});
		},
		insertAnswer: function(event){
			event.preventDefault();

			var form 	 = $(event.currentTarget),
				$button  = form.find("input.btn-submit"),
				textarea = form.find("textarea"),
				data 	 = form.serializeObject(),
				reactions  = parseInt(this.$("span.number").text()),
				view 	 = this;

			if(textarea.val() == '')
				return;

			if(ae_globals.user_confirm && currentUser.register_status == "unconfirm"){
				alert( forgivingheals_front.texts.confirm_account );				
				return false;
			}

			reaction = new Models.Post();
			reaction.set('content',data);
			reaction.save('','',{
				beforeSend:function(){
					view.blockUi.block($button);
				},
				success : function (result, status, jqXHR) {
					view.blockUi.unblock();
					if(status.success){
						viewPost = new Views.PostListItem({
							id: result.get('ID'),
							model: result
						});
						textarea.val('').focusout();

						if(ae_globals.pending_reactions !== 1){
							$("#reactions_main_list").append(viewPost.render(result));
							$("span.reactions-count span.number").text(reactions+1);
						} else {
							alert(status.msg);
						}

					}
				}
			});			
		}
	});	
})(ForgivingHeals.Views, ForgivingHeals.Models, jQuery, Backbone);