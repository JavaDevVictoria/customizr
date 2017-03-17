<?php
class CZR_post_list_single_media_model_class extends CZR_Model {
  public $has_post_media;
  public $has_format_icon_media;
  public $only_thumb;
  public $media_content;
  public $original_thumb_url;
  public $is_full_image;

  public $defaults = array( 'use_placeholder' => false, 'allow_css_centering' => true, 'original_thumb_url' => '' );

  function czr_fn_get_element_class() {
    $post_format           = get_post_format();

    $element_class = ! empty($this-> element_class) ? $this ->element_class : array();
    $element_class = ! is_array( $element_class ) ? explode(' ', $element_class ) : $element_class;

    if ( ! $this -> only_thumb && 'gallery' == $post_format )
      array_push( $element_class, 'czr-carousel' );

    //centering
    if ( $this -> only_thumb || ( ! $this->has_format_icon_media && 'audio' != $post_format ) ) {
      if ( esc_attr( czr_fn_get_opt( 'tc_center_img' ) ) )
        $_centering_class = 'js-media-centering';
      elseif ( $this -> allow_css_centering )
        $_centering_class = 'no-js-media-centering';

      if ( ! empty( $_centering_class ) )
        array_push( $element_class, $_centering_class );
    }

    return $element_class;
  }

  /* To treat default thumb, might be passed by the parent ...*/
  /* Test purpose only */
  function czr_fn_get_media_content() {
    if ( $this -> has_format_icon_media )
      return;

    $post_format = $this -> only_thumb ? '' : get_post_format();

    /* TEMPORARY: HARD CODED */
    switch ( $post_format ) {
      case 'video':
          global $post, $wp_embed;
          $slug =  $post->post_name;
          switch ( $slug ) {
            case 'post-format-video-youtube' :
                $content = 'https://youtu.be/FAECyLvSCHg';
                $class   = 'youtube';
              break;
            case 'post-format-video-wordpresstv' :
                $content = 'https://vimeo.com/176587685';
                $class   = 'vimeo';
              break;
            default :
              $content = '';
              $class   = '';
          }

          global $wp_embed;
          $content = $content ? $wp_embed -> autoembed( $content ) : '';
          $content = ! $content && 'alternate' == czr_fn_get_opt('tc_post_list_grid') && current_user_can('manage_options') ?
            '<div class="tc-placeholder-wrap">
                <p><strong>You need to setup the video post field</strong></p>
            </div>' : $content;

          return $content ? '<div class="video-container '. $class .'">'. $content . '</div>' : ' ';

      case 'audio':
          global $post, $wp_embed;
          $slug =  $post->post_name;
          switch ( $slug ) {
            case 'post-format-audio' :
              $content = 'https://soundcloud.com/digitalescort/something-in-the-way';
              $class   = 'soundcloud';
              break;
            case 'another-post-format-audio' :
              $content = 'https://play.spotify.com/track/4rjnWmrSRqXVkFWdKMG3pV';
              $class   = 'spotify';
              break;
            default :
              $content = '';
              $class   = '';
          }

          global $wp_embed;
          $content = $content ? $wp_embed -> autoembed( $content ) : '';
          $content = ! $content && 'alternate' == czr_fn_get_opt('tc_post_list_grid') && current_user_can('manage_options') ?
            '<div class="tc-placeholder-wrap">
                <p><strong>You need to setup the audio post field</strong></p>
            </div>' : $content;

          return $content ? '<div class="audio-container '. $class .'">'. $content . '</div>' : ' ';
      case 'gallery':
          /* Rough */
          if ( (bool) $gallery = get_post_gallery(get_the_ID(), false) ) {

            $_gallery_html = '';
            /* Loop through all the image and output them one by one */
            foreach( $gallery['src'] as $src )
              $_gallery_html .= '<div class="carousel-cell"><img class="gallery-img wp-post-image" src="'.$src.'" data-mfp-src="'.$src.'" alt="Gallery image" /></div>';

            $the_permalink      = esc_url( apply_filters( 'the_permalink', get_the_permalink() ) );
            $the_title_attribute = the_title_attribute( array( 'before' => __('Permalink to ', 'customizr'), 'echo' => false ) );

            $_bg_link = '<a class="bg-link" rel="bookmark" title="'. $the_title_attribute.'" href="'.$the_permalink.'"></a>';

            $_gallery_nav    = count($gallery['src']) < 2 ? '' : '<div class="tc-gallery-nav">
                          <span class="slider-control btn btn-skin-darkest-shaded inverted slider-prev icn-left-open-big"></span>
                          <span class="slider-control btn btn-skin-darkest-shaded inverted slider-next icn-right-open-big"></span>
                        </div>';
            //post action;
            ob_start();
              czr_fn_render_template( 'modules/post_action_button', array( 'model_args' => array( 'post_action_link' => '#', 'post_action_link_class' => 'expand-img-gallery'  ) ) );
            $_post_action = ob_get_clean();

            $_gallery_html   = sprintf( '%1$s<div class="carousel carousel-inner">%2$s</div>',
                                       $_gallery_nav,
                                       $_gallery_html
            );

            return sprintf( "%s%s%s", $_bg_link, $_post_action, $_gallery_html);
          }
          //we need to return a placeholder;
          return false;

      default:
          $_the_thumb = czr_fn_get_thumbnail_model( 'normal', null, null, null, null, $this -> use_placeholder );

          if ( empty ( $_the_thumb['tc_thumb']) ) {
            return ' ';
          }

          //get_the_post_thumbnail( null, 'normal', array( 'class' => 'post-thumbnail' ) );
          /* use utils tc thumb to retrieve the original image size */
          if ( isset($_the_thumb[ '_thumb_id' ]) )
            $this -> czr_fn_set_property( 'original_thumb_url', wp_get_attachment_image_src( $_the_thumb[ '_thumb_id' ], 'large')[0] );

          $the_permalink      = esc_url( apply_filters( 'the_permalink', get_the_permalink() ) );
          $the_title_attribute = the_title_attribute( array( 'before' => __('Permalink to ', 'customizr'), 'echo' => false ) );


          $_bg_link = '<a class="bg-link" rel="bookmark" title="'. $the_title_attribute.'" href="'.$the_permalink.'"></a>';
          return $_bg_link . $_the_thumb[ 'tc_thumb' ];
    }
  }

  function czr_fn_get_has_media_action() {
    return $this -> has_post_media && ( $this -> only_thumb ||
      ! in_array( get_post_format(), array('video', 'audio', 'gallery') ) );
  }

}