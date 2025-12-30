import { __ } from '@wordpress/i18n';
import { registerBlockType, registerBlockVariation } from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';
import './style.scss';
import variations from './variations';
import edit from './edit';
import save from './save';
import metadata from './block.json';
import { gutenaFormsIcon } from './icon';
import { Icon } from '@wordpress/components';


registerBlockType( metadata, {
	icon: gutenaFormsIcon,
	variations,
	edit,
	save,
} );

/********************************
 Field Label
 ********************************/
const labelIcon = () => (
	<Icon
		icon={ () => (
			<svg
				width="24"
				height="24"
				viewBox="0 0 24 24"
				fill="none"
				xmlns="http://www.w3.org/2000/svg"
			>
				<g clipPath="url(#clip0_293_90)">
					<path
						d="M19.4393 10L14.6798 10.6803L14 15.44L18.4195 19.8592C18.5097 19.9494 18.6319 20 18.7594 20C18.8869 20 19.0092 19.9494 19.0993 19.8592L23.8592 15.0996C23.9494 15.0094 24 14.8871 24 14.7597C24 14.6322 23.9494 14.5099 23.8592 14.4198L19.4393 10ZM19.0993 11.0202L22.8395 14.7597L18.7594 18.839L15.0198 15.0996L15.5294 11.5298L19.0993 11.0202ZM18.08 14.0798C18.2604 13.8994 18.3617 13.6547 18.3617 13.3996C18.3617 13.2733 18.3368 13.1482 18.2884 13.0315C18.24 12.9148 18.1691 12.8088 18.0798 12.7195C17.9905 12.6302 17.8844 12.5594 17.7677 12.511C17.651 12.4627 17.5259 12.4379 17.3995 12.4379C17.1444 12.4379 16.8998 12.5393 16.7194 12.7197C16.539 12.9002 16.4377 13.1448 16.4378 13.4C16.4378 13.6551 16.5392 13.8997 16.7196 14.0801C16.9001 14.2605 17.1448 14.3618 17.3999 14.3617C17.655 14.3617 17.8997 14.2603 18.08 14.0798Z"
						fill="#0EA489"
						stroke="#0EA489"
						strokeWidth="0.5"
					/>
					<rect x="2" y="4" width="20" height="2" fill="#0EA489" />
					<rect x="2" y="11" width="9" height="2" fill="#0EA489" />
					<rect x="2" y="18" width="7" height="2" fill="#0EA489" />
				</g>
				<defs>
					<clipPath id="clip0_293_90">
						<rect width="24" height="24" fill="white" />
					</clipPath>
				</defs>
			</svg>
		) }
	/>
);

//Form input label block variation of paragraph block
registerBlockVariation( 'core/heading', {
	name: 'heading-input-label-gutena',
	title: __( 'Label', 'gutena-forms' ),
	description: 'Form input label',
	category: 'gutena',
	keywords: [ 'label', 'form label', 'gutena forms label' ],
	attributes: {
		level: 3,
		placeholder: 'Label',
		className: 'heading-input-label-gutena',
	},
	icon: labelIcon,
	scope: [ 'block' ],
	isActive: ( { className }, variationAttributes ) =>
		'undefined' !== typeof className &&
		null !== typeof className &&
		0 <= className.indexOf( variationAttributes.className ),
} );

/********************************
 Form Submit Button
 ********************************/
//Form submit button block variation of paragraph block
registerBlockVariation( 'core/buttons', {
	name: 'gutena-forms-submit-buttons',
	title: __( 'Form Submit button', 'gutena-forms' ),
	description: 'Gutena forms submit button',
	category: 'gutena',
	attributes: {
		className: 'gutena-forms-submit-buttons',
	},
	icon: labelIcon,
	scope: [ 'block' ],
	innerBlocks: [
		[
			'core/button',
			{
				className: 'gutena-forms-submit-button',
				placeholder: __( 'Submit', 'gutena-forms' ),
			},
		],
	],
	isActive: ( { className }, variationAttributes ) =>
		'undefined' !== typeof className &&
		null !== typeof className &&
		0 <= className.indexOf( variationAttributes.className ),
} );

/********************************
 Field Error message
 ********************************/
