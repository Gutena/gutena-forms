import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { gutenaFormsFetchEntryData } from '../../api/entries';
import { gutenaFormsStrContains } from '../../utils/functions';

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

	const handleFileValue = ( value ) => {
		let files = [];
		if ( gutenaFormsStrContains( value, ',' ) ) {
			files = String( value ).split( ',' );
		} else {
			files = [ value ]
		}
		let filesArray = {};
		files.forEach( ( file, id ) => {
			let filename = String( file ).split( '/' );
			filename = filename[ filename.length -1 ];
			filesArray[ filename ] = file;
		} );

		return filesArray;
	}

	const handleDataByFieldType = ( fieldType, value ) => {

		switch ( fieldType ) {
			case 'file':

				const files = handleFileValue( value );

				return (
					<>
						{ Object.values( files ).length && (
							<ol>
								{ Object.keys( files ).map( ( filename, key ) => {
									const fileUrl = files[ filename ];

									return (
										<li key={ key }>
											<a
												href={ fileUrl }
												target={ '_blank' }
											>{ filename }</a>
										</li>
									);
								} ) }
							</ol>
						) }
					</>
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
