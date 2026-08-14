import domReady from '@wordpress/dom-ready';
import { addFilter } from '@wordpress/hooks';
import { dispatch, select } from '@wordpress/data';

function removeAllTaxonomyPanels() {
	const slugs = window.prcTaxonomiesSurfaces?.taxonomyPanelSlugs;
	if (!Array.isArray(slugs)) {
		return;
	}
	for (const slug of slugs) {
		if (typeof slug === 'string') {
			dispatch('core/editor').removeEditorPanel(`taxonomy-panel-${slug}`);
		}
	}
}

domReady(() => {
	if (null !== select('core/editor')) {
		dispatch('core/editor').removeEditorPanel('taxonomy-panel-post_tag');

		const canManage =
			true === window.prcTaxonomiesSurfaces?.canManageTaxonomyPanels;
		if (!canManage) {
			removeAllTaxonomyPanels();
		}
	}

	addFilter(
		'i18n.gettext_default',
		'prc-taxonomies/i18n',
		(translation, text) => {
			if (text === 'Assign a category') {
				return 'Assign a topic';
			}
			if (
				text ===
				'Categories provide a helpful way to group related posts together and to quickly tell readers what a post is about.'
			) {
				return 'Topics provide a helpful way to group related posts together and to quickly tell readers what a post is about.';
			}
			return translation;
		}
	);
});
