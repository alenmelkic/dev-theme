# SEO & AEO System - User Guide

Quick reference guide for using the SEO and AEO features in your WordPress theme.

---

## Table of Contents

1. [SEO Features](#seo-features)
2. [AEO Features](#aeo-features)
3. [Settings](#settings)
4. [How to Use](#how-to-use)
5. [Sitemap Management](#sitemap-management)
6. [Troubleshooting](#troubleshooting)

---

## SEO Features

### What's Included?

- **Meta Title Optimization** - Custom SEO titles for posts/pages
- **Meta Description** - Search-friendly descriptions (160 characters)
- **Focus Keywords** - Target keywords for your content
- **Canonical URLs** - Prevent duplicate content issues
- **Robots Meta Tags** - Control indexing (noindex, nofollow)
- **Open Graph Tags** - Social media sharing optimization
- **Twitter Cards** - Enhanced Twitter previews
- **XML Sitemap** - Automatic sitemap generation

### Where to Find SEO Settings?

**In WordPress Admin:**
1. Go to **Appearance → SEO/AEO Settings**
2. Or find the **SEO & AEO** panel in the post editor (right sidebar)

---

## AEO Features

### What is AEO?

**Answer Engine Optimization (AEO)** helps your content appear in AI-powered search engines like:
- ChatGPT
- Google SGE (Search Generative Experience)
- Perplexity AI
- Bing AI Chat

### AEO Features Included:

#### 1. **FAQ Schema**
Automatically detects questions and answers in your content and creates structured data.

**Example:**
```html
<h3>What is SEO?</h3>
<p>SEO is Search Engine Optimization...</p>
```
→ Automatically becomes FAQ schema for AI engines!

#### 2. **HowTo Schema**
Converts step-by-step guides into structured HowTo schema.

**Example:**
```html
<ol>
  <li>First step</li>
  <li>Second step</li>
  <li>Third step</li>
</ol>
```
→ Automatically becomes HowTo schema!

#### 3. **Key Takeaways**
Highlights the main points of your article for AI engines.

Displays a beautiful gradient box with bullet points at the top of your content.

---

## Settings

### Accessing Settings

Go to **WordPress Admin → Appearance → SEO/AEO Settings**

### Available Settings:

#### **General Settings**
- Enable/Disable SEO system
- Set default meta description
- Configure social media defaults

#### **Sitemap Settings**
- Enable/Disable XML sitemap
- Exclude specific post types
- Exclude specific taxonomies
- Include/exclude images
- Set max entries per page

#### **AI Settings** (if configured)
- API key configuration
- AI model selection
- Rate limiting settings

---

## How to Use

### For Individual Posts/Pages:

1. **Edit a post** in WordPress
2. Find the **SEO & AEO** panel (right sidebar)
3. Enter your SEO data:
   - SEO Title (50-60 characters)
   - Meta Description (150-160 characters)
   - Focus Keywords (comma-separated)
4. Click **🤖 Generate with AI** to auto-generate (if enabled)

### AI-Powered Generation:

Click these buttons to auto-generate SEO data:
- **🤖 Generiši sve AI** - Generate all SEO fields at once
- **🤖 Generiši sa AI** (Title) - Generate SEO title only
- **🤖 Generiši sa AI** (Description) - Generate meta description only
- **🤖 Ekstraktuj sa AI** (Keywords) - Extract keywords from content

### For FAQ Content:

Just write your content with questions as headings:

```html
<h3>Your question here?</h3>
<p>Your answer here.</p>
```

The FAQ schema will be automatically generated!

### For HowTo Content:

Use ordered lists for steps:

```html
<h2>How to Do Something</h2>
<ol>
  <li>Step one instructions</li>
  <li>Step two instructions</li>
  <li>Step three instructions</li>
</ol>
```

The HowTo schema will be automatically generated!

### For Key Takeaways:

1. Find **Key Takeaways (AEO)** meta box in post editor
2. Add 3-5 bullet points:
```
• First key point
• Second key point
• Third key point
```
3. Or click **🤖 Generate with AI**

---

## Sitemap Management

### Viewing Your Sitemap

Your sitemap is available at:
```
https://yoursite.com/wp-sitemap.xml
```

### Excluding Content from Sitemap

1. Go to **Appearance → SEO/AEO Settings**
2. Scroll to **XML Sitemap Settings**
3. Check the post types or taxonomies you want to exclude
4. Click **Save Settings**

### Example: Excluding "Death Notices"

If you have a custom post type called "obavijesti-o-smrti" (death notices):
1. Go to sitemap settings
2. Check ✅ **obavijesti-o-smrti**
3. Save
4. These posts will no longer appear in your sitemap

### Noindex Individual Posts

To exclude a single post from search engines:
1. Edit the post
2. Find **Advanced Settings** in SEO panel
3. Check ✅ **Noindex** (discourage search engines)
4. Save

---

## Troubleshooting

### My SEO fields aren't saving!

**Solution:** Make sure you click **Update** or **Publish** on the post.

### I don't see the SEO panel in my editor

**Solution:**
1. Click the ⚙️ (settings) icon in the top right
2. Go to **Panels**
3. Make sure **SEO & AEO** is checked

### AI generation isn't working

**Possible issues:**
1. **No API key configured** - Contact your administrator
2. **Rate limit reached** - Wait an hour (50 requests/hour limit)
3. **Insufficient permissions** - You need Author role or higher

### Sitemap shows excluded content

**Solution:**
1. Re-save sitemap settings
2. Clear WordPress cache (if using cache plugin)
3. Visit: `yoursite.com/wp-sitemap.xml?refresh=1`

### Duplicate meta tags appearing

**Solution:** This shouldn't happen, but if it does:
1. Disable other SEO plugins (Yoast, Rank Math, etc.)
2. Clear cache
3. Check theme doesn't output custom meta tags

---

## Best Practices

### SEO Title
- Keep it **50-60 characters**
- Include your main keyword
- Make it compelling and clickable
- Don't stuff keywords

### Meta Description
- Keep it **150-160 characters**
- Summarize the content
- Include a call-to-action
- Include main keyword naturally

### Keywords
- Use **3-5 focus keywords**
- Choose keywords with search volume
- Use comma-separated format: `keyword1, keyword2, keyword3`

### FAQ Content
- Use real questions people ask
- Keep answers concise (1-2 sentences)
- Use `<h3>` for questions
- Use `<p>` for answers

### HowTo Content
- Use numbered lists (`<ol>`)
- Keep steps clear and actionable
- Each step should be one action
- Use 3-10 steps ideally

### Key Takeaways
- List **3-5 main points**
- Keep them short and punchy
- Use bullet points
- Place at the top of your content

---

## Rate Limits

To prevent abuse and control costs:
- **50 AI requests per hour** per user
- Resets every hour automatically
- Admins can reset limits in settings

If you hit the limit:
- Wait 1 hour
- Or manually enter SEO data
- Or contact admin to reset

---

## Support

For technical issues or questions:
1. Check this guide first
2. Contact your website administrator
3. Review the QA test results in `QA-TEST-RESULTS.md`

---

## System Status

To check if everything is working:

Run this command in your theme directory:
```bash
php qa-final-report.php
```

You should see: ✅ **EXCELLENT - PRODUCTION READY**

---

*Last updated: 2026-01-04*
*Version: 1.0*
*System: SEO & AEO for WordPress*
