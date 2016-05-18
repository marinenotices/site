<?php
/**
 * Super simple widget.
 */
class MNAuthorWidget extends WP_Widget
{
    public function __construct()
    {                      // id_base        ,  visible name
        parent::__construct( 'mn_author_widget', 'MarineNotice Author Widget' );
    }

    public function widget( $args, $instance )
    {
        echo $args['before_widget'];

        if (is_author() || is_singular('notice')) {
            ?>
            <h3 class="title">About <?php printf( esc_attr__( '%s', 'woothemes' ), get_the_author() ); ?></h3>
            <div class="profile-image"><?php echo get_avatar( get_the_author_meta( 'ID' ), '180' ); ?></div>
			<div class="profile-content">
				<?php echo wpautop( get_the_author_meta( 'description' ) ); ?>
				<ul class="social">
					<?php if ( '' != get_the_author_meta( 'twitter' ) ): ?>
					<li><a class="twitter" href="<?php echo esc_url( 'http://twitter.com/' . get_the_author_meta( 'twitter' ) ); ?>"><span><?php _e('Twitter', 'woothemes'); ?></span></a></li>
					<?php endif; ?>
					<?php if ( '' != get_the_author_meta( 'facebook' ) ): ?>
					<li><a class="facebook" href="<?php echo esc_url( get_the_author_meta( 'facebook' ) ); ?>"><span><?php _e('Facebook', 'woothemes'); ?></span></a></li>
					<?php endif; ?>
					<?php if ( '' != get_the_author_meta( 'gplus' ) ): ?>
					<li><a class="gplus" href="<?php echo esc_url( get_the_author_meta( 'gplus' ) ); ?>"><span><?php _e('Google+', 'woothemes'); ?></span></a></li>
					<?php endif; ?>
				</ul>
				<?php if ( !is_author() ): ?>
				<div class="profile-link">
					<a class="button" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>">
						<?php printf( esc_attr__( 'More by %s', 'woothemes' ), get_the_author() ); ?>
					</a>
				</div><!-- #profile-link -->
				<?php endif; ?>
			</div><!-- .post-entries -->

            <h3 class="title">Author's Notices</h3>
            <div class="profile-map-content">
            <?php echo do_shortcode('[navionics authorID="' . get_the_author_meta( 'ID' ) . '" units="false" scale="false" fit="true"]'); ?>
            </div>
            <?php
        }
        echo $args['after_widget'];
    }

/*    public function form( $instance )
    {

    }*/
}