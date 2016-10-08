<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

 get_header(); ?>



<header id="front-hero" role="banner">
  <div class="marketing">
    <div class="tagline small-centered ">
      <h1><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo.png" alt="verifacto" width="310" height="89"></h1>
      <h4 class="subheader"><?php bloginfo( 'description' ); ?></h4>
      <a role="button" class="download large button hide-for-small-only" href="#">Add extension to Chrome</a>
    </div>
  </div>

</header>

<?php do_action( 'foundationpress_before_content' ); ?>
<?php while ( have_posts() ) : the_post(); ?>
<section class="intro" role="main">
  <div class="fp-intro">

    <div <?php post_class() ?> id="post-<?php the_ID(); ?>">
      <?php do_action( 'foundationpress_page_before_entry_content' ); ?>
      <div class="entry-content">
        <?php the_content(); ?>
      </div>
      <footer>
        <?php wp_link_pages( array('before' => '<nav id="page-nav"><p>' . __( 'Pages:', 'foundationpress' ), 'after' => '</p></nav>' ) ); ?>
        <p><?php the_tags(); ?></p>
      </footer>
      <?php do_action( 'foundationpress_page_before_comments' ); ?>
      <?php comments_template(); ?>
      <?php do_action( 'foundationpress_page_after_comments' ); ?>
    </div>

  </div>

</section>
<?php endwhile;?>
<?php do_action( 'foundationpress_after_content' ); ?>

<section class="benefits">
  <header>
    <h2>Fact-checking where you need it, when you need it</h2>
    <h4>Verifacto is a fact-checkin aggregation tool that grants you instant access to the best fact-checking material while you’re reading the news or catching up on social media.</h4>
  </header>

  <div class="section">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/benefict1.jpg" alt="">
    <h3>Inline fact-checking</h3>
    <p>You don't need to search for fact-checking. Verifacto will flag quotes and claims while you read them.</p>
  </div>

  <div class="section">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/benefict2.jpg" alt="">
    <h3>Aggregation</h3>
    <p></p>

  </div>

  <div class="section">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/benefict3.jpg" alt="">
    <h3>Reader submissions</h3>
    <p>You can customize your build to include or remove certain elements, as well as define the size of columns, colors, font size and more.</p>

  </div>

  <div class="cta">
    <a role="button" class="download large button hide-for-small-only" href="#">Add extension</a>
  </div>

</section>




 <?php get_footer();
