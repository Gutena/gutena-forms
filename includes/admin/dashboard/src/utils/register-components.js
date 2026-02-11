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
