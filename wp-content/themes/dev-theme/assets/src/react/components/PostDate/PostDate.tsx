import React from 'react';
import './PostDate.scss';

interface PostDateProps {
    date: string;
    locale?: string;
    format?: 'short' | 'long' | 'full';
    className?: string;
    label?: string;
}

export const PostDate: React.FC<PostDateProps> = ({
    date,
    locale = 'hr-HR',
    format = 'long',
    className = 'post-date',
    label
}) => {
    const formatOptions: Record<string, Intl.DateTimeFormatOptions> = {
        short: {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric'
        },
        long: {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        },
        full: {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }
    };

    const formattedDate = new Date(date).toLocaleDateString(locale, formatOptions[format]);

    return (
        <time dateTime={date} className={className}>
            {label && <span className="date-label">{label} </span>}
            {formattedDate}
        </time>
    );
};

export default PostDate;
