<?php
/**
 * Template for Servicne informacije archive
 */

get_header();
?>

<main id="main" class="site-main servicne-informacije-archive">
    <div class="container">
        <header class="page-header">
            <h1 class="page-title">Servicne informacije</h1>
            <p class="archive-description">
                Važne informacije koje su dostupne 15 dana od objave. Nakon toga se automatski uklanjaju.
            </p>
        </header>

        <!-- React will mount here -->
        <div id="react-servicne-informacije-list"></div>
    </div>
</main>

<?php
get_footer();
