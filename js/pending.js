(function(Views, Models, $, Backbone) {

	Views.PendingStories = Backbone.View.extend({
		el: 'body',
		initialize: function() {
			$(".pending-story").each(function(){
				var model = new Models.Post({
					id 	: 	$(this).attr('data-id'),
					ID 	: 	$(this).attr('data-id'),
				})
				new Views.PendingStoryItem({el: this, model: model});
			});			
		},
	});

	Views.PendingStoryItem = Backbone.View.extend({
		events: {
			'click a.action': 'doAction'
		},
		initialize: function() {
			this.blockUi = new AE.Views.BlockUi();
		},
		doAction: function(event)	{
			event.preventDefault();
			var target = $(event.currentTarget),
				action = target.attr('data-name'),
				view = this;

			if(action == "delete"){

				this.model.destroy({
					beforeSend: function() {
						view.blockUi.block(view.$el);
					},
					success: function(result, status, jqXHR) {
						view.blockUi.unblock();
						if (status.success) {	
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'success',
							});												
							view.$el.fadeOut();
						} else {
							//bootbox.alert(status.msg);
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'error',
							});							
						}
					}
				});

			} else if(action == "approve"){

				this.model.save('do_action','approve',{
					beforeSend: function() {
						view.blockUi.block(view.$el);
					},
					success: function(result, status, jqXHR) {
						view.blockUi.unblock();
						if (status.success) {	
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'success',
							});												
							view.$el.fadeOut();
						} else {
							//bootbox.alert(status.msg);
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'error',
							});							
						}
					}
				});

			}
		}
	});

})(ForgivingHeals.Views, ForgivingHeals.Models, jQuery, Backbone);