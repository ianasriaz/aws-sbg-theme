<?php
/**
 * AWS SBG Universal Activity & Event Registration Engine
 * File: feature-event-registration.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Register Post Types
 */
function aws_sbg_event_post_types() {
	// Register Events Post Type
	register_post_type( 'aws_event', array(
		'labels' => array(
			'name'          => 'Events',
			'singular_name' => 'Event',
			'add_new'       => 'Add New Event',
			'add_new_item'  => 'Add New Event',
		),
		'description'   => 'Manage AWS SBG Activities & Events',
		'public'        => true,
		'show_ui'       => true,
		'show_in_menu'  => 'aws-sbg-suite',
		'supports'      => array( 'title', 'editor', 'thumbnail' ),
		'menu_position' => 22,
		'menu_icon'     => 'dashicons-calendar-alt',
		'has_archive'   => false,
		'rewrite'       => array( 'slug' => 'event', 'with_front' => false ),
	) );

	// Register Event Registrations Post Type
	register_post_type( 'aws_event_reg', array(
		'labels' => array(
			'name'          => 'Registrations Log',
			'singular_name' => 'Registration Log',
		),
		'description'   => 'Event Registrations',
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => 'aws-sbg-suite',
		'supports'      => array( 'title', 'custom-fields' ),
		'menu_position' => 23,
		'menu_icon'     => 'dashicons-tickets-alt',
		'has_archive'   => false,
		'can_export'    => true,
	) );
}
add_action( 'init', 'aws_sbg_event_post_types' );

/**
 * 2. Register Native WP Meta Boxes for Event Configuration & Perks Engine
 */
