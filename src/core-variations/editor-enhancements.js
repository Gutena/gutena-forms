import domReady from '@wordpress/dom-ready';

domReady( () => {
	if ( typeof wp !== 'undefined' && typeof wp.data !== 'undefined' ) {
		wp.data
			.dispatch( 'core/edit-post' )
			.hideBlockTypes( [ 'gutena/form-field', 'gutena/field-group', 'gutena/existing-forms' ] );
	}
} );

if ( ! gutenaFormsBlock.is_pro ) {
	setInterval( function () {
		let elements = [
			'editor-block-list-item-gutena-date-field-group',
			'editor-block-list-item-gutena-time-field-group',
			'editor-block-list-item-gutena-phone-field-group',
			'editor-block-list-item-gutena-country-field-group',
			'editor-block-list-item-gutena-state-field-group',
			'editor-block-list-item-gutena-file-upload-field-group',
			'editor-block-list-item-gutena-url-field-group',
			'editor-block-list-item-gutena-hidden-field-group',
			'editor-block-list-item-gutena-password-field-group',
			'editor-block-list-item-gutena-rating-field-group',
		];

		elements.forEach( ( element ) => {
			const el = document.getElementsByClassName( element );
			if ( el.length && el[ 0 ] ) {
				el[ 0 ].parentNode.setAttribute( 'draggable', 'false' );
				el[ 0 ].setAttribute( 'aria-disabled', 'true' );
				el[ 0 ].style.pointerEvents = 'none';
				el[ 0 ].style.userSelect = 'none';
				el[ 0 ].style.opacity = '0.5';
				el[ 0 ].setAttribute( 'disabled', 'true' );

				if ( el[ 1 ] ) {
					el[ 1 ].parentNode.setAttribute( 'draggable', 'false' );
					el[ 1 ].setAttribute( 'aria-disabled', 'true' );
					el[ 1 ].style.pointerEvents = 'none';
					el[ 1 ].style.userSelect = 'none';
					el[ 1 ].style.opacity = '0.5';
					el[ 1 ].setAttribute( 'disabled', 'true' );
				}
			}
		} );

		const strContains = ( str, substr ) => str.indexOf( substr ) !== -1;
		elements = document.getElementsByClassName( 'block-editor-inserter__panel-title' );

		if ( elements.length ) {
			for ( let i = 0; i < elements.length; i++ ) {
				const el = elements[ i ];
				if ( strContains( el.innerText, 'GUTENA FORMS PRO' ) ) {
					el.parentNode.style.display = 'block';
					el.innerHTML = `${ 'Gutena Forms Premium Fields' }
						<br />
						<a target="_blank" href="https://gutenaforms.com/pricing/?utm_source=editor&utm_medium=website&utm_campaign=free_plugin" style="background-color: #2ab399;color: #fff;padding: 10px;font-size: 12px;border: none;border-radius: 4px;cursor: pointer;transition: background-color .3s;margin-top: 16px;max-width: 280px;width: 100%;font-weight: 600;display: block;text-decoration: none;text-align: center;">
							Upgrade to Unlock these fields
						</a>`;
				}
			}
		}
	}, 100 );
}
