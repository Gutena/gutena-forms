import TemplateCategoryAll from '../../icons/template-category-all';
import TemplateCategoryApplication from '../../icons/template-category-application';
import TemplateCategoryBooking from '../../icons/template-category-booking';
import TemplateCategoryEvent from '../../icons/template-category-event';
import TemplateCategoryLead from '../../icons/template-category-lead';
import TemplateCategoryMarketing from '../../icons/template-category-marketing';
import TemplateCategoryRequest from '../../icons/template-category-request';
import TemplateCategorySupport from '../../icons/template-category-support';
import TemplateCategorySurveys from '../../icons/template-category-surveys';

const TEMPLATE_CATEGORY_ICONS = {
	all: TemplateCategoryAll,
	'application-forms': TemplateCategoryApplication,
	'booking-forms': TemplateCategoryBooking,
	'event-planning': TemplateCategoryEvent,
	'lead-generation': TemplateCategoryLead,
	marketing: TemplateCategoryMarketing,
	'supports-requests': TemplateCategorySupport,
	'surveys-feedback': TemplateCategorySurveys,
	'request-template': TemplateCategoryRequest,
};

export const getTemplateCategoryIcon = ( slug ) => {
	return TEMPLATE_CATEGORY_ICONS[ slug ] || null;
};
