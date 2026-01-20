import { __ } from '@wordpress/i18n';
import { ArrowLeft } from '../icons/arrow';

const GutenaFormsSingleEntryPage = ( { entryId } ) => {

const entry_details = [
	{ label: 'Name', value: 'John Doe' },
	{ label: 'Email', value: 'john.doe@malinator.com' },
	{ label: 'Message', value: 'Hello, this is a test message.' },
];

	return (
		<div className={ 'gutena-forms__entry-screen' }>
			<div>
				<h2 className={ 'heading' } style={ { marginBottom: '30px' } }>
					<ArrowLeft /> Entry 2 / 2
				</h2>
			</div>

			<div className={ 'gutena-forms__entry-screen-container' }>
				<div className={ 'gutena-forms__col-70' }>

					<div className={ 'gutena-froms__entry-meta-box' }>
						<h2 className={ 'heading' }>{ __( 'Entry Data', 'gutena-froms' ) }</h2>

						<div className={ 'gutena-forms__entry-data' }>
							{ entry_details.map( ( detail, index ) => (
								<div key={ index } className={ 'gutena-forms__entry-data-row' }>
									<div className={ 'label' }>{ detail.label }:</div>
									<div className={ 'value' }>{ detail.value }</div>
								</div>
							) ) }
						</div>
					</div>

					<div className={ 'gutena-froms__entry-meta-box' }>
						<h2 className={ 'heading' }>{ __( 'Entry Details', 'gutena-forms' ) }</h2>

						<div className={ 'gutena-forms__entry-data' }>
							{ entry_details.map( ( detail, index ) => (
								<div key={ index } className={ 'gutena-forms__entry-data-row' }>
									<div className={ 'label' }>{ detail.label }:</div>
									<div className={ 'value' }>{ detail.value }</div>
								</div>
							) ) }
						</div>
					</div>
				</div>
				<div className={ 'gutena-forms__col-30' }>

					<div className={ 'gutena-froms__entry-meta-box' }>
						<h2 className={ 'heading' }>{ __( 'Notes', 'gutena-forms' ) }</h2>

					</div>

					<div className={ 'gutena-froms__entry-meta-box' }>
						<h2 className={ 'heading' }>{ __( 'Status', 'gutena-forms' ) }</h2>

					</div>

					<div className={ 'gutena-froms__entry-meta-box' }>
						<h2 className={ 'heading' }>{ __( 'Tags', 'gutena-forms' ) }</h2>

						<p className="desc">
							Separate with commas or the Enter key.
						</p>
					</div>

					<div className={ 'gutena-froms__entry-meta-box' }>
						<h2 className={ 'heading' }>{ __( 'Related Entries', 'gutena-forms' ) }</h2>
						<p className="desc">
							The user who created this entry also submitted the entries below.
						</p>
					</div>
				</div>
			</div>
		</div>
	);
};

export default GutenaFormsSingleEntryPage;
