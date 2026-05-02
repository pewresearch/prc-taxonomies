/**
 * WordPress Dependencies
 */
import domReady from '@wordpress/dom-ready';
import { addFilter } from '@wordpress/hooks';
import { dispatch, select } from '@wordpress/data';

/**
 * Change the "post publish" panel text to reflect the new Topic name for Categories;
 * remove the Tags (post_tag) panel — PRC does not use tags.
 */
domReady(() => {
	if (null !== select('core/editor')) {
		dispatch('core/editor').removeEditorPanel('taxonomy-panel-post_tag');
	}

	addFilter(
		'i18n.gettext_default',
		'prc-taxonomies/i18n',
		(translation, text, domain) => {
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
