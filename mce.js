(function($) {
	tinymce.PluginManager.add('rkmd_mce_button', function( editor, url ) {

		var relatedPostSelector = {
			$el: $('#plugin_form'),
			$box: false,
			$submit: false,
			init : function() {
				this.$box 	   = this.$el.find( '#find-posts');
				this.$submit   = this.$el.find( '#find-posts-submit');
				this.$spinner  = this.$el.find( '.find-box-search .spinner' );
				this.$input    = this.$el.find( '#find-posts-input' );
				this.$response = this.$el.find( '#find-posts-response' );
				this.addEditorButton();
				this.bindEvents();
			},
			addEditorButton: function() {
				var self = this;
				editor.addButton('rkmd_mce_button', {
					text: 'Post invoegen',
					icon: false,
					tooltip: 'Voeg een gerelateerde post toe',
					onclick: function() {
						self.open();
					}
				});
			},
			open: function() {
				this.$response.html('');
				findPosts.open('action','find_posts');
				this.$box.show().find( '#find-posts-head' ).html( 'Gerelateerde post invoegen' + '<div id="find-posts-close"></div>' );
				return false;
			},
			bindEvents: function() {
				var self = this;
				this.$submit.click(function(e) {
					e.preventDefault();
					self.selectPost();
				});
			},
			selectPost: function() {
				var raw_checked = this.$response.find( 'input[type="radio"]:checked' );
				var checked = raw_checked.map(function() { 
					return this.value; 
				}).get();
				if ( ! checked.length ) {
					this.close();
					return;
				} else {
					editor.insertContent( '[rkmd_post id="' + checked[0] + '"]');
					this.close();
				}
			},
			close: function() {
				console.log('close!');
				this.$box.hide();
				this.$overlay  = $( '.ui-find-overlay' );
				this.$overlay.hide();
			}
		};

		relatedPostSelector.init();

	});
})(jQuery);