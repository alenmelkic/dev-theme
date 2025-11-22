import General from './_general';
// Import Bootstrap JavaScript for navbar functionality
import 'bootstrap/js/dist/collapse';
import initSwup from './swup-init';
import initAudioManager from './audio-manager';

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

		// Init Swup (PJAX)
		const swup = initSwup();

		// Re-init header button on navigation
		if (swup && player) {
			swup.hooks.on('content:replace', () => {
				player.reinitHeader();
			});
		}
	},
};

document.addEventListener('DOMContentLoaded', () => {
	App.init();
});
