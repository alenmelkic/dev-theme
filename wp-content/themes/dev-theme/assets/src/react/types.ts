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

declare global {
    interface Window {
        wpReactData: WpReactData;
        siteVars?: any;
    }
}
