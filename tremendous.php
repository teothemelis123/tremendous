<?php
/**
 * Regiter template
 *
 * @category Wodrpress-Plugins
 * @package  WP-FoodTec
 * @author   FoodTec Solutions <info@foodtecsolutions.com>
 * @license  GPLv2 or later
 * @link     https://gitlab.foodtecsolutions.com/fts/wordpress/plugins/wp-foodtec
 * @since    1.0.0
 *
 * @phan-file-suppress PhanUndeclaredGlobalVariable $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>


<?php if (isset($args['account'])): ?>
	<form method="post" class="form-horizontal tremendous_request-form" data-nonce="<?php echo $args['nonce']; ?>" >
		<div class="form-group">
			<div class="tremendous-container">

				<input type="hidden" name="rewards_selection" value="">

				<?php
				foreach ( $args['plan']->thirdPartyAwards as $wp_foodtec_award ) :

					?>
					<div class="reward-box tremendous-label" id="<?php echo $wp_foodtec_award->name; ?>" data-value="<?php echo $wp_foodtec_award->amount; ?>">
						<?php echo '$'.$wp_foodtec_award->displayName; ?>
					</div>

				<?php  endforeach;?>

			</div>

		</div>




		<div class="form-group">
			<div class="col-sm-offset-3 col-sm-9">
				<?php echo $args['recaptcha']; ?>
			</div>
		</div>

		<div class="form-group">
			<div class="col-sm-offset-3 col-sm-9">
				<p class="alert hidden" tabindex="-1"></p>
			</div>
		</div>

		<div class="form-group">
			<div class="col-sm-offset-3 col-sm-9">
				<button type="submit" class="btn btn-primary btn-lg"><?php _e( 'Redeem Gift Card', 'wp-foodtec' ); ?></button>
			</div>
		</div>

	</form>
<?php else:?>
	<p style="text-align: center;"><strong><span class="s1" style="font-size: 140%;">PLEASE <a href="<?php echo $args['login_page']?>">LOGIN</a> TO VIEW AVAILABLE GIFT CARDS</span></strong></p>
<?php endif?>
