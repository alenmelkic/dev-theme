export interface MenuItem {
    id: number;
    title: string;
    url: string;
    current: boolean;
    parent: number;
    target: string;
    description: string;
    children: MenuItem[];
}

export interface WpReactData {
    siteName: string;
    tagline: string;
    homeUrl: string;
    isHome: boolean;
    currentYear: string;
    menuItems: MenuItem[];
    footerWidgets: any[]; // Replace with specific type when known
    socialLinks: any[];   // Replace with specific type when known
    copyrightText?: string;
    pageId?: number;
    pageSlug?: string;
    restUrl?: string;
    nonce?: string;
}

export interface ServicneTag {
    id: number;
    name: string;
    slug: string;
    description: string;
    count: number;
    link: string;
}

export interface ServicneInformacije {
    id: number;
    date: string;
    date_gmt: string;
    modified: string;
    modified_gmt: string;
    slug: string;
    status: string;
    type: string;
    link: string;
    title: {
        rendered: string;
    };
    content: {
        rendered: string;
        protected: boolean;
    };
    excerpt: {
        rendered: string;
        protected: boolean;
    };
    featured_media: number;
    featured_media_url?: string;
    servicne_tag: number[];
    days_remaining: number;
    is_expiring_soon: boolean;
    author_name?: string;
    author_avatar?: string;
    _embedded?: {
        'wp:featuredmedia'?: Array<{
            id: number;
            source_url: string;
            alt_text: string;
            media_details: {
                width: number;
                height: number;
                sizes: Record<string, {
                    source_url: string;
                    width: number;
                    height: number;
                }>;
            };
        }>;
        'wp:term'?: Array<Array<ServicneTag>>;
    };
}

export interface ServicneInformacijeListParams {
    per_page?: number;
    page?: number;
    servicne_tag?: number;
    search?: string;
    orderby?: 'date' | 'title' | 'modified';
    order?: 'asc' | 'desc';
}

declare global {
    interface Window {
        wpReactData: WpReactData;
        siteVars?: any;
    }
}
