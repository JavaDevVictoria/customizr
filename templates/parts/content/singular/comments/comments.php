
<?php
/**
 * The template for displaying the comments block (wrapper for the comments template)
 *
 * @package Customizr
 * @since Customizr 3.5.0
 */
?>
<div id="czr-comments" class="comments-area" <?php czr_fn_echo('element_attributes') ?>>
  <?php do_action('czr_before_comments_template') ?>
  <?php
  /* We kinda need to do this this way as the WP_Comment_Query is generated by the function below */
  comments_template( '' , true ) ?>
  <?php do_action('czr_after_comments_template') ?>
</div>