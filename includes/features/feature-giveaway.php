<?php
/**
 * AWS SBG Multi-Giveaway Engine
 * File: feature-giveaway.php
 *
 * Create unlimited giveaways from WP Admin, each with its own requirements
 * (tick / link-or-text proof / image upload) and its own detail fields.
 * Public visitors fulfil requirements and submit; admins review submissions
 * per giveaway and publish winners.
 *
 * @package AWS_SBG_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AWS_SBG_GIVEAWAY_DBV', '2' );
define( 'AWS_SBG_GIVEAWAY_MAX_UPLOAD', 2 * 1024 * 1024 ); // 2 MB

/* ============================================================================
 * 1. DATABASE SCHEMA
 * ============================================================================ */

function aws_sbg_giveaway_init_db() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// Entries / submissions
	$table_entries = $wpdb->prefix . 'aws_giveaway_entries';
	dbDelta( "CREATE TABLE $table_entries (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		giveaway_id bigint(20) NOT NULL DEFAULT 0,
		campaign_id varchar(50) NOT NULL DEFAULT '',
		ticket_number varchar(50) NOT NULL,
		full_name varchar(255) NOT NULL DEFAULT '',
		email varchar(255) NOT NULL DEFAULT '',
		phone varchar(50) NOT NULL DEFAULT '',
		custom_data longtext,
		source varchar(100) NOT NULL DEFAULT '',
		status varchar(20) NOT NULL DEFAULT 'pending',
		is_winner tinyint(1) NOT NULL DEFAULT 0,
		winner_drawn_at datetime DEFAULT NULL,
		ip_address varchar(45) NOT NULL DEFAULT '',
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		UNIQUE KEY unique_ticket (ticket_number),
		KEY giveaway_idx (giveaway_id),
		KEY is_winner_idx (is_winner)
	) $charset_collate;" );

	// Requirement responses (one row per requirement per submission)
	$table_rv = $wpdb->prefix . 'aws_giveaway_requirement_values';
	dbDelta( "CREATE TABLE $table_rv (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		entry_id bigint(20) NOT NULL DEFAULT 0,
		giveaway_id bigint(20) NOT NULL DEFAULT 0,
		requirement_id varchar(64) NOT NULL DEFAULT '',
		req_type varchar(10) NOT NULL DEFAULT 'check',
		check_state tinyint(1) NOT NULL DEFAULT 0,
		value_text text,
		attachment_id bigint(20) NOT NULL DEFAULT 0,
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY entry_idx (entry_id),
		KEY giveaway_idx (giveaway_id)
	) $charset_collate;" );
}
add_action( 'after_switch_theme', 'aws_sbg_giveaway_init_db' );
add_action( 'admin_init', 'aws_sbg_giveaway_init_db', 10 );

/**
 * Does an index exist on a table?
 */
function aws_sbg_giveaway_index_exists( $table, $index ) {
	global $wpdb;
	$found = $wpdb->get_var( $wpdb->prepare( "SHOW INDEX FROM $table WHERE Key_name = %s", $index ) );
	return ! empty( $found );
}

/**
 * Drop legacy unique keys and add per-giveaway composite unique keys.
 * Runs once, after any legacy rows have been backfilled.
 */
function aws_sbg_giveaway_swap_indexes() {
	global $wpdb;
	$table = $wpdb->prefix . 'aws_giveaway_entries';

	foreach ( array( 'unique_campaign_email', 'unique_campaign_phone' ) as $legacy ) {
		if ( aws_sbg_giveaway_index_exists( $table, $legacy ) ) {
			$wpdb->query( "ALTER TABLE $table DROP INDEX $legacy" );
		}
	}
	if ( ! aws_sbg_giveaway_index_exists( $table, 'unique_giveaway_email' ) ) {
		$wpdb->query( "ALTER TABLE $table ADD UNIQUE KEY unique_giveaway_email (giveaway_id, email)" );
	}
	if ( ! aws_sbg_giveaway_index_exists( $table, 'unique_giveaway_phone' ) ) {
		$wpdb->query( "ALTER TABLE $table ADD UNIQUE KEY unique_giveaway_phone (giveaway_id, phone)" );
	}
}

/* ============================================================================
 * 2. CUSTOM POST TYPE
 * ============================================================================ */

