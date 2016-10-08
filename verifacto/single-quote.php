<?php
/**
 * The template for displaying a quote
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header(); ?>

<?php 
// Get all factcheck related fields.
// We'll need them for global score and sources list
$factchecks = get_posts(array(
	'numberposts' => -1,
	'post_type' => 'factcheck',
	'meta_query' => array(
      array(
          'key' => 'fact_check_quote',
          'value' => $post->ID,
          'compare' => 'LIKE'
      )
  )
));

// Some dummy math for the score
if($factchecks) {
	$number = count($factchecks);
	$sum = 0;
	$mean = 0;

	foreach($factchecks as $factcheck) {
		$sum+= get_field('fact_check_score', $factcheck->ID);
	}

	$mean = $sum/$number;
	$score = 0;

	if( $mean === 5 ) 
	{
		$score = 5;
	} else if ( $mean < 5 ) {
		$score = 4;
	} else if ( $mean <= 4 ) {
		$score = 3;
	} else if ( $mean <= 3 ) {
		$score = 2;
	} else if ( $mean <= 2 ) {
		$score = 1;
	} 

	echo '<!-- SCORE ||| ';
	echo 'TOTAL FACT CHECKS: '. $number .' ||| ';
	echo 'SCORE SUM: '. $sum .' |||  ';
	echo 'SCORE mean: '. $mean .' -->';
}

//Also, get some meta data from the quote, because we'll need it later in the page...
//...like the taxonomy term, in this case, who said it...
$quote_categories = get_the_terms($post->ID, 'politician'); 
$quote_category = $quote_categories[0]->name;
$quote_category_slug = $quote_categories[0]->term_taxonomy_id;


//...and finally, we do scores for the taxonomy term/politician
$author_quotes = get_posts(array(
	'numberposts' => -1,
	'post_type' => 'quote',
	'politician' => $quote_categories[0]->slug
));

// TODO: refactor
// Messy loops
foreach($author_quotes as $author_quote) {

	$author_factchecks = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'factcheck',
		'meta_query' => array(
	      array(
	          'key' => 'fact_check_quote',
	          'value' => $author_quote->ID,
	          'compare' => 'LIKE'
	      )
	  )
	));

}

// Some dummy math for the politician score
if($author_factchecks) {
	$author_number = count($author_factchecks);
	$author_sum = 0;
	$author_mean = 0;

	foreach($author_factchecks as $author_factcheck) {
		$author_sum+= get_field('fact_check_score', $author_factcheck->ID);
	}

	$author_mean = $author_sum/$author_number;
	$author_score = 0;

	if( $author_mean === 5 ) 
	{
		$author_score = 5;
	} else if ( $author_mean < 5 ) {
		$author_score = 4;
	} else if ( $author_mean <= 4 ) {
		$author_score = 3;
	} else if ( $author_mean <= 3 ) {
		$author_score = 2;
	} else if ( $author_mean <= 2 ) {
		$author_score = 1;
	} 

	echo '<!-- SCORE '. $quote_category  .' ||| ';
	echo 'TOTAL FACT CHECKS: '. $author_number .' ||| ';
	echo 'SCORE SUM: '. $author_sum .' |||  ';
	echo 'SCORE mean: '. $author_mean .' -->';
}
?>

<article class="quote full-bleed <?php echo 'quote--score' . $score; ?>">
	<div class="page-full-width">
		<?php do_action( 'foundationpress_before_content' ); ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<div class="main-content" id="post-<?php the_ID(); ?>">
				
				<!-- <header>
					<h1 class="entry-title"><?php the_title(); ?></h1>
				</header> -->

				<h1 class="quote__score">
					<?php 
						// Output corresponding label and icon
						if( $score == 5 ) 
						{
							echo '<span class="i-score5" aria-hidden="true"></span> <span class="quote__score__label">True</span>';
						} else if ( $score == 4 ) {
							echo '<span class="i-score4" aria-hidden="true"></span> <span class="quote__score__label">Mostly true</span>';
						} else if ( $score == 3 ) {
							echo '<span class="i-score3" aria-hidden="true"></span> <span class="quote__score__label">Controversial</span>';
						} else if ( $score == 2 ) {
							echo '<span class="i-score2" aria-hidden="true"></span> <span class="quote__score__label">Mostly false</span>';
						} else if ( $score == 3 ) {
							echo '<span class="i-score1" aria-hidden="true"></span> <span class="quote__score__label">False</span>';
						} 

					?>
				</h1>

				<?php do_action( 'foundationpress_post_before_entry_content' ); ?>
				<div class="quote__content">

					<blockquote class="quote__text">
						<?php if( get_field('quote_context') ) : ?>
				       <?php echo get_field('quote_context') ?>
				    <?php endif; ?>
						<?php if( get_field('quote_text') ) : ?>
				       <?php echo get_field('quote_text') ?>
				    <?php endif; ?>

						
						<footer class="quote__footer">
							<cite class="quote__author">
								<?php
							    echo '<a href="'. get_term_link( $quote_category_slug ) .'">'. $quote_category .'</a>';
								?>
							</cite><?php if( get_field('source') ) : ?><span class="quote__source">, in <a href="<?php  echo get_field('source'); $parse = parse_url(get_field('source')) ?>" target="_blank"><?php echo $parse['host']; ?></a></span><?php endif; ?>

							<div class="quote__dateline dateline">
								<?php foundationpress_entry_meta(); ?>
							</div>
						</footer>
					</blockquote>
				</div>

				<?php do_action( 'foundationpress_post_before_comments' ); ?>
				<?php comments_template(); ?>
				<?php do_action( 'foundationpress_post_after_comments' ); ?>
			</div>
		<?php endwhile;?>

		<?php do_action( 'foundationpress_after_content' ); ?>
	</div>
</article>

<aside class="sources slice">
	<div class="page-full-width">
		<div class="main-content">
			<h3 class="slice__heading">Fact checks</h3>

			<?php
			// Print a list of all the fact check link available
			if($factchecks)
			{
			echo '<ul>';

				foreach($factchecks as $factcheck)
				{
					echo '<li><a href="' . get_field('fact_check_link', $factcheck->ID) . '">' . get_the_title($factcheck->ID) . '</a></li>';
				}

			echo '</ul>';
			}

			?>
		</div>
	</div>
</aside>

<aside class="related slice">
	<div class="page-full-width">
		<div class="slice__header main-content">
			<h3 class="slice__heading">More on <span><?php echo $quote_category; ?></span></h3>
		</div>

		<div class="slice__content">
			<div class="related__avatar small-6 medium-2 columns">
				<div class="image-wrapper">
					<?php
						print apply_filters( 'taxonomy-images-list-the-terms', '', array(
						    'taxonomy'     => 'politician',
						) );
					?>
				</div>
			</div>

			<div class="related__score small-6 medium-10 columns">
				<p>Score</p>
				<div class="stat">
					<?php 
						// Output corresponding label and icon
						if( $score == 5 ) 
						{
							echo '<span class="i-score5" aria-hidden="true"></span> <span class="quote__score__label">True</span>';
						} else if ( $score == 4 ) {
							echo '<span class="i-score4" aria-hidden="true"></span> <span class="quote__score__label">Mostly true</span>';
						} else if ( $score == 3 ) {
							echo '<span class="i-score3" aria-hidden="true"></span> <span class="quote__score__label">Controversial</span>';
						} else if ( $score == 2 ) {
							echo '<span class="i-score2" aria-hidden="true"></span> <span class="quote__score__label">Mostly false</span>';
						} else if ( $score == 3 ) {
							echo '<span class="i-score1" aria-hidden="true"></span> <span class="quote__score__label">False</span>';
						} 
					?>
				</div>
			</div>

			<div class="related__list column">
				<?php
				// Print a list of all the fact check link available
				if($author_quotes)
				{
				echo '<ul>';

					foreach($author_quotes as $author_quote)
					{
						$quote_text = '';

						$quote_factchecks = get_posts(array(
							'numberposts' => -1,
							'post_type' => 'factcheck',
							'meta_query' => array(
						      array(
						          'key' => 'fact_check_quote',
						          'value' => $post->ID,
						          'compare' => 'LIKE'
						      )
						  )
						));

						// Some dummy math for the score
						if($quote_factchecks) {
							$quote_factchecks_number = count($quote_factchecks);
							$quote_factchecks_sum = 0;
							$quote_factchecks_mean = 0;

							foreach($quote_factchecks as $quote_factcheck) {
								$quote_factchecks_sum+= get_field('fact_check_score', $quote_factcheck->ID);
							}

							$quote_factchecks_mean = $quote_factchecks_sum/$quote_factchecks_number;
							$quote_factchecks_score = 0;

							if( $quote_factchecks_mean === 5 ) 
							{
								$quote_factchecks_score = 5;
								$quote_factchecks_label = 'True';
							} else if ( $quote_factchecks_mean < 5 ) {
								$quote_factchecks_score = 4;
								$quote_factchecks_label = 'Mostly true';
							} else if ( $quote_factchecks_mean <= 4 ) {
								$quote_factchecks_score = 3;
								$quote_factchecks_label = 'Controversial';
							} else if ( $quote_factchecks_mean <= 3 ) {
								$quote_factchecks_score = 2;
								$quote_factchecks_label = 'Mostly false';
							} else if ( $quote_factchecks_mean <= 2 ) {
								$quote_factchecks_score = 1;
								$quote_factchecks_label = 'False';
							} 
						}

						if( get_field('quote_context') ) {
							$quote_text .= get_field('quote_context');
				 		}
						if( get_field('quote_text') ) {
				    	$quote_text .= '' . get_field('quote_text');
				    }

						echo '<li><a href="' . get_permalink($author_quote->ID) . '">"';
						echo wp_trim_words( $quote_text, 20 );
						echo '" <span data-tooltip aria-haspopup="true" class="quote__score__tooltip has-tip top" data-disable-hover="false" tabindex="1" title="' . $quote_factchecks_label . '"><span class="i-score' . $quote_factchecks_score .'"></<span></<span>';
						echo '</a></li>';
					}

				echo '</ul>';
				}
				
				?>
			</div>

		</div>
	</div>
</aside>

<?php get_footer();
