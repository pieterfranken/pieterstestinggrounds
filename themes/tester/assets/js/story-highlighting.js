/**
 * Unified Story Highlighting System
 * Uses canvas-based highlighting for both desktop and mobile with consistent appearance
 */
class StoryHighlighter {
    constructor(contentElementId, highlightBtnId, clearBtnId) {
        this.storyContent = document.getElementById(contentElementId);
        this.highlightBtn = document.getElementById(highlightBtnId);
        this.clearHighlightsBtn = document.getElementById(clearBtnId);
        this.highlightMode = false;
        this.isMobile = this.detectMobile();
        this.isDrawing = false;
        this.lastDrawPoint = null;
        this.highlightCanvas = null;
        this.canvasContext = null;
        this.highlightColor = '#ffeb3b';
        this.highlightOpacity = 0.6; // Increased opacity since canvas is now behind text

        this.init();
    }

    detectMobile() {
        // More specific mobile detection - only detect actual mobile devices, not desktop with touch
        const isMobileUserAgent = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        const isSmallScreen = window.innerWidth <= 768; // Mobile screen size
        const isMobileDevice = isMobileUserAgent && isSmallScreen;

        console.log('Mobile detection details:');
        console.log('- User agent mobile:', isMobileUserAgent);
        console.log('- Small screen:', isSmallScreen);
        console.log('- Touch support:', 'ontouchstart' in window);
        console.log('- Max touch points:', navigator.maxTouchPoints);
        console.log('- Final mobile detection:', isMobileDevice);

        return isMobileDevice;
    }

    init() {
        console.log('Initializing StoryHighlighter'); // Debug log
        console.log('Story content element:', this.storyContent); // Debug log
        console.log('Is mobile:', this.isMobile); // Debug log

        this.setupCanvas(); // Always setup canvas for unified highlighting
        this.setupEventListeners();

        console.log('StoryHighlighter initialization complete'); // Debug log
    }

    setupEventListeners() {
        console.log('Setting up event listeners'); // Debug log

        // Toggle highlight mode
        this.highlightBtn.addEventListener('click', () => {
            this.toggleHighlightMode();
        });

        // Clear highlights
        this.clearHighlightsBtn.addEventListener('click', () => {
            this.clearAllHighlights();
        });

        // Set up both mobile and desktop events for testing
        if (this.isMobile) {
            console.log('Setting up mobile events (primary)'); // Debug log
            this.setupMobileEvents();
            console.log('Also setting up desktop events for fallback'); // Debug log
            this.setupDesktopEvents();
        } else {
            console.log('Setting up desktop events (primary)'); // Debug log
            this.setupDesktopEvents();
            console.log('Also setting up mobile events for fallback'); // Debug log
            this.setupMobileEvents();
        }

        console.log('Event listeners setup complete'); // Debug log
    }

    setupCanvas() {
        console.log('Setting up canvas'); // Debug log

        // Create canvas as background layer for unified highlighting (both desktop and mobile)
        this.highlightCanvas = document.createElement('canvas');
        this.highlightCanvas.style.position = 'absolute';
        this.highlightCanvas.style.top = '0';
        this.highlightCanvas.style.left = '0';
        this.highlightCanvas.style.pointerEvents = 'none'; // Canvas doesn't intercept events
        this.highlightCanvas.style.zIndex = '1'; // Behind text content
        this.highlightCanvas.style.opacity = '1';
        this.highlightCanvas.style.background = 'transparent';

        // Make story content container relative for canvas positioning
        this.storyContent.style.position = 'relative';
        this.storyContent.style.zIndex = '2'; // Text content above canvas

        // Insert canvas as first child so it appears behind text
        this.storyContent.insertBefore(this.highlightCanvas, this.storyContent.firstChild);

        this.canvasContext = this.highlightCanvas.getContext('2d');
        console.log('Canvas context:', this.canvasContext); // Debug log

        this.resizeCanvas();

        // Test canvas by drawing a visible test stroke
        this.canvasContext.strokeStyle = '#ff0000';
        this.canvasContext.lineWidth = 5;
        this.canvasContext.globalAlpha = 1.0;
        this.canvasContext.beginPath();
        this.canvasContext.moveTo(20, 20);
        this.canvasContext.lineTo(100, 20);
        this.canvasContext.stroke();
        this.canvasContext.beginPath();
        this.canvasContext.moveTo(20, 30);
        this.canvasContext.lineTo(100, 30);
        this.canvasContext.stroke();
        console.log('Test strokes drawn on canvas at (20,20) and (20,30)'); // Debug log
        console.log('Canvas dimensions:', this.highlightCanvas.width, 'x', this.highlightCanvas.height); // Debug log

        // Resize canvas when window resizes
        window.addEventListener('resize', () => {
            this.resizeCanvas();
        });

        console.log('Canvas setup complete'); // Debug log
    }

