import { useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { PanelBody, ToggleControl, Button } from '@wordpress/components';
import NotificationsListModal from './notifications-list-modal';
import NotificationDetailModal from './notification-detail-modal';

const EmailNotificationsSettings = ( { emailNotifications, setAttributes } ) => {
	const [ isListModalOpen, setIsListModalOpen ] = useState( false );
	const [ isDetailModalOpen, setIsDetailModalOpen ] = useState( false );
	const [ editingNotification, setEditingNotification ] = useState( null );

	const enabled = emailNotifications?.enabled ?? false;
	const notifications = emailNotifications?.notifications ?? [];

	const handleToggle = useCallback(
		( value ) => {
			const next = {
				...emailNotifications,
				enabled: value,
				defaultSettings: false,
			};

			if ( value && ( ! notifications || 0 === notifications.length ) ) {
				next.notifications = [
					{
						id: 'notif_' + Date.now(),
						enabled: true,
						name: __( 'Admin Notification Email', 'gutena-forms' ),
						send_to: '',
						subject: 'New Form Submission - {form_title}',
						message: '',
						from_name: '',
						from_email: '',
						cc: '',
						bcc: '',
						reply_to: '',
						reply_to_first_name: '',
						reply_to_last_name: '',
					},
				];
			}

			setAttributes( { emailNotifications: next } );

			if ( value ) {
				setIsListModalOpen( true );
			}
		},
		[ emailNotifications, notifications, setAttributes ]
	);

	const updateNotifications = useCallback(
		( updated ) => {
			setAttributes( {
				emailNotifications: {
					...emailNotifications,
					notifications: updated,
					enabled: true,
					defaultSettings: false,
				},
			} );
		},
		[ emailNotifications, setAttributes ]
	);

	const handleAdd = useCallback( () => {
		setEditingNotification( null );
		setIsDetailModalOpen( true );
	}, [] );

	const handleEdit = useCallback( ( notif ) => {
		setEditingNotification( notif );
		setIsDetailModalOpen( true );
	}, [] );

	const handleDelete = useCallback(
		( notifId ) => {
			const updated = notifications.filter( ( n ) => n.id !== notifId );
			updateNotifications( updated );
			if ( 0 === updated.length ) {
				setAttributes( {
					emailNotifications: {
						...emailNotifications,
						enabled: false,
						defaultSettings: false,
					},
				} );
			}
		},
		[ notifications, emailNotifications, updateNotifications, setAttributes ]
	);

	const handleToggleNotif = useCallback(
		( notifId ) => {
			const updated = notifications.map( ( n ) =>
				n.id === notifId ? { ...n, enabled: ! n.enabled } : n
			);
			updateNotifications( updated );
		},
		[ notifications, updateNotifications ]
	);

	const handleSaveDetail = useCallback(
		( notif ) => {
			let updated;
			if ( editingNotification ) {
				updated = notifications.map( ( n ) =>
					n.id === notif.id ? notif : n
				);
			} else {
				updated = [ ...notifications, notif ];
			}
			updateNotifications( updated );
			setIsDetailModalOpen( false );
			setEditingNotification( null );
		},
		[ editingNotification, notifications, updateNotifications ]
	);

	const handleCloseListModal = useCallback( () => {
		if ( ! notifications || 0 === notifications.length ) {
			setAttributes( {
				emailNotifications: {
					...emailNotifications,
					enabled: false,
					defaultSettings: false,
				},
			} );
		}
		setIsListModalOpen( false );
	}, [ emailNotifications, notifications, setAttributes ] );

	return (
		<PanelBody title={ __( 'Email Notifications', 'gutena-forms' ) } initialOpen={ true }>
			<ToggleControl
				label={ __( 'Enable Email Notifications', 'gutena-forms' ) }
				help={
					enabled
						? __(
								'Toggle to disable email notifications for this form.',
								'gutena-forms'
						  )
						: __(
								'Toggle to enable email notifications after form submission.',
								'gutena-forms'
						  )
				}
				checked={ enabled }
				onChange={ handleToggle }
			/>
			{ enabled && (
				<Button
					variant="secondary"
					onClick={ () => setIsListModalOpen( true ) }
					className="gf-configure-button"
				>
					{ __( 'Configure', 'gutena-forms' ) }
				</Button>
			) }
			{ isListModalOpen && (
				<NotificationsListModal
					notifications={ notifications }
					onAdd={ handleAdd }
					onEdit={ handleEdit }
					onDelete={ handleDelete }
					onToggle={ handleToggleNotif }
					onClose={ handleCloseListModal }
				/>
			) }
			{ isDetailModalOpen && (
				<NotificationDetailModal
					notification={ editingNotification }
					onSave={ handleSaveDetail }
					onClose={ () => {
						setIsDetailModalOpen( false );
						setEditingNotification( null );
					} }
				/>
			) }
		</PanelBody>
	);
};

export default EmailNotificationsSettings;
