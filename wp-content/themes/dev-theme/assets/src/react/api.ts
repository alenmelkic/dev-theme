import { WpReactData } from './types';

/**
 * Secure fetch wrapper for WordPress REST API
 * Automatically adds the Nonce header to requests
 */
export const secureFetch = async (url: string, options: RequestInit = {}): Promise<Response> => {
    const headers = new Headers(options.headers);

    // Get nonce from global data
    const wpData = window.wpReactData as WpReactData;

    if (wpData && wpData.nonce) {
        headers.set('X-WP-Nonce', wpData.nonce);
    }

    const config: RequestInit = {
        ...options,
        headers
    };

    return fetch(url, config);
};
