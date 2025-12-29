import { Button } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import './style.scss';

const Introduction = ( props ) => {
    const welcome = gutenaFormsIntroduction?.section?.welcome;
    const features = gutenaFormsIntroduction?.section?.features;
    const fields = gutenaFormsIntroduction?.section?.fields;
    const pricing = gutenaFormsIntroduction?.section?.pricing;

    // Check if PRO version is active
    const isProActive = gutenaFormsDashboard?.is_gutena_forms_pro === '1';

    // Get plugin URL from welcome image path
    const getPluginUrl = () => {
        if (welcome?.into_img) {
            return welcome.into_img.replace('/assets/img/welcome.png', '');
        }
        return '';
    };
    const pluginUrl = getPluginUrl();
    const formIllustrationUrl = pluginUrl ? `${pluginUrl}/assets/img/form-illustration.png` : 'assets/img/form-illustration.png';

    // Video modal state
    const [isVideoModalOpen, setIsVideoModalOpen] = useState(false);
    const [isClosing, setIsClosing] = useState(false);

    // Function to extract YouTube video ID from URL
    const getYouTubeVideoId = (url) => {
        if (!url) return '';
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : '';
    };

    const videoUrl = welcome?.intro_video_link || '';
    const videoId = getYouTubeVideoId(videoUrl);

    // Handle closing modal with animation
    const handleCloseModal = () => {
        setIsClosing(true);
    };

    // Reset closing state when modal is fully closed
    useEffect(() => {
        if (isClosing) {
            const timer = setTimeout(() => {
                setIsVideoModalOpen(false);
                setIsClosing(false);
            }, 300); // Match animation duration
            return () => clearTimeout(timer);
        }
    }, [isClosing]);

    // Handle ESC key to close modal and prevent body scroll
    useEffect(() => {
        const handleEsc = (event) => {
            if (event.keyCode === 27 && isVideoModalOpen && !isClosing) { // ESC key
                handleCloseModal();
            }
        };

        if (isVideoModalOpen) {
            document.addEventListener('keydown', handleEsc);
            document.body.style.overflow = 'hidden'; // Prevent body scroll
        }

        return () => {
            document.removeEventListener('keydown', handleEsc);
            document.body.style.overflow = 'unset';
        };
    }, [isVideoModalOpen, isClosing]);

    // Video Play Icon SVG
    const PlayIcon = () => (
        <svg xmlns="http://www.w3.org/2000/svg" width="83" height="83" viewBox="0 0 83 83" fill="none">
            <path d="M41.5 7C22.4698 7 7 22.4836 7 41.5C7 60.5302 22.4698 76 41.5 76C60.5164 76 76 60.5302 76 41.5C76 22.4836 60.5164 7 41.5 7Z" fill="#0DA88C" fillOpacity="0.3"/>
            <path d="M41.5 12C25.2278 12 12 25.2396 12 41.5C12 57.7722 25.2278 71 41.5 71C57.7604 71 71 57.7722 71 41.5C71 25.2396 57.7604 12 41.5 12Z" fill="#0DA88C"/>
            <path d="M36.8208 55.7548L53.5589 42.4193C53.8304 42.1975 54 41.8588 54 41.4968C54 41.1348 53.8304 40.7962 53.5589 40.5743L36.8208 27.2389C36.4815 26.9703 36.0179 26.9236 35.6333 27.1221C35.443 27.2184 35.2827 27.368 35.1707 27.5538C35.0587 27.7396 34.9996 27.9542 35 28.1731V54.8323C35 55.276 35.2488 55.6847 35.6333 55.8832C35.7917 55.9533 35.9613 56 36.131 56C36.3798 56 36.6173 55.9183 36.8208 55.7548Z" fill="white"/>
        </svg>
    );

    // Checkmark Icon SVG
    const CheckmarkIcon = () => (
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
            <circle cx="8" cy="8" r="8" fill="#0DA88C"/>
            <path d="M5 8L7 10L11 6" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
        </svg>
    );

    // Testimonials data
    const testimonials = [
        {
            text: "I was looking for an efficient yet lightweight plugin for a contact form for my professional consultancy website. Gutena form is awesome, fully customisable, you can be up and running in 5 minutes. Extra extra bonus for their support - I had a small query and answered me superfast. Fab plugin. 100% recommended",
            name: "gab81",
            image: pluginUrl ? `${pluginUrl}/assets/img/testimonial/gab81.png` : 'assets/img/testimonial/gab81.png'
        },
        {
            text: "Really happy I stumbled across Gutena Forms. Absolutely perfect for what I was needing and the fact that it's block based is another win.",
            name: "Mike Hindle",
            image: pluginUrl ? `${pluginUrl}/assets/img/testimonial/mike-hindle.png` : 'assets/img/testimonial/mike-hindle.png'
        }
    ];

    // PRO features list
    const proFeatures = [
        'File Uploads',
        'Hidden Field',
        'Password Field',
        'All Advanced Fields',
        'Entry Management',
        'Weekly Form Report',
        'Advanced Entries Filter',
        'Tags Management',
        'Status Management',
        'User Access Management',
        'Entry Notes',
        'Priority Support'
    ];

    return (
        <div className="gf-introduction-page">
            {/* Header Section */}
            <div className="gf-header-section">
                <div className="gf-logo-wrapper">
                    <img
                        src={pluginUrl ? `${pluginUrl}/assets/img/gutena-logo.png` : 'assets/img/gutena-logo.png'}
                        alt="Gutena Forms Logo"
                        className="gf-logo"
                    />
                </div>
                <h1 className="gf-top-heading">
                    {welcome?.title || 'Welcome to Gutena Forms!'}
                </h1>
                <p className="gf-top-description">
                    Thank you for choosing Gutena - the most powerful drag & drop WordPress form builder in the market.
                </p>
            </div>

            {/* Video Section */}
            <div className="gf-video-section">
                <div className="gf-video-content">
                    <div className="gf-video-left">
                        <h2 className="gf-video-heading">
                            How to Create your First Form With Gutena Forms (step by step)
                        </h2>
                        <button
                            onClick={() => setIsVideoModalOpen(true)}
                            className="gf-video-button"
                        >
                            <span className="gf-play-icon">
                                <PlayIcon />
                            </span>
                            <span className="gf-watch-text">Click here to Watch Video</span>
                        </button>
                    </div>
                    <div className="gf-video-right">
                        <img
                            src={formIllustrationUrl}
                            alt="Form illustration"
                            className="gf-form-illustration"
                        />
                    </div>
                </div>
            </div>

            {/* Tag Line Section */}
            <div className="gf-tagline-section">
                <p className="gf-tagline">
                    Gutena makes it easy to create forms in WordPress. You can watch the video tutorial or read our guide on how to create your first form.
                </p>
            </div>

            {/* CTA Section */}
            <div className="gf-cta-section">
                <a
                    href="https://gutenaforms.com/#faq"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="gf-read-guide-link"
                >
                    Read the Full Guide
                </a>
            </div>

            {/* Features & Addons Section */}
            {features && features.items && (
                <div className="gf-features-section">
                    <h2 className="gf-features-heading">{features.title}</h2>
                    <p className="gf-features-description">
                        Gutena is both easy to use and extremely powerful. We have tons of helpful features that allow us to give you everything you need from a form builder.
                    </p>
                    <div className="gf-features-grid">
                        {features.items.map((feature, index) => (
                            <div key={index} className="gf-feature-item">
                                {feature.icon && (
                                    <div className="gf-feature-icon-wrapper">
                                        <img
                                            src={feature.icon}
                                            alt={feature.title || ''}
                                            className="gf-feature-icon"
                                        />
                                    </div>
                                )}
                                <div className="gf-feature-content">
                                    <h3 className="gf-feature-title">{feature.title}</h3>
                                    <p className="gf-feature-description">{feature.description}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                    <a
                        href="https://gutenaforms.com/#features"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="gf-see-all-features"
                    >
                        See All Features
                    </a>
                </div>
            )}

            {/* Upgrade to PRO Section - Only show if PRO is not active */}
            {!isProActive && (
                <div id={ 'gutena-forms-pricing' } className="gf-upgrade-pro-section">
                    <div className="gf-upgrade-left">
                        <h2 className="gf-upgrade-heading">Upgrade to PRO</h2>
                        <div className="gf-upgrade-list">
                            {proFeatures.map((feature, index) => (
                                <div key={index} className="gf-upgrade-item">
                                    <span className="gf-checkmark-icon">
                                        <CheckmarkIcon />
                                    </span>
                                    <span>{feature}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                    <div className="gf-upgrade-right">
                        <div className="gf-pro-label">PRO</div>
                        <div className="gf-pro-price">$59.99</div>
                        <div className="gf-pro-period">Per Year</div>
                        <a
                            href={gutenaFormsDashboard?.pricing_link || 'https://gutenaforms.com/pricing/?utm_source=plugin_dashboard&utm_medium=website&utm_campaign=free_plugin'}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="gf-upgrade-button"
                        >
                            Upgrade Now
                        </a>
                    </div>
                </div>
            )}

            {/* Testimonials Section */}
            <div className="gf-testimonials-section">
                <h2 className="gf-testimonials-heading">Testimonials</h2>
                <div className="gf-testimonials-list">
                    {testimonials.map((testimonial, index) => (
                        <div key={index} className="gf-testimonial-item">
                            {testimonial.image && (
                                <img
                                    src={testimonial.image}
                                    alt={testimonial.name || ''}
                                    className="gf-testimonial-avatar"
                                />
                            )}
                            <div className="gf-testimonial-content">
                                <p className="gf-testimonial-text">{testimonial.text}</p>
                                <p className="gf-testimonial-name">{testimonial.name}</p>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Video Modal */}
            {isVideoModalOpen && (
                <div
                    className={`gf-video-modal ${isClosing ? 'gf-modal-closing' : ''}`}
                    onClick={(e) => {
                        if (e.target.classList.contains('gf-video-modal')) {
                            handleCloseModal();
                        }
                    }}
                >
                    <div className={`gf-video-modal-content ${isClosing ? 'gf-zoom-out' : 'gf-zoom-in'}`}>
                        <button
                            className="gf-video-modal-close"
                            onClick={handleCloseModal}
                            aria-label="Close video"
                        >
                            ×
                        </button>
                        {videoId && (
                            <div className="gf-video-embed">
                                <iframe
                                    width="100%"
                                    height="100%"
                                    src={`https://www.youtube.com/embed/${videoId}?autoplay=1&enablejsapi=1`}
                                    style={{ border: 0 }}
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowFullScreen
                                    title="Gutena Forms Tutorial Video"
                                ></iframe>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}

export default Introduction;
