import React from 'react';
import './PostTerms.scss';

interface Term {
    id: number;
    name: string;
    link: string;
    slug: string;
}

interface PostTermsProps {
    terms: Term[];
    taxonomy: 'category' | 'tag' | 'custom';
    label?: string;
    className?: string;
    showHash?: boolean;
    variant?: 'default' | 'compact';
}

export const PostTerms: React.FC<PostTermsProps> = ({
    terms,
    taxonomy,
    label,
    className,
    showHash = false,
    variant = 'default'
}) => {
    if (!terms || terms.length === 0) return null;

    const baseClass = variant === 'compact' ? 'card-terms' : 'post-terms';
    const finalClass = className || baseClass;

    const termClass = taxonomy === 'category' ? 'category-badge' :
        taxonomy === 'tag' ? 'tag-badge' :
            'term-badge';

    return (
        <div className={finalClass}>
            {label && variant === 'default' && <strong className="terms-label">{label}</strong>}
            <div className={`terms-list ${taxonomy}-list`}>
                {terms.map(term => (
                    <a
                        key={term.id}
                        href={term.link}
                        className={`term-link ${termClass}`}
                    >
                        {showHash && '#'}{term.name}
                    </a>
                ))}
            </div>
        </div>
    );
};

export default PostTerms;
