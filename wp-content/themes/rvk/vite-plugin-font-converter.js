import ttf2woff from 'ttf2woff';
import ttf2woff2 from 'ttf2woff2';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export function fontConverter(options = {}) {
	const {
		inputDir = path.resolve(__dirname, 'assets/fonts'),
		outputDir = path.resolve(__dirname, 'dist/fonts'),
		fonts = [
			'BeVietnamPro-ExtraLight.ttf',
			'BeVietnamPro-Regular.ttf',
			'BeVietnamPro-Medium.ttf',
			'BeVietnamPro-Bold.ttf',
		],
	} = options;

	return {
		name: 'vite-plugin-font-converter',

		async closeBundle() {
			// Create output directory if it doesn't exist
			if (!fs.existsSync(outputDir)) {
				fs.mkdirSync(outputDir, { recursive: true });
			}

			let successCount = 0;

			for (const fontFile of fonts) {
				try {
					const ttfPath = path.resolve(inputDir, fontFile);

					if (!fs.existsSync(ttfPath)) {
						this.warn(`⚠️  Font not found: ${fontFile}`);
						continue;
					}

					const ttfBuffer = fs.readFileSync(ttfPath);
					const baseName = fontFile.replace('.ttf', '');

					// Convert to WOFF2
					try {
						const woff2Buffer = ttf2woff2(ttfBuffer);
						const woff2Path = path.resolve(outputDir, `${baseName}.woff2`);
						fs.writeFileSync(woff2Path, woff2Buffer);
						this.info(`✓ ${fontFile} → WOFF2`);
					} catch (err) {
						this.error(`❌ WOFF2 conversion failed: ${fontFile}`);
						throw err;
					}

					// Convert to WOFF
					try {
						const woffBuffer = Buffer.from(ttf2woff(ttfBuffer).buffer);
						const woffPath = path.resolve(outputDir, `${baseName}.woff`);
						fs.writeFileSync(woffPath, woffBuffer);
						this.info(`✓ ${fontFile} → WOFF`);
					} catch (err) {
						this.error(`❌ WOFF conversion failed: ${fontFile}`);
						throw err;
					}

					successCount++;
				} catch (err) {
					this.error(`❌ Font conversion failed for ${fontFile}:`, err);
					throw new Error(`Font conversion failed. Build aborted.`);
				}
			}

			this.info(
				`✅ Font conversion complete: ${successCount} fonts → ${successCount * 2} files`,
			);
		},
	};
}