function aws_sbg_event_add_meta_boxes() {
	add_meta_box(
		'aws_event_config_meta_box',
		'⚡ Activity & Perks Configuration Engine',
		'aws_sbg_event_render_meta_box',
		'aws_event',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'aws_sbg_event_add_meta_boxes' );

function aws_sbg_event_render_meta_box( $post ) {
	wp_nonce_field( 'aws_event_meta_box_action', 'aws_event_meta_box_nonce' );

	$event_type = get_post_meta( $post->ID, 'aws_event_type', true ) ?: 'paid';
	// Backward compatibility fallback for old values
	if ( $event_type === 'paid_single' || $event_type === 'paid_tiered' ) {
		$event_type = 'paid';
	}

	$event_status = get_post_meta( $post->ID, 'aws_event_status', true ) ?: 'open';
	$price_earlybird = get_post_meta( $post->ID, 'aws_event_price_earlybird', true );
	if ( ! metadata_exists( 'post', $post->ID, 'aws_event_price_earlybird' ) ) {
		$price_earlybird = get_post_meta( $post->ID, 'aws_event_price_pro', true ) ?: '499';
	}

	$price_regular = get_post_meta( $post->ID, 'aws_event_price_regular', true );
	if ( ! metadata_exists( 'post', $post->ID, 'aws_event_price_regular' ) ) {
		$price_regular = get_post_meta( $post->ID, 'aws_event_price_super', true ) ?: '799';
	}
	
	$bank_name = get_post_meta( $post->ID, 'aws_event_bank_name', true ) ?: "Bank Alfalah";
	$bank_account_number = get_post_meta( $post->ID, 'aws_event_bank_account_number', true ) ?: "0123456789";
	$bank_iban = get_post_meta( $post->ID, 'aws_event_bank_iban', true ) ?: "PK36ALFH0123456789";
	// Custom Perks (Textarea, 1 perk per line)
	$perks_free = get_post_meta( $post->ID, 'aws_event_perks_free_custom', true ) ?: "E-Certificate of Participation\nVirtual Masterclasses Access";
	$perks_earlybird = get_post_meta( $post->ID, 'aws_event_perks_earlybird_custom', true ) ?: ( get_post_meta( $post->ID, 'aws_event_perks_pro_custom', true ) ?: "AWS Certification Mock Exam\nAWS Exam Voucher Eligibility\nNetworking Lunch & Refreshments\nExclusive AWS Swags Kit" );
	$perks_regular = get_post_meta( $post->ID, 'aws_event_perks_regular_custom', true ) ?: ( get_post_meta( $post->ID, 'aws_event_perks_super_custom', true ) ?: "AWS Certification Mock Exam\nAWS Exam Voucher Eligibility\nNetworking Lunch & Refreshments\nExclusive AWS Swags Kit\nGuaranteed Reserved Seat" );

	// Field Toggles
	$req_tshirt = get_post_meta( $post->ID, 'aws_event_req_tshirt', true ) ? 'checked' : '';
	$req_github = get_post_meta( $post->ID, 'aws_event_req_github', true ) ? 'checked' : '';
	$req_experience = get_post_meta( $post->ID, 'aws_event_req_experience', true ) ? 'checked' : '';

	// Countdown Timer Fields
	$enable_timer         = get_post_meta( $post->ID, 'aws_event_enable_timer', true ) ? 'checked' : '';
	$timer_end            = get_post_meta( $post->ID, 'aws_event_timer_end', true ) ?: '';
	$timer_title          = get_post_meta( $post->ID, 'aws_event_timer_title', true ) ?: '⏱️ Registration Deadline';
	$timer_expired_action = get_post_meta( $post->ID, 'aws_event_timer_expired_action', true ) ?: 'show_expired_text';

	// Super Community Supporter Fields
	$enable_supporter_raw = get_post_meta( $post->ID, 'aws_event_enable_supporter', true );
	$enable_supporter     = ( '' === $enable_supporter_raw || $enable_supporter_raw ) ? 'checked' : '';
	$price_supporter      = get_post_meta( $post->ID, 'aws_event_price_supporter', true ) ?: '1999';
	$perks_supporter      = get_post_meta( $post->ID, 'aws_event_perks_supporter_custom', true ) ?: "🎫 Full Registration & Access to this Event\n★ Featured on AWS SBG Official Website & Wall of Fame\n👑 Reserved Front-Row VIP Seating\n🎁 Exclusive Community Supporter Swag Badge\n🤝 Priority Access to Masterclasses & Guests";
	?>
	<style>
		.aws-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
		.aws-meta-field { margin-bottom: 12px; }
		.aws-meta-field label { font-weight: bold; display: block; margin-bottom: 4px; }
		.aws-meta-field input[type="text"], .aws-meta-field input[type="datetime-local"], .aws-meta-field select, .aws-meta-field textarea { width: 100%; border-radius: 6px; padding: 6px 10px; border: 1px solid #ccc; }
		.aws-meta-box-sec { background: #f9f9f9; padding: 14px; border: 1px solid #e5e5e5; border-radius: 8px; margin-top: 12px; }
		.aws-[#ad5cff] { color: #ad5cff; }
	</style>

	<!-- Mode Selection & Status Header -->
	<div class="aws-meta-grid">
		<div class="aws-meta-field">
			<label for="aws_event_type">Event Mode Selection:</label>
			<select name="aws_event_type" id="aws_event_type" onchange="toggleAwsEventFields()">
				<option value="free" <?php selected( $event_type, 'free' ); ?>>🟢 Free Event (1-Click RSVP, No Payment)</option>
				<option value="paid" <?php selected( $event_type, 'paid' ); ?>>🟣 Paid Event (Earlybird & Regular Tiers)</option>
			</select>
		</div>

		<div class="aws-meta-field">
			<label for="aws_event_status">Registration Status:</label>
			<select name="aws_event_status" id="aws_event_status">
				<option value="open" <?php selected( $event_status, 'open' ); ?>>✅ Open & Active</option>
				<option value="closed" <?php selected( $event_status, 'closed' ); ?>>🛑 Temporarily Closed / Capacity Full</option>
				<option value="upcoming" <?php selected( $event_status, 'upcoming' ); ?>>⏳ Upcoming (Notify Me Mode)</option>
			</select>
		</div>
	</div>

	<!-- PAID EVENT FIELDS (Only visible when Paid mode selected) -->
	<div id="aws-paid-fields" style="<?php echo $event_type === 'free' ? 'display:none;' : 'display:block;'; ?>">
		<div class="aws-meta-grid">
			<div class="aws-meta-field">
				<label for="aws_event_price_earlybird">Earlybird Ticket Price (PKR):</label>
				<input type="text" name="aws_event_price_earlybird" id="aws_event_price_earlybird" value="<?php echo esc_attr( $price_earlybird ); ?>" placeholder="e.g. 499">
			</div>

			<div class="aws-meta-field">
				<label for="aws_event_price_regular">Regular Ticket Price (PKR):</label>
				<input type="text" name="aws_event_price_regular" id="aws_event_price_regular" value="<?php echo esc_attr( $price_regular ); ?>" placeholder="e.g. 799">
			</div>
		</div>

		<div class="aws-meta-box-sec">
			<h4 style="margin-top:0;">🏦 Bank Payment Account Details</h4>
			<div class="aws-meta-grid">
				<div class="aws-meta-field">
					<label for="aws_event_bank_name">Bank Name:</label>
					<input type="text" name="aws_event_bank_name" id="aws_event_bank_name" value="<?php echo esc_attr( $bank_name ); ?>" placeholder="e.g. Bank Alfalah">
				</div>
				<div class="aws-meta-field">
					<label for="aws_event_bank_account_number">Account Number:</label>
					<input type="text" name="aws_event_bank_account_number" id="aws_event_bank_account_number" value="<?php echo esc_attr( $bank_account_number ); ?>" placeholder="e.g. 0123456789">
				</div>
				<div class="aws-meta-field">
					<label for="aws_event_bank_iban">IBAN:</label>
					<input type="text" name="aws_event_bank_iban" id="aws_event_bank_iban" value="<?php echo esc_attr( $bank_iban ); ?>" placeholder="e.g. PK36ALFH0123456789">
				</div>
			</div>
		</div>

		<div class="aws-meta-box-sec">
			<h4 style="margin-top:0;">🎁 Paid Event Perks Configuration (1 Perk per line — Included in both Earlybird & Regular passes)</h4>
			<div class="aws-meta-field">
				<label>Activity Perks Included:</label>
				<textarea name="aws_event_perks_earlybird_custom" rows="4"><?php echo esc_textarea( $perks_earlybird ); ?></textarea>
			</div>
		</div>
	</div>

	<!-- FREE EVENT FIELDS (Only visible when Free mode selected) -->
	<div id="aws-free-fields" style="<?php echo $event_type === 'free' ? 'display:block;' : 'display:none;'; ?>">
		<div class="aws-meta-box-sec">
			<h4 style="margin-top:0;">🎁 Free Activity Perks (1 Perk per line)</h4>
			<div class="aws-meta-field">
				<label>Free RSVP Perks Included:</label>
				<textarea name="aws_event_perks_free_custom" rows="4"><?php echo esc_textarea( $perks_free ); ?></textarea>
			</div>
		</div>
	</div>

	<!-- SUPER COMMUNITY SUPPORTER CONFIGURATION SECTION -->
	<div class="aws-meta-box-sec" style="border-left: 4px solid #ad5cff; background: #faf5ff;">
		<h4 style="margin-top:0;">🌟 Super Community Supporter Upsell Settings</h4>
		<div class="aws-meta-field">
			<label><input type="checkbox" name="aws_event_enable_supporter" id="aws_event_enable_supporter" value="1" <?php echo $enable_supporter; ?> onchange="toggleAwsSupporterFields()"> Enable "Want to Get Featured? Become a Super Supporter" Checkbox</label>
		</div>

		<div id="aws-supporter-fields" style="<?php echo $enable_supporter ? 'display:block;' : 'display:none;'; ?>">
			<div class="aws-meta-grid">
				<div class="aws-meta-field">
					<label for="aws_event_price_supporter">Super Supporter Pass Price (PKR):</label>
					<input type="text" name="aws_event_price_supporter" id="aws_event_price_supporter" value="<?php echo esc_attr( $price_supporter ); ?>" placeholder="e.g. 1999">
				</div>
				<div class="aws-meta-field">
					<label for="aws_event_perks_supporter_custom">Supporter Perks Included (1 Perk per line):</label>
					<textarea name="aws_event_perks_supporter_custom" rows="3"><?php echo esc_textarea( $perks_supporter ); ?></textarea>
				</div>
			</div>
		</div>
	</div>

	<!-- COUNTDOWN TIMER CONFIGURATION SECTION -->
	<div class="aws-meta-box-sec" style="border-left: 4px solid #ad5cff;">
		<h4 style="margin-top:0;">⏱️ Registration Countdown Timer Settings</h4>
		<div class="aws-meta-field">
			<label><input type="checkbox" name="aws_event_enable_timer" id="aws_event_enable_timer" value="1" <?php echo $enable_timer; ?> onchange="toggleAwsTimerFields()"> Enable Countdown Timer on Form</label>
		</div>

		<div id="aws-timer-fields" style="<?php echo $enable_timer ? 'display:block;' : 'display:none;'; ?>">
			<div class="aws-meta-grid">
				<div class="aws-meta-field">
					<label for="aws_event_timer_end">Timer End Date & Time:</label>
					<input type="datetime-local" name="aws_event_timer_end" id="aws_event_timer_end" value="<?php echo esc_attr( $timer_end ); ?>">
					<small style="color:#666; font-size:11px; display:block; margin-top:2px;">Select target deadline date & time for countdown zero.</small>
				</div>
				<div class="aws-meta-field">
					<label for="aws_event_timer_title">Timer Headline / Label:</label>
					<input type="text" name="aws_event_timer_title" id="aws_event_timer_title" value="<?php echo esc_attr( $timer_title ); ?>" placeholder="e.g. ⏱️ Registration Closes In:">
				</div>
			</div>
			<div class="aws-meta-field" style="margin-top:8px;">
				<label for="aws_event_timer_expired_action">When Timer Expires:</label>
				<select name="aws_event_timer_expired_action" id="aws_event_timer_expired_action">
					<option value="show_expired_text" <?php selected( $timer_expired_action, 'show_expired_text' ); ?>>Show Expired Banner (Form remains visible)</option>
					<option value="auto_close_form" <?php selected( $timer_expired_action, 'auto_close_form' ); ?>>Auto-Close Form & Block Submissions</option>
				</select>
			</div>
		</div>
	</div>

	<!-- OPTIONAL CUSTOM FIELDS TOGGLES -->
	<div class="aws-meta-box-sec">
		<h4 style="margin-top:0;">📝 Optional Registration Form Fields</h4>
		<label><input type="checkbox" name="aws_event_req_tshirt" value="1" <?php echo $req_tshirt; ?>> Require T-Shirt Size Selection (S, M, L, XL, XXL)</label><br>
		<label><input type="checkbox" name="aws_event_req_github" value="1" <?php echo $req_github; ?>> Require GitHub / Portfolio Profile URL</label><br>
		<label><input type="checkbox" name="aws_event_req_experience" value="1" <?php echo $req_experience; ?>> Require Experience Level Selection</label>
	</div>

	<script>
		function toggleAwsEventFields() {
			var mode = document.getElementById('aws_event_type').value;
			var paidDiv = document.getElementById('aws-paid-fields');
			var freeDiv = document.getElementById('aws-free-fields');

			if (mode === 'free') {
				paidDiv.style.display = 'none';
				freeDiv.style.display = 'block';
			} else {
				paidDiv.style.display = 'block';
				freeDiv.style.display = 'none';
			}
		}

		function toggleAwsTimerFields() {
			var timerCb = document.getElementById('aws_event_enable_timer');
			var timerDiv = document.getElementById('aws-timer-fields');
			if (timerCb && timerDiv) {
				timerDiv.style.display = timerCb.checked ? 'block' : 'none';
			}
		}

		function toggleAwsSupporterFields() {
			var supCb = document.getElementById('aws_event_enable_supporter');
			var supDiv = document.getElementById('aws-supporter-fields');
			if (supCb && supDiv) {
				supDiv.style.display = supCb.checked ? 'block' : 'none';
			}
		}
	</script>
	<?php
}

function aws_sbg_event_save_meta_box_data( $post_id ) {
	if ( ! isset( $_POST['aws_event_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['aws_event_meta_box_nonce'], 'aws_event_meta_box_action' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'aws_event_type',
		'aws_event_status',
		'aws_event_description',
		'aws_event_price_earlybird',
		'aws_event_price_regular',
		'aws_event_bank_name',
		'aws_event_bank_account_number',
		'aws_event_bank_iban',
		'aws_event_perks_free_custom',
		'aws_event_perks_earlybird_custom',
		'aws_event_perks_regular_custom',
		'aws_event_timer_end',
		'aws_event_timer_title',
		'aws_event_timer_expired_action',
		'aws_event_price_supporter',
		'aws_event_perks_supporter_custom',
	);

	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	update_post_meta( $post_id, 'aws_event_req_tshirt', isset( $_POST['aws_event_req_tshirt'] ) ? 1 : 0 );
	update_post_meta( $post_id, 'aws_event_req_github', isset( $_POST['aws_event_req_github'] ) ? 1 : 0 );
	update_post_meta( $post_id, 'aws_event_req_experience', isset( $_POST['aws_event_req_experience'] ) ? 1 : 0 );
	update_post_meta( $post_id, 'aws_event_enable_timer', isset( $_POST['aws_event_enable_timer'] ) ? 1 : 0 );
	update_post_meta( $post_id, 'aws_event_enable_supporter', isset( $_POST['aws_event_enable_supporter'] ) ? 1 : 0 );
}
add_action( 'save_post_aws_event', 'aws_sbg_event_save_meta_box_data' );

/**
 * 3. Front-End Form Shortcode Engine
 */
function aws_sbg_event_form_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'event_id'    => 0,
		'activity_id' => 0,
		'slug'        => '',
	), $atts, 'aws_event_form' );

	$event_id = intval( $atts['event_id'] ? $atts['event_id'] : $atts['activity_id'] );
	
	if ( ! $event_id && ! empty( $atts['slug'] ) ) {
		$event_post = get_page_by_path( sanitize_title( $atts['slug'] ), OBJECT, 'aws_event' );
		if ( $event_post ) {
			$event_id = $event_post->ID;
		}
	}

	if ( ! $event_id && is_singular( 'aws_event' ) ) {
		$event_id = get_the_ID();
	}

	if ( ! $event_id ) {
		$latest_events = get_posts( array(
			'post_type'      => 'aws_event',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		) );
		if ( ! empty( $latest_events ) ) {
			$event_id = $latest_events[0]->ID;
		}
	}

	if ( ! $event_id ) {
		return '<div class="p-6 bg-slate-100 rounded-2xl text-center text-slate-600 font-medium">Please create an Activity / Event in WordPress Admin first.</div>';
	}

	$event_title = get_the_title( $event_id );
	$event_post_obj = get_post( $event_id );
	$event_description = '';
	if ( $event_post_obj && ! empty( trim( $event_post_obj->post_content ) ) ) {
		$event_description = wp_strip_all_tags( $event_post_obj->post_content );
	} elseif ( $event_post_obj && ! empty( trim( $event_post_obj->post_excerpt ) ) ) {
		$event_description = wp_strip_all_tags( $event_post_obj->post_excerpt );
	} else {
		$event_description = get_post_meta( $event_id, 'aws_event_description', true );
	}

	$event_type = get_post_meta( $event_id, 'aws_event_type', true ) ?: 'paid';
	if ( $event_type === 'paid_single' || $event_type === 'paid_tiered' ) {
		$event_type = 'paid';
	}

	$event_status = get_post_meta( $event_id, 'aws_event_status', true ) ?: 'open';
	$price_earlybird = get_post_meta( $event_id, 'aws_event_price_earlybird', true );
	if ( ! metadata_exists( 'post', $event_id, 'aws_event_price_earlybird' ) ) {
		$price_earlybird = get_post_meta( $event_id, 'aws_event_price_pro', true ) ?: '499';
	}

	$price_regular = get_post_meta( $event_id, 'aws_event_price_regular', true );
	if ( ! metadata_exists( 'post', $event_id, 'aws_event_price_regular' ) ) {
		$price_regular = get_post_meta( $event_id, 'aws_event_price_super', true ) ?: '799';
	}
	$bank_name = get_post_meta( $event_id, 'aws_event_bank_name', true ) ?: "Bank Alfalah";
	$bank_account_number = get_post_meta( $event_id, 'aws_event_bank_account_number', true ) ?: "0123456789";
	$bank_iban = get_post_meta( $event_id, 'aws_event_bank_iban', true ) ?: "PK36ALFH0123456789";

	// Perks Parsing
	$raw_free_perks = get_post_meta( $event_id, 'aws_event_perks_free_custom', true ) ?: "E-Certificate of Participation\nVirtual Masterclasses Access";
	$raw_earlybird_perks = get_post_meta( $event_id, 'aws_event_perks_earlybird_custom', true ) ?: ( get_post_meta( $event_id, 'aws_event_perks_pro_custom', true ) ?: "AWS Certification Mock Exam\nAWS Exam Voucher Eligibility\nNetworking Lunch & Refreshments\nExclusive AWS Swags Kit" );
	$raw_regular_perks = $raw_earlybird_perks;

	$free_perks = array_filter( array_map( 'trim', explode( "\n", $raw_free_perks ) ) );
	$earlybird_perks = array_filter( array_map( 'trim', explode( "\n", $raw_earlybird_perks ) ) );
	$regular_perks = array_filter( array_map( 'trim', explode( "\n", $raw_regular_perks ) ) );

	// Field Toggles
	$req_tshirt = (bool) get_post_meta( $event_id, 'aws_event_req_tshirt', true );
	$req_github = (bool) get_post_meta( $event_id, 'aws_event_req_github', true );
	$req_experience = (bool) get_post_meta( $event_id, 'aws_event_req_experience', true );

	// Timer Metadata
	$enable_timer         = (bool) get_post_meta( $event_id, 'aws_event_enable_timer', true );
	$timer_end            = get_post_meta( $event_id, 'aws_event_timer_end', true );
	$timer_title          = get_post_meta( $event_id, 'aws_event_timer_title', true ) ?: '⏱️ Registration Deadline';
	$timer_expired_action = get_post_meta( $event_id, 'aws_event_timer_expired_action', true ) ?: 'show_expired_text';
	$timer_end_timestamp  = 0;

	if ( $enable_timer && ! empty( $timer_end ) ) {
		try {
			$timer_end_clean = str_replace( ' ', 'T', trim( $timer_end ) );
			$dt = new DateTime( $timer_end_clean, wp_timezone() );
			$timer_end_timestamp = $dt->getTimestamp() * 1000;
		} catch ( Exception $e ) {
			$timer_end_timestamp = strtotime( $timer_end ) * 1000;
		}
	}

	$is_timer_expired       = ( $timer_end_timestamp > 0 && ( time() * 1000 ) >= $timer_end_timestamp );
	$is_registration_closed = ( $event_status === 'closed' ) || ( $enable_timer && $is_timer_expired && 'auto_close_form' === $timer_expired_action );



	// Supporter Metadata
	$enable_supporter_raw = get_post_meta( $event_id, 'aws_event_enable_supporter', true );
	$enable_supporter     = ( '' === $enable_supporter_raw || $enable_supporter_raw ) ? true : false;
	$price_supporter      = get_post_meta( $event_id, 'aws_event_price_supporter', true ) ?: '1999';
	$raw_supporter_perks  = get_post_meta( $event_id, 'aws_event_perks_supporter_custom', true ) ?: "🎫 Full Registration & Access to this Event\n★ Featured on AWS SBG Official Website & Wall of Fame\n👑 Reserved Front-Row VIP Seating\n🎁 Exclusive Community Supporter Swag Badge\n🤝 Priority Access to Masterclasses & Guests";
	$supporter_perks      = array_filter( array_map( 'trim', explode( "\n", $raw_supporter_perks ) ) );

	ob_start();
	
	// --- REUSABLE INLINE STYLES ---
	$container_style = 'width:100%; max-width:720px; margin:0 auto; font-family:system-ui, -apple-system, sans-serif; box-sizing:border-box;';
	$input_style = 'display:block; width:100%; padding:12px 14px; margin:0; background:#f8fafc; border:2px solid #e2e8f0; border-radius:10px; color:#0f172a; font-size:14px; box-sizing:border-box; outline:none; transition:border-color 0.2s;';
	$label_style = 'display:block; font-size:11px; font-weight:800; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;';
	$section_title = 'display:flex; align-items:center; gap:10px; margin-bottom:16px;';
	$badge_style = 'width:26px; height:26px; background:#ad5cff; color:#fff; font-size:13px; font-weight:900; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;';
	$badge_text = 'font-size:13px; font-weight:900; color:#0f172a; text-transform:uppercase; letter-spacing:0.05em; margin:0; padding:0; line-height:1;';
	$divider = 'margin-top:28px; border-top:2px solid #f1f5f9; padding-top:28px;';
	
	// Flexbox Responsive Grid (No Media Queries needed)
	$grid_container = 'display:flex; flex-wrap:wrap; gap:16px;';
	$grid_item_half = 'flex: 1 1 min(100%, 280px); box-sizing:border-box;';
	$grid_item_full = 'flex: 1 1 100%; box-sizing:border-box;';

	?>
	<!-- 1. Strict CSS Reset for this form to stop Theme interference & guarantee Mobile Responsiveness -->
	<style>
		.aws-sbg-strict-form input[type="hidden"] {
			display: none !important;
			width: 0 !important;
			height: 0 !important;
			margin: 0 !important;
			padding: 0 !important;
			border: 0 !important;
			opacity: 0 !important;
			pointer-events: none !important;
			position: absolute !important;
		}
		.aws-sbg-strict-form input:focus, .aws-sbg-strict-form select:focus {
			border-color: #ad5cff !important;
			background: #ffffff !important;
		}
		.aws-sbg-strict-form * {
			box-sizing: border-box !important;
		}
		.aws-sbg-card-wrap {
			background: #ffffff;
			border-radius: 20px;
			padding: 20px 16px;
			box-shadow: 0 10px 40px rgba(0,0,0,0.06);
			border: 1px solid #e2e8f0;
			position: relative;
			overflow: hidden;
			box-sizing: border-box;
		}
		@media (min-width: 640px) {
			.aws-sbg-card-wrap {
				padding: 32px;
				border-radius: 24px;
			}
		}
		@media (min-width: 500px) {
			.aws-t-num {
				font-size: 26px !important;
			}
			.aws-t-box {
				padding: 10px 6px !important;
			}
		}
		@keyframes reg-spin { to { transform: rotate(360deg); } }
		@keyframes pulse {
			0% { box-shadow: 0 0 0 0 rgba(173, 92, 255, 0.4); }
			70% { box-shadow: 0 0 0 8px rgba(173, 92, 255, 0); }
			100% { box-shadow: 0 0 0 0 rgba(173, 92, 255, 0); }
		}
	</style>

	<div class="aws-sbg-strict-form" style="<?php echo $container_style; ?>">
		<div class="aws-sbg-card-wrap">
			
			<!-- Title -->
			<div style="text-align:center; padding-bottom:20px; border-bottom:2px solid #f1f5f9; margin-bottom:24px;">
				<h3 style="font-size:20px; font-weight:900; color:#0f172a; text-transform:uppercase; letter-spacing:0.04em; margin:0; padding:0; line-height:1.25;">
					<?php echo esc_html( $event_title ); ?>
				</h3>
				<?php if ( ! empty( $event_description ) ) : ?>
					<p style="font-size:14px; color:#64748b; margin:10px auto 0; padding:0; max-width:600px; line-height:1.5;">
						<?php echo esc_html( $event_description ); ?>
					</p>
				<?php endif; ?>
			</div>

			<!-- COUNTDOWN TIMER BANNER (ULTRA MOBILE RESPONSIVE) -->
			<?php if ( $enable_timer && $timer_end_timestamp > 0 ) : ?>
				<div id="aws-event-timer-container-<?php echo esc_attr( $event_id ); ?>" 
				     style="background: #fbf8ff; border-radius: 16px; padding: 14px 10px; text-align: center; margin-bottom: 24px; border: 1.5px solid #ad5cff; box-shadow: 0 6px 20px rgba(173, 92, 255, 0.1); color: #0f172a; width: 100%; box-sizing: border-box;">
					<div style="font-size: 11px; font-weight: 900; color: #ad5cff; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; gap: 6px;">
						<span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #ad5cff; animation: pulse 1.5s infinite;"></span>
						<?php echo esc_html( $timer_title ); ?>
					</div>
					
					<div id="aws-timer-grid-<?php echo esc_attr( $event_id ); ?>" style="display: flex; justify-content: space-between; align-items: center; gap: 3px; width: 100%; box-sizing: border-box;">
						<div class="aws-t-box" style="flex: 1; min-width: 0; background: #ffffff; border: 1.5px solid rgba(173, 92, 255, 0.25); border-radius: 10px; padding: 8px 2px; text-align: center; box-shadow: 0 2px 6px rgba(173, 92, 255, 0.06);">
							<span id="aws-timer-days-<?php echo esc_attr( $event_id ); ?>" class="aws-t-num" style="display: block; font-size: 20px; font-weight: 900; color: #ad5cff; font-family: ui-monospace, monospace; line-height: 1;">00</span>
							<span style="font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; margin-top: 3px; display: block;">Days</span>
						</div>
						<span style="font-size: 16px; font-weight: 900; color: #ad5cff; opacity: 0.6; flex-shrink: 0;">:</span>
						<div class="aws-t-box" style="flex: 1; min-width: 0; background: #ffffff; border: 1.5px solid rgba(173, 92, 255, 0.25); border-radius: 10px; padding: 8px 2px; text-align: center; box-shadow: 0 2px 6px rgba(173, 92, 255, 0.06);">
							<span id="aws-timer-hours-<?php echo esc_attr( $event_id ); ?>" class="aws-t-num" style="display: block; font-size: 20px; font-weight: 900; color: #ad5cff; font-family: ui-monospace, monospace; line-height: 1;">00</span>
							<span style="font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; margin-top: 3px; display: block;">Hours</span>
						</div>
						<span style="font-size: 16px; font-weight: 900; color: #ad5cff; opacity: 0.6; flex-shrink: 0;">:</span>
						<div class="aws-t-box" style="flex: 1; min-width: 0; background: #ffffff; border: 1.5px solid rgba(173, 92, 255, 0.25); border-radius: 10px; padding: 8px 2px; text-align: center; box-shadow: 0 2px 6px rgba(173, 92, 255, 0.06);">
							<span id="aws-timer-mins-<?php echo esc_attr( $event_id ); ?>" class="aws-t-num" style="display: block; font-size: 20px; font-weight: 900; color: #ad5cff; font-family: ui-monospace, monospace; line-height: 1;">00</span>
							<span style="font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; margin-top: 3px; display: block;">Mins</span>
						</div>
						<span style="font-size: 16px; font-weight: 900; color: #ad5cff; opacity: 0.6; flex-shrink: 0;">:</span>
						<div class="aws-t-box" style="flex: 1; min-width: 0; background: #ffffff; border: 1.5px solid rgba(173, 92, 255, 0.25); border-radius: 10px; padding: 8px 2px; text-align: center; box-shadow: 0 2px 6px rgba(173, 92, 255, 0.06);">
							<span id="aws-timer-secs-<?php echo esc_attr( $event_id ); ?>" class="aws-t-num" style="display: block; font-size: 20px; font-weight: 900; color: #ad5cff; font-family: ui-monospace, monospace; line-height: 1;">00</span>
							<span style="font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; margin-top: 3px; display: block;">Secs</span>
						</div>
					</div>

					<div id="aws-timer-expired-msg-<?php echo esc_attr( $event_id ); ?>" style="display: none; margin-top: 10px; font-size: 13px; font-weight: 800; color: #ad5cff; background: rgba(173, 92, 255, 0.08); border: 1px solid rgba(173, 92, 255, 0.3); border-radius: 10px; padding: 8px 12px;">
						⌛ Registration Deadline Has Passed!
					</div>
				</div>

				<script>
					(function() {
						var rawTimerEnd = "<?php echo esc_js( $timer_end ); ?>";
						var eventId = <?php echo intval( $event_id ); ?>;
						var autoClose = <?php echo $timer_expired_action === 'auto_close_form' ? 'true' : 'false'; ?>;
						var endTime = 0;

						if (rawTimerEnd) {
							var cleanStr = rawTimerEnd.trim().replace(' ', 'T');
							if (cleanStr.length === 16) {
								cleanStr += ':00';
							}
							var parsedDate = new Date(cleanStr);
							if (parsedDate && !isNaN(parsedDate.getTime())) {
								endTime = parsedDate.getTime();
							}
						}
						if (!endTime) {
							endTime = <?php echo floatval( $timer_end_timestamp ); ?>;
						}


						function updateTimer() {
							var now = new Date().getTime();
							var distance = endTime - now;

							var daysEl = document.getElementById('aws-timer-days-' + eventId);
							var hoursEl = document.getElementById('aws-timer-hours-' + eventId);
							var minsEl = document.getElementById('aws-timer-mins-' + eventId);
							var secsEl = document.getElementById('aws-timer-secs-' + eventId);
							var gridEl = document.getElementById('aws-timer-grid-' + eventId);
							var expiredEl = document.getElementById('aws-timer-expired-msg-' + eventId);

							if (!daysEl) return;

							if (distance <= 0) {
								daysEl.innerText = "00";
								hoursEl.innerText = "00";
								minsEl.innerText = "00";
								secsEl.innerText = "00";
								if (expiredEl) expiredEl.style.display = "block";
								if (gridEl) gridEl.style.opacity = "0.4";

								if (autoClose) {
									var timerContainer = document.getElementById('aws-event-timer-container-' + eventId);
									var formEl = document.querySelector('.aws-sbg-strict-form form');
									var closedTemplate = document.getElementById('aws-reg-closed-screen-template-' + eventId);

									if (closedTemplate) {
										if (timerContainer) timerContainer.style.display = "none";
										if (formEl) formEl.style.display = "none";
										closedTemplate.style.display = "block";
										closedTemplate.style.opacity = "0";
										closedTemplate.style.transition = "opacity 0.5s ease-in-out";
										setTimeout(function() { closedTemplate.style.opacity = "1"; }, 10);
									} else if (formEl) {
										formEl.style.opacity = "0.5";
										formEl.style.pointerEvents = "none";
									}
								}

								return;
							}

							var days = Math.floor(distance / (1000 * 60 * 60 * 24));
							var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
							var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
							var seconds = Math.floor((distance % (1000 * 60)) / 1000);

							daysEl.innerText = days < 10 ? '0' + days : days;
							hoursEl.innerText = hours < 10 ? '0' + hours : hours;
							minsEl.innerText = minutes < 10 ? '0' + minutes : minutes;
							secsEl.innerText = seconds < 10 ? '0' + seconds : seconds;
						}

						updateTimer();
						setInterval(updateTimer, 1000);
					})();
				</script>
			<?php endif; ?>

			<?php if ( $is_registration_closed ) : ?>
				<!-- BEAUTIFUL & MINIMAL REGISTRATION CLOSED SCREEN -->
				<div id="aws-reg-closed-screen-<?php echo esc_attr( $event_id ); ?>" 
				     style="background: #ffffff; border-radius: 20px; padding: 44px 24px; text-align: center; border: 1.5px solid #e2e8f0; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box; position: relative; overflow: hidden;">
					<div style="position: absolute; top: -50px; left: 50%; transform: translateX(-50%); width: 200px; height: 200px; background: radial-gradient(circle, rgba(173, 92, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%); pointer-events: none; border-radius: 50%;"></div>

					<div style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 100px; margin-bottom: 24px;">
						<span style="display: block; width: 8px; height: 8px; border-radius: 50%; background: #e11d48;"></span>
						<span style="font-size: 11px; font-weight: 800; color: #be123c; text-transform: uppercase; letter-spacing: 0.08em;">Registrations Closed</span>
					</div>

					<div style="width: 72px; height: 72px; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.04);">
						<svg style="width: 32px; height: 32px; color: #475569;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
							<path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
						</svg>
					</div>

					<h3 style="font-size: 22px; font-weight: 900; color: #0f172a; margin: 0 0 10px; padding: 0; line-height: 1.25;">
						Registration Time Has Ended
					</h3>

					<p style="font-size: 14px; color: #64748b; margin: 0 auto 24px; max-width: 460px; line-height: 1.6;">
						Thank you for your interest! Registrations for <strong style="color: #0f172a;"><?php echo esc_html( $event_title ); ?></strong> have officially closed.
					</p>

					<div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 16px; padding: 18px 16px; margin-bottom: 28px; text-align: left; display: flex; align-items: flex-start; gap: 12px;">
						<div style="font-size: 20px; flex-shrink: 0; line-height: 1;">💡</div>
						<div>
							<span style="display: block; font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 2px;">Want to join our next activity?</span>
							<span style="display: block; font-size: 12px; color: #64748b; line-height: 1.5;">Join our official AWS SBG community network to get instant updates on future masterclasses, hackathons, and certification vouchers.</span>
						</div>
					</div>

					<div style="display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; align-items: center;">
						<a href="<?php echo esc_url( home_url( '/events/' ) ); ?>" 
						   style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 22px; background: #ad5cff; color: #ffffff; font-weight: 800; font-size: 13px; border-radius: 12px; text-decoration: none; box-shadow: 0 4px 14px rgba(173, 92, 255, 0.3); transition: all 0.2s;">
							<span>Explore All Events</span>
							<svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
						</a>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" 
						   style="display: inline-flex; align-items: center; justify-content: center; padding: 12px 22px; background: #f1f5f9; color: #334155; font-weight: 800; font-size: 13px; border-radius: 12px; text-decoration: none; transition: all 0.2s;">
							Return to Homepage
						</a>
					</div>
				</div>

			<?php elseif ( isset( $_GET['status'] ) && $_GET['status'] === 'success' ) :
				$reg_id     = isset( $_GET['reg_id'] ) ? sanitize_text_field( $_GET['reg_id'] ) : 'N/A';
				$fname_full = isset( $_GET['fname'] ) ? sanitize_text_field( wp_unslash( $_GET['fname'] ) ) : '';
				$fname_parts = explode( ' ', trim( $fname_full ) );
				$first_name = ! empty( $fname_parts[0] ) ? $fname_parts[0] : '';
			?>
				<!-- SUCCESS INLINE CARD -->
				<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
				<div x-data x-init="setTimeout(()=>{if(typeof confetti!=='undefined')confetti({particleCount:150,spread:100,origin:{y:0.4},colors:['#ad5cff','#00ef88','#ff9900']});},200)"
				     style="background:#ffffff; border-radius:24px; border:2px solid #10b981; padding:32px 24px; text-align:center; margin-bottom:20px; box-shadow:0 12px 35px rgba(16,185,129,0.12);">
					<div style="width:60px; height:60px; background:#ecfdf5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; border:2px solid #a7f3d0;">
						<svg style="width:30px; height:30px; color:#10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
					</div>
					<h3 style="font-size:22px; font-weight:900; color:#0f172a; margin:0 0 6px; padding:0;">You're Registered<?php echo $first_name ? ', ' . esc_html( $first_name ) : ''; ?>! 🎉</h3>
					<p style="font-size:14px; color:#64748b; margin:0 0 24px; padding:0; line-height:1.5;">Your registration for <strong style="color:#0f172a;"><?php echo esc_html( $event_title ); ?></strong> is confirmed.</p>
					
					<!-- DEDICATED EXAM SLOT RESERVATION CTA HERO CARD -->
					<div style="background: linear-gradient(135deg, #0b0e17 0%, #171b26 100%); border-radius:20px; padding:26px 22px; margin:0 auto 20px; border:1px solid rgba(173,92,255,0.4); box-shadow:0 10px 30px rgba(0,0,0,0.25); text-align:center;">
						<div style="display:inline-block; font-size:10px; font-weight:900; background:rgba(173,92,255,0.2); color:#c084fc; border:1px solid rgba(173,92,255,0.5); padding:4px 14px; border-radius:20px; text-transform:uppercase; letter-spacing:0.12em; margin-bottom:12px;">
							⚡ REQUIRED NEXT STEP
						</div>
						<h4 style="font-size:18px; font-weight:900; color:#ffffff; margin:0 0 8px; line-height:1.3;">
							Book Your Exam Time Slot Now
						</h4>
						<p style="font-size:13px; color:#94a3b8; margin:0 0 20px; line-height:1.5; max-width:440px; margin-left:auto; margin-right:auto;">
							To participate in the AWS Certification Self-Assessment and receive your access code, please reserve your preferred time slot immediately.
						</p>
						<a href="https://awsairuniversity.com/reserve-slot/" 
						   style="display:inline-flex; align-items:center; justify-content:center; gap:10px; width:100%; max-width:380px; padding:16px 24px; background:linear-gradient(135deg, #ad5cff 0%, #8b31ff 100%); color:#ffffff; font-weight:900; font-size:15px; border-radius:14px; text-decoration:none; box-shadow:0 6px 20px rgba(173,92,255,0.45); transition:all 0.2s; text-transform:uppercase; letter-spacing:0.04em;">
							<span>Reserve Exam Slot Now</span>
							<svg style="width:18px; height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
						</a>
					</div>

					<div style="display:flex; align-items:center; justify-content:center; gap:16px; flex-wrap:wrap; margin-top:16px;">
						<?php if ( ! empty( $reg_id ) && $reg_id !== 'N/A' ) : ?>
							<span style="font-size:12px; font-weight:700; color:#64748b; background:#f1f5f9; padding:6px 14px; border-radius:8px; font-family:ui-monospace, monospace;">
								Reg #: <?php echo esc_html( $reg_id ); ?>
							</span>
						<?php endif; ?>
						<a href="<?php echo esc_url( get_permalink() ); ?>" style="font-size:13px; font-weight:700; color:#64748b; text-decoration:underline;">Return to Event</a>
					</div>
				</div>
			<?php else : ?>

			<!-- Alpine.js Logic -->
			<script>
				document.addEventListener('alpine:init', () => {
					Alpine.data('awsEventForm', () => ({
						ticketTier: '<?php echo $event_type === 'free' ? 'free' : ( ! empty( $price_earlybird ) ? 'earlybird' : 'regular' ); ?>',
						isSupporter: false,
						priceEarlybird: '<?php echo esc_js( $price_earlybird ); ?>',
						priceRegular: '<?php echo esc_js( $price_regular ); ?>',
						priceSupporter: '<?php echo esc_js( $price_supporter ); ?>',
						isSubmitting: false,
						
						get currentPrice() {
							if (this.isSupporter || this.ticketTier === 'supporter') return this.priceSupporter;
							if (this.ticketTier === 'earlybird') return this.priceEarlybird;
							if (this.ticketTier === 'regular') return this.priceRegular;
							return '0';
						},

						get currentTierLabel() {
							if (this.isSupporter || this.ticketTier === 'supporter') return '⭐ Super Community Supporter Pass';
							if (this.ticketTier === 'earlybird') return 'Earlybird Pass';
							if (this.ticketTier === 'regular') return 'Regular Pass';
							return 'Free RSVP';
						},
						copiedAcc: false,
						copiedIban: false,
						bankAccText: <?php echo wp_json_encode( $bank_account_number ); ?>,
						bankIbanText: <?php echo wp_json_encode( $bank_iban ); ?>,
						isCompressing: false,
						compressedSize: '',
						originalSize: '',
						previewUrl: '',
						
						copyText(type) {
							if (navigator.clipboard) {
								if (type === 'acc') {
									navigator.clipboard.writeText(this.bankAccText);
									this.copiedAcc = true;
									setTimeout(() => { this.copiedAcc = false; }, 2500);
								} else if (type === 'iban') {
									navigator.clipboard.writeText(this.bankIbanText);
									this.copiedIban = true;
									setTimeout(() => { this.copiedIban = false; }, 2500);
								}
							}
						},
						
						async handleImageUpload(e) {
							const file = e.target.files[0];
							if (!file || !file.type.startsWith('image/')) return;
							
							this.isCompressing = true;
							
							let oSize = (file.size / 1024).toFixed(1) + ' KB';
							if (file.size > 1048576) oSize = (file.size / 1048576).toFixed(2) + ' MB';
							this.originalSize = oSize;
							
							try {
								const f = await new Promise((res, rej) => {
									const r = new FileReader();
									r.readAsDataURL(file);
									r.onload = (ev) => {
										const img = new Image();
										img.src = ev.target.result;
										img.onload = () => {
											const c = document.createElement('canvas');
											let w = img.width, h = img.height, M = 1200;
											if (w > h) { if (w > M) { h *= M / w; w = M; } } else { if (h > M) { w *= M / h; h = M; } }
											c.width = w; c.height = h;
											c.getContext('2d').drawImage(img, 0, 0, w, h);
											c.toBlob((b) => {
												if (!b) return rej('fail');
												res(new File([b], file.name.replace(/\.[^/.]+$/, '') + '.webp', { type: 'image/webp' }));
											}, 'image/webp', 0.8);
										};
									};
								});
								
								const dt = new DataTransfer();
								dt.items.add(f);
								e.target.files = dt.files;
								
								let cSize = (f.size / 1024).toFixed(1) + ' KB';
								if (f.size > 1048576) cSize = (f.size / 1048576).toFixed(2) + ' MB';
								this.compressedSize = cSize;
								this.previewUrl = URL.createObjectURL(f);
							} catch (err) {
								console.warn(err);
							} finally {
								this.isCompressing = false;
							}
						},
						
						handleFormSubmit(e) {
							e.preventDefault();
							this.isSubmitting = true;
							setTimeout(() => { e.target.submit(); }, 600);
						}
					}));
				});
			</script>

			<!-- Hidden Template for Real-Time Auto-Replacement when countdown finishes -->
			<div id="aws-reg-closed-screen-template-<?php echo esc_attr( $event_id ); ?>" 
			     style="display: none; background: #ffffff; border-radius: 20px; padding: 44px 24px; text-align: center; border: 1.5px solid #e2e8f0; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box; position: relative; overflow: hidden;">
				<div style="position: absolute; top: -50px; left: 50%; transform: translateX(-50%); width: 200px; height: 200px; background: radial-gradient(circle, rgba(173, 92, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%); pointer-events: none; border-radius: 50%;"></div>

				<div style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 100px; margin-bottom: 24px;">
					<span style="display: block; width: 8px; height: 8px; border-radius: 50%; background: #e11d48;"></span>
					<span style="font-size: 11px; font-weight: 800; color: #be123c; text-transform: uppercase; letter-spacing: 0.08em;">Registrations Closed</span>
				</div>

				<div style="width: 72px; height: 72px; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.04);">
					<svg style="width: 32px; height: 32px; color: #475569;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						<path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
					</svg>
				</div>

				<h3 style="font-size: 22px; font-weight: 900; color: #0f172a; margin: 0 0 10px; padding: 0; line-height: 1.25;">
					Registration Time Has Ended
				</h3>

				<p style="font-size: 14px; color: #64748b; margin: 0 auto 24px; max-width: 460px; line-height: 1.6;">
					Thank you for your interest! Registrations for <strong style="color: #0f172a;"><?php echo esc_html( $event_title ); ?></strong> have officially closed.
				</p>

				<div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 16px; padding: 18px 16px; margin-bottom: 28px; text-align: left; display: flex; align-items: flex-start; gap: 12px;">
					<div style="font-size: 20px; flex-shrink: 0; line-height: 1;">💡</div>
					<div>
						<span style="display: block; font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 2px;">Want to join our next activity?</span>
						<span style="display: block; font-size: 12px; color: #64748b; line-height: 1.5;">Join our official AWS SBG community network to get instant updates on future masterclasses, hackathons, and certification vouchers.</span>
					</div>
				</div>

				<div style="display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; align-items: center;">
					<a href="<?php echo esc_url( home_url( '/events/' ) ); ?>" 
					   style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 22px; background: #ad5cff; color: #ffffff; font-weight: 800; font-size: 13px; border-radius: 12px; text-decoration: none; box-shadow: 0 4px 14px rgba(173, 92, 255, 0.3); transition: all 0.2s;">
						<span>Explore All Events</span>
						<svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
					</a>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" 
					   style="display: inline-flex; align-items: center; justify-content: center; padding: 12px 22px; background: #f1f5f9; color: #334155; font-weight: 800; font-size: 13px; border-radius: 12px; text-decoration: none; transition: all 0.2s;">
						Return to Homepage
					</a>
				</div>
			</div>

			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" enctype="multipart/form-data" style="margin:0; padding:0;"

			      x-data="awsEventForm"
			      @submit="handleFormSubmit($event)">

				<!-- Hidden fields block -->
				<div style="display:none; width:0; height:0; overflow:hidden;">
					<input type="hidden" name="action" value="submit_aws_event_registration">
					<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
					<?php wp_nonce_field( 'aws_event_registration_action', 'aws_event_registration_nonce' ); ?>
				</div>

				<!-- SECTION 1: Personal Details -->
				<div>
					<div style="<?php echo $section_title; ?>">
						<span style="<?php echo $badge_style; ?>">1</span>
						<h4 style="<?php echo $badge_text; ?>">Personal Details</h4>
					</div>
					
					<div style="<?php echo $grid_container; ?>">
						<div style="<?php echo $grid_item_half; ?>">
							<label for="reg_name" style="<?php echo $label_style; ?>">Full Name <span style="color:#ef4444;">*</span></label>
							<input type="text" id="reg_name" name="full_name" required placeholder="e.g. Ali" style="<?php echo $input_style; ?>">
						</div>
						<div style="<?php echo $grid_item_half; ?>">
							<label for="reg_email" style="<?php echo $label_style; ?>">Email Address <span style="color:#ef4444;">*</span></label>
							<input type="email" id="reg_email" name="email" required placeholder="you@domain.com" style="<?php echo $input_style; ?>">
						</div>
						<div style="<?php echo $grid_item_half; ?>">
							<label for="reg_whatsapp" style="<?php echo $label_style; ?>">WhatsApp Number <span style="color:#ef4444;">*</span></label>
							<input type="tel" id="reg_whatsapp" name="whatsapp_number" required placeholder="03000000000" style="<?php echo $input_style; ?>">
						</div>
						<div style="<?php echo $grid_item_half; ?>">
							<label for="reg_uni" style="<?php echo $label_style; ?>">University / Organization <span style="color:#ef4444;">*</span></label>
							<input type="text" id="reg_uni" name="university_organization" required placeholder="Air University Islamabad" style="<?php echo $input_style; ?>">
						</div>
						<?php if ( $req_tshirt ) : ?>
						<div style="<?php echo $grid_item_half; ?>">
							<label for="reg_tshirt" style="<?php echo $label_style; ?>">T-Shirt Size <span style="color:#ef4444;">*</span></label>
							<select id="reg_tshirt" name="tshirt_size" required style="<?php echo $input_style; ?>">
								<option value="">Select Size...</option>
								<option value="Small">Small (S)</option>
								<option value="Medium">Medium (M)</option>
								<option value="Large">Large (L)</option>
								<option value="XL">Extra Large (XL)</option>
								<option value="XXL">Double XL (XXL)</option>
							</select>
						</div>
						<?php endif; ?>
						<?php if ( $req_experience ) : ?>
						<div style="<?php echo $grid_item_half; ?>">
							<label for="reg_exp" style="<?php echo $label_style; ?>">Experience Level <span style="color:#ef4444;">*</span></label>
							<select id="reg_exp" name="experience_level" required style="<?php echo $input_style; ?>">
								<option value="Beginner">Beginner (New to Cloud)</option>
								<option value="Intermediate">Intermediate (Some AWS knowledge)</option>
								<option value="Advanced">Advanced (DevOps / Architect)</option>
							</select>
						</div>
						<?php endif; ?>
						<?php if ( $req_github ) : ?>
						<div style="<?php echo $grid_item_full; ?>">
							<label for="reg_github" style="<?php echo $label_style; ?>">GitHub / Portfolio URL <span style="color:#ef4444;">*</span></label>
							<input type="url" id="reg_github" name="github_url" required placeholder="https://github.com/username" style="<?php echo $input_style; ?>">
						</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Hidden Ticket Tier Value -->
				<div style="display:none; width:0; height:0; overflow:hidden;">
					<input type="hidden" name="ticket_tier" :value="isSupporter ? 'supporter' : ticketTier">
				</div>

				<?php /* TEMPORARILY COMMENTED OUT: "Want to Get Featured? Become a Super Supporter" */ ?>
				<?php if ( false && $event_type === 'free' && $enable_supporter ) : ?>
				<!-- FREE EVENT SUPPORTER UPSELL COMPACT CHECKBOX -->
				<div style="margin-top:24px; border-radius:16px; padding:16px 20px; transition:all 0.2s;"
				     :style="{
				         background: isSupporter ? '#fbf8ff' : '#f8fafc',
				         border: isSupporter ? '2px solid #ad5cff' : '1.5px solid #cbd5e1',
				         boxShadow: isSupporter ? '0 8px 24px rgba(173,92,255,0.12)' : 'none'
				     }">
					<label style="display:flex; align-items:center; justify-content:space-between; gap:12px; cursor:pointer; margin:0;">
						<div style="display:flex; align-items:center; gap:12px; min-width:0;">
							<input type="checkbox" name="is_supporter_upsell" value="1" x-model="isSupporter"
							       @change="if(isSupporter){ ticketTier = 'supporter'; } else { ticketTier = 'free'; }"
							       style="width:20px; height:20px; accent-color:#ad5cff; flex-shrink:0; cursor:pointer;">
							<span style="font-size:14px; font-weight:800; line-height:1.3;" :style="{ color: isSupporter ? '#0f172a' : '#475569' }">
								🌟 Want to Get Featured? <span :style="{ color: isSupporter ? '#ad5cff' : '#64748b' }">Become a Super Supporter</span>
							</span>
						</div>
						<span style="font-size:11px; font-weight:900; padding:4px 12px; border-radius:20px; flex-shrink:0; transition:all 0.2s;"
						      :style="{
						          background: isSupporter ? '#ad5cff' : '#e2e8f0',
						          color: isSupporter ? '#ffffff' : '#64748b',
						          boxShadow: isSupporter ? '0 2px 8px rgba(173,92,255,0.25)' : 'none'
						      }">
							Rs. <?php echo esc_html( $price_supporter ); ?>
						</span>
					</label>

					<div x-show="isSupporter" style="margin-top:12px; padding-top:12px; border-top:1px dashed rgba(173,92,255,0.3);">
						<p style="font-size:12px; color:#64748b; margin:0 0 8px 0; line-height:1.4;">
							Support the AWS Student Builder Group community and receive exclusive recognition &amp; VIP perks:
						</p>
						<ul style="margin:0; padding:0; list-style:none; display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:6px;">
							<?php foreach ( $supporter_perks as $perk ) : ?>
								<li style="font-size:12px; font-weight:700; color:#475569; display:flex; align-items:center; gap:6px;">
									<span style="color:#ad5cff; font-weight:900;">✓</span>
									<span><?php echo esc_html( $perk ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
				<?php endif; ?>

				<?php if ( $event_type !== 'free' ) : ?>
				<!-- SECTION 2: Select Ticket -->
				<div style="<?php echo $divider; ?>">
					<div style="<?php echo $section_title; ?>">
						<span style="<?php echo $badge_style; ?>">2</span>
						<h4 style="<?php echo $badge_text; ?>">Select Ticket Pass</h4>
					</div>
					
					<div style="<?php echo $grid_container; ?>">
						<?php if ( ! empty( $price_earlybird ) ) : ?>
						<!-- Earlybird -->
						<label class="ticket-label" style="<?php echo $grid_item_half; ?> cursor:pointer; position:relative; overflow:hidden;"
						       onmouseenter="if(!this.querySelector('input').checked){this.querySelector('.ticket-card').style.borderColor='#ad5cff'; this.querySelector('.ticket-card').style.background='#faf5ff';}"
						       onmouseleave="if(!this.querySelector('input').checked){this.querySelector('.ticket-card').style.borderColor='#e2e8f0'; this.querySelector('.ticket-card').style.background='#ffffff';}">
							<input type="radio" name="ticket_tier" value="earlybird" x-model="ticketTier" style="position:absolute; opacity:0; width:0; height:0;">
							<div class="ticket-card" :style="{ 
								border: ticketTier === 'earlybird' ? '2px solid #ad5cff' : '2px solid #e2e8f0', 
								background: ticketTier === 'earlybird' ? '#faf5ff' : '#ffffff',
								boxShadow: ticketTier === 'earlybird' ? '0 10px 25px rgba(173,92,255,0.15)' : '0 4px 6px rgba(0,0,0,0.02)',
								padding: '20px', borderRadius: '16px', transition: 'all 0.2s', height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'flex-start', position: 'relative', overflow: 'hidden', boxSizing: 'border-box'
							}">
								<div x-show="ticketTier === 'earlybird'" style="position:absolute; top:0; right:0; background:#ad5cff; color:#fff; font-size:10px; font-weight:900; text-transform:uppercase; padding:4px 12px; border-bottom-left-radius:12px; letter-spacing:0.05em; margin:0;">Selected</div>
								
								<div style="margin-bottom:12px;">
									<span style="display:inline-block; background:#dcfce7; color:#166534; font-size:10px; font-weight:900; text-transform:uppercase; padding:4px 8px; border-radius:6px; letter-spacing:0.05em; margin-bottom:8px;">Earlybird Discount</span>
									<div style="font-size:18px; font-weight:900; color:#0f172a; margin:0; padding:0; line-height:1.2;">Earlybird Pass</div>
								</div>
								
								<div style="font-size:24px; font-weight:900; color:#ad5cff; margin:0 0 16px 0; padding:0; line-height:1;">Rs. <?php echo esc_html( $price_earlybird ); ?></div>
								
								<div style="margin-top:auto; border-top:1px solid #e2e8f0; padding-top:16px;">
									<div style="font-size:11px; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">🎁 What's Included:</div>
									<ul style="margin:0; padding:0; list-style:none;">
										<?php foreach ( $earlybird_perks as $perk ) : ?>
											<li style="font-size:13px; color:#475569; margin-bottom:8px; display:flex; align-items:flex-start; gap:8px;">
												<span style="color:#10b981; flex-shrink:0; font-weight:900;">✓</span>
												<span style="line-height:1.4;"><?php echo esc_html( $perk ); ?></span>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
						</label>
						<?php endif; ?>

						<!-- Regular -->
						<label class="ticket-label" style="<?php echo ! empty( $price_earlybird ) ? $grid_item_half : $grid_item_full; ?> cursor:pointer; position:relative; overflow:hidden;"
						       onmouseenter="if(!this.querySelector('input').checked){this.querySelector('.ticket-card').style.borderColor='#ad5cff'; this.querySelector('.ticket-card').style.background='#faf5ff';}"
						       onmouseleave="if(!this.querySelector('input').checked){this.querySelector('.ticket-card').style.borderColor='#e2e8f0'; this.querySelector('.ticket-card').style.background='#ffffff';}">
							<input type="radio" name="ticket_tier" value="regular" x-model="ticketTier" style="position:absolute; opacity:0; width:0; height:0;">
							<div class="ticket-card" :style="{ 
								border: ticketTier === 'regular' ? '2px solid #ad5cff' : '2px solid #e2e8f0', 
								background: ticketTier === 'regular' ? '#faf5ff' : '#ffffff',
								boxShadow: ticketTier === 'regular' ? '0 10px 25px rgba(173,92,255,0.15)' : '0 4px 6px rgba(0,0,0,0.02)',
								padding: '20px', borderRadius: '16px', transition: 'all 0.2s', height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'flex-start', position: 'relative', overflow: 'hidden', boxSizing: 'border-box'
							}">
								<div x-show="ticketTier === 'regular'" style="position:absolute; top:0; right:0; background:#ad5cff; color:#fff; font-size:10px; font-weight:900; text-transform:uppercase; padding:4px 12px; border-bottom-left-radius:12px; letter-spacing:0.05em; margin:0;">Selected</div>
								
								<div style="margin-bottom:12px;">
									<span style="display:inline-block; background:#f3e8ff; color:#6b21a8; font-size:10px; font-weight:900; text-transform:uppercase; padding:4px 8px; border-radius:6px; letter-spacing:0.05em; margin-bottom:8px;">Standard Price</span>
									<div style="font-size:18px; font-weight:900; color:#0f172a; margin:0; padding:0; line-height:1.2;">Regular Pass</div>
								</div>
								
								<div style="font-size:24px; font-weight:900; color:#ad5cff; margin:0 0 16px 0; padding:0; line-height:1;">Rs. <?php echo esc_html( $price_regular ); ?></div>
								
								<div style="margin-top:auto; border-top:1px solid #e2e8f0; padding-top:16px;">
									<div style="font-size:11px; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">🎁 What's Included:</div>
									<ul style="margin:0; padding:0; list-style:none;">
										<?php foreach ( $regular_perks as $perk ) : ?>
											<li style="font-size:13px; color:#475569; margin-bottom:8px; display:flex; align-items:flex-start; gap:8px;">
												<span style="color:#10b981; flex-shrink:0; font-weight:900;">✓</span>
												<span style="line-height:1.4;"><?php echo esc_html( $perk ); ?></span>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
						</label>

						<?php /* TEMPORARILY COMMENTED OUT: "Want to Get Featured? Become a Super Supporter" */ ?>
						<?php if ( false && $enable_supporter ) : ?>
						<!-- PAID EVENT SUPPORTER UPSELL OPTION -->
						<div style="<?php echo $grid_item_full; ?> margin-top:12px;">
							<div style="border-radius:16px; padding:16px 20px; transition:all 0.2s;"
							     :style="{
							         background: isSupporter ? '#fbf8ff' : '#f8fafc',
							         border: isSupporter ? '2px solid #ad5cff' : '1.5px solid #cbd5e1',
							         boxShadow: isSupporter ? '0 8px 24px rgba(173,92,255,0.12)' : 'none'
							     }">
								<label style="display:flex; align-items:center; justify-content:space-between; gap:12px; cursor:pointer; margin:0;">
									<div style="display:flex; align-items:center; gap:12px; min-width:0;">
										<input type="checkbox" name="is_supporter_upsell" value="1" x-model="isSupporter"
										       @change="if(isSupporter){ ticketTier = 'supporter'; } else { ticketTier = '<?php echo ! empty( $price_earlybird ) ? 'earlybird' : 'regular'; ?>'; }"
										       style="width:20px; height:20px; accent-color:#ad5cff; flex-shrink:0; cursor:pointer;">
										<span style="font-size:14px; font-weight:800; line-height:1.3;" :style="{ color: isSupporter ? '#0f172a' : '#475569' }">
											🌟 Want to Get Featured? <span :style="{ color: isSupporter ? '#ad5cff' : '#64748b' }">Become a Super Supporter</span>
										</span>
									</div>
									<span style="font-size:11px; font-weight:900; padding:4px 12px; border-radius:20px; flex-shrink:0; transition:all 0.2s;"
									      :style="{
									          background: isSupporter ? '#ad5cff' : '#e2e8f0',
									          color: isSupporter ? '#ffffff' : '#64748b',
									          boxShadow: isSupporter ? '0 2px 8px rgba(173,92,255,0.25)' : 'none'
									      }">
										Rs. <?php echo esc_html( $price_supporter ); ?>
									</span>
								</label>

								<div x-show="isSupporter" style="margin-top:12px; padding-top:12px; border-top:1px dashed rgba(173,92,255,0.3);">
									<p style="font-size:12px; color:#64748b; margin:0 0 8px 0; line-height:1.4;">
										Support the AWS Student Builder Group community and receive exclusive recognition &amp; VIP perks:
									</p>
									<ul style="margin:0; padding:0; list-style:none; display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:6px;">
										<?php foreach ( $supporter_perks as $perk ) : ?>
											<li style="font-size:12px; font-weight:700; color:#475569; display:flex; align-items:center; gap:6px;">
												<span style="color:#ad5cff; font-weight:900;">✓</span>
												<span><?php echo esc_html( $perk ); ?></span>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
						</div>
						<?php endif; ?>

					</div>
				</div>
				<?php endif; ?>

				<!-- SECTION 3: Payment (Visible for Paid Events OR when Super Supporter is checked) -->
				<div x-show="ticketTier !== 'free' || isSupporter" style="<?php echo $divider; ?>">
					<div style="<?php echo $section_title; ?>">
						<span style="<?php echo $badge_style; ?>"><?php echo $event_type === 'free' ? '2' : '3'; ?></span>
						<h4 style="<?php echo $badge_text; ?>">Payment Details &amp; Confirmation</h4>
					</div>

					<!-- Light Bank Card -->
					<div style="background:#ffffff; border-radius:16px; overflow:hidden; margin-bottom:24px; border:1px solid #e2e8f0; box-shadow:0 8px 25px rgba(0,0,0,0.04);">
						<div style="padding:16px 14px; display:flex; flex-direction:column; gap:14px;">
							
							<!-- INTEGRATED DYNAMIC AMOUNT TO TRANSFER -->
							<div style="background:linear-gradient(135deg, #fbf8ff 0%, #f3e8ff 100%); border:1.5px solid #ad5cff; border-radius:12px; padding:12px 14px; display:flex; align-items:center; justify-content:space-between; gap:10px; box-shadow:0 4px 15px rgba(173,92,255,0.08);">
								<div style="min-width:0; flex:1;">
									<span style="display:block; font-size:10px; font-weight:800; color:#ad5cff; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Amount to Transfer</span>
									<span style="display:block; font-size:13px; font-weight:900; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" x-text="currentTierLabel"></span>
								</div>
								<div style="display:inline-flex; align-items:center; background:#ad5cff; color:#ffffff; font-size:15px; font-weight:900; padding:6px 12px; border-radius:8px; font-family:ui-monospace, monospace; box-shadow:0 3px 10px rgba(173,92,255,0.25); flex-shrink:0; white-space:nowrap;">
									Rs. <span x-text="currentPrice" style="margin-left:3px;"></span>
								</div>
							</div>

							<div style="display:flex; flex-wrap:wrap; gap:16px;">
								<div style="flex:1 1 100%;">
									<span style="display:block; font-size:11px; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Bank Name</span>
									<span style="display:block; font-size:15px; font-weight:900; color:#0f172a;"><?php echo esc_html( $bank_name ); ?></span>
								</div>
							</div>
							
							<div style="display:flex; flex-direction:column; gap:12px;">
								<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; gap:12px;">
									<div style="min-width:0; flex:1;">
										<span style="display:block; font-size:10px; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px;">Account Number</span>
										<span style="display:block; font-size:16px; font-weight:900; color:#ad5cff; font-family:ui-monospace, monospace; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo esc_html( $bank_account_number ); ?></span>
									</div>
									<button type="button" @click="copyText('acc')"
									        title="Copy Account Number"
									        aria-label="Copy Account Number"
									        style="width:40px; height:40px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; cursor:pointer; font-family:inherit; transition:all 0.2s ease-in-out; outline:none; box-sizing:border-box;"
									        :style="copiedAcc ? 'background:#fbf8ff; color:#ad5cff; border:1.5px solid #ad5cff; transform:scale(1.05);' : 'background:#ffffff; color:#ad5cff; border:1.5px solid #e2e8f0;'"
									        onmouseenter="this.style.borderColor='#ad5cff'; this.style.background='#fbf8ff';"
									        onmouseleave="if(!this.classList.contains('copied')){this.style.borderColor='#e2e8f0'; this.style.background='#ffffff';}">
										<svg x-show="!copiedAcc" style="width:18px; height:18px; flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
											<path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
										</svg>
										<svg x-show="copiedAcc" style="width:18px; height:18px; flex-shrink:0; color:#ad5cff;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
											<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
										</svg>
									</button>
								</div>
								
								<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; gap:12px;">
									<div style="min-width:0; flex:1;">
										<span style="display:block; font-size:10px; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px;">IBAN</span>
										<span style="display:block; font-size:14px; font-weight:900; color:#ad5cff; font-family:ui-monospace, monospace; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo esc_html( $bank_iban ); ?></span>
									</div>
									<button type="button" @click="copyText('iban')"
									        title="Copy IBAN"
									        aria-label="Copy IBAN"
									        style="width:40px; height:40px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; cursor:pointer; font-family:inherit; transition:all 0.2s ease-in-out; outline:none; box-sizing:border-box;"
									        :style="copiedIban ? 'background:#fbf8ff; color:#ad5cff; border:1.5px solid #ad5cff; transform:scale(1.05);' : 'background:#ffffff; color:#ad5cff; border:1.5px solid #e2e8f0;'"
									        onmouseenter="this.style.borderColor='#ad5cff'; this.style.background='#fbf8ff';"
									        onmouseleave="if(!this.classList.contains('copied')){this.style.borderColor='#e2e8f0'; this.style.background='#ffffff';}">
										<svg x-show="!copiedIban" style="width:18px; height:18px; flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
											<path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
										</svg>
										<svg x-show="copiedIban" style="width:18px; height:18px; flex-shrink:0; color:#ad5cff;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
											<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
										</svg>
									</button>
								</div>
							</div>
						</div>
					</div>

					<!-- Upload Box -->
					<div>
						<label style="<?php echo $label_style; ?>">Upload Payment Receipt Screenshot <span style="color:#ef4444;">*</span></label>
						<div style="position:relative; border:2px dashed #e2e8f0; border-radius:12px; background:#ffffff; cursor:pointer; overflow:hidden; transition:all 0.2s;"
						     onmouseenter="this.style.borderColor='#ad5cff'; this.style.background='#faf5ff';"
						     onmouseleave="this.style.borderColor='#e2e8f0'; this.style.background='#ffffff';">
							
							<input type="file" name="payment_screenshot" :required="ticketTier !== 'free' || isSupporter" accept="image/*" @change="handleImageUpload($event)"
							       style="position:absolute; inset:0; width:100%; height:100%; opacity:0; cursor:pointer; z-index:10; margin:0; padding:0; border:none;">
							
							<!-- Default State -->
							<div x-show="!previewUrl && !isCompressing" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:24px 20px; text-align:center;">
								<svg style="width:32px; height:32px; color:#94a3b8; margin:0 auto 8px; display:block; float:none; clear:both;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
									<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
								</svg>
								<div style="font-size:14px; font-weight:700; color:#475569; margin-bottom:4px; margin-top:0;">Upload Screenshot</div>
								<div style="font-size:12px; color:#94a3b8; margin:0;">PNG or JPG (auto-optimized)</div>
							</div>
							
							<!-- Loading State (Hidden by default using inline display:none to prevent flicker) -->
							<div x-show="isCompressing" style="display:none; flex-direction:column; align-items:center; justify-content:center; padding:24px 20px;">
								<div style="width:24px; height:24px; border:2px solid #ad5cff; border-top-color:transparent; border-radius:50%; animation:reg-spin 0.8s linear infinite; margin-bottom:8px;"></div>
								<div style="font-size:12px; font-weight:800; color:#ad5cff; margin:0;">Optimizing...</div>
							</div>
							
							<!-- Success State (Hidden by default) -->
							<div x-show="previewUrl && !isCompressing" style="display:none; align-items:center; justify-content:center; gap:12px; padding:16px;">
								<img :src="previewUrl" style="width:48px; height:48px; object-fit:cover; border-radius:8px; border:1px solid #e2e8f0; flex-shrink:0;">
								<div style="text-align:left;">
									<div style="font-size:13px; font-weight:800; color:#059669; margin-bottom:2px; display:flex; align-items:center; gap:6px;">
										✓ Ready to Upload 
										<span style="background:#dcfce7; color:#166534; font-size:10px; padding:2px 6px; border-radius:4px; font-weight:900;">WebP</span>
									</div>
									<div style="font-size:11px; color:#64748b; margin:0;">
										<span x-text="originalSize" style="text-decoration:line-through; opacity:0.7;"></span> 
										<span style="margin:0 2px;">→</span> 
										<span x-text="compressedSize" style="font-weight:800; color:#ad5cff;"></span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Confirmation Checkbox -->
				<div style="margin-top:24px; display:flex; align-items:flex-start; gap:12px; background:#f8fafc; padding:16px; border-radius:12px; border:1px solid #e2e8f0;">
					<input type="checkbox" id="reg_confirm" name="confirm_details" required style="width:20px; height:20px; margin-top:2px; accent-color:#ad5cff; cursor:pointer; flex-shrink:0;">
					<label for="reg_confirm" style="font-size:14px; color:#475569; font-weight:600; line-height:1.5; cursor:pointer; margin:0;">
						I confirm that all the details provided above are correct and accurate.
					</label>
				</div>

				<!-- Submit Button -->
				<div style="margin-top:24px;">
					<button type="submit" :disabled="isSubmitting"
					        style="display:flex; align-items:center; justify-content:center; gap:12px; width:100%; padding:18px 24px; background:linear-gradient(135deg, #ad5cff, #7000ff); color:#ffffff; font-size:16px; font-weight:900; border:none; border-radius:16px; cursor:pointer; box-shadow:0 10px 30px rgba(173,92,255,0.3); font-family:inherit; transition:opacity 0.2s; margin:0; line-height:1;">
						
						<span x-show="!isSubmitting" style="color:#ffffff; margin:0; line-height:1;">Confirm Registration</span>
						
						<span x-show="isSubmitting" :style="{ display: isSubmitting ? 'inline-flex' : 'none' }" style="display:none; align-items:center; justify-content:center; gap:10px; color:#ffffff; line-height:1;">
							<span style="width:20px; height:20px; border:3px solid rgba(255,255,255,0.3); border-top-color:#ffffff; border-radius:50%; animation:reg-spin 0.8s linear infinite; display:inline-block; flex-shrink:0; box-sizing:border-box;"></span>
							<span style="color:#ffffff; margin:0; line-height:1; font-weight:800;">Processing securely...</span>
						</span>
					</button>
				</div>

			</form>
			<?php endif; ?>

		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'aws_event_form', 'aws_sbg_event_form_shortcode' );
