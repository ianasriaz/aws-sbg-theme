<?php
/**
 * Template Name: AWS Event Certificate Generator
 * Slug: /claim-certificate/
 * Description: Attendee self-service portal for AWS SBG Event Certificates with Survey and Referral link gates.
 */

get_header();
?>

<!-- LIGHT THEME CLEAN & MINIMAL CERTIFICATE GENERATOR PORTAL -->
<div class="min-h-screen bg-[#f8fafc] text-[#0f172a] font-sans selection:bg-[#ad5cff] selection:text-white py-10 px-4 sm:px-6 lg:px-8 relative overflow-hidden" x-data="awsCertPortal()">
	
	<!-- Background Soft Subtle Gradients -->
	<div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-[#ad5cff]/8 rounded-full blur-[140px] pointer-events-none"></div>
	<div class="absolute bottom-10 right-10 w-[400px] h-[400px] bg-[#7000ff]/5 rounded-full blur-[120px] pointer-events-none"></div>

	<div class="max-w-2xl mx-auto relative z-10">

		<!-- HERO HEADER -->
		<div class="text-center mb-8 space-y-3">
			<div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#fbf8ff] border border-[#ad5cff]/30 text-xs font-extrabold text-[#ad5cff] tracking-wide uppercase shadow-sm">
				<span class="w-2 h-2 rounded-full bg-[#ad5cff] animate-pulse"></span>
				AWS SBG AIR UNIVERSITY
			</div>
			<h1 class="text-3xl sm:text-4xl font-black tracking-tight text-[#0f172a]">
				Certificate <span class="text-[#ad5cff]">Generator</span>
			</h1>
			<p class="text-[#64748b] text-sm max-w-lg mx-auto leading-relaxed">
				Claim your verified official community event certificate after completing the attendee survey and Builder Center account verification.
			</p>
		</div>

		<!-- MAIN CARD CONTAINER (LIGHT CLEAN CARD) -->
		<div class="bg-white border border-[#e2e8f0] rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/50 space-y-6">

			<!-- STEP 1: EVENT SELECTION & ATTENDEE FORM -->
			<div x-show="stage === 'form'" class="space-y-5">
				
				<div>
					<label class="block text-xs font-extrabold uppercase tracking-wider text-[#475569] mb-2">Select Active Workshop / Event</label>
					<select x-model="selectedEventId" @change="onEventChange()" class="w-full bg-[#f8fafc] border-2 border-[#e2e8f0] focus:border-[#ad5cff] rounded-2xl px-4 py-3.5 text-[#0f172a] font-bold focus:outline-none transition-all">
						<option value="">-- Choose Active Workshop / Session --</option>
						<template x-for="ev in events" :key="ev.id">
							<option :value="ev.id" x-text="ev.title + ' (' + ev.event_date + ')'"></option>
						</template>
					</select>
					<div x-show="events.length === 0" class="mt-2 text-xs text-amber-800 bg-amber-50 border border-amber-200 p-3.5 rounded-2xl font-medium" x-cloak>
						📢 No active sessions available right now. Please check back when a new session is announced by community leaders!
					</div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label class="block text-xs font-extrabold uppercase tracking-wider text-[#475569] mb-2">Full Name *</label>
						<input type="text" x-model="participantName" placeholder="As printed on Certificate" required class="w-full bg-[#f8fafc] border-2 border-[#e2e8f0] focus:border-[#ad5cff] rounded-2xl px-4 py-3 text-[#0f172a] font-bold focus:outline-none transition-all">
					</div>
					<div>
						<label class="block text-xs font-extrabold uppercase tracking-wider text-[#475569] mb-2">Email Address *</label>
						<input type="email" x-model="participantEmail" placeholder="username@gmail.com" required class="w-full bg-[#f8fafc] border-2 border-[#e2e8f0] focus:border-[#ad5cff] rounded-2xl px-4 py-3 text-[#0f172a] font-bold focus:outline-none transition-all">
					</div>
				</div>

				<button type="button" @click="proceedToTasks()" :disabled="!selectedEventId || !participantName || !participantEmail" class="w-full py-4 px-6 rounded-2xl font-black text-sm bg-gradient-to-r from-[#ad5cff] to-[#7000ff] disabled:opacity-40 text-white shadow-lg shadow-[#ad5cff]/25 hover:opacity-95 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
					<span>Proceed to Certification Checklist ➔</span>
				</button>
			</div>

			<!-- STEP 2: GATED CHECKLIST (AWS BUILDER DUAL PATH & SURVEY SCREENSHOT) -->
			<div x-show="stage === 'tasks'" class="space-y-6" x-cloak>
				
				<!-- ATTENDEE HEADER SUMMARY -->
				<div class="flex items-center justify-between bg-[#fbf8ff] border border-[#ad5cff]/20 rounded-2xl p-4">
					<div>
						<span class="text-[10px] text-[#ad5cff] font-black uppercase tracking-wider">Participant</span>
						<h3 class="text-base font-black text-[#0f172a]" x-text="participantName"></h3>
						<span class="text-xs text-[#64748b]" x-text="participantEmail"></span>
					</div>
					<button @click="stage = 'form'" class="text-xs font-bold text-[#ad5cff] hover:underline bg-white px-3 py-1.5 rounded-xl border border-[#ad5cff]/30 shadow-sm">Change Info</button>
				</div>

				<!-- RETURNING MEMBER BYPASS BANNER -->
				<div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center gap-3" x-show="existingAccountFound" x-cloak>
					<span class="w-7 h-7 rounded-full bg-emerald-500 text-white font-bold text-sm flex items-center justify-center flex-shrink-0">✓</span>
					<div class="text-xs text-emerald-900 leading-relaxed">
						<strong>Welcome back!</strong> Your email was recognized in our community database. AWS Builder Center account setup is already verified!
					</div>
				</div>

				<!-- STEP 1: AWS BUILDER CENTER ACCOUNT VERIFICATION -->
				<div class="p-5 rounded-2xl border transition-all" :class="builderDone ? 'bg-[#fbf8ff] border-[#ad5cff]' : 'bg-[#f8fafc] border-[#e2e8f0]'" x-show="!existingAccountFound">
					<div class="space-y-4">
						<div class="flex items-center justify-between gap-3">
							<div class="flex items-center gap-3">
								<div class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-xs" :class="builderDone ? 'bg-[#ad5cff] text-white' : 'bg-[#e2e8f0] text-[#64748b]'">
									<span x-text="builderDone ? '✓' : '1'"></span>
								</div>
								<div>
									<h4 class="font-black text-sm text-[#0f172a]">Verify your Builder Center status</h4>
									<p class="text-xs text-[#64748b]">Creating your account through our link supports AWS SBG Air University.</p>
								</div>
							</div>
							<span x-show="builderDone" class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase">Verified ✓</span>
						</div>

						<!-- Two Primary Action Buttons -->
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
							<button type="button" @click="selectNewBuilder()" class="p-4 rounded-2xl border-2 text-left transition-all flex flex-col justify-between" :class="builderMode === 'new' ? 'bg-[#fbf8ff] border-[#ad5cff] shadow-md' : 'bg-white border-[#e2e8f0] hover:border-[#ad5cff]/50'">
								<div>
									<span class="inline-block px-2.5 py-0.5 rounded-full bg-[#ad5cff]/10 text-[#ad5cff] text-[10px] font-black uppercase mb-2">Recommended 🌟</span>
									<h5 class="font-black text-xs text-[#0f172a] mb-1">🆕 I DON'T HAVE AN AWS BUILDER ACCOUNT</h5>
									<p class="text-[11px] text-[#64748b] leading-relaxed">Sign up via community link &amp; get Community Supporter status.</p>
								</div>
							</button>

							<button type="button" @click="selectExistingBuilder()" class="p-4 rounded-2xl border-2 text-left transition-all flex flex-col justify-between" :class="builderMode === 'existing' ? 'bg-[#fbf8ff] border-[#ad5cff] shadow-md' : 'bg-white border-[#e2e8f0] hover:border-[#ad5cff]/50'">
								<div>
									<span class="inline-block px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-black uppercase mb-2">Fast Track ⚡</span>
									<h5 class="font-black text-xs text-[#0f172a] mb-1">✅ I ALREADY HAVE AN ACCOUNT</h5>
									<p class="text-[11px] text-[#64748b] leading-relaxed">Already registered? Continue directly to survey.</p>
								</div>
							</button>
						</div>

						<!-- NEW BUILDER SUB-FLOW -->
						<div x-show="builderMode === 'new'" class="space-y-3 pt-2 bg-white p-4 rounded-2xl border border-[#ad5cff]/30">
							<p class="text-xs text-[#475569] leading-relaxed font-medium">
								1. Click below to sign up on AWS Builder Center via our official community link:
							</p>
							<button type="button" @click="openReferral()" class="w-full py-2.5 px-4 rounded-xl text-xs font-black bg-[#ad5cff] hover:bg-[#9947f5] text-white shadow-md transition-all flex items-center justify-center gap-2">
								<span>Open Referral Link (builder.aws.com) ↗</span>
							</button>

							<div class="space-y-1.5 pt-1">
								<label class="block text-[11px] font-extrabold uppercase text-[#475569]">2. Paste your new AWS Builder Profile Handle or URL *</label>
								<input type="text" x-model="builderProfileUrl" @input="checkBuilderProfile()" placeholder="https://builder.aws.com/community/profile/... or @username" class="w-full bg-[#f8fafc] border-2 border-[#e2e8f0] focus:border-[#ad5cff] rounded-xl px-3.5 py-2.5 text-xs text-[#0f172a] font-bold focus:outline-none transition-all">
							</div>

							<!-- REWARD CARD -->
							<div x-show="builderDone" class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex items-start gap-2.5 text-xs text-emerald-900" x-cloak>
								<span class="text-base flex-shrink-0">🌟</span>
								<div>
									<strong class="font-black text-emerald-800">COMMUNITY SUPPORTER UNLOCKED!</strong>
									<p class="text-[11px] text-emerald-700 mt-0.5">You helped AWS SBG Air University grow its official community. Thank you! 💜</p>
								</div>
							</div>
						</div>

						<!-- EXISTING BUILDER SUB-FLOW -->
						<div x-show="builderMode === 'existing'" class="space-y-2 pt-1 bg-white p-4 rounded-2xl border border-[#e2e8f0]">
							<div class="flex items-center gap-2 text-emerald-700 font-black text-xs">
								<span>✓ Existing Account Verified</span>
							</div>
							<p class="text-xs text-[#64748b] leading-relaxed">
								Already have an account? You're good to go! Please proceed directly to the survey below.
							</p>
						</div>

					</div>
				</div>

				<!-- STEP 2: AWS ATTENDEE SURVEY -->
				<div class="p-5 rounded-2xl border transition-all" :class="surveyDone ? 'bg-[#fbf8ff] border-[#ad5cff]' : 'bg-[#f8fafc] border-[#e2e8f0]'">
					<div class="space-y-3">
						<div class="flex items-center justify-between gap-3">
							<div class="flex items-center gap-3">
								<div class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-xs" :class="surveyDone ? 'bg-[#ad5cff] text-white' : 'bg-[#e2e8f0] text-[#64748b]'">
									<span x-text="surveyDone ? '✓' : (existingAccountFound ? '1' : '2')"></span>
								</div>
								<div>
									<h4 class="font-black text-sm text-[#0f172a]">Official AWS Attendee Survey</h4>
									<p class="text-xs text-[#64748b]">Mandatory survey to log event feedback with AWS.</p>
								</div>
							</div>
							<button type="button" @click="openSurvey()" :disabled="surveyDone" class="px-4 py-2 rounded-xl text-xs font-black transition-all" :class="surveyDone ? 'bg-emerald-100 text-emerald-800' : (surveyOpened ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-[#ad5cff] hover:bg-[#9947f5] text-white shadow-md')">
								<span x-text="surveyDone ? 'Completed ✓' : (surveyOpened ? 'Survey Opened ↗' : 'Open Survey Form ↗')"></span>
							</button>
						</div>

						<div class="text-xs text-[#475569] bg-white p-3 rounded-xl border border-[#e2e8f0] leading-relaxed" x-show="surveyOpened && !surveyDone">
							💡 <strong>Next Step:</strong> Complete the survey on the opened tab, take a screenshot of the <em>"Thank You for Submitting!"</em> page, and attach it below.
						</div>
					</div>
				</div>

				<!-- STEP 3: UPLOAD SURVEY SCREENSHOT -->
				<div class="p-5 rounded-2xl border transition-all" :class="screenshotDone ? 'bg-[#fbf8ff] border-[#ad5cff]' : 'bg-[#f8fafc] border-[#e2e8f0]'">
					<div class="space-y-3">
						<div class="flex items-center justify-between gap-3">
							<div class="flex items-center gap-3">
								<div class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-xs" :class="screenshotDone ? 'bg-[#ad5cff] text-white' : 'bg-[#e2e8f0] text-[#64748b]'">
									<span x-text="screenshotDone ? '✓' : (existingAccountFound ? '2' : '3')"></span>
								</div>
								<div>
									<h4 class="font-black text-sm text-[#0f172a]">Upload Survey Screenshot</h4>
									<p class="text-xs text-[#64748b]">Attach screenshot proving survey completion.</p>
								</div>
							</div>
							<label class="px-4 py-2 rounded-xl text-xs font-black cursor-pointer transition-all flex items-center gap-2" :class="screenshotDone ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : (verifyingScreenshot ? 'bg-amber-100 text-amber-800' : 'bg-[#0f172a] text-white shadow-md hover:bg-slate-800')">
								<template x-if="verifyingScreenshot">
									<svg class="animate-spin h-3.5 w-3.5 text-amber-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
								</template>
								<span x-text="screenshotDone ? 'Screenshot Verified ✓' : (verifyingScreenshot ? 'Verifying Screenshot...' : 'Attach Screenshot 📷')"></span>
								<input type="file" accept="image/*" @change="handleScreenshotUpload($event)" class="hidden" :disabled="verifyingScreenshot || screenshotDone">
							</label>
						</div>
						
						<template x-if="screenshotPreview">
							<div class="mt-2 flex items-center gap-3 bg-white p-2.5 rounded-xl border border-[#e2e8f0]">
								<img :src="screenshotPreview" class="w-12 h-12 object-cover rounded-lg border border-[#ad5cff]/40">
								<div class="flex items-center gap-2">
									<template x-if="verifyingScreenshot">
										<svg class="animate-spin h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
									</template>
									<span class="text-xs font-bold" :class="screenshotDone ? 'text-emerald-700' : 'text-amber-800'" x-text="screenshotDone ? 'Survey Screenshot Verified & Ready!' : 'Verifying Image Submission...'"></span>
								</div>
							</div>
						</template>
					</div>
				</div>

				<!-- UNLOCK CERTIFICATE BUTTON -->
				<button type="button" @click="submitAndGenerate()" :disabled="!surveyDone || (!existingAccountFound && !builderDone) || !screenshotDone || generating" class="w-full py-4 px-6 rounded-2xl font-black text-sm bg-gradient-to-r from-[#ad5cff] to-[#7000ff] disabled:opacity-40 text-white shadow-xl shadow-[#ad5cff]/25 hover:opacity-95 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
					<template x-if="generating">
						<svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
					</template>
					<span x-text="generating ? 'Generating Verified PNG...' : '📜 Issue & Download Official Certificate (PNG)'"></span>
				</button>
			</div>

			<!-- SUCCESS / CERTIFICATE READY STAGE -->
			<div x-show="stage === 'success'" class="text-center space-y-6 py-6" x-cloak>
				<div class="w-16 h-16 rounded-full bg-emerald-100 border border-emerald-300 text-emerald-700 text-3xl flex items-center justify-center mx-auto">
					🎉
				</div>
				<h3 class="text-2xl font-black text-[#0f172a]">Certificate Successfully Generated!</h3>
				<p class="text-sm text-[#64748b]">
					Verification Code: <strong class="text-[#ad5cff] font-mono" x-text="verificationCode"></strong>
				</p>

				<button type="button" @click="downloadCanvasPNG()" class="py-4 px-8 rounded-2xl font-black text-sm bg-gradient-to-r from-[#ad5cff] to-[#7000ff] text-white shadow-xl hover:opacity-95 transition-all inline-flex items-center gap-2">
					<span>📥 Redownload Certificate (PNG)</span>
				</button>
			</div>

		</div>

		<div class="text-center mt-6">
			<p class="text-xs text-[#94a3b8] font-bold">
				AWS Student Builder Group at Air University Islamabad • Certification System
			</p>
		</div>

	</div>
</div>

<script>
function awsCertPortal() {
	return {
		stage: 'form',
		events: [],
		selectedEventId: '',
		selectedEvent: null,
		participantName: '',
		participantEmail: '',
		studentId: '',
		builderMode: 'new',
		builderProfileUrl: '',
		builderDone: false,
		surveyDone: false,
		surveyOpened: false,
		referralOpened: false,
		screenshotDone: false,
		screenshotPreview: '',
		verifyingScreenshot: false,
		existingAccountFound: false,
		generating: false,
		verificationCode: '',
		issuedDetails: null,

		init() {
			this.fetchEvents();
		},

		async fetchEvents() {
			try {
				const res = await fetch('/wp-json/aws-sbg/v1/events/active');
				const data = await res.json();
				if (data.success && data.events && data.events.length > 0) {
					this.events = data.events;
				} else {
					this.events = [];
				}
			} catch(e) {
				this.events = [];
			}

			if (this.events.length > 0) {
				this.selectedEventId = this.events[0].id;
				this.onEventChange();
			}
		},

		onEventChange() {
			this.selectedEvent = this.events.find(e => e.id == this.selectedEventId);
		},

		selectNewBuilder() {
			this.builderMode = 'new';
			this.checkBuilderProfile();
		},

		selectExistingBuilder() {
			this.builderMode = 'existing';
			this.builderDone = true;
		},

		checkBuilderProfile() {
			if (this.builderMode === 'existing') {
				this.builderDone = true;
			} else {
				const val = this.builderProfileUrl.trim();
				this.builderDone = (val.length >= 3);
			}
		},

		async proceedToTasks() {
			if (!this.selectedEventId || !this.participantName || !this.participantEmail) return;

			// Reset task states for fresh check
			this.builderDone = false;
			this.builderProfileUrl = '';
			this.builderMode = 'new';
			this.existingAccountFound = false;
			this.surveyDone = false;
			this.surveyOpened = false;
			this.screenshotDone = false;
			this.screenshotPreview = '';

			// Check DB history for this email
			try {
				const res = await fetch('/wp-json/aws-sbg/v1/events/check-email', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ email: this.participantEmail.trim() })
				});
				const data = await res.json();
				const localCompleted = localStorage.getItem('aws_builder_cert_issued_' + this.participantEmail.trim().toLowerCase());
				if (data.has_builder_account || localCompleted) {
					this.builderDone = true;
					this.existingAccountFound = true;
				}
			} catch(e){}

			this.stage = 'tasks';
		},

		openSurvey() {
			if (this.selectedEvent && this.selectedEvent.survey_url) {
				window.open(this.selectedEvent.survey_url, '_blank');
			}
			this.surveyOpened = true;
		},

		openReferral() {
			if (this.selectedEvent && this.selectedEvent.referral_url) {
				window.open(this.selectedEvent.referral_url, '_blank');
			}
			this.referralOpened = true;
		},

		handleScreenshotUpload(event) {
			const file = event.target.files[0];
			if (!file) return;
			this.verifyingScreenshot = true;
			const reader = new FileReader();
			reader.onload = (e) => {
				this.screenshotPreview = e.target.result;
				setTimeout(() => {
					this.verifyingScreenshot = false;
					this.screenshotDone = true;
					this.surveyDone = true;
				}, 1200);
			};
			reader.readAsDataURL(file);
		},

		async submitAndGenerate() {
			this.generating = true;
			try {
				const res = await fetch('/wp-json/aws-sbg/v1/events/generate-certificate', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({
						event_id: this.selectedEventId,
						participant_name: this.participantName,
						participant_email: this.participantEmail,
						student_id: this.studentId,
						builder_profile_url: this.builderProfileUrl,
						is_new_builder: this.builderMode === 'new' ? 1 : 0,
						survey_screenshot: this.screenshotPreview
					})
				});

				const data = await res.json();
				if (data.success) {
					this.verificationCode = data.verification_code;
					this.issuedDetails = data;
					if (this.participantEmail) {
						localStorage.setItem('aws_builder_cert_issued_' + this.participantEmail.trim().toLowerCase(), 'true');
					}
					await this.renderAndDownloadCanvas();
					this.stage = 'success';
				} else {
					alert(data.message || 'Error generating certificate.');
				}
			} catch(e) {
				alert('Connection error. Please try again.');
			} finally {
				this.generating = false;
			}
		},

		async renderAndDownloadCanvas() {
			const templateImg = await new Promise((resolve) => {
				const img = new Image();
				img.crossOrigin = 'anonymous';
				img.onload = () => resolve(img);
				const templatePath = '<?php echo esc_url( get_template_directory_uri() . '/Certificate Template/General Certificate Template.png' ); ?>';
				img.src = templatePath + '?v=<?php echo time(); ?>';
			});

			const canvas = document.createElement('canvas');
			const ctx = canvas.getContext('2d');

			if (templateImg) {
				canvas.width = templateImg.naturalWidth || 2560;
				canvas.height = templateImg.naturalHeight || 1810;
				ctx.drawImage(templateImg, 0, 0, canvas.width, canvas.height);
			} else {
				canvas.width = 1920;
				canvas.height = 1357;
				ctx.fillStyle = '#0b0e17';
				ctx.fillRect(0, 0, canvas.width, canvas.height);
			}

			const w = canvas.width;
			const h = canvas.height;
			const scaleX = w / 1920;
			const scaleY = h / 1357;

			// 1. Top-Left Dynamic Building Blocks
			const purpleBoxX = 105 * scaleX;
			const purpleBoxY = 105 * scaleY;

			const topHeading = (this.issuedDetails && this.issuedDetails.cert_top_heading) ? this.issuedDetails.cert_top_heading : (this.selectedEvent && this.selectedEvent.cert_top_heading ? this.selectedEvent.cert_top_heading : '★ AWS CERTIFICATION SELF-ASSESSMENT 2026 ★');
			const mainHeading = (this.issuedDetails && this.issuedDetails.cert_main_heading) ? this.issuedDetails.cert_main_heading : (this.selectedEvent && this.selectedEvent.cert_main_heading ? this.selectedEvent.cert_main_heading : 'Certificate');
			const subHeading = (this.issuedDetails && this.issuedDetails.cert_sub_heading) ? this.issuedDetails.cert_sub_heading : (this.selectedEvent && this.selectedEvent.cert_sub_heading ? this.selectedEvent.cert_sub_heading : 'PASSED — AWS Certification Self-Assessment');
			const purpleFooter = (this.issuedDetails && this.issuedDetails.cert_purple_footer) ? this.issuedDetails.cert_purple_footer : (this.selectedEvent && this.selectedEvent.cert_purple_footer ? this.selectedEvent.cert_purple_footer : 'Organized by AWS Student Builder Group at Air University Islamabad');
			const certDesc = (this.issuedDetails && this.issuedDetails.cert_description) ? this.issuedDetails.cert_description : (this.selectedEvent && this.selectedEvent.cert_description ? this.selectedEvent.cert_description : 'For successfully completing the AWS Certification Self-Assessment');

			// Top Eyebrow Badge
			ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';
			if ('letterSpacing' in ctx) ctx.letterSpacing = (2 * scaleX) + 'px';
			ctx.font = `700 ${Math.round(15 * scaleX)}px "Segoe UI", -apple-system, sans-serif`;
			ctx.fillText(topHeading, purpleBoxX + (70 * scaleX), purpleBoxY + (115 * scaleY));
			if ('letterSpacing' in ctx) ctx.letterSpacing = '0px';

			// Main Heading
			ctx.fillStyle = '#ffffff';
			ctx.font = `bold ${Math.round(72 * scaleX)}px "Georgia", "Times New Roman", serif`;
			ctx.fillText(mainHeading, purpleBoxX + (70 * scaleX), purpleBoxY + (215 * scaleY));

			// Sub-Heading
			ctx.fillStyle = '#ffffff';
			ctx.font = `800 ${Math.round(24 * scaleX)}px "Segoe UI", sans-serif`;
			ctx.fillText(subHeading, purpleBoxX + (70 * scaleX), purpleBoxY + (305 * scaleY));

			// Accent Line
			ctx.strokeStyle = 'rgba(255, 255, 255, 0.35)';
			ctx.lineWidth = 2;
			ctx.beginPath();
			ctx.moveTo(purpleBoxX + (70 * scaleX), purpleBoxY + (345 * scaleY));
			ctx.lineTo(purpleBoxX + (620 * scaleX), purpleBoxY + (345 * scaleY));
			ctx.stroke();

			// Purple Section Footer Text
			ctx.fillStyle = '#f3e8ff';
			ctx.font = `500 ${Math.round(21 * scaleX)}px "Segoe UI", sans-serif`;
			ctx.fillText(purpleFooter, purpleBoxX + (70 * scaleX), purpleBoxY + (400 * scaleY));

			// 2. Bottom-Right Participant Block
			const infoX = 1125 * scaleX;
			const infoY = 685 * scaleY;
			const infoW = (1920 - 1125 - 105) * scaleX;

			ctx.fillStyle = '#cbd5e1';
			ctx.font = `500 ${Math.round(22 * scaleX)}px "Courier New", Courier, monospace`;
			ctx.textAlign = 'center';
			ctx.fillText('Proudly presented to', infoX + infoW / 2, infoY + (55 * scaleY));

			ctx.fillStyle = '#ffffff';
			ctx.font = `bold ${Math.round(54 * scaleX)}px "Georgia", "Times New Roman", serif`;
			ctx.fillText(this.participantName, infoX + infoW / 2, infoY + (145 * scaleY));

			const dateStr = new Date().toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' });

			ctx.fillStyle = '#94a3b8';
			ctx.font = `400 ${Math.round(18 * scaleX)}px "Segoe UI", sans-serif`;

			const wrapTextCenter = (context, text, x, y, maxWidth, lineHeight) => {
				const words = text.split(' ');
				let line = '';
				let currentY = y;
				for (let n = 0; n < words.length; n++) {
					const testLine = line + words[n] + ' ';
					const metrics = context.measureText(testLine);
					const testWidth = metrics.width;
					if (testWidth > maxWidth && n > 0) {
						context.fillText(line.trim(), x, currentY);
						line = words[n] + ' ';
						currentY += lineHeight;
					} else {
						line = testLine;
					}
				}
				context.fillText(line.trim(), x, currentY);
				return currentY;
			};

			const maxDescWidth = infoW - (60 * scaleX);
			const certDescY = infoY + (215 * scaleY);
			const finalY = wrapTextCenter(ctx, certDesc, infoX + infoW / 2, certDescY, maxDescWidth, 28 * scaleY);

			ctx.fillText(`(Code: ${this.verificationCode} • Issued: ${dateStr})`, infoX + infoW / 2, finalY + (40 * scaleY));

			// 3. Bottom Disclaimer inside Trophy Box
			const trophyBoxX = 105 * scaleX;
			const trophyBoxY = (105 + 520 + 40) * scaleY;
			const trophyBoxW = 960 * scaleX;
			const trophyBoxH = (1357 - 665 - 75 - 30) * scaleY;

			ctx.fillStyle = 'rgba(255, 255, 255, 0.45)';
			ctx.font = `400 ${Math.round(13 * scaleX)}px "Segoe UI", sans-serif`;
			ctx.textAlign = 'center';
			ctx.fillText('This is a community-issued self-assessment certificate and does not represent an official AWS certification.', trophyBoxX + trophyBoxW / 2, trophyBoxY + trophyBoxH - (25 * scaleY));

			ctx.textAlign = 'left';

			this.renderedCanvas = canvas;
			this.downloadCanvasPNG();
		},

		downloadCanvasPNG() {
			if (!this.renderedCanvas) return;
			this.renderedCanvas.toBlob((blob) => {
				const url = URL.createObjectURL(blob);
				const link = document.createElement('a');
				const safeName = this.participantName.replace(/[^a-zA-Z0-9]/g, '_');
				link.download = `AWS-Air-University-Certificate-${safeName}.png`;
				link.href = url;
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
				URL.revokeObjectURL(url);
			}, 'image/png');
		}
	}
}
</script>

<?php
get_footer();
