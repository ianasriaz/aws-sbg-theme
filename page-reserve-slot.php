<?php
/**
 * Template Name: AWS Exam Slot Reservation
 * 
 * Clean, lightweight self-service slot reservation portal.
 */
get_header();
?>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="min-h-screen bg-slate-50 text-slate-900 font-sans py-10 px-4 sm:px-6 lg:px-8 flex items-center justify-center"
     x-data="awsSlotReservation()"
     x-init="initData()">

	<div class="w-full max-w-2xl mx-auto space-y-6">

		<!-- BRAND HEADER -->
		<div class="text-center space-y-2 pt-2 pb-2">
			<h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
				Slot Reservation
			</h1>
			<p class="text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
				Select your preferred exam time slot below to secure your seat and generate your official access code.
			</p>
		</div>

		<!-- 1. MAIN RESERVATION CARD (FORM STAGE) -->
		<div x-show="currentStage === 'form'" x-transition:enter="transition ease-out duration-200" class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-8 shadow-sm space-y-6">

			<!-- ERROR NOTICE -->
			<template x-if="errorMessage">
				<div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-start gap-2.5">
					<svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
					<span x-text="errorMessage"></span>
				</div>
			</template>

			<!-- LOADING SKELETON -->
			<div x-show="loadingSlots" class="space-y-4 py-8 text-center text-slate-400">
				<div class="inline-block w-8 h-8 border-3 border-purple-600 border-t-transparent rounded-full animate-spin"></div>
				<div class="text-xs font-medium">Loading available exam slots...</div>
			</div>

			<!-- PUBLIC RESERVATIONS DISABLED STATE -->
			<div x-show="!loadingSlots && !isPublicEnabled" class="text-center py-8 space-y-3" x-cloak>
				<div class="w-12 h-12 rounded-full bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto text-xl">
					🔒
				</div>
				<h3 class="text-base font-bold text-slate-800">Public Slot Reservations Are Currently Closed</h3>
				<p class="text-xs text-slate-500 max-w-md mx-auto">
					Self-service slot reservations are temporarily paused by the organizers. If you have already received an access code, you can proceed directly to the exam portal.
				</p>
				<div class="pt-2">
					<a href="<?php echo esc_url( home_url( '/exam-portal/' ) ); ?>" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition-all shadow-sm">
						Go to Exam Portal &rarr;
					</a>
				</div>
			</div>

			<!-- NO SLOTS FOUND STATE -->
			<div x-show="!loadingSlots && isPublicEnabled && slots.length === 0" class="text-center py-8 space-y-3" x-cloak>
				<div class="w-12 h-12 rounded-full bg-slate-100 border border-slate-200 text-slate-500 flex items-center justify-center mx-auto text-xl">
					📅
				</div>
				<h3 class="text-base font-bold text-slate-800">No Exam Slots Scheduled Right Now</h3>
				<p class="text-xs text-slate-500 max-w-md mx-auto">
					There are no upcoming active exam time slots configured at this moment. Please check back later or contact your event administrator.
				</p>
			</div>

			<!-- FORM CONTENT -->
			<form x-show="!loadingSlots && isPublicEnabled && slots.length > 0" @submit.prevent="submitReservation" class="space-y-6" x-cloak>

				<!-- STEP 1: SELECT TIME SLOT -->
				<div class="space-y-3">
					<label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700">
						1. Choose Your Preferred Exam Slot <span class="text-purple-600">*</span>
					</label>

					<div class="space-y-3">
						<template x-for="slot in slots" :key="slot.id">
							<label :class="{
								'border-purple-600 bg-purple-50/50 ring-2 ring-purple-600/20 shadow-sm': selectedSlotId === slot.id && slot.is_bookable,
								'border-slate-200/90 bg-white hover:border-purple-300 hover:shadow-xs': selectedSlotId !== slot.id && slot.is_bookable,
								'border-slate-200 bg-slate-50/80 cursor-not-allowed': !slot.is_bookable
							}" class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 sm:p-4.5 rounded-2xl border transition-all cursor-pointer">
								
								<!-- LEFT: RADIO & DETAILS -->
								<div class="flex items-start sm:items-center gap-3.5 min-w-0">
									<!-- RADIO OR LOCK INDICATOR -->
									<template x-if="slot.is_bookable">
										<input type="radio"
										       name="selected_slot"
										       :value="slot.id"
										       x-model="selectedSlotId"
										       class="w-4 h-4 text-purple-600 border-slate-300 focus:ring-purple-500 mt-0.5 sm:mt-0 shrink-0 cursor-pointer">
									</template>
									<template x-if="!slot.is_bookable">
										<div class="w-4 h-4 rounded-full bg-slate-200/80 border border-slate-300/60 flex items-center justify-center text-[9px] text-slate-500 mt-0.5 sm:mt-0 shrink-0 select-none" title="Fully Booked">
											🔒
										</div>
									</template>
									
									<div class="min-w-0">
										<div class="flex items-center gap-2 flex-wrap">
											<span class="text-sm font-extrabold tracking-tight" :class="slot.is_bookable ? 'text-slate-900' : 'text-slate-700'" x-text="slot.slot_name"></span>
											<template x-if="!slot.is_bookable">
												<span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200/60 select-none">
													Closed
												</span>
											</template>
										</div>
										<div class="flex items-center gap-2 text-xs mt-1.5 flex-wrap" :class="slot.is_bookable ? 'text-slate-600' : 'text-slate-500'">
											<span class="inline-flex items-center gap-1 font-medium whitespace-nowrap bg-slate-100/80 px-2 py-0.5 rounded-md text-slate-600">
												<span>📅</span> <span x-text="slot.date_formatted"></span>
											</span>
											<span class="inline-flex items-center gap-1 font-semibold whitespace-nowrap bg-slate-100/80 px-2 py-0.5 rounded-md" :class="slot.is_bookable ? 'text-slate-800' : 'text-slate-600'">
												<span>⏱️</span> <span x-text="slot.time_window"></span>
											</span>
										</div>
									</div>
								</div>

								<!-- RIGHT / BOTTOM: AVAILABILITY STATUS BADGE -->
								<div class="self-start sm:self-center shrink-0 pl-7 sm:pl-0">
									<template x-if="slot.is_bookable">
										<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-700 border border-emerald-500/25 shadow-xs whitespace-nowrap">
											<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
											Available
										</span>
									</template>
									<template x-if="!slot.is_bookable">
										<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-rose-50 text-rose-600 border border-rose-200 shadow-xs whitespace-nowrap">
											<span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
											Fully Booked
										</span>
									</template>
								</div>

							</label>
						</template>
					</div>
				</div>

				<!-- STEP 2: PARTICIPANT DETAILS -->
				<div class="space-y-4 pt-2 border-t border-slate-100">
					<label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700">
						2. Participant Details
					</label>

					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<!-- Full Name -->
						<div class="space-y-1.5">
							<label class="block text-xs font-bold text-slate-700">Full Name <span class="text-purple-600">*</span></label>
							<input type="text"
							       x-model="fullName"
							       required
							       placeholder="e.g. Muhammad Ali"
							       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 text-xs font-medium focus:bg-white focus:border-purple-600 focus:ring-1 focus:ring-purple-600 outline-none transition-all">
						</div>

						<!-- Email Address -->
						<div class="space-y-1.5">
							<label class="block text-xs font-bold text-slate-700">Email Address <span class="text-purple-600">*</span></label>
							<input type="email"
							       x-model="email"
							       required
							       placeholder="e.g. candidate@university.edu"
							       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 text-xs font-medium focus:bg-white focus:border-purple-600 focus:ring-1 focus:ring-purple-600 outline-none transition-all">
						</div>
					</div>
					
					<!-- RESERVATION POLICY NOTE (SAFE & LIGHT UI) -->
					<div class="p-3.5 rounded-xl bg-purple-50/40 border border-purple-100/90 text-slate-600 text-xs flex items-start gap-2.5">
						<span class="text-sm shrink-0 mt-0.5 text-purple-600">🛡️</span>
						<div class="space-y-0.5 min-w-0">
							<div class="font-bold text-slate-800 text-xs">Important Reservation Notice</div>
							<div class="text-[11.5px] text-slate-500 leading-relaxed">
								Please use your <strong>registered email address only</strong>. Once your exam slot is booked, you do not need to book again. Your official access code will be generated and dispatched to your inbox immediately.
							</div>
						</div>
					</div>
				</div>

				<!-- SUBMIT BUTTON -->
				<div class="pt-2">
					<button type="submit"
					        :disabled="submitting || !selectedSlotId || !fullName || !email"
					        class="w-full py-3.5 px-6 rounded-xl font-bold text-sm bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white shadow-sm hover:shadow-md transition-all flex items-center justify-center gap-2">
						<template x-if="submitting">
							<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
						</template>
						<span>Confirm & Generate Code &rarr;</span>
					</button>
				</div>

			</form>
		</div>

		<!-- 2. CONFIRMATION SUCCESS STAGE -->
		<div x-show="currentStage === 'success'" x-transition:enter="transition ease-out duration-300" class="bg-white border border-slate-200/90 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6 text-center" x-cloak>
			
			<div class="w-14 h-14 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center mx-auto text-2xl shadow-sm">
				✓
			</div>

			<div class="space-y-1">
				<h2 class="text-xl font-extrabold text-slate-900">
					Exam Slot Confirmed!
				</h2>
				<p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
					Hello <strong class="text-slate-800" x-text="successData.member_name"></strong>, your seat has been reserved. Please keep your access code safe.
				</p>
			</div>

			<!-- ACCESS CODE HIGHLIGHT CARD -->
			<div class="p-5 rounded-2xl bg-slate-900 text-white space-y-2.5 max-w-md mx-auto shadow-inner">
				<div class="text-[10px] font-bold text-purple-400 uppercase tracking-widest">
					YOUR OFFICIAL ACCESS CODE
				</div>
				<div class="font-mono text-3xl font-extrabold text-emerald-400 tracking-widest" x-text="successData.access_code">
				</div>
				<div class="pt-1">
					<button @click="copyAccessCode()" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-bold text-slate-200 transition-all">
						<span x-text="copiedCode ? '✓ Copied to Clipboard' : '📋 Copy Access Code'"></span>
					</button>
				</div>
			</div>

			<!-- CONFIRMED SCHEDULE BOX -->
			<div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 space-y-1.5 max-w-md mx-auto text-left">
				<div class="font-bold text-slate-900 flex items-center gap-2">
					<span>⏱️ Reserved Slot:</span>
					<span class="text-purple-700" x-text="successData.slot_name"></span>
				</div>
				<div class="flex items-center gap-2 text-slate-600">
					<span>📅 Date:</span>
					<strong class="text-slate-800" x-text="successData.date_formatted"></strong>
				</div>
				<div class="flex items-center gap-2 text-slate-600">
					<span>⏰ Time Window:</span>
					<strong class="text-slate-800 font-mono" x-text="successData.time_window + ' (' + (successData.timezone || 'Local') + ')'"></strong>
				</div>
			</div>

			<div class="text-[11px] text-slate-400 max-w-sm mx-auto">
				📧 A confirmation email containing your access code and exam guidelines has also been dispatched to <strong class="text-slate-600" x-text="successData.member_email"></strong>.
			</div>

			<!-- ACTION BUTTONS -->
			<div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
				<a :href="successData.portal_url || '<?php echo esc_url( home_url( '/exam-portal/' ) ); ?>'" class="w-full sm:w-auto px-8 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm hover:shadow-md transition-all">
					Start Exam &rarr;
				</a>
				<button @click="resetForm()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all">
					Reserve Another Slot
				</button>
			</div>

		</div>

		<!-- FOOTER BRANDING -->
		<div class="text-center pt-4 text-slate-500">
			<div class="text-xs text-slate-400 flex items-center justify-center gap-1.5 flex-wrap">
				<span>Developed with <span class="text-rose-500 font-bold">♥</span> by <strong class="text-slate-600 font-semibold">Anas and team</strong></span>
				<span class="text-slate-300">•</span>
				<span class="inline-flex items-center gap-1.5 text-slate-600 font-medium">
					<span>☁️</span> Deployed on <strong class="text-slate-700 font-bold">AWS</strong> <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-slate-200/90 text-slate-700 border border-slate-300/80">ap-south-1</span>
				</span>
			</div>
		</div>

	</div>

