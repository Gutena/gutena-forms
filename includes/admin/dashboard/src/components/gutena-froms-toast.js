import { ToastContainer, toast } from 'react-toastify';
import { addFilter } from '@wordpress/hooks';

addFilter(
	'gutenaFormsPro.core.toast',
	'gutena-forms/gutena-forms-toast',
	() => {
		return {
			'toast': toast,
		}
	}
);

const GutenaFromsToast = () => {

	return (
		<ToastContainer
			position="bottom-right"
			autoClose={ 5000 }
		/>
	);
};

export default GutenaFromsToast;
