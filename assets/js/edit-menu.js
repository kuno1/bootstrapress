/**
 * Edit menu.
 */

const $ = jQuery;
const { sprintf, __ } = wp.i18n;

/* global BootStrapMenu:false */

class NavImage {

  constructor( id, el ) {
    this.id    = id;
    this.image = 0;
    this.$el = $( el );
    this.elements( this.$el.find( '.field-move' ), 'before' );
    this.media = null;
    this.checkImage();
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
    } );
  }

  onChange() {

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
    $target[ direction ]( $button );
    $button.on( 'click', '.bp-image-delete', ( e ) => {
      e.preventDefault();
      this.deleteImage();
    } );
  }
}

/**
 * Initialize menu image.
 *
 * @param {Element} el
 */
const initializeMenuImage = ( el ) => {
  let id = $( el ).attr( 'id' ).replace( /[^0-9]/g, '' );
  new NavImage( id, el );
};

// Add image section in nav-menu.php
$( document ).ready( function() {
  $( '.menu-item' ).each( function( index, el ) {
    initializeMenuImage( el );
  } );
} );

// Add new image section to added memnu.
$( document ).on( 'menu-item-added', function( event, args ) {
  initializeMenuImage( args[0] );
} );

