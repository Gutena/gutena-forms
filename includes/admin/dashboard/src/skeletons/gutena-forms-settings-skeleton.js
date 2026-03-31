import Skeleton from 'react-loading-skeleton';

const GutenaFormsSettingsSkeleton = () => {
	return (
		<div className={ 'gutena-forms-settings-skeleton' }>
			<div className={ 'gutena-forms-settings-skeleton__heading' }>
				<Skeleton width={ 183 } height={ 24 } borderRadius={ 4 } />
				<div className={ 'gutena-forms-settings-skeleton__description' }>
					<Skeleton width={ '100%' } height={ 12 } borderRadius={ 4 } />
					<Skeleton width={ 302 } height={ 12 } borderRadius={ 4 } />
				</div>
			</div>

			<div className={ 'gutena-forms-settings-skeleton__meta-box' }>
				<div className={ 'gutena-forms-settings-skeleton__toggle-row' }>
					<Skeleton width={ 36 } height={ 20 } borderRadius={ 100 } />
					<Skeleton width={ 256 } height={ 19 } borderRadius={ 4 } />
				</div>

				<div className={ 'gutena-forms-settings-skeleton__field' }>
					<Skeleton width={ 54 } height={ 12 } borderRadius={ 4 } />
					<div className={ 'gutena-forms-settings-skeleton__field-input' }>
						<Skeleton width={ 169 } height={ 7 } borderRadius={ 4 } />
					</div>
					<Skeleton width={ 334 } height={ 12 } borderRadius={ 4 } />
				</div>

				<Skeleton width={ 100 } height={ 28 } borderRadius={ 4 } />
			</div>
		</div>
	);
};

export default GutenaFormsSettingsSkeleton;
