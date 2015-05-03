<script type="text/template" id="search_preview_template">
	<# _.each(stories, function(story){ #>
	<div class="i-preview">
		<a href="{{= story.permalink }}">
			<div class="i-preview-content">
				<span class="i-preview-title">
					{{= story.post_title.replace( search_term, '<strong>' + search_term + "</strong>" ) }}
				</span>
			</div>
		</a>
	</div>
	<# }); #>
	<div class="i-preview i-preview-showall">
		<# if ( total > 0 && pages > 1 ) { #>
		<a href="{{= search_link }}"><?php printf( __('View all %s results', ET_DOMAIN), '{{= total }}' ); ?></a>
		<# } else if ( pages == 1) { #>
		<a href="{{= search_link }}"><?php _e('View all results', ET_DOMAIN) ?></a>
		<# } else { #>
		<a> <?php _e('No results found', ET_DOMAIN) ?> </a>
		<# } #>
	</div>
</script>