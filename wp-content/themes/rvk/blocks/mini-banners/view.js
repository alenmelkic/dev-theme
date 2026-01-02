/**
 * Mini Banners Infinite Carousel
 *
 * Handles infinite auto-scrolling with bidirectional support
 * and smooth performance using requestAnimationFrame.
 */

class MiniBannersCarousel {
	constructor(element) {
		this.element = element;
		this.tracks = element.querySelectorAll('.carousel-track');
		this.isManuallyPaused = false;
		this.isTransientPaused = false; // For hover, focus, visibility
		this.animationFrameId = null;
		this.trackStates = new Map();
		this.liveRegion = element.querySelector('[role="status"]');
		this.pauseButton = element.querySelector('.carousel-pause-btn');

		// Respect reduced motion preference
		this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		if (!this.prefersReducedMotion) {
			this.init();
		} else {
			this.isManuallyPaused = true;
			this.announceToScreenReader('Carousel animation disabled due to motion preference');
		}
	}

	init() {
		this.tracks.forEach(track => this.initTrack(track));
		this.setupEventListeners();
		this.startAnimation();
	}

	initTrack(track) {
		const row = track.querySelector('.carousel-row');
		const direction = track.getAttribute('data-direction') || 'ltr';
		const isMobile = window.innerWidth < 768;
		const isSecondRow = track.classList.contains('carousel-track-second');

		// Handle "Split & Merge" logic
		const allItems = Array.from(row.querySelectorAll('.banner-item'));

		if (!isMobile) {
			// Desktop: Split banners between rows (Row 1 even, Row 2 odd)
			allItems.forEach((item) => {
				const originalId = parseInt(item.getAttribute('data-id')) || 0;
				const isEven = originalId % 2 === 0;

				if (isSecondRow) {
					if (isEven) item.style.display = 'none';
				} else {
					if (!isEven) item.style.display = 'none';
				}
			});
		} else {
			// Mobile: Show all items in first row (CSS handles hiding second row)
			allItems.forEach(item => item.style.display = '');
		}

		// Measure total width of visible items
		const visibleItems = Array.from(row.querySelectorAll('.banner-item')).filter(item => item.style.display !== 'none');
		let totalWidth = 0;

		visibleItems.forEach(item => {
			totalWidth += item.offsetWidth;
		});

		// Add gaps (1rem = 16px typically) - use count of visible items
		const gap = parseFloat(getComputedStyle(row).gap) || 16;
		if (visibleItems.length > 0) {
			totalWidth += gap * visibleItems.length;
		}

		// Reset point is 1/2 of total (since we doubled the array in PHP now)
		const resetPoint = totalWidth / 2;

		// Get responsive speed
		const speed = this.getScrollSpeed();

		// Initialize state
		const state = {
			row,
			direction,
			totalWidth,
			resetPoint,
			speed,
			currentPosition: direction === 'rtl' ? -resetPoint : 0,
			lastTimestamp: null
		};

		this.trackStates.set(track, state);
		this.updatePosition(state);
	}

	getScrollSpeed() {
		const width = window.innerWidth;

		if (width < 768) return 30; // Mobile: 30px/sec
		if (width < 1024) return 40; // Tablet: 40px/sec
		return 50; // Desktop: 50px/sec
	}

	updatePosition(state) {
		const transform = `translate3d(${state.currentPosition}px, 0, 0)`;
		state.row.style.transform = transform;
	}

	animate(timestamp) {
		if (this.isManuallyPaused || this.isTransientPaused) {
			this.animationFrameId = requestAnimationFrame(this.animate.bind(this));
			return;
		}

		this.trackStates.forEach(state => {
			// Calculate delta time
			if (!state.lastTimestamp) {
				state.lastTimestamp = timestamp;
			}

			const deltaTime = timestamp - state.lastTimestamp;
			state.lastTimestamp = timestamp;

			// Calculate movement (pixels per frame)
			const movement = (state.speed / 1000) * deltaTime;

			// Update position based on direction
			if (state.direction === 'ltr') {
				state.currentPosition -= movement;

				// Reset at 1/2 point
				if (Math.abs(state.currentPosition) >= state.resetPoint) {
					state.currentPosition += state.resetPoint;
				}
			} else {
				// RTL: scroll right
				state.currentPosition += movement;

				if (state.currentPosition >= 0) {
					state.currentPosition -= state.resetPoint;
				}
			}

			this.updatePosition(state);
		});

		this.animationFrameId = requestAnimationFrame(this.animate.bind(this));
	}

