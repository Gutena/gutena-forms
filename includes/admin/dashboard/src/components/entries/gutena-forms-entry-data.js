import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { gutenaFormsFetchEntryData } from '../../api/entries'

const GutenaFormsEntryData = ( { entryId } ) => {

	const [ entryData, setEntryData ] = useState( null );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchEntryData( entryId )
			.then( ( entryData ) => {
				setLoading( false );
				setEntryData( entryData );
			} );
	}, [ entryId ] );

	const handleDataByFieldType = ( fieldType, value ) => {
		console.log( fieldType )
		switch ( fieldType ) {
			case 'file':
				let filename = String( value ).split( '/' );
				filename = filename[ filename.length - 1 ];

				return (
					<a
						target={ '_blank' }
						href={ value }
					>{ filename }</a>
				);
			case 'url':
				return (
					<a
						href={ value }
						target={ '_blank' }
					>{ value }</a>
				);
			case 'phone':
				return (
					<a
						href={ `tel:${ value }` }
					>{ value }</a>
				);
			case 'email':
				return (
					<a
						href={ `mailto:${ value }` }
					>{ value }</a>
				);
			default:
				return value;
		}
	};

	return (
		<div className={ 'gutena-froms__entry-meta-box' }>
			<h2 className={ 'heading' }>{ __( 'Entry Data', 'gutena-froms' ) }</h2>

			{ ! loading && entryData && (
				<div className={ 'gutena-forms__entry-data' }>
					{ Object.keys( entryData ).map( ( entryKey, key ) => {

						const data = entryData[ entryKey ];

						return (
							<div key={ key } className={ 'gutena-forms__entry-data-row' }>
								<div className={ 'label' }>
									{ data.label }
								</div>
								<div className={ 'value' }>
									{ handleDataByFieldType( data.fieldType, data.value ) }
								</div>
							</div>
						);
					} ) }
				</div>
			) }
		</div>
	);
};

export default GutenaFormsEntryData;
