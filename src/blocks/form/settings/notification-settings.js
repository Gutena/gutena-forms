import { TextControl, ToggleControl, SelectControl, PanelBody } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const NotificationSettings = ( { settings, methods } ) => {

	const {
		emailFromName,
		emailNotifyAdmin,
		adminEmails,
		adminEmailSubject,
		replyToEmail,
		replyToName,
		replyToLastName,
	} = settings;
	const {
		setAttributes,
		getEmailFields,
		getTextFields,

	} = methods;

	const [ emailFields, setEmailFields ] = useState( [] );
	const [ textFields, setTextFields ] = useState( [] );

	useEffect( () => {
		setEmailFields( getEmailFields() );
		setTextFields( getTextFields() );
	}, [ getEmailFields, getTextFields ] );

	useEffect( () => {
		if ( ! String( replyToEmail ).trim().length && emailFields[ 1 ] ) {
			setAttributes( { replyToEmail: emailFields[ 1 ].value } );
		}

		if ( ! String( replyToName ).trim().length && textFields[ 1 ] ) {
			setAttributes( { replyToName: textFields[ 1 ].value } );
		}
	}, [ emailFields, textFields ] );

	return (
		<>
			<PanelBody title="Notification" initialOpen={ true }>
				<TextControl
					label={ __( 'From Name', 'gutena-forms' ) }
					value={ emailFromName }
					onChange={ ( emailFromName ) =>
						setAttributes( { emailFromName } )
					}
				/>
				<ToggleControl
					label={ __( 'Admin notification', 'gutena-forms' ) }
					help={
						emailNotifyAdmin
							? __(
								'Toggle to stop email notification',
								'gutena-forms'
							)
							: __(
								'Toggle to enable email notification after form submission',
								'gutena-forms'
							)
					}
					checked={ emailNotifyAdmin }
					onChange={ ( emailNotifyAdmin ) =>
						setAttributes( { emailNotifyAdmin } )
					}
				/>
				{ emailNotifyAdmin ? (
					<>
						<TextControl
							label={ __( 'Email to', 'gutena-forms' ) }
							value={ adminEmails }
							onChange={ ( adminEmails ) =>
								setAttributes( { adminEmails } )
							}
						/>

						<TextControl
							label={ __(
								'Email subject',
								'gutena-forms'
							) }
							value={ adminEmailSubject }
							onChange={ ( adminEmailSubject ) =>
								setAttributes( { adminEmailSubject } )
							}
						/>

						<SelectControl
							label={ __(
								'Reply To Email',
								'gutena-forms'
							) }
							value={ replyToEmail }
							options={ emailFields }
							onChange={ ( replyToEmail ) =>
								setAttributes( { replyToEmail } )
							}
							help={ __(
								'Select email field for reply to address',
								'gutena-forms'
							) }
							__nextHasNoMarginBottom
						/>

						<SelectControl
							label={ __(
								'Reply To Name ( First Name )',
								'gutena-forms'
							) }
							value={ replyToName }
							options={ textFields }
							onChange={ ( replyToName ) =>
								setAttributes( { replyToName } )
							}
							help={ __(
								'Select first or full name field for reply to address',
								'gutena-forms'
							) }
							__nextHasNoMarginBottom
						/>
						<SelectControl
							label={ __(
								'Reply To Name ( Last Name )',
								'gutena-forms'
							) }
							value={ replyToLastName }
							options={ textFields }
							onChange={ ( replyToLastName ) =>
								setAttributes( { replyToLastName } )
							}
							help={ __(
								'Select last name field for reply to address',
								'gutena-forms'
							) }
							__nextHasNoMarginBottom
						/>
					</>
				) : (
					''
				) }
			</PanelBody>
		</>
	);
};

export default NotificationSettings;
