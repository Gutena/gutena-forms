import Skeleton from 'react-loading-skeleton';

const GutenaFormsFormsSkeleton = () => {

	return (
		<div className={ 'gutena-forms-forms-skeleton' }>
			<div className={ 'gutena-forms__mb-30 gutena-forms-forms-skeleton__title' }>
				<Skeleton width={ 142 } height={ 34 } />
				<Skeleton width={ 94 } height={ 34 } />
			</div>

			<div className={ 'gutena-forms-forms-skeleton__table' }>
				<div className={ 'gutena-forms-forms-skeleton__toolbar' }>
					<div className={ 'gutena-forms-forms-skeleton__left-actions' }>
						<Skeleton width={ 58 } height={ 26 } />
						<Skeleton width={ 78 } height={ 26 } />
					</div>
					<div className={ 'gutena-forms-forms-skeleton__right-actions' }>
						<Skeleton width={ 74 } height={ 26 } />
						<Skeleton width={ 122 } height={ 26 } />
						<Skeleton width={ 122 } height={ 26 } />
					</div>
				</div>

				<div className={ 'gutena-forms-forms-skeleton__header-row' }>
					<Skeleton width={ 20 } height={ 20 } />
					<Skeleton width={ 94 } height={ 22 } />
					<Skeleton width={ 46 } height={ 22 } />
					<Skeleton width={ 84 } height={ 22 } />
					<Skeleton width={ 84 } height={ 22 } />
					<Skeleton width={ 62 } height={ 22 } />
				</div>

				{ [ ...Array( 3 ) ].map( ( _, index ) => (
					<div className={ 'gutena-forms-forms-skeleton__body-row' } key={ index }>
						<Skeleton width={ 20 } height={ 20 } />
						<Skeleton width={ 74 } height={ 22 } />
						<Skeleton width={ 26 } height={ 22 } />
						<Skeleton width={ 90 } height={ 22 } />
						<Skeleton width={ 94 } height={ 22 } />
						<Skeleton width={ 58 } height={ 22 } />
					</div>
				) ) }

				<div className={ 'gutena-forms-forms-skeleton__footer' }>
					<div className={ 'gutena-forms-forms-skeleton__footer-left' }>
						<Skeleton width={ 50 } height={ 22 } />
						<Skeleton width={ 26 } height={ 22 } />
					</div>
					<div className={ 'gutena-forms-forms-skeleton__footer-right' }>
						<Skeleton width={ 20 } height={ 20 } />
						<Skeleton width={ 20 } height={ 20 } />
					</div>
				</div>
			</div>
		</div>
	);
};

export default GutenaFormsFormsSkeleton;
