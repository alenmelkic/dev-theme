import React from 'react';
import './FeaturedImage.scss';

interface FeaturedImageProps {
    src: string;
    alt: string;
    className?: string;
    loading?: 'lazy' | 'eager';
    variant?: 'post' | 'card';
}

export const FeaturedImage: React.FC<FeaturedImageProps> = ({
    src,
    alt,
    className,
    loading = 'lazy',
    variant = 'post'
}) => {
    if (!src) return null;

    const baseClass = variant === 'card' ? 'card-featured-image' : 'post-featured-image';
    const finalClass = className || baseClass;

    return (
        <div className={finalClass}>
            <img
                src={src}
                alt={alt}
                loading={loading}
            />
        </div>
    );
};

export default FeaturedImage;
