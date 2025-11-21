import React from 'react';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import FullPageApp from '../features/app/FullPageApp';
import { WpReactData } from '../types';

// Initialize React Query Client
const queryClient = new QueryClient();

// Initialize full page React app
const appElement = document.getElementById('react-app');
const wpData = window.wpReactData as WpReactData || {};

if (appElement) {
  const root = createRoot(appElement);
  root.render(
    <React.StrictMode>
      <QueryClientProvider client={queryClient}>
        <FullPageApp data={wpData} />
      </QueryClientProvider>
    </React.StrictMode>
  );
  console.log('✅ Full page React app rendered successfully!');
} else {
  console.log('ℹ️ No react-app element found, skipping full page app initialization');
}