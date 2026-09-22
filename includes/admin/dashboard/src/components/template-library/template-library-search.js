import { __ } from '@wordpress/i18n';
import Search from '../../icons/search';

const TemplateLibrarySearch = ( { value, onChange } ) => (
	<div className="gutena-forms-template-library__search">
		<label className="screen-reader-text" htmlFor="gutena-forms-template-library-search">
			{ __( 'Search templates', 'gutena-forms' ) }
		</label>
		<Search />
		<input
			id="gutena-forms-template-library-search"
			type="search"
			value={ value }
			onChange={ ( event ) => onChange( event.target.value ) }
			placeholder={ __( 'Search templates…', 'gutena-forms' ) }
			autoComplete="off"
		/>
	</div>
);

export default TemplateLibrarySearch;
