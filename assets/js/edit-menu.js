/**
 * Edit menu.
 */

const $ = jQuery;
const { sprintf, __ } = wp.i18n;

/* global BootStrapMenu:false */

class NavImage {

  constructor( id, el, find, direction ) {
    this.id    = id;
    this.image = 0;
    this.$el = $( el );
    this.elements( this.$el.find( find ), direction );
    this.media = null;
    this.checkImage();
    this.initialized = false;
  }

  checkImage() {
    wp.apiFetch( {
      path: sprintf( 'bootstrapress/v1/menu/%d/image', this.id ),
    } ).then( ( res ) => {
      if ( res.success ) {
        this.image = res.id;
        this.setImage( res.src, res.title );
      }
    } ).catch( ( res ) => {
      // Error.
      window.console && console.log( res );
    } ).finally( () => {
      this.initialized = true;
    } );
  }

  setImage( src, title ) {
    const $img = this.$el.find( '.bp-image' );
    if ( src  ) {
      if ( $img.length ) {
        // Already exists.
        $img.find( 'img' ).attr( 'src', src );
      } else {
        // Insert image.
        const image = `
            <figure class="bp-image">
                <img class="bp-image-img" src="%1$s" alt="%2$s" />
                <figcaption class="bp-image-caption">%2$s</figcaption>
                <button class="bp-image-delete"><span class="dashicons dashicons-no"></span></button>
            </figure>
        `;
        this.$el.find( '.bp-image-button' ).before( sprintf( image, src, title ) );
      }
    } else {
      // Remove elements.
      $img.remove();
    }
    if ( this.initialized ) {
      $( document ).trigger( 'bp-image-changed', [ this.$el ] );
    }
  }

  imageSelect() {
    if ( ! this.media ) {
      this.media = wp.media({
        className: 'media-frame bp-image-selector',
        frame: 'select',
        multiple: false,
        title: BootStrapMenu.title,
        library: {
          type: 'image',
        },
        button: {
          text: BootStrapMenu.button,
        }
      });
      this.media.on( 'select', () => {
        this.media.state().get( 'selection' ).each( ( image ) => {
          const { id } = image.toJSON();
          wp.apiFetch( {
            path: sprintf( 'bootstrapress/v1/menu/%d/image', this.id ),
            method: 'POST',
            data: {
              attachment_id: id
            },
          } ).then( ( res ) => {
            this.image = res.id;
            this.setImage( res.src, res.title );
          } ).catch( ( res ) => {
            window.console && console.log( res );
          } );
        });
      });
    }
    this.media.open();
  }

  deleteImage() {
    wp.apiFetch( {
      path: sprintf( 'bootstrapress/v1/menu/%d/image', this.id ),
      method: 'DELETE',
    } ).then( ( res ) => {
      this.image = 0;
      this.setImage( '' );
      $( document ).trigger( 'bp-image-changed', [ this.$el ] );
    } ).catch( ( res ) => {
      window.console && console.log( res );
    } );
  }

  elements( $target, direction = 'after' ) {
    const $button = $( sprintf( `
    <div class="bp-image-wrapper">
      <label class="bp-image-label">%s</label>
      <button class="button bp-image-button">%s</button>
    </div>
    `, BootStrapMenu.label, BootStrapMenu.open ) );
    $button.on( 'click', '.bp-image-button', ( e ) => {
      e.preventDefault();
      this.imageSelect();
    } );
    $button.on( 'click', '.bp-image-delete', ( e ) => {
      e.preventDefault();
      this.deleteImage();
    } );
    $target[ direction ]( $button );
  }
}

/**
 * Initialize menu image.
 *
 * @param {Element} el
 * @param {String} find Selector to find elements.
 * @param {String} direction 'after' or 'before'
 */
const initializeMenuImage = ( el, find, direction ) => {
  let id = $( el ).attr( 'id' ).replace( /[^0-9]/g, '' );
  new NavImage( id, el, find, direction );
};

// Add image section in nav-menu.php
$( document ).ready( function() {
  $( '.menu-item' ).each( function( index, el ) {
    initializeMenuImage( el, '.field-move', 'before' );
  } );
} );

// Add new image section to added memnu.
$( document ).on( 'menu-item-added', function( event, args ) {
  initializeMenuImage( args[0], '.field-move', 'before' );
} );

const enSureImageWrapper = ( control ) => {
  const ids = [];
  const $control = $( control );
  const menuId = $control.attr( 'id' ).replace( /\D/gm, '' );
  $( control ).on( 'click', function() {
    const $lists = $( `#customize-control-nav_menu-${menuId}-name` ).parents( '.control-section-nav_menu' );
    setTimeout( function() {
      // Find children.
      $lists.find( '.customize-control-nav_menu_item' ).each( function ( index, el ) {
        const id = $( el ).attr('id');
        if ( 0 > ids.indexOf( id ) && ! $( el ).find( '.bp-image-button' ).length ) {
          ids.push( id );
          initializeMenuImage(el, '.menu-item-actions', 'before');
        }
      });
    }, 10 );
  } );
};

// Register section in customize.php
$( document ).ready( function() {
  $( '.control-section-nav_menu' ).each( function( index, control ) {
    enSureImageWrapper( control );
  } );
} );

// Check if new menu has image controle.
$( document ).on( 'expand', '.customize-control-nav_menu_item', function() {
  const $panel = $( this );
  if ( ! $panel.find( '.bp-image-button' ).length ) {
    if ( ! $panel.attr( 'id' ).match( /--\d+$/ ) ) {
      initializeMenuImage( this, '.menu-item-actions', 'before');
    }
  }
} );

$( document ).on( 'bp-image-changed', function( event, $el ) {
  if ( wp.customize ) {
    const iframe = $('iframe[name^=customize-preview]')[0];
    if ( ! iframe ) {
      return;
    }
    const window = iframe.contentWindow? iframe.contentWindow : iframe.contentDocument.defaultView;
    if ( window && window.wp && window.wp.customize ) {
      window.wp.customize.preview.send( 'refresh' );
    }
  }
} );
