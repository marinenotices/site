<?php
if (!defined('ABSPATH')) exit;
/**
 * Page Template
 *
 * This template is the default page template. It is used to display content when someone is viewing a
 * singular view of a page ('page' post_type) unless another page template overrules this one.
 * @link http://codex.wordpress.org/Pages
 *
 * @package WooFramework
 * @subpackage Template
 */
get_header();
global $woo_options;
?>

    <div id="content" class="page">

        <div class="wrapper">

            <?php woo_main_before(); ?>

            <section id="main">

                <?php
                if (have_posts()) {
                    $count = 0;
                    while (have_posts()) {
                        the_post();
                        $count++;
                        ?>
                        <article <?php post_class(); ?>>
                            <?php $categories = get_the_terms($post, 'notice_category'); ?>

                            <header>
                                <h1><img class="marker alignnone" src="<?php echo MarineNotice::getNoticeIconURL(MarineNotice::getNoticeTypeFromPost($post)) ?>"><?php the_title(); ?></h1>
                            </header>

                            <section class="entry">

                                <?php if (has_post_thumbnail()): ?>
                                    <?php the_post_thumbnail(); ?>
                                <?php endif; ?>

                                <?php $location = MNPostmeta::getLocationsFromPost($post, true); ?>
                                <?php if ($location !== null): ?>
                                    <p><strong>Latitude: <?php echo $location['lat']; ?></strong><br />
                                    <strong>Longitude: <?php echo $location['long']; ?></strong></p>
                                <?php endif; ?>

                                <?php the_content(); ?>

                                <?php wp_link_pages(array('before' => '<div class="page-link">' . __('Pages:', 'woothemes'), 'after' => '</div>')); ?>
                            </section><!-- /.entry -->

                            <?php edit_post_link(__('{ Edit }', 'woothemes'), '<span class="small">', '</span>'); ?>

                        </article><!-- /.post -->

                        <?php

                    } // End WHILE Loop
                } else {
                    ?>
                    <article <?php post_class(); ?>>
                        <p><?php _e('Sorry, no posts matched your criteria.', 'woothemes'); ?></p>
                    </article><!-- /.post -->
                <?php } // End IF Statement ?>

            </section><!-- /#main -->

            <?php woo_main_after(); ?>

            <?php get_sidebar(); ?>

        </div><!-- /.wrapper -->

        <?php comments_template(); ?>

    </div><!-- /#content -->

<?php get_footer(); ?>