import Skeleton from 'react-loading-skeleton';

const GutenaFormsEntriesSkeleton = () => {
	return (
		<div className={ 'gutena-forms-entries-skeleton' }>
			<div className={ 'gutena-forms__mb-30' }>
				<Skeleton width={ 183 } height={ 32 } borderRadius={ 4 } />
			</div>

			<div className={ 'gutena-forms-entries-skeleton__table' }>
				<div className={ 'gutena-forms-entries-skeleton__toolbar' }>
					<div className={ 'gutena-forms-entries-skeleton__left-actions' }>
						<Skeleton width={ 40 } height={ 24 } borderRadius={ 4 } />
						<Skeleton width={ 78 } height={ 24 } borderRadius={ 4 } />
					</div>
					<div className={ 'gutena-forms-entries-skeleton__right-actions' }>
						<Skeleton width={ 58 } height={ 24 } borderRadius={ 4 } />
					</div>
				</div>

				<div className={ 'gutena-forms-entries-skeleton__header-row' }>
					<Skeleton width={ 12 } height={ 12 } borderRadius={ 3 } />
					<Skeleton width={ 46 } height={ 16 } borderRadius={ 4 } />
					<Skeleton width={ 86 } height={ 16 } borderRadius={ 4 } />
					<Skeleton width={ 86 } height={ 16 } borderRadius={ 4 } />
					<Skeleton width={ 86 } height={ 16 } borderRadius={ 4 } />
					<Skeleton width={ 54 } height={ 16 } borderRadius={ 4 } />
					<Skeleton width={ 40 } height={ 16 } borderRadius={ 4 } />
				</div>

				{ [ ...Array( 4 ) ].map( ( _, index ) => (
					<div className={ 'gutena-forms-entries-skeleton__body-row' } key={ index }>
						<Skeleton width={ 12 } height={ 12 } borderRadius={ 3 } />
						<Skeleton width={ 46 } height={ 16 } borderRadius={ 4 } />
						<Skeleton width={ 103 } height={ 16 } borderRadius={ 4 } />
						<Skeleton width={ 113 } height={ 16 } borderRadius={ 4 } />
						<Skeleton width={ 102 } height={ 16 } borderRadius={ 4 } />
						<Skeleton width={ 71 } height={ 16 } borderRadius={ 4 } />
						<Skeleton width={ 45 } height={ 16 } borderRadius={ 4 } />
					</div>
				) ) }

				<div className={ 'gutena-forms-entries-skeleton__footer' }>
					<div className={ 'gutena-forms-entries-skeleton__footer-left' }>
						<Skeleton width={ 54 } height={ 16 } borderRadius={ 4 } />
						<Skeleton width={ 18 } height={ 16 } borderRadius={ 4 } />
					</div>
					<div className={ 'gutena-forms-entries-skeleton__footer-right' }>
						<Skeleton width={ 10 } height={ 10 } borderRadius={ 4 } />
						<Skeleton width={ 10 } height={ 10 } borderRadius={ 4 } />
					</div>
				</div>
			</div>
		</div>
	);
};

export default GutenaFormsEntriesSkeleton;
