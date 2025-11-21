import React from 'react';
import { createRoot } from 'react-dom/client';
import Header from '../features/navigation/Header';
import Footer from '../features/footer/Footer';
import { WpReactData } from '../types';

// WordPress data that can be passed from PHP
const wpData = window.wpReactData as WpReactData || {};

// Initialize Header component
const headerElement = document.getElementById('react-header');
console.log('🔍 Header element found:', headerElement);

if (headerElement) {
  console.log('✅ Rendering header component...');
  try {
    const headerRoot = createRoot(headerElement);
    headerRoot.render(
      <React.StrictMode>
        <Header
          siteName={wpData.siteName || 'Site Name'}
          tagline={wpData.tagline || ''}
          homeUrl={wpData.homeUrl || '/'}
          menuItems={wpData.menuItems || []}
          isHome={wpData.isHome || false}
        />
      </React.StrictMode>
    );
    console.log('✅ Header component rendered successfully!');
  } catch (error) {
    console.error('❌ Error rendering header:', error);
  }
} else {
  console.error('❌ react-header element not found in DOM');
}

// Initialize Footer component
const footerElement = document.getElementById('react-footer');
if (footerElement) {
  const footerRoot = createRoot(footerElement);
  footerRoot.render(
    <React.StrictMode>
      <Footer
        copyrightText={wpData.copyrightText || ''}
        siteName={wpData.siteName || 'Site Name'}
        currentYear={wpData.currentYear || new Date().getFullYear()}
        footerWidgets={wpData.footerWidgets || []}
        socialLinks={wpData.socialLinks || []}
      />
    </React.StrictMode>
  );
}