    resizeCanvas() {
        if (!this.highlightCanvas) return;

        const rect = this.storyContent.getBoundingClientRect();
        this.highlightCanvas.width = this.storyContent.scrollWidth;
        this.highlightCanvas.height = this.storyContent.scrollHeight;
        this.highlightCanvas.style.width = this.storyContent.scrollWidth + 'px';
        this.highlightCanvas.style.height = this.storyContent.scrollHeight + 'px';
    }

    setupMobileEvents() {
        // Touch events for mobile highlighting
        this.storyContent.addEventListener('touchstart', (e) => {
            if (!this.highlightMode) return;
            e.preventDefault();
            this.startDrawing(e.touches[0]);
        });

        this.storyContent.addEventListener('touchmove', (e) => {
            if (!this.highlightMode || !this.isDrawing) return;
            e.preventDefault();
            this.draw(e.touches[0]);
        });

        this.storyContent.addEventListener('touchend', (e) => {
            if (!this.highlightMode) return;
            e.preventDefault();
            this.stopDrawing();
        });
    }

    setupDesktopEvents() {
        // Desktop mouse events for unified canvas highlighting
        this.storyContent.addEventListener('mousedown', (e) => {
            if (!this.highlightMode) return;
            e.preventDefault();
            console.log('Desktop mousedown event triggered'); // Debug log
            this.startDrawing(e);
        });

        this.storyContent.addEventListener('mousemove', (e) => {
            if (!this.highlightMode || !this.isDrawing) return;
            e.preventDefault();
            console.log('Desktop mousemove event triggered'); // Debug log
            this.draw(e);
        });

        this.storyContent.addEventListener('mouseup', (e) => {
            if (!this.highlightMode) return;
            e.preventDefault();
            console.log('Desktop mouseup event triggered'); // Debug log
            this.stopDrawing();
        });

        // Also handle mouse leave to stop drawing if mouse leaves the area
        this.storyContent.addEventListener('mouseleave', (e) => {
            if (!this.highlightMode || !this.isDrawing) return;
            console.log('Desktop mouseleave event triggered'); // Debug log
            this.stopDrawing();
        });

        // Prevent text selection during highlighting
        this.storyContent.addEventListener('selectstart', (e) => {
            if (this.highlightMode) {
                e.preventDefault();
            }
        });
    }

