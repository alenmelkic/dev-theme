import { useQuery } from '@tanstack/react-query';
import { secureFetch } from '../api';

export const useWpPosts = (postType: string = 'posts', params: Record<string, string> = {}) => {
    return useQuery({
        queryKey: ['wp', postType, params],
        queryFn: async () => {
            const queryString = new URLSearchParams(params).toString();
            const url = `/wp-json/wp/v2/${postType}?${queryString}`;
            const response = await secureFetch(url);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        }
    });
};

export const useWpPage = (slug: string) => {
    return useQuery({
        queryKey: ['wp', 'pages', slug],
        queryFn: async () => {
            const response = await secureFetch(`/wp-json/wp/v2/pages?slug=${slug}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            return data.length > 0 ? data[0] : null;
        },
        enabled: !!slug
    });
};
