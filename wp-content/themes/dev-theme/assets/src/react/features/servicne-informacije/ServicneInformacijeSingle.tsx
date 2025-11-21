import React from 'react';
import { useServicnaInformacija } from '../../hooks/useServicneInformacije';
import { Author } from '../../components/Author';

interface ServicneInformacijeSingleProps {
    slug: string;
}

export const ServicneInformacijeSingle: React.FC<ServicneInformacijeSingleProps> = ({ slug }) => {
    const { data: post, isLoading, error } = useServicnaInformacija(slug);

    if (error) {
        return (
            <div className="servicna-informacija-error">
                <p>Greška pri učitavanju informacije. Molimo pokušajte ponovo.</p>
            </div>
        );
    }

    if (isLoading) {
        return (
            <div className="servicna-informacija-loading">
                <p>Učitavanje...</p>
            </div>
        );
    }

    if (!post) {
        return (
            <div className="servicna-informacija-not-found">
                <h1>Informacija nije pronađena</h1>
                <p>Tražena informacija možda više ne postoji ili je uklonjena.</p>
                <a href="/servicne-informacije/" className="back-link">← Nazad na sve informacije</a>
            </div>
        );
    }

    return (
        <article className="servicna-informacija-single">
            {/* Header */}
            <div className="post-header">
                <div className="post-meta-top">
                    <time dateTime={post.date}>
                        Objavljeno: {new Date(post.date).toLocaleDateString('hr-HR', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        })}
                    </time>
                </div>

                <h1
                    className="post-title"
                    dangerouslySetInnerHTML={{ __html: post.title.rendered }}
                />

                {/* Author Info */}
                <Author
                    name={post.author_name}
                    avatar={post.author_avatar}
                    size="medium"
                />

                {/* Tags */}
                {post._embedded?.['wp:term'] && post._embedded['wp:term'][0]?.length > 0 && (
                    <div className="post-terms">
                        <div className="tags">
                            <strong>Oznake:</strong>
                            {post._embedded['wp:term'][0].map(term => (
                                <a
                                    key={term.id}
                                    href={term.link}
                                    className="term-link tag-link"
                                >
                                    #{term.name}
                                </a>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* Featured Image */}
            {post.featured_media_url && (
                <div className="post-featured-image">
                    <img
                        src={post.featured_media_url}
                        alt={post.title.rendered}
                    />
                </div>
            )}

            {/* Content */}
            <div
                className="post-content"
                dangerouslySetInnerHTML={{ __html: post.content.rendered }}
            />
        </article>
    );
};

export default ServicneInformacijeSingle;