    toggleHighlightMode() {
        this.highlightMode = !this.highlightMode;
        console.log('Highlight mode toggled to:', this.highlightMode); // Debug log
        console.log('Is mobile device:', this.isMobile); // Debug log

        if (this.highlightMode) {
            this.storyContent.classList.add('highlight-mode');
            this.highlightBtn.classList.remove('btn-outline-warning');
            this.highlightBtn.classList.add('btn-warning');

            // Disable text selection for both platforms (canvas stays behind, doesn't intercept events)
            this.storyContent.style.userSelect = 'none';
            this.storyContent.style.webkitUserSelect = 'none';
            this.storyContent.style.mozUserSelect = 'none';
            this.storyContent.style.msUserSelect = 'none';

            console.log('Highlight mode enabled, events should be active'); // Debug log
        } else {
            this.storyContent.classList.remove('highlight-mode');
            this.highlightBtn.classList.remove('btn-warning');
            this.highlightBtn.classList.add('btn-outline-warning');

            // Re-enable text selection
            this.storyContent.style.userSelect = '';
            this.storyContent.style.webkitUserSelect = '';
            this.storyContent.style.mozUserSelect = '';
            this.storyContent.style.msUserSelect = '';

            console.log('Highlight mode disabled'); // Debug log
        }
    }

    startDrawing(event) {
        this.isDrawing = true;
        const rect = this.storyContent.getBoundingClientRect();

        // Handle both mouse and touch events
        const clientX = event.clientX || (event.touches && event.touches[0].clientX);
        const clientY = event.clientY || (event.touches && event.touches[0].clientY);

        console.log('startDrawing called with coordinates:', clientX, clientY); // Debug log
        console.log('Story content rect:', rect); // Debug log

        this.lastDrawPoint = {
            x: clientX - rect.left + this.storyContent.scrollLeft,
            y: clientY - rect.top + this.storyContent.scrollTop
        };

        console.log('lastDrawPoint set to:', this.lastDrawPoint); // Debug log
    }

    draw(event) {
        if (!this.isDrawing || !this.lastDrawPoint) {
            console.log('draw() early return - isDrawing:', this.isDrawing, 'lastDrawPoint:', this.lastDrawPoint); // Debug log
            return;
        }

        const rect = this.storyContent.getBoundingClientRect();

        // Handle both mouse and touch events
        const clientX = event.clientX || (event.touches && event.touches[0].clientX);
        const clientY = event.clientY || (event.touches && event.touches[0].clientY);

        const currentPoint = {
            x: clientX - rect.left + this.storyContent.scrollLeft,
            y: clientY - rect.top + this.storyContent.scrollTop
        };

        console.log('Drawing from', this.lastDrawPoint, 'to', currentPoint); // Debug log

        // Use source-over to prevent overlap darkening and apply consistent opacity
        this.canvasContext.globalCompositeOperation = 'source-over';
        this.canvasContext.globalAlpha = this.highlightOpacity;
        this.canvasContext.strokeStyle = this.highlightColor;
        this.canvasContext.lineWidth = 25; // Wider stroke for better visibility behind text
        this.canvasContext.lineCap = 'round';
        this.canvasContext.lineJoin = 'round';

        this.canvasContext.beginPath();
        this.canvasContext.moveTo(this.lastDrawPoint.x, this.lastDrawPoint.y);
        this.canvasContext.lineTo(currentPoint.x, currentPoint.y);
        this.canvasContext.stroke();

        console.log('Canvas stroke drawn'); // Debug log

        // Reset alpha for future operations
        this.canvasContext.globalAlpha = 1.0;

        this.lastDrawPoint = currentPoint;
    }

    stopDrawing() {
        this.isDrawing = false;
        this.lastDrawPoint = null;
    }

    clearAllHighlights() {
        // Clear canvas highlights (unified for both desktop and mobile)
        if (this.canvasContext) {
            this.canvasContext.clearRect(0, 0, this.highlightCanvas.width, this.highlightCanvas.height);
        }

        // Also clear any legacy text-based highlights that might exist
        const highlights = this.storyContent.querySelectorAll('.highlighted');
        highlights.forEach(highlight => {
            const parent = highlight.parentNode;
            while (highlight.firstChild) {
                parent.insertBefore(highlight.firstChild, highlight);
            }
            parent.removeChild(highlight);
        });
    }
}

// Initialize highlighting when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a story quiz page
    if (document.getElementById('story-content')) {
        new StoryHighlighter('story-content', 'highlight-btn', 'clear-highlights-btn');
    }
});
