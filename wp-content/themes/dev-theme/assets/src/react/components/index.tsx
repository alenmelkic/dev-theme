import React from 'react';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import Header from '../features/navigation/Header';
import Footer from '../features/footer/Footer';
import { ServicneInformacijeList, ServicneInformacijeSingle } from '../features/servicne-informacije';
import { WpReactData } from '../types';

// WordPress data that can be passed from PHP
const wpData = window.wpReactData as WpReactData || {};

// Create a QueryClient instance for React Query
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: 1,
      staleTime: 5 * 60 * 1000, // 5 minutes
    },
  },
});

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

// Initialize Servicne informacije List (Archive page)
const servicneListElement = document.getElementById('react-servicne-informacije-list');
if (servicneListElement) {
  console.log('✅ Rendering Servicne informacije list...');
  const listRoot = createRoot(servicneListElement);
  listRoot.render(
    <React.StrictMode>
      <QueryClientProvider client={queryClient}>
        <ServicneInformacijeList initialPerPage={12} />
      </QueryClientProvider>
    </React.StrictMode>
  );
}

// Initialize Servicna informacija Single (Single post page)
const servicnaSingleElement = document.getElementById('react-servicna-informacija-single');
if (servicnaSingleElement) {
  const slug = servicnaSingleElement.getAttribute('data-slug');
  if (slug) {
    console.log('✅ Rendering Servicna informacija single:', slug);
    const singleRoot = createRoot(servicnaSingleElement);
    singleRoot.render(
      <React.StrictMode>
        <QueryClientProvider client={queryClient}>
          <ServicneInformacijeSingle slug={slug} />
        </QueryClientProvider>
      </React.StrictMode>
    );
  }
}