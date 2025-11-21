import React from 'react';
import './PostTitle.scss';

interface PostTitleProps {
    title: string;
    level?: 'h1' | 'h2' | 'h3';
    className?: string;
}

export const PostTitle: React.FC<PostTitleProps> = ({
    title,
    level = 'h1',
    className = 'post-title'
}) => {
    const Tag = level;

    return (
        <Tag
            className={className}
            dangerouslySetInnerHTML={{ __html: title }}
        />
    );
};

export default PostTitle;
