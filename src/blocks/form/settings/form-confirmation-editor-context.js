import { createContext, useContext } from '@wordpress/element';

export const FormConfirmationEditorContext = createContext( null );

const noop = () => {};

const defaultContext = {
	isEnabled: false,
	displayEnabled: false,
	confirmation: {},
	confirmationDefaults: {},
	openFormConfirmationModal: noop,
	closeFormConfirmationModal: noop,
	saveFormConfirmation: noop,
	handleToggle: noop,
	handleConfigure: noop,
};

export const useFormConfirmationEditor = () => {
	const context = useContext( FormConfirmationEditorContext );
	return context || defaultContext;
};
