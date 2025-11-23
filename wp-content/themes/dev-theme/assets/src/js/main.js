import General from './_general';
// Import Bootstrap JavaScript for navbar functionality
import 'bootstrap/js/dist/collapse';
import initSwup from './swup-init';
import initAudioManager from './audio-manager';
import { initSoundCloudPlayers, cleanupSoundCloudPlayers } from './soundcloud-custom-player';

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

		// Init Audio Player
		const player = initAudioManager();

		// Init SoundCloud Players
		initSoundCloudPlayers();

		// Init Swup (PJAX)
		const swup = initSwup();

		// Expose Swup globally for other scripts
		window.swup = swup;

		// Re-init header button on navigation
		if (swup) {
			swup.hooks.on('content:replace', () => {
				if (player) {
					player.reinitHeader();
				}
				cleanupSoundCloudPlayers();
				initSoundCloudPlayers();
			});
		}
	},
};

document.addEventListener('DOMContentLoaded', () => {
	App.init();
});
