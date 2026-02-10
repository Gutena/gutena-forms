import { addFilter } from '@wordpress/hooks';
const GutenaFormsListBox = ( { style = {}, leftContent, middleContent, rightContent } ) => {
	return (
		<div
			className={ 'gutena-forms__pro-single-list-wrapper' }
			style={ style }
		>
			<div
				className={ 'gutena-forms__pro-single-list' }
			>
				<div
					className={ 'gutena-forms__pro-single-list-icon' }
				>
					{ leftContent }
				</div>
				<div
					className={ 'gutena-forms__pro-single-list-title' }
				>
					{ middleContent }
				</div>
			</div>
			<div
				className={ 'gutena-forms__pro-single-tag-delete-icon' }
			>
				{ rightContent }
			</div>
		</div>
	);
}

addFilter(
	'gutenaFormsPro.core.components',
	'gutena-forms-free',
	( components ) => {

		components['GutenaFormsListBox'] = GutenaFormsListBox;

		return components;
	}
);

export default GutenaFormsListBox;
