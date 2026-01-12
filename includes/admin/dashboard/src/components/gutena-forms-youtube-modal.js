import { useEffect, useState } from '@wordpress/element';
import { Button } from '@wordpress/components';
import Close from '../icons/close';

/**
 * YouTube Video Modal Component
 * 
 * @param {Object} props Component props
 * @param {boolean} props.isOpen Whether the modal is open
 * @param {Function} props.onClose Function to call when modal closes
 * @param {string} props.videoUrl YouTube video URL (e.g., 'https://www.youtube.com/watch?v=VIDEO_ID')
 * @param {string} props.videoId YouTube video ID (alternative to videoUrl)
 * @returns {JSX.Element} YouTube Video Modal Component
 */
const GutenaFormsYouTubeModal = ( { isOpen = false, onClose, videoUrl, videoId } ) => {
	const [ isVisible, setIsVisible ] = useState( false );
	const [ isAnimating, setIsAnimating ] = useState( false );

	useEffect( () => {
		if ( isOpen ) {
			setIsVisible( true );
			// Trigger zoom-in animation
			setTimeout( () => {
				setIsAnimating( true );
			}, 10 );
		} else {
			// Trigger zoom-out animation
			setIsAnimating( false );
			// Hide after animation completes
			setTimeout( () => {
				setIsVisible( false );
			}, 300 );
		}
	}, [ isOpen ] );

	// Extract video ID from URL or use provided videoId
	const getVideoId = () => {
		if ( videoId ) {
			return videoId;
		}
		if ( videoUrl ) {
			const match = videoUrl.match( /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/ );
			return match ? match[1] : null;
		}
		return null;
	};

	const videoIdValue = getVideoId();

	if ( ! isVisible || ! videoIdValue ) {
		return null;
	}

	const handleClose = ( e ) => {
		e.preventDefault();
		e.stopPropagation();
		if ( onClose ) {
			onClose();
		}
	};

	const handleBackdropClick = ( e ) => {
		if ( e.target === e.currentTarget ) {
			handleClose( e );
		}
	};

	const embedUrl = `https://www.youtube.com/embed/${ videoIdValue }?autoplay=1&rel=0`;

	return (
		<div 
			className={ `gutena-forms__youtube-modal-wrapper ${ isAnimating ? 'zoom-in' : 'zoom-out' }` }
			onClick={ handleBackdropClick }
		>
			<div className={ 'gutena-forms__youtube-modal-content' }>
				<Button
					className={ 'gutena-forms__youtube-modal-close' }
					onClick={ handleClose }
					aria-label="Close video"
				>
					<Close />
				</Button>
				<div className={ 'gutena-forms__youtube-modal-iframe-container' }>
					<iframe
						src={ embedUrl }
						title="YouTube video player"
						frameBorder="0"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
						allowFullScreen
						className={ 'gutena-forms__youtube-modal-iframe' }
					/>
				</div>
			</div>
		</div>
	);
};

export default GutenaFormsYouTubeModal;
