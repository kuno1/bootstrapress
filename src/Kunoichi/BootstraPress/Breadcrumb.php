<?php

namespace Kunoichi\BootstraPress;

use Kunoichi\BootstraPress\Breadcrumb\Item;

/**
 * Breadcrumb controller.
 *
 * @package bootstrapress
 */
class Breadcrumb {
	
	/**
	 * @var Item[]
	 */
	static $links = [];
	
	/**
	 * Constructor forbidden.
	 */
	final private function __construct() {}
	
	/**
	 * Display breadcrumbs.
	 *
	 * @param array $args
	 * @return void
	 */
	public static function display( $args = [] ) {
		$args = wp_parse_args( $args, [
			'type'  => 'list',
			'label' => '',
			'container_class' => 'breadcrumb',
			'item_class'      => 'breadcrumb-item',
			'text_class'      => 'breadcrumb-text',
			'link_class'      => 'breadcrumb-link',
			'separator'       => '',
		] );
		/** @var Item[] $links */
		$links = apply_filters( 'bootstrapress_breadcrumbs', self::get_page_items( $args['label'] ) );
		if ( ! $links ) {
			return;
		}
		switch ( $args['type'] ) {
			case 'unordered-list':
				$container_wrap = 'ul';
				$item_wrap      = 'li';
				break;
			case 'div':
				$container_wrap = 'div';
				$item_wrap      = 'div';
				break;
			case 'text':
				$container_wrap = 'div';
				$item_wrap      = '';
				break;
			default:
				$container_wrap = 'ol';
				$item_wrap      = 'li';
				break;
		}
		$counter = 0;
		$output = [];
		foreach ( $links as $link ) {
			$counter++;
			$span_class = $args['text_class'];
			if ( ! $link->link ) {
				$span_class .= ' breadcrumb-nolink';
			}
			$html = sprintf(
				'<span class="%2$s" property="name">%1$s</span>',
				esc_html( $link->label ),
				esc_attr( $span_class )
			);
			if ( $link->link ) {
				$html = sprintf(
					'<a href="%1$s" class="%4$s" property="item" typeof="WebPage"%3$s%5$s>%2$s</a>',
					esc_url( $link->link ),
					$html,
					$link->rel ? sprintf( ' rel="%s"', esc_attr( $link->rel ) ) : '',
					$args['link_class'],
					$link->current ? ' aria-current="page"' : ''
				);
			}
			if ( $item_wrap ) {
				$html = sprintf(
					'<%2$s class="%4$s" property="itemListElement" typeof="ListItem">%1$s<meta property="position" content="%3$d" /></%2$s>',
					$html,
					$item_wrap,
					$counter,
					esc_attr( $args['item_class'] )
				);
			}
			$output[] = $html;
		}
		echo sprintf( '<nav class="breadcrumb-container" aria-label="Breadcrumb"><%2$s class="%3$s" vocab="https://schema.org/" typeof="BreadcrumbList">%1$s</%2$s></nav>', implode( $args['separator'], $output ), $container_wrap, esc_attr( $args['container_class'] ) );
	}
	
