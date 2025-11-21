import React from 'react';
import './Author.scss';

interface AuthorProps {
    name?: string;
    avatar?: string;
    size?: 'small' | 'medium' | 'large';
    className?: string;
}

export const Author: React.FC<AuthorProps> = ({
    name,
    avatar,
    size = 'medium',
    className = ''
}) => {
    if (!name && !avatar) {
        return null;
    }

    const sizeClasses = {
        small: 'author-small',
        medium: 'author-medium',
        large: 'author-large'
    };

    const avatarSizes = {
        small: 32,
        medium: 48,
        large: 64
    };

    return (
        <div className={`author ${sizeClasses[size]} ${className}`}>
            {avatar && (
                <img
                    src={avatar}
                    alt={name || 'Author'}
                    className="author-avatar"
                    width={avatarSizes[size]}
                    height={avatarSizes[size]}
                />
            )}
            {name && (
                <span className="author-name">{name}</span>
            )}
        </div>
    );
};

export default Author;
