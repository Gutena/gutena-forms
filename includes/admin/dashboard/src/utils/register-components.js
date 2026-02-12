/**
 * Register Free Components to Pro Version
 *
 * @since 1.6.0
 * @package GutenaForms
 */

import { addFilter } from '@wordpress/hooks';
import { AddNew } from '../icons/plus';
import GutenaFormsSubmitButton from '../components/fields/gutena-forms-submit-button';
import { Bin } from '../icons/bin';
import GutenaFormsListBox from '../components/gutena-forms-list-box';
import { toast } from 'react-toastify';
import Ellipse from '../icons/ellipse';
import Tag from '../icons/tag';
import Profile from '../icons/profile';
import { __ } from '@wordpress/i18n';
import Notes from '../icons/notes';
import {ComboboxControl, SelectControl} from '@wordpress/components';

addFilter(
	'gutenaFormsPro.core.components',
	'gutena-forms-free',
	( components ) => {

		components['AddNew'] 				  = AddNew;
		components['GutenaFormsSubmitButton'] = GutenaFormsSubmitButton;
		components['Bin'] 					  = Bin;
		components['GutenaFormsListBox'] 	  = GutenaFormsListBox;
		components['Ellipse'] 				  = Ellipse;
		components['Tag']                     = Tag;
		components['Profile']                 = Profile;
		components['Notes'] 				  = Notes;

		return components;
	}
);

addFilter(
	'gutenaFormsPro.core.toast',
	'gutena-forms-free',
	( data ) => {

		data['toast'] = toast;

		return data;
	}
);

addFilter(
	'gutenaFormsFree.core.pro-components',
	'gutena-forms-free-dummy-components',
	( components ) => {

		components['TagsComponent']   = ( { entryId, onClick } ) => {

			return (
				<div
					className={ 'gutena-froms__entry-meta-box' }
					onClick={ onClick }
				>
					<h2 className={ 'heading' }>{ __( 'Tags', 'gutena-forms' ) }</h2>
					<p className="desc">
						Separate with commas or the Enter key.
					</p>
						<div
							style={ { position: 'absolute', top: '10px', right: '10px' } }
						>
							<div
								style={ {
									width: '150px',
								} }
							>
								<SelectControl
									disabled={ true }
									options={ [
										{ label: 'Form', value: 'read' },
									] }
									value={ 'read' }
								/>
						</div>
					</div>
				</div>
			);
		};
		components['StatusComponent'] = ( { entryId, onClick } ) 	=> {

			return (
				<div
					className={ 'gutena-froms__entry-meta-box dummy-content' }
					onClick={ onClick }
				>
					<h2 className={ 'heading' }>{ __( 'Status', 'gutena-forms' ) }</h2>

					<div
						style={ { position: 'absolute', top: '10px', right: '10px' } }
					>
						<div
							style={ {
								width: '150px',
							} }
						>
							<SelectControl
								disabled={ true }
								options={ [
									{ label: 'Read', value: 'read' },
								] }
								value={ 'read' }
							/>
						</div>
					</div>
				</div>
			);
		};
		components['NotesComponent']  = ( { entryId, onClick } ) 	=> {

			return (
				<div
					className={ 'gutena-froms__entry-meta-box dummy-content' }
					onClick={ onClick }
				>
					<h2 className={ 'heading' }>{ __( 'Notes', 'gutena-forms' ) }</h2>

					<div
						className={ 'notes-button' }
					>
						<div>
							Add Notes
						</div>
					</div>

					<div
						className={ 'notes-container' }
					>
						<div className={ 'notes-content' }>
							<p>
								<span>
									<Notes />
								</span>
								Add an internal note.
							</p>
						</div>
					</div>
				</div>
			);
		};

		return components;
	},
	10
);
