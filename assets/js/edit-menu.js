/**
 * Edit menu.
 */

const $ = jQuery;
const { sprintf, __ } = wp.i18n;

class NavImage {

  constructor( id, el ) {
    this.id    = id;
    this.image = 0;
    this.$el = $( el );
    this.elements( this.$el.find( '.field-move' ), 'before' );
    this.checkImage();
  }

  checkImage() {
    wp.apiFetch( {
      path: sprintf( 'bootstrapress/v1/menu/%d/image', this.id ),
    } ).then( ( res ) => {
      console.log( res );
      if ( res.success ) {
        this.image = res.id;
        this.setImage( res.src, res.title );
      }
    } ).catch( ( res ) => {
      // Error.
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
                <img class="bp-image-img" src="%s" alt="%s" />
                <figcaption>%s</figcaption>
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
    alert( sprintf( 'Select image for %d', this.id ) );
  }

  elements( $target, direction = 'after' ) {
    const $button = $( `
    <div class="bp-image-wrapper">
      <button class="button bp-image-button">Click</button>
    </div>
    ` );
    $button.on( 'click', '.bp-image-button', ( e ) => {
      e.preventDefault();
      this.imageSelect();
    } );
    $target[ direction ]( $button )
  }
}


$( document ).ready( function() {
  $( '.menu-item' ).each( function( index, el ) {
    let id = $( el ).attr( 'id' ).replace( /[^0-9]/g, '' );
    new NavImage( id, el );
  } );
} );
