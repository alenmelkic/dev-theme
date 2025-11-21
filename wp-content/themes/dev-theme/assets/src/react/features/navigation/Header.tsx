import React from 'react';
import { MenuItem as MenuItemType } from '../../types';

interface MenuItemProps {
  item: MenuItemType;
  isMobile?: boolean;
}

const MenuItem: React.FC<MenuItemProps> = ({ item, isMobile = false }) => {
  const [isDropdownOpen, setIsDropdownOpen] = React.useState(false);
  const hasChildren = item.children && item.children.length > 0;

  const toggleDropdown = (e: React.MouseEvent) => {
    if (isMobile && hasChildren) {
      e.preventDefault();
      setIsDropdownOpen(!isDropdownOpen);
    }
  };

  const handleMouseEnter = () => {
    if (!isMobile && hasChildren) {
      setIsDropdownOpen(true);
    }
  };

  const handleMouseLeave = () => {
    if (!isMobile && hasChildren) {
      setIsDropdownOpen(false);
    }
  };

  if (hasChildren) {
    // Dropdown menu item
    return (
      <li
        className={`nav-item dropdown ${item.current ? 'active' : ''}`}
        onMouseEnter={handleMouseEnter}
        onMouseLeave={handleMouseLeave}
      >
        <a
          className={`nav-link dropdown-toggle ${item.current ? 'active' : ''}`}
          href={item.url}
          target={item.target || '_self'}
          onClick={toggleDropdown}
          aria-expanded={hasChildren ? isDropdownOpen : undefined}
          role="button"
          data-bs-toggle="dropdown"
        >
          {item.title}
        </a>

        <ul className={`dropdown-menu ${isDropdownOpen ? 'show' : ''}`}>
          {item.children.map((child) => (
            <li key={child.id}>
              <a
                className={`dropdown-item ${child.current ? 'active' : ''}`}
                href={child.url}
                target={child.target || '_self'}
              >
                {child.title}
              </a>
            </li>
          ))}
        </ul>
      </li>
    );
  }

  // Regular menu item
  return (
    <li className={`nav-item ${item.current ? 'active' : ''}`}>
      <a
        className={`nav-link ${item.current ? 'active' : ''}`}
        href={item.url}
        target={item.target || '_self'}
      >
        {item.title}
      </a>
    </li>
  );
};

interface HeaderProps {
  siteName: string;
  tagline?: string;
  homeUrl: string;
  menuItems: MenuItemType[];
  isHome: boolean;
}

const Header: React.FC<HeaderProps> = ({
  siteName = 'Site Name',
  tagline = '',
  homeUrl = '/',
  menuItems = [],
  isHome = false
}) => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = React.useState(false);

  // Debug menu items in Header component
  console.log('🎯 Header received menuItems:', menuItems);
  console.log('🎯 Header menuItems length:', menuItems.length);

  const toggleMobileMenu = () => {
    setIsMobileMenuOpen(!isMobileMenuOpen);
  };

  // Close mobile menu when clicking outside or on menu links
  React.useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (isMobileMenuOpen && !(event.target as Element).closest('.navbar')) {
        setIsMobileMenuOpen(false);
      }
    };

    const handleNavLinkClick = (event: MouseEvent) => {
      // Close mobile menu when clicking on nav links (but not dropdown toggles)
      if (isMobileMenuOpen && (event.target as Element).matches('.nav-link:not(.dropdown-toggle)')) {
        setIsMobileMenuOpen(false);
      }
    };

    document.addEventListener('click', handleClickOutside);
    document.addEventListener('click', handleNavLinkClick);

    return () => {
      document.removeEventListener('click', handleClickOutside);
      document.removeEventListener('click', handleNavLinkClick);
    };
  }, [isMobileMenuOpen]);

  return (
    <header id="masthead" className="site-header" role="banner">
      <nav className="navbar navbar-expand-lg navbar-light bg-white">
        <div className="container">
          {/* Site Branding */}
          <div className="navbar-brand">
            {isHome ? (
              <h1 className="site-title mb-0">
                <a href={homeUrl} rel="home" className="text-decoration-none">{siteName}</a>
              </h1>
            ) : (
              <div className="site-title mb-0">
                <a href={homeUrl} rel="home" className="text-decoration-none">{siteName}</a>
              </div>
            )}

            {tagline && (
              <p className="site-description mb-0 small text-muted">{tagline}</p>
            )}
          </div>

          {/* Mobile Menu Toggle Button */}
          <button
            className="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded={isMobileMenuOpen}
            aria-label="Toggle navigation"
            onClick={toggleMobileMenu}
          >
            <span className="navbar-toggler-icon"></span>
          </button>

          {/* Navigation Menu */}
          <div className={`collapse navbar-collapse ${isMobileMenuOpen ? 'show' : ''}`} id="navbarNav">
            <ul className="navbar-nav ms-auto">
              {menuItems.map((item) => (
                <MenuItem
                  key={item.id}
                  item={item}
                  isMobile={isMobileMenuOpen}
                />
              ))}
            </ul>
          </div>
        </div>
      </nav>
    </header>
  );
};

export default Header;