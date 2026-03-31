import Skeleton from 'react-loading-skeleton';

const GutenaFormsSettingsTemplateSkeleton = () => {
	return (
		<div className={ 'gutena-forms-settings-template-skeleton' }>
			<div className={ 'gutena-forms-settings-template-skeleton__description' }>
				<Skeleton width={ 180 } height={ 12 } borderRadius={ 4 } />
			</div>

			<div className={ 'gutena-forms-settings-template-skeleton__list' }>
				{ [ ...Array( 4 ) ].map( ( _, index ) => (
					<div className={ 'gutena-forms-settings-template-skeleton__list-item' } key={ index }>
						<Skeleton width={ 16 } height={ 16 } borderRadius={ 4 } />
						<Skeleton width={ index % 2 === 0 ? 160 : 140 } height={ 14 } borderRadius={ 4 } />
						<Skeleton width={ 110 } height={ 28 } borderRadius={ 4 } />
					</div>
				) ) }
			</div>

			<Skeleton width={ 120 } height={ 28 } borderRadius={ 4 } />
		</div>
	);
};

export default GutenaFormsSettingsTemplateSkeleton;
