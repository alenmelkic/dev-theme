import React from 'react';

interface FooterWidget {
  title?: string;
  content: string;
}

interface SocialLink {
  url: string;
  label: string;
  platform: string;
}

interface FooterProps {
  copyrightText?: string;
  siteName?: string;
  currentYear?: string | number;
  footerWidgets?: FooterWidget[];
  socialLinks?: SocialLink[];
}

const Footer: React.FC<FooterProps> = ({
  copyrightText = '',
  siteName = 'Site Name',
  currentYear = new Date().getFullYear(),
  footerWidgets = [],
  socialLinks = []
}) => {
  return (
    <footer id="colophon" className="site-footer">
      <div className="container">
        {footerWidgets.length > 0 && (
          <div className="footer-widgets">
            <div className="row">
              {footerWidgets.map((widget, index) => (
                <div key={index} className={`col-md-${12 / footerWidgets.length}`}>
                  <div className="footer-widget">
                    {widget.title && <h3 className="widget-title">{widget.title}</h3>}
                    <div
                      className="widget-content"
                      dangerouslySetInnerHTML={{ __html: widget.content }}
                    />
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {socialLinks.length > 0 && (
          <div className="social-links">
            <ul className="social-menu">
              {socialLinks.map((link, index) => (
                <li key={index}>
                  <a
                    href={link.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label={link.label}
                  >
                    <i className={`fab fa-${link.platform}`} aria-hidden="true"></i>
                    <span className="sr-only">{link.label}</span>
                  </a>
                </li>
              ))}
            </ul>
          </div>
        )}

        <div className="site-info">
          <div className="copyright">
            {copyrightText ? (
              <p dangerouslySetInnerHTML={{ __html: copyrightText }} />
            ) : (
              <p>&copy; {currentYear} {siteName}. All rights reserved.</p>
            )}
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;