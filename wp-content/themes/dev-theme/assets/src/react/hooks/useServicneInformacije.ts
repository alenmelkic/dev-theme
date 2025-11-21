import { useQuery } from '@tanstack/react-query';
import { secureFetch } from '../api';
import type { ServicneInformacije, ServicneInformacijeListParams, ServicneTag } from '../types';

// Get REST URL base from WordPress
const getRestUrl = () => {
    const wpData = window.wpReactData;
    return wpData?.restUrl || '/wp-json/';
};

/**
 * Hook to fetch all Servicne informacije posts
 */
export const useServicneInformacije = (params: ServicneInformacijeListParams = {}) => {
    const queryParams = new URLSearchParams();

    // Set default values
    queryParams.set('per_page', String(params.per_page || 10));
    queryParams.set('_embed', '1'); // Embed featured media and terms

    if (params.page) queryParams.set('page', String(params.page));
    if (params.servicne_tag) queryParams.set('servicne_tag', String(params.servicne_tag));
    if (params.search) queryParams.set('search', params.search);
    if (params.orderby) queryParams.set('orderby', params.orderby);
    if (params.order) queryParams.set('order', params.order);

    return useQuery({
        queryKey: ['servicne-informacije', params],
        queryFn: async () => {
            const url = `${getRestUrl()}wp/v2/servicne-informacije?${queryParams.toString()}`;
            console.log('Fetching from URL:', url);
            const response = await secureFetch(url);
            if (!response.ok) {
                console.error('Failed to fetch servicne informacije:', response.status, response.statusText);
                const errorText = await response.text();
                console.error('Error details:', errorText);
                throw new Error(`Failed to fetch servicne informacije: ${response.status}`);
            }
            const data: ServicneInformacije[] = await response.json();

            // Extract featured image URL from embedded data
            return data.map(post => ({
                ...post,
                featured_media_url: post._embedded?.['wp:featuredmedia']?.[0]?.source_url || ''
            }));
        }
    });
};

/**
 * Hook to fetch a single Servicna informacija by slug
 */
export const useServicnaInformacija = (slug: string) => {
    return useQuery({
        queryKey: ['servicna-informacija', slug],
        queryFn: async () => {
            const url = `${getRestUrl()}wp/v2/servicne-informacije?slug=${slug}&_embed=1`;
            console.log('Fetching single post from URL:', url);
            const response = await secureFetch(url);
            if (!response.ok) {
                console.error('Failed to fetch servicna informacija:', response.status, response.statusText);
                const errorText = await response.text();
                console.error('Error details:', errorText);
                throw new Error(`Failed to fetch servicna informacija: ${response.status}`);
            }
            const data: ServicneInformacije[] = await response.json();

            if (data.length === 0) return null;

            const post = data[0];
            return {
                ...post,
                featured_media_url: post._embedded?.['wp:featuredmedia']?.[0]?.source_url || ''
            };
        },
        enabled: !!slug
    });
};

/**
 * Hook to fetch Servicne oznake (tags)
 */
export const useServicneOznake = () => {
    return useQuery({
        queryKey: ['servicne-oznake'],
        queryFn: async () => {
            const url = `${getRestUrl()}wp/v2/servicne-oznake?per_page=100`;
            const response = await secureFetch(url);
            if (!response.ok) {
                throw new Error('Failed to fetch tags');
            }
            return response.json() as Promise<ServicneTag[]>;
        }
    });
};
