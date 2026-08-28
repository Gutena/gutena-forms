import { useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Modal, Button, ToggleControl } from '@wordpress/components';

const NotificationsListModal = ( {
	notifications,
	onAdd,
	onEdit,
	onDelete,
	onToggle,
	onClose,
} ) => {
	const [ showConfirmDelete, setShowConfirmDelete ] = useState( null );

	const handleDeleteConfirm = useCallback(
		( notifId ) => {
			onDelete( notifId );
			setShowConfirmDelete( null );
		},
		[ onDelete ]
	);

	return (
		<Modal
			title={ __( 'Email Notifications', 'gutena-forms' ) }
			onRequestClose={ onClose }
			className="gf-notification-list-modal"
			__experimentalShowHeader
			style={ { maxWidth: '800px' } }
		>
			<div className="gf-notification-list-header">
				<span className="gf-notification-list-count">
					{ notifications.length === 0
						? __( 'No notifications configured.', 'gutena-forms' )
						: sprintf(
								/* translators: %d: number of notifications */
								__( '%d notification(s) configured.', 'gutena-forms' ),
								notifications.length
						  ) }
				</span>
				<Button
					variant="primary"
					onClick={ onAdd }
					className="gf-notification-add-btn"
				>
					{ __( '+ Add Notification', 'gutena-forms' ) }
				</Button>
			</div>

			{ notifications.length > 0 && (
				<table className="gf-notification-table">
					<thead>
						<tr>
							<th className="gf-notification-col-name">
								{ __( 'Name', 'gutena-forms' ) }
							</th>
							<th className="gf-notification-col-subject">
								{ __( 'Subject', 'gutena-forms' ) }
							</th>
							<th className="gf-notification-col-status">
								{ __( 'Status', 'gutena-forms' ) }
							</th>
							<th className="gf-notification-col-actions">
								{ __( 'Actions', 'gutena-forms' ) }
							</th>
						</tr>
					</thead>
					<tbody>
						{ notifications.map( ( notif ) => (
							<tr key={ notif.id } className="gf-notification-row">
								<td className="gf-notification-col-name">
									<span className="gf-notification-name-text">
										{ notif.name || __( 'Untitled', 'gutena-forms' ) }
									</span>
								</td>
								<td className="gf-notification-col-subject">
									<span className="gf-notification-subject-text">
										{ notif.subject || __( 'No subject', 'gutena-forms' ) }
									</span>
								</td>
								<td className="gf-notification-col-status">
									<ToggleControl
										checked={ notif.enabled !== false }
										onChange={ () => onToggle( notif.id ) }
										__nextHasNoMarginBottom
									/>
								</td>
								<td className="gf-notification-col-actions">
									{ showConfirmDelete === notif.id ? (
										<div className="gf-notification-delete-confirm">
											<span>{ __( 'Delete?', 'gutena-forms' ) }</span>
											<Button
												variant="link"
												isDestructive
												onClick={ () => handleDeleteConfirm( notif.id ) }
											>
												{ __( 'Yes', 'gutena-forms' ) }
											</Button>
											<Button
												variant="link"
												onClick={ () => setShowConfirmDelete( null ) }
											>
												{ __( 'No', 'gutena-forms' ) }
											</Button>
										</div>
									) : (
										<div className="gf-notification-action-buttons">
											<Button
												variant="link"
												onClick={ () => onEdit( notif ) }
												className="gf-notification-edit-btn"
											>
												{ __( 'Edit', 'gutena-forms' ) }
											</Button>
											<Button
												variant="link"
												isDestructive
												onClick={ () => setShowConfirmDelete( notif.id ) }
												className="gf-notification-delete-btn"
											>
												{ __( 'Delete', 'gutena-forms' ) }
											</Button>
										</div>
									) }
								</td>
							</tr>
						) ) }
					</tbody>
				</table>
			) }

			<div className="gf-notification-list-footer">
				<Button variant="secondary" onClick={ onClose }>
					{ __( 'Close', 'gutena-forms' ) }
				</Button>
			</div>
		</Modal>
	);
};

export default NotificationsListModal;
