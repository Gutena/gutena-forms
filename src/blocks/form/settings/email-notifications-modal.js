import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, ToggleControl } from '@wordpress/components';
import { close } from '@wordpress/icons';
import EmailNotificationsEditView from './email-notifications-edit-view';
import {
	cloneNotifications,
	createEmptyNotification,
} from './email-notifications-utils';

const EmailNotificationsModal = ( {
	isOpen,
	initialNotifications,
	notificationDefaults,
	formFields,
	textFieldOptions,
	onSave,
	onClose,
} ) => {
	const [ notifications, setNotifications ] = useState( [] );
	const [ editingNotification, setEditingNotification ] = useState( null );

	useEffect( () => {
		if ( isOpen ) {
			setNotifications( cloneNotifications( initialNotifications ) );
			setEditingNotification( null );
		}
	}, [ isOpen, initialNotifications ] );

	useEffect( () => {
		if ( ! isOpen ) {
			return undefined;
		}

		const previousOverflow = document.body.style.overflow;
		document.body.style.overflow = 'hidden';

		const handleEscape = ( event ) => {
			if ( 'Escape' === event.key ) {
				if ( editingNotification ) {
					setEditingNotification( null );
				} else {
					onClose();
				}
			}
		};

		document.addEventListener( 'keydown', handleEscape );

		return () => {
			document.body.style.overflow = previousOverflow;
			document.removeEventListener( 'keydown', handleEscape );
		};
	}, [ isOpen, editingNotification, onClose ] );

	if ( ! isOpen ) {
		return null;
	}

	const handleToggleNotification = ( id, enabled ) => {
		setNotifications( ( current ) =>
			current.map( ( notification ) =>
				notification.id === id
					? { ...notification, enabled }
					: notification
			)
		);
	};

	const handleDeleteNotification = ( id ) => {
		setNotifications( ( current ) =>
			current.filter( ( notification ) => notification.id !== id )
		);
	};

	const handleEditSave = ( updatedNotification ) => {
		setNotifications( ( current ) => {
			const exists = current.some(
				( notification ) => notification.id === updatedNotification.id
			);

			if ( exists ) {
				return current.map( ( notification ) =>
					notification.id === updatedNotification.id
						? updatedNotification
						: notification
				);
			}

			return [ ...current, updatedNotification ];
		} );
		setEditingNotification( null );
	};

	const handleAddNotification = () => {
		setEditingNotification(
			createEmptyNotification( notificationDefaults || {} )
		);
	};

	const handleSave = () => {
		onSave( cloneNotifications( notifications ) );
	};

	const handleEditBack = () => {
		setEditingNotification( null );
	};

	const handleRequestClose = () => {
		if ( editingNotification ) {
			handleEditBack();
			return;
		}

		onClose();
	};

	const isDetailView = !! editingNotification;

	return (
		<div
			className={ `gutena-forms-email-notifications-modal${
				isDetailView ? ' is-detail-view' : ''
			}` }
			role="dialog"
			aria-modal="true"
			aria-labelledby="gutena-forms-email-notifications-modal-title"
		>
			<button
				type="button"
				className="gutena-forms-email-notifications-modal__overlay"
				aria-label={ __( 'Close dialog', 'gutena-forms' ) }
				onClick={ handleRequestClose }
			/>

			<div className="gutena-forms-email-notifications-modal__dialog">
				<div className="gutena-forms-email-notifications-modal__header">
					<h2
						id="gutena-forms-email-notifications-modal-title"
						className="gutena-forms-email-notifications-modal__title"
					>
						{ __( 'Email Notifications', 'gutena-forms' ) }
					</h2>
					<Button
						className="gutena-forms-email-notifications-modal__close"
						icon={ close }
						label={ __( 'Close', 'gutena-forms' ) }
						onClick={ handleRequestClose }
					/>
				</div>

				<div className="gutena-forms-email-notifications-modal__body">
					{ isDetailView ? (
						<EmailNotificationsEditView
							notification={ editingNotification }
							notificationDefaults={ notificationDefaults }
							formFields={ formFields }
							textFieldOptions={ textFieldOptions }
							onSave={ handleEditSave }
						/>
					) : (
						<>
							<p className="gutena-forms-email-notifications-modal__subtitle">
								{ __(
									'Control email alerts sent to admins or users after a form submission.',
									'gutena-forms'
								) }
							</p>

							<div className="gutena-forms-email-notifications-modal__toolbar">
								<Button
									variant="primary"
									className="gutena-forms-email-notifications-modal__add-button"
									onClick={ handleAddNotification }
								>
									{ __( 'Add Notification', 'gutena-forms' ) }
								</Button>
							</div>

							{ notifications.length > 0 ? (
								<div className="gutena-forms-email-notifications-modal__table-wrap">
									<table className="gutena-forms-email-notifications-table">
										<thead>
											<tr>
												<th>{ __( 'Status', 'gutena-forms' ) }</th>
												<th>{ __( 'Name', 'gutena-forms' ) }</th>
												<th>{ __( 'Subject', 'gutena-forms' ) }</th>
												<th>{ __( 'Actions', 'gutena-forms' ) }</th>
											</tr>
										</thead>
										<tbody>
											{ notifications.map( ( notification ) => (
												<tr key={ notification.id }>
													<td>
														<ToggleControl
															className="gutena-forms-email-notifications-table__toggle"
															label=""
															hideLabelFromVision
															checked={ !! notification.enabled }
															onChange={ ( enabled ) =>
																handleToggleNotification(
																	notification.id,
																	enabled
																)
															}
														/>
													</td>
													<td>{ notification.name }</td>
													<td>{ notification.subject }</td>
													<td>
														<div className="gutena-forms-email-notifications-table__actions">
															<Button
																variant="tertiary"
																className="gutena-forms-email-notifications-table__action"
																onClick={ () => {
																	setEditingNotification( {
																		...notification,
																	} );
																} }
															>
																{ __( 'Edit', 'gutena-forms' ) }
															</Button>
															<Button
																variant="tertiary"
																className="gutena-forms-email-notifications-table__action is-destructive"
																isDestructive
																onClick={ () =>
																	handleDeleteNotification(
																		notification.id
																	)
																}
															>
																{ __( 'Delete', 'gutena-forms' ) }
															</Button>
														</div>
													</td>
												</tr>
											) ) }
										</tbody>
									</table>
								</div>
							) : (
								<div className="gutena-forms-email-notifications-modal__empty">
									<p>
										{ __(
											'No notifications configured yet.',
											'gutena-forms'
										) }
									</p>
								</div>
							) }
						</>
					) }
				</div>

				<div className="gutena-forms-email-notifications-modal__footer">
					{ isDetailView ? (
						<>
							<Button
								variant="secondary"
								className="gutena-forms-email-notifications-modal__cancel"
								onClick={ handleEditBack }
							>
								{ __( 'Cancel', 'gutena-forms' ) }
							</Button>
							<Button
								variant="primary"
								className="gutena-forms-email-notifications-modal__save"
								form="gutena-email-notification-edit-form"
								type="submit"
							>
								{ __( 'Save', 'gutena-forms' ) }
							</Button>
						</>
					) : (
						<>
							<Button
								variant="secondary"
								className="gutena-forms-email-notifications-modal__cancel"
								onClick={ onClose }
							>
								{ __( 'Cancel', 'gutena-forms' ) }
							</Button>
							<Button
								variant="primary"
								className="gutena-forms-email-notifications-modal__save"
								onClick={ handleSave }
							>
								{ __( 'Save Changes', 'gutena-forms' ) }
							</Button>
						</>
					) }
				</div>
			</div>
		</div>
	);
};

export default EmailNotificationsModal;
