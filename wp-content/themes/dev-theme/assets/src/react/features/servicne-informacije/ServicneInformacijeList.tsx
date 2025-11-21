import React, { useState } from 'react';
import { useServicneInformacije, useServicneOznake } from '../../hooks/useServicneInformacije';
import { Author } from '../../components/Author';
import type { ServicneInformacijeListParams } from '../../types';

interface ServicneInformacijeListProps {
    initialPerPage?: number;
}

export const ServicneInformacijeList: React.FC<ServicneInformacijeListProps> = ({
    initialPerPage = 12
}) => {
    const [filters, setFilters] = useState<ServicneInformacijeListParams>({
        per_page: initialPerPage,
        page: 1,
        orderby: 'date',
        order: 'desc'
    });

    const { data: posts, isLoading, error } = useServicneInformacije(filters);
    const { data: oznake } = useServicneOznake();

    const handleTagFilter = (tagId: number | undefined) => {
        setFilters(prev => ({
            ...prev,
            servicne_tag: tagId,
            page: 1
        }));
    };

    const handleSearch = (searchTerm: string) => {
        setFilters(prev => ({
            ...prev,
            search: searchTerm || undefined,
            page: 1
        }));
    };

    if (error) {
        return (
            <div className="servicne-informacije-error">
                <p>Greška pri učitavanju informacija. Molimo pokušajte ponovo.</p>
            </div>
        );
    }

    return (
        <div className="servicne-informacije-container">
            {/* Filters */}
            <div className="servicne-informacije-filters">
                <div className="filter-group">
                    <input
                        type="search"
                        placeholder="Pretraži informacije..."
                        onChange={(e) => handleSearch(e.target.value)}
                        className="search-input"
                    />
                </div>

                {oznake && oznake.length > 0 && (
                    <div className="filter-group">
                        <label htmlFor="tag-filter">Oznaka:</label>
                        <select
                            id="tag-filter"
                            onChange={(e) => handleTagFilter(e.target.value ? Number(e.target.value) : undefined)}
                            value={filters.servicne_tag || ''}
                        >
                            <option value="">Sve oznake</option>
                            {oznake.map(oznaka => (
                                <option key={oznaka.id} value={oznaka.id}>
                                    {oznaka.name} ({oznaka.count})
                                </option>
                            ))}
                        </select>
                    </div>
                )}
            </div>

            {/* Loading State */}
            {isLoading && (
                <div className="servicne-informacije-loading">
                    <p>Učitavanje...</p>
                </div>
            )}

            {/* Posts Grid */}
            {!isLoading && posts && posts.length > 0 && (
                <div className="servicne-informacije-grid">
                    {posts.map(post => (
                        <article key={post.id} className="servicna-informacija-card">
                            {post.featured_media_url && (
                                <div className="card-image">
                                    <img
                                        src={post.featured_media_url}
                                        alt={post.title.rendered}
                                        loading="lazy"
                                    />
                                </div>
                            )}

                            <div className="card-content">
                                <h2 className="card-title">
                                    <a href={post.link} dangerouslySetInnerHTML={{ __html: post.title.rendered }} />
                                </h2>

                                {post.excerpt.rendered && (
                                    <div
                                        className="card-excerpt"
                                        dangerouslySetInnerHTML={{ __html: post.excerpt.rendered }}
                                    />
                                )}

                                {/* Author Info */}
                                <Author
                                    name={post.author_name}
                                    avatar={post.author_avatar}
                                    size="small"
                                />

                                <div className="card-meta">
                                    <time dateTime={post.date}>
                                        {new Date(post.date).toLocaleDateString('hr-HR')}
                                    </time>
                                </div>

                                {post._embedded?.['wp:term'] && (
                                    <div className="card-terms">
                                        {post._embedded['wp:term'][0]?.map(term => (
                                            <span key={term.id} className="term-badge tag-badge">
                                                #{term.name}
                                            </span>
                                        ))}
                                    </div>
                                )}

                                <a href={post.link} className="card-link">
                                    Pročitaj više →
                                </a>
                            </div>
                        </article>
                    ))}
                </div>
            )}

            {/* No Results */}
            {!isLoading && posts && posts.length === 0 && (
                <div className="servicne-informacije-empty">
                    <p>Nema pronađenih informacija.</p>
                </div>
            )}
        </div>
    );
};

export default ServicneInformacijeList;