	/**
	 * Get breadcrumb page item.
	 *
	 * @param string $home
	 *
	 * @return Item[]
	 */
	public static function get_page_items( $home = '' ) {
		if ( ! $home ) {
			$home = apply_filters( 'bootstrapress_breadcrumb_home', __( 'Home' ) );
		}
		if ( self::$links ) {
			return self::$links;
		}
		$links = [];
		// Single page.
		if ( is_singular() || is_attachment() ) {
			/** @var \WP_Post $post */
			$post = get_queried_object();
			// Set archive.
			if ( $archive = self::get_archive_page( $post ) ) {
				$links[] = $archive;
			}
			// Set parents.
			foreach ( self::get_ancestors( $post ) as $parent ) {
				$links[] = $parent;
			}
			// Set taxonomies.
			$taxonomies = [];
			switch ( $post->post_type ) {
				case 'page':
				case 'attachment':
					// Do nothing.
					break;
				case 'post':
					$taxonomies[ 'category' ] = get_the_category( $post->ID );
					break;
				default: // CPT
					$post_type_object = get_post_type_object( $post->post_type );
					if ( ! $post_type_object ) {
						break;
					}
					foreach ( $post_type_object->taxonomies as $taxonomy ) {
						$tax_obj = get_taxonomy( $taxonomy );
						if ( ! $tax_obj || ! $tax_obj->public ) {
							continue;
						}
						$terms = get_the_terms( $post, $taxonomy );
						if ( $tax_obj->hierarchical ) {
							$taxonomies[ $tax_obj->name ] = $terms;
						} elseif ( $terms && ! is_wp_error( $terms ) ) {
							$taxonomies[ $tax_obj->name ] = [ current( $terms ) ];
						}
					}
					break;
			}
			foreach ( $taxonomies as $taxonomy => $terms ) {
				if ( ! $terms || is_wp_error( $terms ) ) {
					continue;
				}
				foreach ( $terms as $term ) {
					/** @var \WP_Term $term */
					$links[] = new Item( $term->name, get_term_link( $term ), [
						'rel' => 'tag',
					] );
				}
			}
			$links[] = new Item( get_the_title( get_queried_object() ), get_permalink( get_queried_object() ), [
				'current' => true,
			] );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			/** @var \WP_Term $term */
			$term       = get_queried_object();
			$taxonomy   = get_taxonomy( $term->taxonomy );
			$links[]    = new Item( $taxonomy->label );
			$term_links = [];
			if ( $taxonomy->hierarchical ) {
				$parent_id = $term->parent;
				while ( $parent_id ) {
					$parent = get_term( $parent_id, $term->taxonomy );
					if ( $parent && ! is_wp_error( $parent ) ) {
						array_unshift( $term_links, new Item( $parent->name, get_term_link( $parent ), [
							'rel' => 'tag',
						] ) );
						$parent_id = $parent->parent;
					} else {
						$parent_id = 0;
					}
				}
			}
			foreach ( $term_links as $term_link ) {
				$links[] = $term_link;
			}
			$links[] = new Item( $term->name, get_term_link( $term ), [
				'rel'     => 'tag',
				'current' => true,
			] );
		} elseif ( is_author() ) {
			/** @var \WP_User $author */
			$author = get_queried_object();
			$links[] = new Item( __( 'Authors' ) );
			$links[] = new Item( $author->display_name, get_author_posts_url( $author->ID ) );
		} elseif ( is_date() ) {
			$links[] = new Item( __( 'Date' ) );
			if ( $year = get_query_var( 'year' ) ) {
				$links[] = new Item( $year, get_year_link( $year ) );
				if ( $month = get_query_var( 'monthnum' ) ) {
					$links[] = new Item( $month, get_month_link( $year, $month ) );
					if ( $day = get_query_var( 'day' ) ) {
						$links[] = new Item( $day, get_day_link( $year, $month, $day ) );
					}
				}
			}
		} elseif ( is_search() ) {
			$links[] = new Item( sprintf( esc_html__( 'Search results of "%s"', 'ku-mag' ), get_search_query() ), false, [
				'current' => true,
			] );
		} elseif ( is_home() && ! is_front_page() ) {
			// This is blog page.
			$links[] = new Item( get_the_title( get_option( 'page_for_posts' ) ), '', [
				'current' => true,
			] );
		} elseif ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );
			$post_type_obj = get_post_type_object( $post_type );
			if ( $post_type && $post_type_obj ) {
				$post_type_label = apply_filters( 'bootstrapress_breadcrumb_post_type_label', $post_type_obj->label, $post_type_obj, $post_type );
				$links[] = new Item( $post_type_label, '', [
					'current' => true,
				] );
			}
		}
		array_unshift( $links, new Item( $home, home_url( '' ), [
			'rel' => 'home',
		] ) );
		self::$links = $links;
		return self::$links;
	}
	
	/**
	 * Get archive page.
	 *
	 * @param \WP_Post $post
	 *
	 * @return Item
	 */
	public static function get_archive_page( $post ) {
		switch ( $post->post_type ) {
			case 'post':
				if ( $index_page = get_option( 'page_for_posts' ) ) {
					return new Item( get_the_title( $index_page ), get_permalink( $index_page ) );
				}
				break;
			case 'attachment':
			case 'page':
				// Do nothing
				break;
			default:
				$post_type_object = get_post_type_object( $post->post_type );
				if ( $post_type_object && $post_type_object->has_archive ) {
					return new Item( $post_type_object->labels->name, get_post_type_archive_link( $post_type_object->name ) );
				}
				break;
		}
		return null;
	}
	
	/**
	 * Get post ancestors.
	 *
	 * @param \WP_Post $post
	 *
	 * @return Item[]
	 */
	public static function get_ancestors( $post ) {
		$parents = [];
		$parent_id = $post->post_parent;
		while ( $parent_id ) {
			$parent = get_post( $parent_id );
			if ( $parent && ( 'publish' === $parent->post_status ) && is_post_type_viewable( $post->post_type ) ) {
				// Parent found.
				array_unshift( $parents, new Item( get_the_title( $parent ), get_permalink( $parent ) ) );
				$parent_id = $parent->post_parent;
			} else {
				// No parent.
				$parent_id = 0;
			}
		}
		return $parents;
	}
}
