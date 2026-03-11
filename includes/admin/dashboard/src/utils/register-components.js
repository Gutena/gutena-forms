/**
 * Register Free Components to Pro Version
 *
 * @since 1.7.0
 * @package GutenaForms
 */

import './pro-components';
import { addFilter } from '@wordpress/hooks';
import { AddNew } from '../icons/plus';
import GutenaFormsSubmitButton from '../components/fields/gutena-forms-submit-button';
import { Bin } from '../icons/bin';
import GutenaFormsListBox from '../components/gutena-forms-list-box';
import { toast } from 'react-toastify';
import Ellipse from '../icons/ellipse';
import Tag from '../icons/tag';
import Profile from '../icons/profile';
import Notes from '../icons/notes';
import Edit from '../icons/edit';
import { FilledStar, Star } from "../icons/star";
import {gutenaFormsStrContains} from "./functions";

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
		components['Edit'] 				      = Edit;
		components['Star'] 				  	  = Star;
		components['FilledStar'] 			  = FilledStar;

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

addFilter( 'gutenaFormsPro.core.functions', 'gutena-forms-free-fetch-functions', ( functions ) => {

	functions['gutenaFormsStrContains'] = gutenaFormsStrContains;

	return functions;
} );
