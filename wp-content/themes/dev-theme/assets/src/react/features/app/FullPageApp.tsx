import React, { Suspense } from 'react';
import Header from '../navigation/Header';
import Footer from '../footer/Footer';
import { WpReactData } from '../../types';
import { useWpPage, useWpPosts } from '../../hooks/useWpData';

interface FullPageAppProps {
  data: WpReactData;
}

interface Post {
  id: number;
  link: string;
  title: {
    rendered: string;
  };
}

const RecentPosts = () => {
  const { data: posts, isLoading, isError } = useWpPosts('posts', { per_page: '5' });

  if (isLoading) return <div>Loading posts...</div>;
  if (isError) return <div>Error loading posts.</div>;

  return (
    <div className="recent-posts">
      <h3>Recent Posts</h3>
      <ul>
        {posts?.map((post: Post) => (
          <li key={post.id}>
            <a href={post.link}>{post.title.rendered}</a>
          </li>
        ))}
      </ul>
    </div>
  );
};

const FullPageApp: React.FC<FullPageAppProps> = ({ data: wpData }) => {
  // Get current page ID or slug from URL
  const currentPath = window.location.pathname;
  const slug = currentPath === '/' ? 'home' : currentPath.replace(/\//g, '');

  const { data: pageData, isLoading, isError } = useWpPage(slug);

  if (isLoading) {
    return (
      <div className="site-wrapper">
        <Header
          siteName={wpData.siteName}
          tagline={wpData.tagline}
          homeUrl={wpData.homeUrl}
          menuItems={wpData.menuItems}
          isHome={wpData.isHome}
        />
        <main className="site-main container py-5">
          <div className="text-center">
            <div className="spinner-border" role="status">
              <span className="visually-hidden">Loading...</span>
            </div>
          </div>
        </main>
        <Suspense fallback={<div className="text-center py-3">Loading footer...</div>}>
          <Footer
            copyrightText={wpData.copyrightText}
            siteName={wpData.siteName}
            currentYear={wpData.currentYear}
            footerWidgets={wpData.footerWidgets}
            socialLinks={wpData.socialLinks}
          />
        </Suspense>
      </div>
    );
  }

  return (
    <div className="site-wrapper">
      <Header
        siteName={wpData.siteName}
        tagline={wpData.tagline}
        homeUrl={wpData.homeUrl}
        menuItems={wpData.menuItems}
        isHome={wpData.isHome}
      />

      <main id="primary" className="site-main container py-5">
        {isError || !pageData ? (
          <div className="error-message">
            <h1>Page Not Found</h1>
            <p>Sorry, the content you are looking for could not be found.</p>
            <RecentPosts />
          </div>
        ) : (
          <article className="page-content">
            <h1 className="page-title mb-4">{pageData.title.rendered}</h1>
            <div
              className="entry-content"
              dangerouslySetInnerHTML={{ __html: pageData.content.rendered }}
            />

            {/* Show recent posts on home page or if specifically requested */}
            {(wpData.isHome || slug === 'home') && (
              <div className="mt-5">
                <hr />
                <RecentPosts />
              </div>
            )}
          </article>
        )}
      </main>

      <Suspense fallback={<div className="text-center py-3">Loading footer...</div>}>
        <Footer
          copyrightText={wpData.copyrightText}
          siteName={wpData.siteName}
          currentYear={wpData.currentYear}
          footerWidgets={wpData.footerWidgets}
          socialLinks={wpData.socialLinks}
        />
      </Suspense>
    </div>
  );
};

export default FullPageApp;