const fieldErrorMessage = () => (
	<Icon
		icon={ () => (
			<svg
				width="16"
				height="16"
				viewBox="0 0 16 16"
				fill="none"
				xmlns="http://www.w3.org/2000/svg"
			>
				<path
					d="M8 16C3.5816 16 0 12.4184 0 8C0 3.5816 3.5816 0 8 0C12.4184 0 16 3.5816 16 8C16 12.4184 12.4184 16 8 16ZM8 14.4C9.69739 14.4 11.3253 13.7257 12.5255 12.5255C13.7257 11.3253 14.4 9.69739 14.4 8C14.4 6.30261 13.7257 4.67475 12.5255 3.47452C11.3253 2.27428 9.69739 1.6 8 1.6C6.30261 1.6 4.67475 2.27428 3.47452 3.47452C2.27428 4.67475 1.6 6.30261 1.6 8C1.6 9.69739 2.27428 11.3253 3.47452 12.5255C4.67475 13.7257 6.30261 14.4 8 14.4ZM7.2 10.4H8.8V12H7.2V10.4ZM7.2 4H8.8V8.8H7.2V4Z"
					fill="#C51919"
				/>
			</svg>
		) }
	/>
);
//Field Error message block variation of paragraph block
registerBlockVariation( 'core/paragraph', {
	name: 'gutena-forms-field-error-msg',
	title: __( 'Field Error message', 'gutena-forms' ),
	description: 'Gutena Forms Field Error message',
	category: 'gutena',
	attributes: {
		dropCap: false,
		placeholder: 'Thanks..',
		className: 'gutena-forms-field-error-msg',
	},
	icon: fieldErrorMessage,
	scope: [ 'block' ],
	isActive: ( { className }, variationAttributes ) =>
		'undefined' !== typeof className &&
		null !== typeof className &&
		0 <= className.indexOf( variationAttributes.className ),
} );

//Form Error message block variation of paragraph block
registerBlockVariation( 'core/paragraph', {
	name: 'gutena-forms-error-text',
	title: __( 'Error text', 'gutena-forms' ),
	description: __( 'Gutena forms error text', 'gutena-forms' ),
	category: 'gutena',
	attributes: {
		dropCap: false,
		className: 'gutena-forms-error-text',
	},
	icon: fieldErrorMessage,
	scope: [ 'block' ],
	isActive: ( { className }, variationAttributes ) =>
		'undefined' !== typeof className &&
		null !== typeof className &&
		0 <= className.indexOf( variationAttributes.className ),
} );

//hide field block
domReady( () => {
    //check wp
    if ( typeof wp !== 'undefined' && typeof wp.data !== 'undefined' ){
		/** https://github.com/WordPress/gutenberg/issues/14139 **/
        wp.data.dispatch( 'core/edit-post' ).hideBlockTypes( [ 'gutena/form-field', 'gutena/field-group', 'gutena/existing-forms' ] );
    }
});

if ( ! gutenaFormsBlock.is_pro ) {
	setInterval( function () {
		var elements;
		elements = [
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

		elements.forEach( element => {
			let el = document.getElementsByClassName(element);
			if (el.length && el[0]) {
				el[0].parentNode.setAttribute('draggable', 'false');
				el[0].setAttribute('aria-disabled', 'true');
				el[0].style.pointerEvents = 'none';
				el[0].style.userSelect = 'none';
				el[0].style.opacity = '0.5';
				el[0].setAttribute('disabled', 'true');
				el[0].parentNode.addEventListener(
					'click',
					function ( e ) {
						window.open( 'https://gutenaforms.com/pricing/?utm_source=editor&utm_medium=website&utm_campaign=free_plugin', '_blank' );
					}
				);
			}
		} );

		const strContains = ( str, substr ) => {
			return str.indexOf( substr ) !== -1;
		}
		elements = document.getElementsByClassName( 'block-editor-inserter__panel-title' );

		if ( elements.length ) {
			for ( var i = 0; i < elements.length; i++ ) {
				var el = elements[i];
				if ( strContains( el.innerText, 'GUTENA FORMS PRO' ) ) {
					el.parentNode.style.display = 'block';
					el.innerHTML = `${ 'Gutena Forms Premium Fields' }
						<br />
						<a target="_blank" href="https://gutenaforms.com/pricing/?utm_source=editor&utm_medium=website&utm_campaign=free_plugin" style="background-color: #2ab399;color: #fff;padding: 10px;font-size: 12px;border: none;border-radius: 4px;cursor: pointer;transition: background-color .3s;margin-top: 16px;max-width: 280px;width: 100%;font-weight: 600;display: block;text-decoration: none;text-align: center;">
							Upgrade to Unlock these fields
						</a>`
				}
			}
		}
	}, 100 );
}