</div>

<script>
function awsSlotReservation() {
	return {
		currentStage: 'form',
		loadingSlots: true,
		isPublicEnabled: true,
		slots: [],
		selectedSlotId: '',
		fullName: '',
		email: '',
		submitting: false,
		errorMessage: '',
		copiedCode: false,
		successData: {},

		async initData() {
			this.loadingSlots = true;
			this.errorMessage = '';
			try {
				const response = await fetch('<?php echo esc_url( rest_url( 'aws-sbg/v1/exam/available-slots' ) ); ?>');
				const data = await response.json();
				if (data.success) {
					this.isPublicEnabled = data.public_enabled !== false;
					this.slots = data.slots || [];
					
					// Auto-select first available slot if any
					const firstAvail = this.slots.find(s => !s.is_full);
					if (firstAvail) {
						this.selectedSlotId = firstAvail.id;
					}
				} else {
					this.errorMessage = data.message || 'Unable to load exam slots.';
				}
			} catch (err) {
				this.errorMessage = 'Network connection error loading slots. Please refresh.';
			} finally {
				this.loadingSlots = false;
			}
		},

		async submitReservation() {
			if (!this.selectedSlotId || !this.fullName.trim() || !this.email.trim()) {
				this.errorMessage = 'Please complete all required fields and select a slot.';
				return;
			}

			this.submitting = true;
			this.errorMessage = '';

			try {
				const response = await fetch('<?php echo esc_url( rest_url( 'aws-sbg/v1/exam/reserve-slot' ) ); ?>', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						slot_id: this.selectedSlotId,
						name: this.fullName.trim(),
						email: this.email.trim()
					})
				});

				const data = await response.json();

				if (data.success) {
					this.successData = data;
					this.currentStage = 'success';
				} else {
					this.errorMessage = data.message || 'Reservation failed. Please try again.';
				}
			} catch (err) {
				this.errorMessage = 'Connection error submitting reservation. Please check your internet.';
			} finally {
				this.submitting = false;
			}
		},

		copyAccessCode() {
			if (!this.successData.access_code) return;
			navigator.clipboard.writeText(this.successData.access_code);
			this.copiedCode = true;
			setTimeout(() => {
				this.copiedCode = false;
			}, 3000);
		},

		resetForm() {
			this.currentStage = 'form';
			this.fullName = '';
			this.email = '';
			this.successData = {};
			this.initData();
		}
	};
}
</script>

<?php get_footer(); ?>
