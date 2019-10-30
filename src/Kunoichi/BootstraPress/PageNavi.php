<?php

namespace Kunoichi\BootstraPress;

/**
 * Class PageNavi
 *
 * @package Kunoichi\BootstraPress
 */
class PageNavi {
	
	const UNLIKELY_BIG = 999999999;
	
	/**
	 * Display Bootstrap styled pagination.
	 *
	 * @param array $args
	 */
	public static function pagination( $args = [] ) {
		$args = wp_parse_args( $args, [
			'position'  => 'start',
			'prev_text' => '&lsaquo;',
			'next_text' => '&rsaquo;',
			'end_size'  => 1,
			'mid_size'  => 2,
			'prev_next' => true,
			'aria_label' => 'Page navigation',
		] );
		global $wp_query;
		$links = paginate_links( array_merge( $args, [
			'base'    => str_replace( self::UNLIKELY_BIG, '%#%', esc_url( get_pagenum_link( self::UNLIKELY_BIG ) ) ),
			'format'  => '?paged=%#%',
			'current' => max( 1, get_query_var('paged') ),
			'total'   => $wp_query->max_num_pages,
			'type'    => 'array',
		] ) );
		if ( ! $links ) {
			return;
		}
		?>
		<nav aria-label="<?php echo esc_attr( $args['aria_label'] ) ?>">
			<ul class="pagination justify-content-<?php echo esc_attr( $args['position'] ) ?>">
				<?php echo implode( " ", array_map( function( $link ) {
					if ( false !== strpos( $link, '<span' ) ) {
						if ( false !== strpos( $link, 'dots' ) ) {
							return sprintf( '<li class="page-item disabled">%s</li>', str_replace( 'dots', 'dots page-link', $link ) );
						} else {
							return sprintf( '<li class="page-item active">%s</li>', str_replace( 'class=\'', 'class=\'page-link ', $link ) );
						}
					} else {
						return sprintf( '<li class="page-item">%s</li>', str_replace( 'page-numbers', 'page-numbers page-link', $link ) );
					}
				}, $links ) ); ?>
			</ul>
		</nav>
		<?php
	}
	
	/**
	 * Display pagination for pages.
	 *
	 * @param array $args
	 */
	public static function link_pages( $args = [] ) {
		$args = wp_parse_args( $args, [
			'size'       => 'pagination-sm',
			'aria_label' => 'Pagination',
			'prefix'     => '',
		] );
		$links = wp_link_pages( [
			'before' => '',
			'after'  => '',
			'separator' => ':::',
			'echo'   => false,
		] );
		if ( empty( $links ) ) {
			return;
		}
		printf( '<nav aria-label="%s"><ul class="pagination%s">',
			    esc_attr( $args['aria_label'] ),
			 $args['size'] ? ' ' . esc_attr( $args['size'] ) : '' );
		if ( $args['prefix'] ) {
			printf( '<li class="page-item page-item-prefix disabled"><span class="page-link page-link-prefix">%s</span></li>', esc_html( $args['prefix'] ) );
		}
		echo implode( '', array_map( function( $link ) {
			if ( false !== strpos( $link, '<span' ) ) {
				if ( false !== strpos( $link, 'current' ) ) {
					return sprintf( '<li class="page-item active">%s</li>', str_replace( 'current', 'current page-link', $link ) );
				} else {
					return sprintf( '<li class="page-item disabled">%s</li>', str_replace( 'class="', 'class="page-link ', $link ) );
				}
			} else {
				return sprintf( '<li class="page-item">%s</li>', str_replace( 'class="', 'class="page-link ', $link ) );
			}
		}, explode( ':::', $links ) ) );
		echo '</ul></nav>';
	}
}
