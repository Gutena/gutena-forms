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
import SettingsLoading from "../skeletons/settings-loading";
import { SelectControl } from '@wordpress/components';
import Activecampaign from '../icons/activecampaign';
import Brevo from '../icons/brevo';
import Mailchimp from '../icons/mailchimp';
import { NavLink } from 'react-router';
import GutenaFormsDescWrapper from '../components/gutena-forms-desc-wrapper';
import GutenaFormsToggleField from '../components/fields/gutena-forms-toggle-field';
import Settings from '../icons/settings';

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
		components['Activecampaign'] 		  = Activecampaign;
		components['Brevo'] 				  = Brevo;
		components['Mailchimp'] 			  = Mailchimp;
		components['NavLink'] 			      = NavLink;
		components['GutenaFormsDescWrapper']  = GutenaFormsDescWrapper;
		components['GutenaFormsToggleField']  = GutenaFormsToggleField;
		components['Settings']  			  = Settings;

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

addFilter(
	'gutenaFormsPro.skeletons', 'gutenaForms.free.skeleton.merge', ( skeletons ) => {

		skeletons['SettingsLoading'] = SettingsLoading;

		return skeletons;
	}
);
