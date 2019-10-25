<?php
/**
 * the template file to display content search result page
 * instead create a folder 'buddyboss-global-search' inside your theme, copy this file over there, and make changes there
 */
$no_results_class = ! BP_Search::instance()->has_search_results() ? 'bp-search-no-results' : '';
?>

<div class="bp-search-page buddypress-wrap bp-dir-hori-nav">

	<div class="bp-search-results-wrapper dir-form <?php echo $no_results_class; ?>">

		<nav class="search_filters item-list-tabs main-navs dir-navs bp-navs no-ajax" role="navigation">
			<ul class="component-navigation search-nav">
				<?php bp_search_filters(); ?>
			</ul>
		</nav>

		<div class="bp-search-form-wrapper dir-search no-ajax">
			<?php bp_search_buffer_template_part( 'search-form' ); ?>
		</div>

		<div class="search_results">
			<?php bp_search_results(); ?>
		</div>

	</div>

</div><!-- .bp-search-page -->
