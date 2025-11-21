import General from './_general';
// Import Bootstrap JavaScript for navbar functionality
import 'bootstrap/js/dist/collapse';

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
	},
};

document.addEventListener('DOMContentLoaded', () => {
	App.init();
});
