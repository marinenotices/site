<?php
/*
Template Name: Authors
*/

if (!defined('ABSPATH')) exit;

$number = 10;
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$offset = ($paged - 1) * $number;
$users = get_users();
$query = get_users('&offset=' . $offset . '&number=' . $number);
$total_users = count($users);
$total_query = count($query);
$total_pages = intval($total_users / $number) + 1;

if ($total_users > $total_query) {
    $current_page = max(1, get_query_var('paged'));
    $paginationHTML = '<div class="woo-pagination">' . paginate_links(array(
        'base' => get_pagenum_link(1) . '%_%',
        'format' => 'page/%#%/',
        'total' => $total_pages,
        'current' => $current_page,
        'prev_next' => true,
        'prev_text' => __('&larr; Previous', 'woothemes'),
        'next_text' => __('Next &rarr;', 'woothemes'),
        'show_all' => false,
        'end_size' => 1,
        'mid_size' => 1,
        'add_fragment' => '',
        'type' => 'plain',
        'before' => '<div class="pagination woo-pagination">',
        'after' => '</div>',
        'use_search_permastruct' => true
    )) . '</div>';
}

get_header(); ?>

    <div id="content" class="authors">

        <div class="wrapper">

            <?php woo_main_before(); ?>

            <section id="main">

                <?php if (have_posts()): ?>
                    <?php $count = 0; ?>
                    <?php while (have_posts()): ?>
                        <?php the_post(); ?>
                        <?php $count++; ?>
                        <article <?php post_class(); ?>>

                            <header>
                                <h1><?php the_title(); ?></h1>
                            </header>

                            <section class="entry">
                                <?php the_content(); ?>

                                <?php wp_link_pages(array('before' => '<div class="page-link">' . __('Pages:', 'woothemes'), 'after' => '</div>')); ?>
                            </section><!-- /.entry -->


                            <?php edit_post_link(__('{ Edit }', 'woothemes'), '<span class="small">', '</span>'); ?>

                        </article><!-- /.post -->

                    <?php endwhile; ?>

                <?php endif; ?>

                <?php woo_loop_before(); ?>

                <?php echo $paginationHTML; ?>

                <?php foreach ($query as $q): ?>

                    <div class="user-data">
                        <div class="user-avatar">
                            <?php echo get_avatar($q->ID, 80); ?>
                        </div>

                        <h4 class="user-name">
                            <a href="<?php echo get_author_posts_url($q->ID); ?>">
                                <?php echo get_the_author_meta('display_name', $q->ID); ?>
                            </a>
                        </h4>


                        <?php if (get_the_author_meta('description', $q->ID) != '') : ?>
                            <p><?php echo get_the_author_meta('description', $q->ID); ?></p>
                        <?php endif; ?>
                    </div>

                <?php endforeach; ?>

                <?php woo_loop_after(); ?>

                <?php echo $paginationHTML; ?>

            </section><!-- /#main -->

            <?php woo_main_after(); ?>

            <?php get_sidebar(); ?>

        </div><!-- /.wrapper -->

    </div><!-- /#content -->

<?php get_footer(); ?>