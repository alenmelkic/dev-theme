import React from 'react';
import styles from './Header.module.scss';
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

  const menuItemClasses = [
    styles.menuItem,
    item.current ? styles.current : '',
    hasChildren ? styles.hasChildren : ''
  ].filter(Boolean).join(' ');

  const subMenuClasses = [
    styles.subMenu,
    isDropdownOpen ? styles.isOpen : ''
  ].filter(Boolean).join(' ');

  return (
    <li
      className={menuItemClasses}
      onMouseEnter={handleMouseEnter}
      onMouseLeave={handleMouseLeave}
    >
      <a
        href={item.url}
        target={item.target || '_self'}
        onClick={toggleDropdown}
        aria-expanded={hasChildren ? isDropdownOpen : undefined}
        aria-haspopup={hasChildren ? 'true' : undefined}
      >
        {item.title}
        {hasChildren && isMobile && (
          <span className={styles.dropdownToggle} aria-hidden="true">
            {isDropdownOpen ? '−' : '+'}
          </span>
        )}
      </a>

      {hasChildren && (
        <ul
          className={subMenuClasses}
          style={{ display: isDropdownOpen ? 'block' : (isMobile ? 'none' : '') }}
        >
          {item.children.map((child) => (
            <MenuItem key={child.id} item={child} isMobile={isMobile} />
          ))}
        </ul>
      )}
    </li>
  );
};

interface HeaderWithModulesProps {
  siteName: string;
  tagline?: string;
  homeUrl: string;
  menuItems: MenuItemType[];
  isHome: boolean;
}

const HeaderWithModules: React.FC<HeaderWithModulesProps> = ({
  siteName = 'Site Name',
  tagline = '',
  homeUrl = '/',
  menuItems = [],
  isHome = false
}) => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = React.useState(false);

  console.log('🎯 Header received menuItems:', menuItems);
  console.log('🎯 Header menuItems length:', menuItems.length);

  const toggleMobileMenu = () => {
    setIsMobileMenuOpen(!isMobileMenuOpen);
  };

  // Close mobile menu when clicking outside
  React.useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (isMobileMenuOpen && !(event.target as Element).closest(`.${styles.navigation}`)) {
        setIsMobileMenuOpen(false);
      }
    };

    document.addEventListener('click', handleClickOutside);
    return () => document.removeEventListener('click', handleClickOutside);
  }, [isMobileMenuOpen]);

  const menuWrapperClasses = [
    styles.menuWrapper,
    'collapse navbar-collapse',
    isMobileMenuOpen ? 'show' : ''
  ].filter(Boolean).join(' ');

  return (
    <header id="masthead" className={styles.header} role="banner">
      <div className="container">
        <div className="row align-items-center">
          <div className="col-auto">
            <div className="site-branding">
              {isHome ? (
                <h1 className={styles.siteTitle}>
                  <a href={homeUrl} rel="home">{siteName}</a>
                </h1>
              ) : (
                <div className={styles.siteTitle}>
                  <a href={homeUrl} rel="home">{siteName}</a>
                </div>
              )}

              {tagline && (
                <p className={`${styles.siteDescription} small text-muted`}>{tagline}</p>
              )}
            </div>
          </div>

          <div className="col">
            <nav id="site-navigation" className={`${styles.navigation} ms-auto`} role="navigation" aria-label="Primary menu">
              {/* Mobile Menu Toggle */}
              <button
                className="menu-toggle d-lg-none btn btn-outline-primary"
                aria-controls="menu-main"
                aria-expanded={isMobileMenuOpen}
                onClick={toggleMobileMenu}
                type="button"
              >
                <span className="visually-hidden">Toggle Menu</span>
                <span className={styles.togglerIcon}>
                  <span></span>
                  <span></span>
                  <span></span>
                </span>
              </button>

              {/* Desktop & Mobile Menu */}
              <div className={menuWrapperClasses}>
                <ul id="menu-main" className={`${styles.menu} navbar-nav ms-auto`}>
                  {menuItems.map((item) => (
                    <MenuItem
                      key={item.id}
                      item={item}
                      isMobile={isMobileMenuOpen}
                    />
                  ))}
                </ul>
              </div>
            </nav>
          </div>
        </div>
      </div>
    </header>
  );
};

export default HeaderWithModules;