add_shortcode( 'aws_activity_form', 'aws_sbg_event_form_shortcode' );

/**
 * Robust Auto-Incrementing Registration Number Generator
 * Ensures deleting old registrations never causes counter resets or duplicate IDs.
 */
function aws_sbg_generate_next_registration_number() {
	global $wpdb;

	$next_num = get_option( 'aws_sbg_next_registration_number' );

	if ( false === $next_num || intval( $next_num ) < 1001 ) {
		$highest_meta = $wpdb->get_var( "
			SELECT meta_value 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = 'registration_id' 
			ORDER BY CAST(REPLACE(meta_value, '#', '') AS UNSIGNED) DESC 
			LIMIT 1
		" );

		if ( $highest_meta ) {
			$num_val  = intval( str_replace( '#', '', $highest_meta ) );
			$next_num = max( 1001, $num_val + 1 );
		} else {
			$next_num = 1001;
		}
	} else {
		$next_num = intval( $next_num );
	}

	update_option( 'aws_sbg_next_registration_number', $next_num + 1 );

	return '#' . $next_num;
}

/**
 * 4. Submission Handler
 */
function aws_sbg_handle_event_registration() {
	if ( $_SERVER['REQUEST_METHOD'] !== 'POST' || ! isset( $_POST['action'] ) || $_POST['action'] !== 'submit_aws_event_registration' ) {
		return;
	}

	if ( ! isset( $_POST['aws_event_registration_nonce'] ) || ! wp_verify_nonce( $_POST['aws_event_registration_nonce'], 'aws_event_registration_action' ) ) {
		wp_die( 'Security check failed. Please refresh and try again.' );
	}

	$event_id    = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;

	// Server-side check for deadline expiration if timer is set to auto-close form
	$enable_timer         = (bool) get_post_meta( $event_id, 'aws_event_enable_timer', true );
	$timer_end            = get_post_meta( $event_id, 'aws_event_timer_end', true );
	$timer_expired_action = get_post_meta( $event_id, 'aws_event_timer_expired_action', true );

	if ( $enable_timer && ! empty( $timer_end ) && 'auto_close_form' === $timer_expired_action ) {
		try {
			$timer_end_clean = str_replace( ' ', 'T', trim( $timer_end ) );
			$dt = new DateTime( $timer_end_clean, wp_timezone() );
			if ( time() > $dt->getTimestamp() ) {
				wp_die( 'Registration deadline has passed. Submissions are no longer accepted for this event.' );
			}
		} catch ( Exception $e ) {
			// ignore fallback
		}
	}

	$full_name   = isset( $_POST['full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) : '';

	$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$whatsapp    = isset( $_POST['whatsapp_number'] ) ? sanitize_text_field( wp_unslash( $_POST['whatsapp_number'] ) ) : '';
	$university  = isset( $_POST['university_organization'] ) ? sanitize_text_field( wp_unslash( $_POST['university_organization'] ) ) : '';

	$event_type  = get_post_meta( $event_id, 'aws_event_type', true ) ?: 'free';
	$raw_ticket  = isset( $_POST['ticket_tier'] ) ? sanitize_text_field( wp_unslash( $_POST['ticket_tier'] ) ) : '';

	$tshirt_size      = isset( $_POST['tshirt_size'] ) ? sanitize_text_field( wp_unslash( $_POST['tshirt_size'] ) ) : '';
	$github_url       = isset( $_POST['github_url'] ) ? esc_url_raw( wp_unslash( $_POST['github_url'] ) ) : '';
	$experience_level = isset( $_POST['experience_level'] ) ? sanitize_text_field( wp_unslash( $_POST['experience_level'] ) ) : '';

	$is_supporter_upsell = ( isset( $_POST['is_supporter_upsell'] ) && '1' === $_POST['is_supporter_upsell'] ) || 'supporter' === $raw_ticket;

	if ( $is_supporter_upsell ) {
		$ticket_tier = 'supporter';
	} elseif ( ! empty( $raw_ticket ) && 'free' !== $raw_ticket ) {
		$ticket_tier = $raw_ticket;
	} elseif ( 'paid' === $event_type ) {
		$ticket_tier = 'regular';
	} else {
		$ticket_tier = 'free';
	}

	if ( empty( $full_name ) || empty( $email ) || empty( $whatsapp ) || empty( $university ) ) {
		wp_die( 'Please fill in all required fields.' );
	}

	$reg_number   = aws_sbg_generate_next_registration_number();
	$title_suffix = $is_supporter_upsell ? ' ⭐ Super Supporter' : '';

	$post_id = wp_insert_post( array(
		'post_type'   => 'aws_event_reg',
		'post_title'  => $reg_number . ' - ' . $full_name . $title_suffix,
		'post_status' => 'publish',
	) );

	if ( is_wp_error( $post_id ) ) {
		wp_die( 'Failed to save registration. Please try again.' );
	}

	update_post_meta( $post_id, 'registration_id', $reg_number );
	update_post_meta( $post_id, 'event_id', $event_id );
	update_post_meta( $post_id, 'email', $email );
	update_post_meta( $post_id, 'whatsapp_number', $whatsapp );
	update_post_meta( $post_id, 'university_organization', $university );
	update_post_meta( $post_id, 'ticket_tier', $ticket_tier );
	update_post_meta( $post_id, 'is_supporter', $is_supporter_upsell ? 'yes' : 'no' );
	update_post_meta( $post_id, 'tshirt_size', $tshirt_size );
	update_post_meta( $post_id, 'github_url', $github_url );
	update_post_meta( $post_id, 'experience_level', $experience_level );

	update_post_meta( $post_id, 'attended', 'no' );
	update_post_meta( $post_id, 'lunch_claimed', 'no' );
	update_post_meta( $post_id, 'swag_claimed', 'no' );
	update_post_meta( $post_id, 'custom_perk_claimed', 'no' );

	if ( ! empty( $_FILES['payment_screenshot']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$attachment_id = media_handle_upload( 'payment_screenshot', $post_id );
		if ( ! is_wp_error( $attachment_id ) ) {
			update_post_meta( $post_id, 'payment_screenshot', $attachment_id );
		}
	}

	$redirect_url = add_query_arg( array(
		'status' => 'success',
		'reg_id' => urlencode( $reg_number ),
		'fname'  => urlencode( $full_name ),
	), wp_get_referer() ? wp_get_referer() : home_url( '/' ) );

	wp_redirect( $redirect_url );
	exit;
}
add_action( 'admin_post_nopriv_submit_aws_event_registration', 'aws_sbg_handle_event_registration' );
add_action( 'admin_post_submit_aws_event_registration', 'aws_sbg_handle_event_registration' );