	startAnimation() {
		if (this.animationFrameId) {
			cancelAnimationFrame(this.animationFrameId);
		}
		this.animationFrameId = requestAnimationFrame(this.animate.bind(this));
	}

	/**
	 * Manual toggle (User clicked the button)
	 */
	toggleManualPause() {
		if (this.isManuallyPaused) {
			this.isManuallyPaused = false;
			this.trackStates.forEach(state => {
				state.lastTimestamp = null;
			});
			this.updatePauseButton(false);
			this.announceToScreenReader('Carousel playing');
		} else {
			this.isManuallyPaused = true;
			this.updatePauseButton(true);
			this.announceToScreenReader('Carousel paused');
		}
	}

	/**
	 * Transient pause (Hover, Visibility, Focus)
	 * Does NOT update the button UI
	 */
	setTransientPause(isPaused) {
		this.isTransientPaused = isPaused;
		if (!isPaused) {
			this.trackStates.forEach(state => {
				state.lastTimestamp = null;
			});
		}
	}

	updatePauseButton(isPaused) {
		if (!this.pauseButton) return;

		if (isPaused) {
			this.pauseButton.textContent = 'Play';
			this.pauseButton.setAttribute('aria-label', 'Play carousel auto-scroll');
			this.pauseButton.setAttribute('data-state', 'paused');
		} else {
			this.pauseButton.textContent = 'Pause';
			this.pauseButton.setAttribute('aria-label', 'Pause carousel auto-scroll');
			this.pauseButton.setAttribute('data-state', 'playing');
		}
	}

	announceToScreenReader(message) {
		if (!this.liveRegion) return;

		// Clear and set message for screen readers
		this.liveRegion.textContent = '';
		setTimeout(() => {
			this.liveRegion.textContent = message;
		}, 100);
	}

	setupEventListeners() {
		// Play/Pause button
		if (this.pauseButton) {
			this.pauseButton.addEventListener('click', () => {
				this.toggleManualPause();
			});
		}

		// Pause on hover
		this.element.addEventListener('mouseenter', () => this.setTransientPause(true));
		this.element.addEventListener('mouseleave', () => this.setTransientPause(false));

		// Pause when tab hidden
		document.addEventListener('visibilitychange', () => {
			this.setTransientPause(document.hidden);
		});

		// Recalculate on resize (debounced)
		let resizeTimeout;
		window.addEventListener('resize', () => {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(() => {
				this.trackStates.forEach(state => {
					state.speed = this.getScrollSpeed();
				});
				this.updateAriaHidden();
			}, 300);
		});

		// Pause when link focused
		const links = this.element.querySelectorAll('.banner-link');
		links.forEach(link => {
			link.addEventListener('focus', () => this.setTransientPause(true));
			link.addEventListener('blur', () => this.setTransientPause(false));
		});

		// Initial aria-hidden setup
		this.updateAriaHidden();
	}

	updateAriaHidden() {
		const tracks = this.element.querySelectorAll('.carousel-track');
		const isMobile = window.innerWidth < 768;

		tracks.forEach((track, index) => {
			if (index === 0) {
				// First row always visible
				track.setAttribute('aria-hidden', 'false');
			} else if (track.classList.contains('carousel-track-second')) {
				// Second row hidden on mobile (matches CSS display: none)
				track.setAttribute('aria-hidden', isMobile ? 'true' : 'false');
			}
		});
	}

	shuffleRowItems(row) {
		const items = Array.from(row.children);
		if (items.length <= 1) return;

		// Fisher-Yates Shuffle
		for (let i = items.length - 1; i > 0; i--) {
			const j = Math.floor(Math.random() * (i + 1));
			[items[i], items[j]] = [items[j], items[i]];
		}

		// Re-append in new order
		items.forEach(item => row.appendChild(item));
	}

	destroy() {
		if (this.animationFrameId) {
			cancelAnimationFrame(this.animationFrameId);
		}
	}
}

// Initialize all carousels
function initMiniBannersCarousels() {
	const carousels = document.querySelectorAll('.rvk-mini-banners-carousel');

	if (carousels.length === 0) return;

	carousels.forEach(carousel => {
		new MiniBannersCarousel(carousel);
	});
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initMiniBannersCarousels);
} else {
	initMiniBannersCarousels();
}

// Swup compatibility (page transitions)
document.addEventListener('swup:content:replace', initMiniBannersCarousels);
document.addEventListener('swup:page:view', initMiniBannersCarousels);