function aws_sbg_register_giveaway_cpt() {
	register_post_type( 'aws_giveaway', array(
		'labels'          => array(
			'name'          => 'Giveaways',
			'singular_name' => 'Giveaway',
			'add_new_item'  => 'Add New Giveaway',
			'edit_item'     => 'Edit Giveaway',
			'all_items'     => 'All Giveaways',
			'menu_name'     => '🎁 Giveaways',
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'aws-sbg-suite',
		'supports'        => array( 'title' ),
		'has_archive'     => false,
		'rewrite'         => false,
		'menu_icon'       => 'dashicons-megaphone',
		'capability_type' => 'post',
	) );
}
add_action( 'init', 'aws_sbg_register_giveaway_cpt', 5 );

/* ============================================================================
 * 3. HELPERS
 * ============================================================================ */

function aws_sbg_default_giveaway_fields() {
	return array(
		array( 'id' => 'full_name', 'label' => 'Full Name', 'type' => 'text', 'required' => 1, 'options' => '' ),
		array( 'id' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => 1, 'options' => '' ),
		array( 'id' => 'phone', 'label' => 'Phone / WhatsApp', 'type' => 'tel', 'required' => 1, 'options' => '' ),
	);
}

function aws_sbg_get_giveaway_meta( $giveaway_id ) {
	$defaults = array(
		'giveaway_status'       => 'open',
		'giveaway_description'  => '',
		'opens_at'              => '',
		'closes_at'             => '',
		'allowed_email_domains' => 'students.au.edu.pk',
		'winner_count'          => 5,
		'ticket_prefix'         => 'AU-AWS',
		'prize_title'           => '',
		'prize_description'     => '',
	);

	$meta = array();
	foreach ( $defaults as $key => $default ) {
		$val          = get_post_meta( $giveaway_id, $key, true );
		$meta[ $key ] = ( '' === $val || null === $val ) ? $default : $val;
	}
	return $meta;
}

function aws_sbg_get_giveaway_requirements( $giveaway_id ) {
	$reqs = get_post_meta( $giveaway_id, 'giveaway_requirements', true );
	return is_array( $reqs ) ? $reqs : array();
}

function aws_sbg_get_giveaway_fields( $giveaway_id ) {
	$fields = get_post_meta( $giveaway_id, 'giveaway_fields', true );
	if ( ! is_array( $fields ) || empty( $fields ) ) {
		$fields = aws_sbg_default_giveaway_fields();
	}
	return $fields;
}

/**
 * Published giveaways matching the given statuses (date window applied to "open").
 */
function aws_sbg_get_giveaways( $statuses = array( 'open' ) ) {
	$query = new WP_Query( array(
		'post_type'      => 'aws_giveaway',
		'post_status'    => 'publish',
		'posts_per_page' => 50,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => array(
			array(
				'key'     => 'giveaway_status',
				'value'   => (array) $statuses,
				'compare' => 'IN',
			),
		),
	) );

	$giveaways = array();
	foreach ( $query->posts as $post ) {
		$meta = aws_sbg_get_giveaway_meta( $post->ID );
		if ( 'open' === $meta['giveaway_status'] ) {
			$now = current_time( 'timestamp' );
			if ( ! empty( $meta['opens_at'] ) && strtotime( $meta['opens_at'] ) > $now ) {
				continue;
			}
			if ( ! empty( $meta['closes_at'] ) && strtotime( $meta['closes_at'] ) < $now ) {
				continue;
			}
		}
		$giveaways[] = $post;
	}
	return $giveaways;
}

function aws_sbg_get_giveaway_live_count( $giveaway_id ) {
	$giveaway_id = intval( $giveaway_id );
	$cache_key   = 'aws_sbg_giveaway_count_' . $giveaway_id;
	$count       = get_transient( $cache_key );

	if ( false === $count ) {
		global $wpdb;
		$table = $wpdb->prefix . 'aws_giveaway_entries';
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE giveaway_id = %d", $giveaway_id ) );
		set_transient( $cache_key, $count, 60 );
	}
	return (int) $count;
}

function aws_sbg_invalidate_giveaway_count( $giveaway_id ) {
	delete_transient( 'aws_sbg_giveaway_count_' . intval( $giveaway_id ) );
}

function aws_sbg_validate_giveaway_domain( $email, $allowed_domains_str ) {
	$allowed_domains_str = trim( (string) $allowed_domains_str );
	if ( empty( $allowed_domains_str ) || '*' === $allowed_domains_str ) {
		return true;
	}

	$domains = array_map( 'trim', explode( ',', strtolower( $allowed_domains_str ) ) );
	$parts   = explode( '@', strtolower( $email ) );
	if ( count( $parts ) !== 2 ) {
		return false;
	}

	$email_domain = $parts[1];
	foreach ( $domains as $domain ) {
		if ( empty( $domain ) ) {
			continue;
		}
		if ( $email_domain === $domain || str_ends_with( $email_domain, '.' . $domain ) ) {
			return true;
		}
	}
	return false;
}

function aws_sbg_giveaway_is_open( $giveaway_id ) {
	$post = get_post( $giveaway_id );
	if ( ! $post || 'aws_giveaway' !== $post->post_type || 'publish' !== $post->post_status ) {
		return false;
	}
	$meta = aws_sbg_get_giveaway_meta( $giveaway_id );
	return ( 'open' === $meta['giveaway_status'] );
}

/* ============================================================================
 * 4. ADMIN META BOXES
 * ============================================================================ */

function aws_sbg_giveaway_add_meta_boxes() {
	add_meta_box( 'aws_sbg_giveaway_settings', 'Settings', 'aws_sbg_giveaway_render_settings_box', 'aws_giveaway', 'normal', 'high' );
	add_meta_box( 'aws_sbg_giveaway_fields', 'Details fields (what entrants fill in)', 'aws_sbg_giveaway_render_fields_box', 'aws_giveaway', 'normal', 'high' );
	add_meta_box( 'aws_sbg_giveaway_reqs', 'Requirements (what entrants must do)', 'aws_sbg_giveaway_render_reqs_box', 'aws_giveaway', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'aws_sbg_giveaway_add_meta_boxes' );

function aws_sbg_giveaway_render_settings_box( $post ) {
	wp_nonce_field( 'aws_sbg_giveaway_meta_action', 'aws_sbg_giveaway_meta_nonce' );
	$m = aws_sbg_get_giveaway_meta( $post->ID );
	?>
	<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:14px;">
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Status</label>
			<select name="giveaway_status" style="width:100%;">
				<option value="open" <?php selected( $m['giveaway_status'], 'open' ); ?>>Open, accepting entries</option>
				<option value="closed" <?php selected( $m['giveaway_status'], 'closed' ); ?>>Closed</option>
				<option value="announced" <?php selected( $m['giveaway_status'], 'announced' ); ?>>Winners announced</option>
			</select>
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Number of Winners</label>
			<input type="number" name="winner_count" min="1" max="100" value="<?php echo esc_attr( $m['winner_count'] ); ?>" style="width:100%;">
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Ticket Prefix</label>
			<input type="text" name="ticket_prefix" value="<?php echo esc_attr( $m['ticket_prefix'] ); ?>" style="width:100%;">
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Who can enter?</label>
			<select name="email_restriction" style="width:100%;">
				<option value="air_university" <?php selected( '*' !== $m['allowed_email_domains'] ); ?>>Air University students only (@students.au.edu.pk)</option>
				<option value="general" <?php selected( '*' === $m['allowed_email_domains'] ); ?>>General, accept any email address</option>
			</select>
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Opens At (optional)</label>
			<input type="datetime-local" name="opens_at" value="<?php echo esc_attr( $m['opens_at'] ); ?>" style="width:100%;">
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Closes At (optional)</label>
			<input type="datetime-local" name="closes_at" value="<?php echo esc_attr( $m['closes_at'] ); ?>" style="width:100%;">
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Prize Headline</label>
			<input type="text" name="prize_title" value="<?php echo esc_attr( $m['prize_title'] ); ?>" style="width:100%;" placeholder="e.g. 5 Official AWS Swag Bundles">
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Prize Details</label>
			<input type="text" name="prize_description" value="<?php echo esc_attr( $m['prize_description'] ); ?>" style="width:100%;">
		</div>
	</div>
	<div style="margin-top:14px;">
		<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Description (shown to entrants)</label>
		<textarea name="giveaway_description" rows="3" style="width:100%;"><?php echo esc_textarea( $m['giveaway_description'] ); ?></textarea>
	</div>
	<?php
}

function aws_sbg_giveaway_render_reqs_box( $post ) {
	$reqs = aws_sbg_get_giveaway_requirements( $post->ID );
	?>
	<p style="margin-top:0;color:#64748b;font-size:13px;">Entrants must complete each requirement before submitting.</p>
	<input type="hidden" name="requirements_json" id="aws-reqs-json" value="<?php echo esc_attr( wp_json_encode( $reqs ) ); ?>">
	<div id="aws-reqs-list" style="display:flex;flex-direction:column;gap:10px;"></div>
	<button type="button" id="aws-add-req" class="button" style="margin-top:12px;">+ Add Requirement</button>

	<script>
	(function(){
		var types = { check: 'Tick (self-confirm)', proof: 'Tick + link/text proof', upload: 'Image upload (max 2 MB)' };
		var list = document.getElementById('aws-reqs-list');
		var hidden = document.getElementById('aws-reqs-json');
		var data = [];
		try { data = JSON.parse(hidden.value || '[]'); } catch(e) { data = []; }

		function esc(v){ return String(v == null ? '' : v).replace(/"/g,'&quot;'); }

		function rowHtml(r) {
			var opts = '';
			Object.keys(types).forEach(function(k){
				opts += '<option value="'+k+'"'+(r.type===k?' selected':'')+'>'+types[k]+'</option>';
			});
			return '<div class="aws-req-row" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;display:grid;grid-template-columns:1fr 210px 110px 40px;gap:10px;align-items:center;">'
				+ '<input type="text" class="req-label" placeholder="Requirement label (e.g. Follow on Instagram)" value="'+esc(r.label)+'" style="padding:6px 10px;">'
				+ '<select class="req-type" style="padding:6px 10px;">'+opts+'</select>'
				+ '<label style="font-size:12px;display:flex;align-items:center;gap:6px;"><input type="checkbox" class="req-required"'+(r.required?' checked':'')+'> Required</label>'
				+ '<button type="button" class="button-link aws-del-req" style="color:#ef4444;text-decoration:none;">✕</button>'
				+ '<input type="text" class="req-help" placeholder="Optional helper text" value="'+esc(r.help)+'" style="grid-column:1/-1;padding:6px 10px;">'
				+ '</div>';
		}
		function render() {
			list.innerHTML = '';
			data.forEach(function(r){ list.insertAdjacentHTML('beforeend', rowHtml(r)); });
			bind();
		}
		function bind() {
			list.querySelectorAll('.aws-del-req').forEach(function(b){
				b.onclick = function(){ this.closest('.aws-req-row').remove(); };
			});
		}
		function serialize() {
			var out = [];
			list.querySelectorAll('.aws-req-row').forEach(function(row){
				var label = row.querySelector('.req-label').value.trim();
				if (!label) return;
				out.push({
					id: 'req_' + Math.random().toString(36).slice(2,9),
					label: label,
					type: row.querySelector('.req-type').value,
					required: row.querySelector('.req-required').checked ? 1 : 0,
					help: row.querySelector('.req-help').value.trim()
				});
			});
			hidden.value = JSON.stringify(out);
		}
		document.getElementById('aws-add-req').onclick = function(){
			list.insertAdjacentHTML('beforeend', rowHtml({type:'check',required:1}));
			bind();
		};
		var form = document.getElementById('post');
		if (form) form.addEventListener('submit', serialize);
		render();
	})();
	</script>
	<?php
}

function aws_sbg_giveaway_render_fields_box( $post ) {
	$fields = get_post_meta( $post->ID, 'giveaway_fields', true );
	if ( ! is_array( $fields ) || empty( $fields ) ) {
		$fields = aws_sbg_default_giveaway_fields();
	}
	?>
	<p style="margin-top:0;color:#64748b;font-size:13px;">The personal details entrants fill in. Name / Email / Phone are pre-added.</p>
	<input type="hidden" name="fields_json" id="aws-fields-json" value="<?php echo esc_attr( wp_json_encode( $fields ) ); ?>">
	<div id="aws-fields-list" style="display:flex;flex-direction:column;gap:10px;"></div>
	<button type="button" id="aws-add-field" class="button" style="margin-top:12px;">+ Add Field</button>

	<script>
	(function(){
		var types = { text:'Text', email:'Email', tel:'Phone', textarea:'Long text', select:'Dropdown' };
		var list = document.getElementById('aws-fields-list');
		var hidden = document.getElementById('aws-fields-json');
		var data = [];
		try { data = JSON.parse(hidden.value || '[]'); } catch(e) { data = []; }

		function esc(v){ return String(v == null ? '' : v).replace(/"/g,'&quot;'); }

		function rowHtml(f) {
			var opts = '';
			Object.keys(types).forEach(function(k){
				opts += '<option value="'+k+'"'+(f.type===k?' selected':'')+'>'+types[k]+'</option>';
			});
			return '<div class="aws-field-row" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;display:grid;grid-template-columns:1fr 160px 110px 40px;gap:10px;align-items:center;">'
				+ '<input type="text" class="fld-label" placeholder="Field label" value="'+esc(f.label)+'" style="padding:6px 10px;">'
				+ '<select class="fld-type" style="padding:6px 10px;">'+opts+'</select>'
				+ '<label style="font-size:12px;display:flex;align-items:center;gap:6px;"><input type="checkbox" class="fld-required"'+(f.required?' checked':'')+'> Required</label>'
				+ '<button type="button" class="button-link aws-del-field" style="color:#ef4444;text-decoration:none;">✕</button>'
				+ '<input type="text" class="fld-options" placeholder="Dropdown options, comma separated" value="'+esc(f.options)+'" style="grid-column:1/-1;padding:6px 10px;">'
				+ '</div>';
		}
		function render() {
			list.innerHTML = '';
			data.forEach(function(f){ list.insertAdjacentHTML('beforeend', rowHtml(f)); });
			bind();
		}
		function bind() {
			list.querySelectorAll('.aws-del-field').forEach(function(b){
				b.onclick = function(){ this.closest('.aws-field-row').remove(); };
			});
		}
		function serialize() {
			var out = [];
			list.querySelectorAll('.aws-field-row').forEach(function(row){
				var label = row.querySelector('.fld-label').value.trim();
				if (!label) return;
				out.push({
					id: 'field_' + Math.random().toString(36).slice(2,9),
					label: label,
					type: row.querySelector('.fld-type').value,
					required: row.querySelector('.fld-required').checked ? 1 : 0,
					options: row.querySelector('.fld-options').value.trim()
				});
			});
			hidden.value = JSON.stringify(out);
		}
		document.getElementById('aws-add-field').onclick = function(){
			list.insertAdjacentHTML('beforeend', rowHtml({type:'text'}));
			bind();
		};
		var form = document.getElementById('post');
		if (form) form.addEventListener('submit', serialize);
		render();
	})();
	</script>
	<?php
}

function aws_sbg_giveaway_save_meta( $post_id ) {
	if ( ! isset( $_POST['aws_sbg_giveaway_meta_nonce'] ) || ! wp_verify_nonce( $_POST['aws_sbg_giveaway_meta_nonce'], 'aws_sbg_giveaway_meta_action' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text_keys = array( 'giveaway_status', 'opens_at', 'closes_at', 'ticket_prefix', 'prize_title', 'prize_description' );
	foreach ( $text_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	// Email eligibility: map the simple choice to the stored domain rule.
	if ( isset( $_POST['email_restriction'] ) ) {
		$restriction = sanitize_text_field( wp_unslash( $_POST['email_restriction'] ) );
		update_post_meta( $post_id, 'allowed_email_domains', ( 'general' === $restriction ) ? '*' : 'students.au.edu.pk' );
	}

	if ( isset( $_POST['winner_count'] ) ) {
		update_post_meta( $post_id, 'winner_count', max( 1, intval( $_POST['winner_count'] ) ) );
	}
	if ( isset( $_POST['giveaway_description'] ) ) {
		update_post_meta( $post_id, 'giveaway_description', sanitize_textarea_field( wp_unslash( $_POST['giveaway_description'] ) ) );
	}

	if ( isset( $_POST['requirements_json'] ) ) {
		$decoded = json_decode( wp_unslash( $_POST['requirements_json'] ), true );
		$clean   = array();
		if ( is_array( $decoded ) ) {
			foreach ( $decoded as $r ) {
				if ( empty( $r['label'] ) ) {
					continue;
				}
				$type    = in_array( $r['type'] ?? '', array( 'check', 'proof', 'upload' ), true ) ? $r['type'] : 'check';
				$clean[] = array(
					'id'       => sanitize_key( $r['id'] ?? uniqid( 'req_' ) ),
					'label'    => sanitize_text_field( $r['label'] ),
					'type'     => $type,
					'required' => ! empty( $r['required'] ) ? 1 : 0,
					'help'     => sanitize_text_field( $r['help'] ?? '' ),
				);
			}
		}
		update_post_meta( $post_id, 'giveaway_requirements', $clean );
	}

	if ( isset( $_POST['fields_json'] ) ) {
		$decoded = json_decode( wp_unslash( $_POST['fields_json'] ), true );
		$clean   = array();
		if ( is_array( $decoded ) ) {
			foreach ( $decoded as $f ) {
				if ( empty( $f['label'] ) ) {
					continue;
				}
				$type    = in_array( $f['type'] ?? '', array( 'text', 'email', 'tel', 'textarea', 'select' ), true ) ? $f['type'] : 'text';
				$clean[] = array(
					'id'       => sanitize_key( $f['id'] ?? uniqid( 'field_' ) ),
					'label'    => sanitize_text_field( $f['label'] ),
					'type'     => $type,
					'required' => ! empty( $f['required'] ) ? 1 : 0,
					'options'  => sanitize_text_field( $f['options'] ?? '' ),
				);
			}
		}
		if ( ! empty( $clean ) ) {
			update_post_meta( $post_id, 'giveaway_fields', $clean );
		}
	}
}
add_action( 'save_post_aws_giveaway', 'aws_sbg_giveaway_save_meta' );

/* ============================================================================
 * 5. PUBLIC SUBMISSION HANDLER (native multipart POST so uploads work)
 * ============================================================================ */

function aws_sbg_giveaway_submission_error( $giveaway_id, $code ) {
	$url = wp_get_referer() ? wp_get_referer() : home_url( '/giveaway/' );
	$url = add_query_arg( array( 'gw_status' => 'error', 'gw_error' => $code, 'gw_id' => intval( $giveaway_id ) ), $url );
	wp_safe_redirect( $url );
	exit;
}

function aws_sbg_handle_giveaway_submission() {
	if ( ! isset( $_POST['giveaway_nonce'] ) || ! wp_verify_nonce( $_POST['giveaway_nonce'], 'aws_sbg_giveaway_submit' ) ) {
		wp_die( 'Security check failed.', 'Error', array( 'response' => 403 ) );
	}

	$giveaway_id = isset( $_POST['giveaway_id'] ) ? intval( $_POST['giveaway_id'] ) : 0;
	if ( ! aws_sbg_giveaway_is_open( $giveaway_id ) ) {
		aws_sbg_giveaway_submission_error( $giveaway_id, 'closed' );
	}

	if ( ! empty( $_POST['website_url'] ) ) { // honeypot
		aws_sbg_giveaway_submission_error( $giveaway_id, 'spam' );
	}

	global $wpdb;
	$table_entries = $wpdb->prefix . 'aws_giveaway_entries';
	$table_rv      = $wpdb->prefix . 'aws_giveaway_requirement_values';

	$meta   = aws_sbg_get_giveaway_meta( $giveaway_id );
	$fields = aws_sbg_get_giveaway_fields( $giveaway_id );
	$reqs   = aws_sbg_get_giveaway_requirements( $giveaway_id );

	// 1. Detail fields
	$values = array();
	$raw    = isset( $_POST['gw_field'] ) ? (array) wp_unslash( $_POST['gw_field'] ) : array();
	foreach ( $fields as $field ) {
		$fid = $field['id'];
		$val = isset( $raw[ $fid ] ) ? $raw[ $fid ] : '';
		switch ( $field['type'] ) {
			case 'email':
				$val = sanitize_email( $val );
				break;
			case 'textarea':
				$val = sanitize_textarea_field( $val );
				break;
			default:
				$val = sanitize_text_field( $val );
		}
		if ( ! empty( $field['required'] ) && '' === trim( (string) $val ) ) {
			aws_sbg_giveaway_submission_error( $giveaway_id, 'required' );
		}
		$values[ $fid ] = $val;
	}

	$full_name = isset( $values['full_name'] ) ? $values['full_name'] : '';
	$email     = isset( $values['email'] ) ? $values['email'] : '';
	$phone     = isset( $values['phone'] ) ? $values['phone'] : '';

	if ( empty( $email ) ) {
		foreach ( $fields as $field ) {
			if ( 'email' === $field['type'] && ! empty( $values[ $field['id'] ] ) ) {
				$email = $values[ $field['id'] ];
				break;
			}
		}
	}
	if ( empty( $full_name ) ) {
		$full_name = $email ? strstr( $email, '@', true ) : 'Entrant';
	}

	if ( ! is_email( $email ) ) {
		aws_sbg_giveaway_submission_error( $giveaway_id, 'email' );
	}
	if ( ! aws_sbg_validate_giveaway_domain( $email, $meta['allowed_email_domains'] ) ) {
		aws_sbg_giveaway_submission_error( $giveaway_id, 'domain' );
	}

	$clean_phone = preg_replace( '/[^0-9+]/', '', $phone );

	// 2. Duplicate check per giveaway
	$existing = $wpdb->get_row( $wpdb->prepare(
		"SELECT id, ticket_number FROM $table_entries WHERE giveaway_id = %d AND (email = %s OR (phone <> '' AND phone = %s)) LIMIT 1",
		$giveaway_id,
		$email,
		$clean_phone
	) );
	if ( $existing ) {
		$url = wp_get_referer() ? wp_get_referer() : home_url( '/giveaway/' );
		$url = add_query_arg( array( 'gw_status' => 'exists', 'gw_ticket' => $existing->ticket_number, 'gw_id' => $giveaway_id ), $url );
		wp_safe_redirect( $url );
		exit;
	}

	// 3. Ticket number
	$prefix = ! empty( $meta['ticket_prefix'] ) ? trim( $meta['ticket_prefix'] ) : 'AU-AWS';
	$max_id = (int) $wpdb->get_var( "SELECT MAX(id) FROM $table_entries" );
	$ticket = $prefix . '-' . ( 1000 + $max_id + 1 );
	$ip     = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$source = isset( $_POST['gw_source'] ) ? sanitize_text_field( wp_unslash( $_POST['gw_source'] ) ) : 'web';

	$inserted = $wpdb->insert(
		$table_entries,
		array(
			'giveaway_id'   => $giveaway_id,
			'campaign_id'   => 'gw-' . $giveaway_id,
			'ticket_number' => $ticket,
			'full_name'     => $full_name,
			'email'         => $email,
			'phone'         => $clean_phone,
			'custom_data'   => wp_json_encode( $values ),
			'source'        => $source,
			'status'        => 'pending',
			'is_winner'     => 0,
			'ip_address'    => $ip,
			'created_at'    => current_time( 'mysql' ),
		),
		array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
	);

	if ( false === $inserted ) {
		aws_sbg_giveaway_submission_error( $giveaway_id, 'db' );
	}
	$entry_id = (int) $wpdb->insert_id;

	// 4. Requirements
	$allowed_mimes = array( 'image/jpeg', 'image/png', 'image/webp' );
	foreach ( $reqs as $req ) {
		$rid        = $req['id'];
		$type       = $req['type'];
		$required   = ! empty( $req['required'] );
		$checked    = isset( $_POST['gw_check'][ $rid ] ) ? 1 : 0;
		$value_text = isset( $_POST['gw_text'][ $rid ] ) ? sanitize_text_field( wp_unslash( $_POST['gw_text'][ $rid ] ) ) : '';
		$attach     = 0;

		if ( 'upload' === $type ) {
			$file_key = 'gw_file_' . $rid;
			if ( ! empty( $_FILES[ $file_key ]['name'] ) ) {
				$file = $_FILES[ $file_key ];

				if ( $file['size'] > AWS_SBG_GIVEAWAY_MAX_UPLOAD ) {
					aws_sbg_giveaway_submission_error( $giveaway_id, 'filesize' );
				}
				$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
				if ( ! in_array( $check['type'], $allowed_mimes, true ) ) {
					aws_sbg_giveaway_submission_error( $giveaway_id, 'filetype' );
				}

				require_once ABSPATH . 'wp-admin/includes/image.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/media.php';

				// Keep server load low: entry uploads arrive pre-optimised WebP, so skip
				// generating intermediate thumbnail sizes (none are used for these entries).
				$skip_sizes = function () { return array(); };
				add_filter( 'intermediate_image_sizes_advanced', $skip_sizes );

				$attach_id = media_handle_upload( $file_key, 0 );

				remove_filter( 'intermediate_image_sizes_advanced', $skip_sizes );
				if ( is_wp_error( $attach_id ) ) {
					aws_sbg_giveaway_submission_error( $giveaway_id, 'upload' );
				}
				$attach  = (int) $attach_id;
				$checked = 1;
			}
			if ( $required && ! $attach ) {
				aws_sbg_giveaway_submission_error( $giveaway_id, 'reqfile' );
			}
		} elseif ( 'proof' === $type ) {
			if ( $required && ( ! $checked || '' === $value_text ) ) {
				aws_sbg_giveaway_submission_error( $giveaway_id, 'reqproof' );
			}
		} else {
			if ( $required && ! $checked ) {
				aws_sbg_giveaway_submission_error( $giveaway_id, 'reqcheck' );
			}
		}

		$wpdb->insert(
			$table_rv,
			array(
				'entry_id'       => $entry_id,
				'giveaway_id'    => $giveaway_id,
				'requirement_id' => $rid,
				'req_type'       => $type,
				'check_state'    => $checked,
				'value_text'     => $value_text,
				'attachment_id'  => $attach,
				'created_at'     => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%d', '%s', '%d', '%s' )
		);
	}

	aws_sbg_invalidate_giveaway_count( $giveaway_id );

	$url = wp_get_referer() ? wp_get_referer() : home_url( '/giveaway/' );
	$url = add_query_arg( array( 'gw_status' => 'success', 'gw_ticket' => $ticket, 'gw_name' => $full_name, 'gw_id' => $giveaway_id ), $url );
	wp_safe_redirect( $url );
	exit;
}
add_action( 'admin_post_nopriv_aws_sbg_giveaway_submit', 'aws_sbg_handle_giveaway_submission' );
add_action( 'admin_post_aws_sbg_giveaway_submit', 'aws_sbg_handle_giveaway_submission' );

/* ============================================================================
 * 6. REST: live count
 * ============================================================================ */

add_action( 'rest_api_init', function () {
	register_rest_route( 'aws-sbg/v1', '/giveaway/stats', array(
		'methods'             => 'GET',
		'callback'            => function ( WP_REST_Request $request ) {
			$gid = intval( $request->get_param( 'giveaway_id' ) );
			return rest_ensure_response( array(
				'success' => true,
				'count'   => aws_sbg_get_giveaway_live_count( $gid ),
			) );
		},
		'permission_callback' => '__return_true',
	) );
} );

/* ============================================================================
 * 7. ADMIN: submissions screen, winner tools, CSV
 * ============================================================================ */

function aws_sbg_register_giveaway_admin_menu() {
	add_submenu_page(
		'aws-sbg-suite',
		'🎁 Giveaway Submissions',
		'🎁 Giveaway Submissions',
		'manage_options',
		'aws-giveaway-submissions',
		'aws_sbg_render_giveaway_submissions_page'
	);
}
add_action( 'admin_menu', 'aws_sbg_register_giveaway_admin_menu', 22 );

function aws_sbg_giveaway_row_actions( $actions, $post ) {
	if ( 'aws_giveaway' === $post->post_type ) {
		$actions['submissions'] = '<a href="' . esc_url( admin_url( 'admin.php?page=aws-giveaway-submissions&giveaway_id=' . $post->ID ) ) . '">View Submissions</a>';
	}
	return $actions;
}
add_filter( 'post_row_actions', 'aws_sbg_giveaway_row_actions', 10, 2 );

function aws_sbg_ajax_pick_giveaway_winners() {
	check_ajax_referer( 'aws_giveaway_admin_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
	}

	global $wpdb;
	$table       = $wpdb->prefix . 'aws_giveaway_entries';
	$giveaway_id = isset( $_POST['giveaway_id'] ) ? intval( $_POST['giveaway_id'] ) : 0;
	$count       = isset( $_POST['count'] ) ? intval( $_POST['count'] ) : 5;
	if ( $count < 1 ) {
		$count = 5;
	}
	if ( ! $giveaway_id ) {
		wp_send_json_error( array( 'message' => 'No giveaway selected.' ) );
	}

	$winners = $wpdb->get_results( $wpdb->prepare(
		"SELECT id, ticket_number, full_name, email, phone, source, created_at FROM $table
		 WHERE giveaway_id = %d AND is_winner = 0 ORDER BY RAND() LIMIT %d",
		$giveaway_id,
		$count
	), ARRAY_A );

	if ( empty( $winners ) ) {
		wp_send_json_error( array( 'message' => 'No eligible non-winner entries found in this giveaway.' ) );
	}
	wp_send_json_success( array( 'winners' => $winners ) );
}
add_action( 'wp_ajax_aws_sbg_pick_giveaway_winners', 'aws_sbg_ajax_pick_giveaway_winners' );

function aws_sbg_ajax_reroll_giveaway_winner() {
	check_ajax_referer( 'aws_giveaway_admin_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
	}

	global $wpdb;
	$table       = $wpdb->prefix . 'aws_giveaway_entries';
	$giveaway_id = isset( $_POST['giveaway_id'] ) ? intval( $_POST['giveaway_id'] ) : 0;
	$exclude_ids = isset( $_POST['exclude_ids'] ) ? array_map( 'intval', (array) $_POST['exclude_ids'] ) : array();

	$exclude_sql = '';
	if ( ! empty( $exclude_ids ) ) {
		$exclude_sql = ' AND id NOT IN (' . implode( ',', $exclude_ids ) . ') ';
	}

	$candidate = $wpdb->get_row( $wpdb->prepare(
		"SELECT id, ticket_number, full_name, email, phone, source, created_at FROM $table
		 WHERE giveaway_id = %d AND is_winner = 0 $exclude_sql ORDER BY RAND() LIMIT 1",
		$giveaway_id
	), ARRAY_A );

	if ( ! $candidate ) {
		wp_send_json_error( array( 'message' => 'No additional eligible candidates available to re-roll.' ) );
	}
	wp_send_json_success( array( 'winner' => $candidate ) );
}
add_action( 'wp_ajax_aws_sbg_reroll_giveaway_winner', 'aws_sbg_ajax_reroll_giveaway_winner' );

function aws_sbg_ajax_publish_giveaway_winners() {
	check_ajax_referer( 'aws_giveaway_admin_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
	}

	global $wpdb;
	$table       = $wpdb->prefix . 'aws_giveaway_entries';
	$giveaway_id = isset( $_POST['giveaway_id'] ) ? intval( $_POST['giveaway_id'] ) : 0;
	$winner_ids  = isset( $_POST['winner_ids'] ) ? array_map( 'intval', (array) $_POST['winner_ids'] ) : array();

	if ( ! $giveaway_id || empty( $winner_ids ) ) {
		wp_send_json_error( array( 'message' => 'No winners selected to publish.' ) );
	}

	$wpdb->update( $table, array( 'is_winner' => 0, 'winner_drawn_at' => null ), array( 'giveaway_id' => $giveaway_id ) );

	$now = current_time( 'mysql' );
	foreach ( $winner_ids as $id ) {
		$wpdb->update( $table, array( 'is_winner' => 1, 'winner_drawn_at' => $now ), array( 'id' => $id ) );
	}

	update_post_meta( $giveaway_id, 'giveaway_status', 'announced' );
	aws_sbg_invalidate_giveaway_count( $giveaway_id );

	wp_send_json_success( array(
		'message' => 'Winners confirmed and published successfully!',
		'count'   => count( $winner_ids ),
	) );
}
add_action( 'wp_ajax_aws_sbg_publish_giveaway_winners', 'aws_sbg_ajax_publish_giveaway_winners' );

function aws_sbg_ajax_purge_giveaway_entries() {
	check_ajax_referer( 'aws_giveaway_admin_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
	}

	global $wpdb;
	$giveaway_id = isset( $_POST['giveaway_id'] ) ? intval( $_POST['giveaway_id'] ) : 0;
	if ( ! $giveaway_id ) {
		wp_send_json_error( array( 'message' => 'No giveaway selected.' ) );
	}

	$table_rv = $wpdb->prefix . 'aws_giveaway_requirement_values';
	$files    = $wpdb->get_col( $wpdb->prepare( "SELECT attachment_id FROM $table_rv WHERE giveaway_id = %d AND attachment_id > 0", $giveaway_id ) );
	foreach ( $files as $att ) {
		wp_delete_attachment( intval( $att ), true );
	}
	$wpdb->delete( $table_rv, array( 'giveaway_id' => $giveaway_id ), array( '%d' ) );
	$wpdb->delete( $wpdb->prefix . 'aws_giveaway_entries', array( 'giveaway_id' => $giveaway_id ), array( '%d' ) );

	aws_sbg_invalidate_giveaway_count( $giveaway_id );
	wp_send_json_success( array( 'message' => 'All entries for this giveaway have been purged.' ) );
}
add_action( 'wp_ajax_aws_sbg_purge_giveaway_entries', 'aws_sbg_ajax_purge_giveaway_entries' );

function aws_sbg_ajax_export_giveaway_csv() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	global $wpdb;
	$giveaway_id = isset( $_GET['giveaway_id'] ) ? intval( $_GET['giveaway_id'] ) : 0;
	$table       = $wpdb->prefix . 'aws_giveaway_entries';
	$table_rv    = $wpdb->prefix . 'aws_giveaway_requirement_values';

	$entries = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE giveaway_id = %d ORDER BY id ASC", $giveaway_id ), ARRAY_A );

	$responses = array();
	$rows      = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_rv WHERE giveaway_id = %d", $giveaway_id ), ARRAY_A );
	foreach ( $rows as $r ) {
		$responses[ $r['entry_id'] ][ $r['requirement_id'] ] = $r;
	}

	$reqs = aws_sbg_get_giveaway_requirements( $giveaway_id );

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=giveaway-' . $giveaway_id . '-' . gmdate( 'Y-m-d' ) . '.csv' );

	$output = fopen( 'php://output', 'w' );
	$header = array( 'Ticket', 'Name', 'Email', 'Phone', 'Custom Data', 'Source', 'Winner', 'Created At' );
	foreach ( $reqs as $req ) {
		$header[] = 'Req: ' . $req['label'];
	}
	fputcsv( $output, $header );

	foreach ( $entries as $e ) {
		$line = array( $e['ticket_number'], $e['full_name'], $e['email'], $e['phone'], $e['custom_data'], $e['source'], $e['is_winner'] ? 'WINNER' : 'Participant', $e['created_at'] );
		foreach ( $reqs as $req ) {
			$rv = $responses[ $e['id'] ][ $req['id'] ] ?? null;
			if ( ! $rv ) {
				$line[] = '';
			} elseif ( 'upload' === $req['type'] ) {
				$line[] = $rv['attachment_id'] ? wp_get_attachment_url( $rv['attachment_id'] ) : 'No file';
			} elseif ( 'proof' === $req['type'] ) {
				$line[] = $rv['value_text'] . ( $rv['check_state'] ? ' (confirmed)' : '' );
			} else {
				$line[] = $rv['check_state'] ? 'Yes' : 'No';
			}
		}
		fputcsv( $output, $line );
	}
	fclose( $output );
	exit;
}
add_action( 'wp_ajax_aws_sbg_export_giveaway_csv', 'aws_sbg_ajax_export_giveaway_csv' );

function aws_sbg_render_giveaway_submissions_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	global $wpdb;
	$table    = $wpdb->prefix . 'aws_giveaway_entries';
	$table_rv = $wpdb->prefix . 'aws_giveaway_requirement_values';
	$nonce    = wp_create_nonce( 'aws_giveaway_admin_nonce' );

	$giveaways = get_posts( array(
		'post_type'      => 'aws_giveaway',
		'posts_per_page' => 100,
		'post_status'    => 'any',
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	$giveaway_id = isset( $_GET['giveaway_id'] ) ? intval( $_GET['giveaway_id'] ) : 0;
	if ( ! $giveaway_id && ! empty( $giveaways ) ) {
		$giveaway_id = $giveaways[0]->ID;
	}

	$reqs = $giveaway_id ? aws_sbg_get_giveaway_requirements( $giveaway_id ) : array();

	$per_page     = 50;
	$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
	$offset       = ( $current_page - 1 ) * $per_page;
	$search       = isset( $_GET['s'] ) ? sanitize_text_field( trim( $_GET['s'] ) ) : '';

	$where = $wpdb->prepare( ' WHERE giveaway_id = %d', $giveaway_id );
	if ( '' !== $search ) {
		$like   = '%' . $wpdb->esc_like( $search ) . '%';
		$where .= $wpdb->prepare( ' AND (ticket_number LIKE %s OR full_name LIKE %s OR email LIKE %s OR phone LIKE %s)', $like, $like, $like, $like );
	}

	$entries        = $wpdb->get_results( "SELECT * FROM $table $where ORDER BY id DESC LIMIT $per_page OFFSET $offset", ARRAY_A );
	$total_filtered = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table $where" );
	$total_pages    = (int) ceil( $total_filtered / $per_page );

	$responses = array();
	if ( ! empty( $entries ) ) {
		$ids = implode( ',', array_map( 'intval', wp_list_pluck( $entries, 'id' ) ) );
		$rv  = $wpdb->get_results( "SELECT * FROM $table_rv WHERE entry_id IN ($ids)", ARRAY_A );
		foreach ( $rv as $r ) {
			$responses[ $r['entry_id'] ][ $r['requirement_id'] ] = $r;
		}
	}

	$total_entries = $giveaway_id ? aws_sbg_get_giveaway_live_count( $giveaway_id ) : 0;
	$winners       = $giveaway_id ? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE giveaway_id = %d AND is_winner = 1", $giveaway_id ) ) : 0;
	$meta          = $giveaway_id ? aws_sbg_get_giveaway_meta( $giveaway_id ) : array( 'winner_count' => 5, 'giveaway_status' => 'open' );
	?>
	<div class="wrap" style="max-width:1200px; margin:20px auto 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
		<?php if ( function_exists( 'aws_sbg_render_admin_nav_tabs' ) ) { aws_sbg_render_admin_nav_tabs( 'giveaways' ); } ?>

		<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px 28px;margin-bottom:24px;">
			<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
				<div>
					<h1 style="margin:0;font-size:22px;font-weight:800;color:#0f172a;">Giveaway Submissions</h1>
					<p style="margin:4px 0 0;color:#64748b;font-size:13px;">Review entries per giveaway, inspect uploads, and publish winners.</p>
				</div>
				<div style="display:flex;gap:10px;">
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=aws_giveaway' ) ); ?>" class="button button-primary">+ New Giveaway</a>
					<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=aws_sbg_export_giveaway_csv&giveaway_id=' . $giveaway_id ) ); ?>" class="button">Export CSV</a>
				</div>
			</div>

			<form method="get" style="margin-top:18px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
				<input type="hidden" name="page" value="aws-giveaway-submissions">
				<select name="giveaway_id" style="min-width:280px;padding:6px 10px;">
					<?php if ( empty( $giveaways ) ) : ?>
						<option value="0">No giveaways yet</option>
					<?php else : ?>
						<?php foreach ( $giveaways as $g ) : ?>
							<option value="<?php echo esc_attr( $g->ID ); ?>" <?php selected( $giveaway_id, $g->ID ); ?>>
								<?php echo esc_html( $g->post_title ); ?> (#<?php echo esc_html( $g->ID ); ?>)
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search name, email, ticket..." style="padding:6px 10px;width:240px;">
				<button type="submit" class="button">Search</button>
			</form>

			<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-top:18px;">
				<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 18px;">
					<div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;">Total Entries</div>
					<div style="font-size:26px;font-weight:900;color:#0f172a;"><?php echo esc_html( number_format( $total_entries ) ); ?></div>
				</div>
				<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 18px;">
					<div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;">Published Winners</div>
					<div style="font-size:26px;font-weight:900;color:#9353ff;"><?php echo esc_html( $winners ); ?></div>
				</div>
				<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 18px;">
					<div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;">Status</div>
					<div style="margin-top:4px;font-weight:800;font-size:13px;color:#0f172a;"><?php echo esc_html( ucfirst( $meta['giveaway_status'] ) ); ?></div>
				</div>
			</div>
		</div>

		<?php if ( ! $giveaway_id ) : ?>
			<div style="background:#fff;border:2px dashed #e2e8f0;border-radius:16px;padding:60px 20px;text-align:center;">
				<h3 style="margin:0 0 6px;color:#0f172a;">No giveaways yet</h3>
				<p style="color:#64748b;">Create your first giveaway to start collecting entries.</p>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=aws_giveaway' ) ); ?>" class="button button-primary">+ Create Giveaway</a>
			</div>
		<?php else : ?>

			<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;margin-bottom:24px;">
				<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;border-bottom:1px solid #e2e8f0;padding-bottom:16px;">
					<div>
						<h3 style="margin:0;font-size:17px;font-weight:800;color:#0f172a;">Winner selection</h3>
						<p style="margin:4px 0 0;color:#64748b;font-size:13px;">Pick <?php echo esc_html( $meta['winner_count'] ); ?> random winners, re-roll slots, then publish.</p>
					</div>
					<button type="button" id="aws-draw-btn" class="button button-primary">Pick <?php echo esc_html( $meta['winner_count'] ); ?> winners</button>
				</div>
				<div id="aws-draw-results" style="margin-top:18px;"></div>
				<div id="aws-publish-section" style="display:none;margin-top:18px;padding-top:16px;border-top:1px solid #e2e8f0;text-align:right;">
					<button type="button" id="aws-publish-btn" class="button button-primary" style="background:#10b981;border-color:#10b981;">Publish winners</button>
				</div>
			</div>

			<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
				<table style="width:100%;border-collapse:collapse;text-align:left;font-size:13px;">
					<thead>
						<tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;color:#475569;font-size:11px;text-transform:uppercase;letter-spacing:0.06em;">
							<th style="padding:12px 18px;">Ticket</th>
							<th style="padding:12px 18px;">Name</th>
							<th style="padding:12px 18px;">Email</th>
							<th style="padding:12px 18px;">Phone</th>
							<th style="padding:12px 18px;">Requirements</th>
							<th style="padding:12px 18px;">Status</th>
							<th style="padding:12px 18px;">Date</th>
						</tr>
					</thead>
					<tbody>
					<?php if ( empty( $entries ) ) : ?>
						<tr><td colspan="7" style="padding:50px 20px;text-align:center;color:#64748b;">No entries yet for this giveaway.</td></tr>
					<?php else : ?>
						<?php foreach ( $entries as $e ) : ?>
							<tr style="border-bottom:1px solid #f1f5f9;vertical-align:top;">
								<td style="padding:12px 18px;font-family:monospace;font-weight:800;color:#9353ff;">#<?php echo esc_html( $e['ticket_number'] ); ?></td>
								<td style="padding:12px 18px;font-weight:700;color:#0f172a;"><?php echo esc_html( $e['full_name'] ); ?></td>
								<td style="padding:12px 18px;color:#475569;"><?php echo esc_html( $e['email'] ); ?></td>
								<td style="padding:12px 18px;">
									<?php if ( $e['phone'] ) : ?>
										<a href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $e['phone'] ) ); ?>" target="_blank" style="color:#059669;font-weight:700;text-decoration:none;"><?php echo esc_html( $e['phone'] ); ?></a>
									<?php endif; ?>
								</td>
								<td style="padding:12px 18px;color:#334155;font-size:12px;">
									<?php
									if ( empty( $reqs ) ) {
										echo '<span style="color:#94a3b8;">None</span>';
									}
									foreach ( $reqs as $req ) {
										$rv = $responses[ $e['id'] ][ $req['id'] ] ?? null;
										echo '<div style="margin-bottom:4px;">';
										if ( ! $rv ) {
											echo '<span style="color:#cbd5e1;">◻</span> ' . esc_html( $req['label'] );
										} elseif ( 'upload' === $req['type'] ) {
											if ( $rv['attachment_id'] ) {
												$url = wp_get_attachment_url( $rv['attachment_id'] );
												if ( $url ) {
													echo '<a href="' . esc_url( $url ) . '" target="_blank"><img src="' . esc_url( $url ) . '" loading="lazy" style="height:34px;width:34px;object-fit:cover;border-radius:6px;vertical-align:middle;"></a> ';
												}
											}
											echo esc_html( $req['label'] );
										} elseif ( 'proof' === $req['type'] ) {
											echo '<span style="color:#059669;">✔</span> ' . esc_html( $req['label'] ) . ': ' . esc_html( $rv['value_text'] );
										} else {
											echo ( $rv['check_state'] ? '<span style="color:#059669;">✔</span> ' : '<span style="color:#cbd5e1;">◻</span> ' ) . esc_html( $req['label'] );
										}
										echo '</div>';
									}
									?>
								</td>
								<td style="padding:12px 18px;">
									<?php if ( $e['is_winner'] ) : ?>
										<span style="background:#ecfdf5;border:1px solid #a7f3d0;color:#059669;font-size:11px;font-weight:800;padding:2px 8px;border-radius:6px;">Winner</span>
									<?php else : ?>
										<span style="background:#f1f5f9;color:#64748b;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;">Participant</span>
									<?php endif; ?>
								</td>
								<td style="padding:12px 18px;color:#64748b;font-size:12px;"><?php echo esc_html( mysql2date( 'M j, g:i a', $e['created_at'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					</tbody>
				</table>

				<?php if ( $total_pages > 1 ) : ?>
					<div style="padding:14px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#64748b;">
						<div>Page <?php echo esc_html( $current_page ); ?> of <?php echo esc_html( $total_pages ); ?></div>
						<div style="display:flex;gap:6px;">
							<?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
								<a href="<?php echo esc_url( add_query_arg( 'paged', $i ) ); ?>" style="padding:4px 10px;border:1px solid #cbd5e1;border-radius:6px;text-decoration:none;<?php echo ( $i === $current_page ) ? 'background:#9353ff;color:#fff;' : 'color:#0f172a;'; ?>"><?php echo esc_html( $i ); ?></a>
							<?php endfor; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<div style="margin-top:24px;background:#fff1f2;border:1px solid #fecdd3;border-radius:12px;padding:18px 24px;display:flex;justify-content:space-between;align-items:center;">
				<div>
					<h4 style="margin:0;color:#9f1239;font-size:14px;font-weight:800;">Purge Entries</h4>
					<p style="margin:2px 0 0;color:#e11d48;font-size:12px;">Deletes all entries and uploaded files for this giveaway.</p>
				</div>
				<button type="button" id="aws-sbg-purge-btn" class="button" style="color:#e11d48;border-color:#fecdd3;">Purge Entries</button>
			</div>
		<?php endif; ?>
	</div>

	<script>
	document.addEventListener('DOMContentLoaded', function () {
		var ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
		var nonce = '<?php echo esc_js( $nonce ); ?>';
		var giveawayId = <?php echo intval( $giveaway_id ); ?>;
		var current = [];

		function fd(action) {
			var f = new FormData();
			f.append('action', action);
			f.append('nonce', nonce);
			f.append('giveaway_id', giveawayId);
			return f;
		}
		function esc(v){ return String(v == null ? '' : v).replace(/</g,'&lt;'); }

		function render() {
			var box = document.getElementById('aws-draw-results');
			if (!box) return;
			if (!current.length) { box.innerHTML = ''; document.getElementById('aws-publish-section').style.display = 'none'; return; }
			var html = '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;" id="aws-winners-grid">';
			current.forEach(function (w, idx) {
				var phone = (w.phone || '').replace(/[^0-9]/g, '');
				html += '<div class="aws-winner-card" data-id="' + w.id + '" style="background:#fff;border:2px solid #9353ff;border-radius:12px;padding:16px;">'
					+ '<div style="display:flex;justify-content:space-between;margin-bottom:8px;">'
					+ '<span style="background:#f5f3ff;color:#7c3aed;font-weight:900;font-size:11px;padding:2px 8px;border-radius:6px;">Winner #' + (idx + 1) + '</span>'
					+ '<span style="font-family:monospace;font-weight:800;color:#9353ff;font-size:12px;">#' + esc(w.ticket_number) + '</span></div>'
					+ '<div style="font-weight:800;color:#0f172a;">' + esc(w.full_name) + '</div>'
					+ '<div style="font-size:12px;color:#64748b;margin-bottom:10px;">' + esc(w.email) + '</div>'
					+ '<div style="display:flex;gap:8px;">'
					+ '<a href="https://wa.me/' + phone + '" target="_blank" class="button" style="flex:1;text-align:center;">WhatsApp</a>'
					+ '<button type="button" class="button aws-reroll-btn" data-idx="' + idx + '">Re-roll</button>'
					+ '</div></div>';
			});
			html += '</div>';
			box.innerHTML = html;
			document.getElementById('aws-publish-section').style.display = 'block';
			box.querySelectorAll('.aws-reroll-btn').forEach(function (btn) {
				btn.onclick = reroll;
			});
		}

		function reroll() {
			var idx = parseInt(this.getAttribute('data-idx'));
			var btn = this;
			btn.disabled = true;
			var data = fd('aws_sbg_reroll_giveaway_winner');
			current.forEach(function (c) { data.append('exclude_ids[]', c.id); });
			fetch(ajaxUrl, { method: 'POST', body: data })
				.then(r => r.json())
				.then(function (res) {
					if (res.success && res.data.winner) { current[idx] = res.data.winner; render(); }
					else { btn.disabled = false; alert(res.data && res.data.message ? res.data.message : 'No replacement available.'); }
				})
				.catch(function () { btn.disabled = false; alert('Network error.'); });
		}

		var drawBtn = document.getElementById('aws-draw-btn');
		if (drawBtn) {
			drawBtn.addEventListener('click', function () {
				drawBtn.disabled = true;
				drawBtn.innerText = 'Selecting…';
				fetch(ajaxUrl, { method: 'POST', body: fd('aws_sbg_pick_giveaway_winners') })
					.then(r => r.json())
					.then(function (res) {
						drawBtn.disabled = false;
						drawBtn.innerText = 'Re-select winners';
						if (res.success && res.data.winners) { current = res.data.winners; render(); }
						else { alert(res.data && res.data.message ? res.data.message : 'Error selecting winners.'); }
					})
					.catch(function () { drawBtn.disabled = false; drawBtn.innerText = 'Pick winners'; alert('Network error.'); });
			});
		}

		var publishBtn = document.getElementById('aws-publish-btn');
		if (publishBtn) {
			publishBtn.addEventListener('click', function () {
				if (!current.length) { alert('No winners selected.'); return; }
				if (!confirm('Publish these ' + current.length + ' winners and set the giveaway to Announce mode?')) { return; }
				publishBtn.disabled = true;
				var data = fd('aws_sbg_publish_giveaway_winners');
				current.forEach(function (c) { data.append('winner_ids[]', c.id); });
				fetch(ajaxUrl, { method: 'POST', body: data })
					.then(r => r.json())
					.then(function (res) {
						publishBtn.disabled = false;
						if (res.success) { alert('Winners published!'); window.location.reload(); }
						else { alert(res.data && res.data.message ? res.data.message : 'Error publishing winners.'); }
					});
			});
		}

		var purgeBtn = document.getElementById('aws-sbg-purge-btn');
		if (purgeBtn) {
			purgeBtn.addEventListener('click', function () {
				if (!confirm('Permanently delete all entries and uploads for this giveaway?')) { return; }
				purgeBtn.disabled = true;
				fetch(ajaxUrl, { method: 'POST', body: fd('aws_sbg_purge_giveaway_entries') })
					.then(r => r.json())
					.then(function (res) {
						if (res.success) { alert('Entries purged.'); window.location.reload(); }
						else { purgeBtn.disabled = false; alert('Error purging entries.'); }
					});
			});
		}
	});
	</script>
	<?php
}

/* ============================================================================
 * 8. MIGRATION (legacy single campaign -> first giveaway)
 * ============================================================================ */

function aws_sbg_giveaway_maybe_migrate() {
	if ( get_option( 'aws_sbg_giveaway_migrated' ) ) {
		return;
	}
	if ( ! post_type_exists( 'aws_giveaway' ) ) {
		return;
	}

	global $wpdb;
	$table  = $wpdb->prefix . 'aws_giveaway_entries';
	$legacy = get_option( 'aws_sbg_giveaway_settings', array() );

	if ( is_array( $legacy ) && ! empty( $legacy ) ) {
		$gid = wp_insert_post( array(
			'post_type'   => 'aws_giveaway',
			'post_status' => 'publish',
			'post_title'  => ! empty( $legacy['campaign_title'] ) ? $legacy['campaign_title'] : 'AWS SBG Campus Giveaway',
		) );

		if ( $gid && ! is_wp_error( $gid ) ) {
			update_post_meta( $gid, 'giveaway_description', $legacy['campaign_description'] ?? '' );
			update_post_meta( $gid, 'giveaway_status', ( ( $legacy['campaign_status'] ?? 'active' ) === 'announced' ) ? 'announced' : 'open' );
			$legacy_domains = trim( (string) ( $legacy['allowed_email_domains'] ?? 'students.au.edu.pk' ) );
			update_post_meta( $gid, 'allowed_email_domains', ( '*' === $legacy_domains ) ? '*' : 'students.au.edu.pk' );
			update_post_meta( $gid, 'ticket_prefix', $legacy['ticket_prefix'] ?? 'AU-AWS' );
			update_post_meta( $gid, 'winner_count', intval( $legacy['winner_count'] ?? 5 ) );
			update_post_meta( $gid, 'prize_title', $legacy['prize_title'] ?? '' );
			update_post_meta( $gid, 'prize_description', $legacy['prize_description'] ?? '' );

			$reqs = array();
			if ( ! empty( $legacy['tasks'] ) && is_array( $legacy['tasks'] ) ) {
				foreach ( $legacy['tasks'] as $t ) {
					$reqs[] = array(
						'id'       => 'req_' . sanitize_key( $t['id'] ?? uniqid() ),
						'label'    => $t['title'] ?? 'Task',
						'type'     => 'check',
						'required' => ! empty( $t['is_required'] ) ? 1 : 0,
						'help'     => $t['subtitle'] ?? '',
					);
				}
			}
			if ( ! empty( $reqs ) ) {
				update_post_meta( $gid, 'giveaway_requirements', $reqs );
			}
			update_post_meta( $gid, 'giveaway_fields', aws_sbg_default_giveaway_fields() );

			$wpdb->query( $wpdb->prepare( "UPDATE $table SET giveaway_id = %d, campaign_id = %s WHERE giveaway_id = 0", $gid, 'gw-' . $gid ) );
		}
	}

	aws_sbg_giveaway_swap_indexes();

	update_option( 'aws_sbg_giveaway_migrated', time() );
	update_option( 'aws_sbg_giveaway_db_version', AWS_SBG_GIVEAWAY_DBV );
}
add_action( 'admin_init', 'aws_sbg_giveaway_maybe_migrate', 20 );

/* ============================================================================
 * 9. FRESHERS WEEK GIVEAWAY SEEDER
 * ============================================================================ */

/**
 * Create the Freshers Week giveaway once. The marker meta keeps this idempotent,
 * and a giveaway the organiser has since edited is never overwritten.
 */
function aws_sbg_maybe_seed_freshers_giveaway() {
	if ( ! post_type_exists( 'aws_giveaway' ) ) {
		return;
	}

	$existing = get_posts( array(
		'post_type'        => 'aws_giveaway',
		'post_status'      => 'any',
		'posts_per_page'   => 1,
		'fields'           => 'ids',
		'no_found_rows'    => true,
		'meta_key'         => '_aws_sbg_seed',
		'meta_value'       => 'freshers-2026',
	) );
	if ( ! empty( $existing ) ) {
		return;
	}

	$giveaway_id = wp_insert_post( array(
		'post_type'   => 'aws_giveaway',
		'post_status' => 'publish',
		'post_title'  => 'Freshers Week: Verify and Win AWS Swag',
	) );

	if ( ! $giveaway_id || is_wp_error( $giveaway_id ) ) {
		return;
	}

	update_post_meta( $giveaway_id, '_aws_sbg_seed', 'freshers-2026' );
	update_post_meta( $giveaway_id, 'giveaway_status', 'open' );
	update_post_meta( $giveaway_id, 'allowed_email_domains', 'students.au.edu.pk' );
	update_post_meta( $giveaway_id, 'winner_count', 3 );
	update_post_meta( $giveaway_id, 'ticket_prefix', 'FRESH' );
	update_post_meta( $giveaway_id, 'prize_title', 'AWS swag bundle' );
	update_post_meta( $giveaway_id, 'prize_description', 'Three entries are picked at random and each wins an AWS swag bundle.' );
	update_post_meta( $giveaway_id, 'giveaway_description', 'Create your AWS Builder Center account, verify that you are a student, then upload a screenshot of your verified profile to enter.' );

	update_post_meta( $giveaway_id, 'giveaway_fields', array(
		array( 'id' => 'full_name', 'label' => 'Full name', 'type' => 'text', 'required' => 1, 'options' => '' ),
		array( 'id' => 'email', 'label' => 'Air University email', 'type' => 'email', 'required' => 1, 'options' => '' ),
	) );

	update_post_meta( $giveaway_id, 'giveaway_requirements', array(
		array(
			'id'       => 'builder_proof',
			'label'    => 'Upload a screenshot of your verified Builder Center profile',
			'type'     => 'upload',
			'required' => 1,
			'help'     => 'The screenshot should show your name and verified student status.',
		),
	) );
}
add_action( 'admin_init', 'aws_sbg_maybe_seed_freshers_giveaway', 25 );
