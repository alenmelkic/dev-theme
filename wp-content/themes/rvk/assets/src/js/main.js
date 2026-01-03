import General from './_general';
import '../scss/main.scss'; // Import SCSS for Vite HMR
// Import Bootstrap JavaScript for navbar functionality
import 'bootstrap/js/dist/collapse';
import initSwup from './swup-init';
import initAudioManager from './audio-manager';
import { initSoundCloudPlayers, cleanupSoundCloudPlayers } from './soundcloud-custom-player';
import { initFacebookVideoPlayers, cleanupFacebookVideoPlayers } from './facebook-video-player';
import { initYouTubeVideoPlayers, cleanupYouTubeVideoPlayers } from './youtube-video-player';
import { initNavigation } from './navigation';
import { initLoadMore } from './load-more';

const App = {
	/**
	 * App.init
	 */
	init() {
		// General scripts
		function initGeneral() {
			return new General();
		}
		initGeneral();

		// Init Navigation
		initNavigation();
		window.initNavigation = initNavigation;

		// Init Audio Player
		const player = initAudioManager();

		// Init SoundCloud Players
		initSoundCloudPlayers();

		// Init Facebook Video Players
		initFacebookVideoPlayers();

		// Init YouTube Video Players
		initYouTubeVideoPlayers();

		// Init Swup (PJAX)
		const swup = initSwup();

		// Expose Swup globally for other scripts
		window.swup = swup;

		// Init Load More
		initLoadMore();

		// Re-init header button on navigation
		if (swup) {
			swup.hooks.on('content:replace', () => {
				if (player) {
					player.reinitHeader();
				}
				cleanupSoundCloudPlayers();
				initSoundCloudPlayers();
				cleanupFacebookVideoPlayers();
				initFacebookVideoPlayers();
				cleanupYouTubeVideoPlayers();
				initYouTubeVideoPlayers();
				initLoadMore();
			});
		}

		// Handle Load More re-init
		document.addEventListener('load-more:loaded', (e) => {
			cleanupSoundCloudPlayers();
			initSoundCloudPlayers();
			cleanupFacebookVideoPlayers();
			initFacebookVideoPlayers();
			cleanupYouTubeVideoPlayers();
			initYouTubeVideoPlayers();
		});
	},
};

document.addEventListener('DOMContentLoaded', () => {
	if (window.devThemeAppInitialized) {
		return;
	}
	window.devThemeAppInitialized = true;
	App.init();
});
