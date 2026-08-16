<?php
/**
 * Template Name: AWS Mock Exam Portal
 * 
 * Full-Screen Distraction-Free Pearson VUE Style Exam Simulator.
 * Powered by Alpine.js & Tailwind CSS.
 * 
 * @package AWS_SBG_Theme
 */

// Omit WordPress theme header & footer for 100% full-screen immersive exam environment
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="dark">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php wp_title( '|', true, 'right' ); ?> AWS Certification Mock Exam Portal</title>
	
	<!-- Google Fonts: Inter & Outfit & Great Vibes -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
	
	<!-- Tailwind CSS & Alpine.js -->
	<script src="https://cdn.tailwindcss.com?plugins=typography"></script>
	<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
	
	<script>
		tailwind.config = {
			darkMode: 'class',
			theme: {
				extend: {
					colors: {
						brand: '#ad5cff',
						'brand-dark': '#8b31ff',
						neon: '#00ef88',
						darkbg: '#07040d',
						cardbg: '#140c24',
						sidebarbg: '#0f091c'
					},
					fontFamily: {
						sans: ['Inter', 'sans-serif'],
						display: ['Outfit', 'sans-serif']
					}
				}
			}
		}
	</script>

	<style>
		body {
			background-color: #0c0814;
			background-image: 
				radial-gradient(at 50% 0%, rgba(147, 51, 234, 0.15) 0px, transparent 60%),
				radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.08) 0px, transparent 60%);
			background-attachment: fixed;
		}

		/* Anti-copy protection CSS */
		.aws-no-select {
			-webkit-touch-callout: none;
			-webkit-user-select: none;
			-khtml-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
		}

		/* Custom Scrollbars */
		::-webkit-scrollbar {
			width: 6px;
			height: 6px;
		}
		::-webkit-scrollbar-track {
			background: #0c0814;
		}
		::-webkit-scrollbar-thumb {
			background: #334155;
			border-radius: 9999px;
		}
		::-webkit-scrollbar-thumb:hover {
			background: #7c3aed;
		}

		@media (max-width: 767px) {
			.aws-desktop-only {
				display: none !important;
			}
			.aws-mobile-blocker {
				display: block !important;
			}
		}
		@media (min-width: 768px) {
			.aws-mobile-blocker {
				display: none !important;
			}
		}
	</style>

	<?php wp_head(); ?>
	<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
</head>
<body class="bg-[#0c0814] text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between" x-data="awsExamApp()">

	<!-- MAIN APP BODY CONTAINER -->
	<main class="flex-1 py-6 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto w-full flex flex-col justify-center">

		<!-- 0. INSTANT MOBILE DEVICE BLOCKER CARD -->
		<div class="aws-mobile-blocker max-w-md mx-auto w-full bg-white border border-amber-200 rounded-[28px] p-8 sm:p-10 shadow-2xl text-center space-y-6">
			<div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 mx-auto flex items-center justify-center border border-amber-100 shadow-sm">
				<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
			</div>

			<div>
				<span class="inline-block text-[10px] font-extrabold px-3 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-200 uppercase tracking-widest mb-3">Desktop / Laptop Required</span>
				<h2 class="font-display text-2xl font-extrabold text-slate-900">Mobile Access Disabled</h2>
				<p class="text-xs text-slate-600 mt-2 leading-relaxed">
					To maintain exam security, proctoring integrity, and mandatory full-screen mode, the AWS Certification Preparation Exam Portal is restricted to <strong>Desktop and Laptop computers only</strong>.
				</p>
			</div>

			<div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-700 space-y-2 text-left">
				<div class="font-bold text-purple-700">📌 How to take your exam:</div>
				<ol class="list-decimal list-inside text-slate-600 space-y-1">
					<li>Open a web browser on your PC or Laptop.</li>
					<li>Navigate to the Exam Portal URL.</li>
					<li>Enter your event access code to launch testing.</li>
				</ol>
			</div>

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="block w-full py-3.5 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 rounded-2xl font-bold text-xs transition-all">
				Return to Home Page
			</a>
		</div>

		<!-- DESKTOP ONLY EXAM CONTAINER -->
		<div class="aws-desktop-only">

		<!-- 1. ENTRY STAGE: ACCESS CODE ENTRY -->
		<div x-show="!isMobileDevice && currentStage === 'entry'" x-transition:enter="transition ease-out duration-300 transform opacity-0 scale-95" class="max-w-md mx-auto w-full bg-white border border-slate-200/90 rounded-[28px] p-8 sm:p-10 shadow-2xl shadow-purple-950/20 text-slate-800">
			
			<div class="text-center mb-8">
				<div class="w-16 h-16 rounded-2xl bg-purple-50 border border-purple-100 text-purple-600 mx-auto flex items-center justify-center mb-5 shadow-sm">
					<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"/></svg>
				</div>
				<h1 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Enter Access Code</h1>
				<p class="text-slate-500 text-xs sm:text-sm mt-2">Enter your access code to start your AWS Certification Preparation Exam</p>
			</div>

			<div x-show="errorMessage" class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-3" x-cloak>
				<svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
				<span x-text="errorMessage"></span>
			</div>

			<form @submit.prevent="verifyCode()" class="space-y-6">
				<div>
					<label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2 text-center">Event Access Code</label>
					<input type="text" x-model="inputCode" placeholder="AWS-ADSUZ" uppercase required class="w-full bg-slate-50 border-2 border-slate-200 focus:border-purple-600 focus:bg-white rounded-2xl px-4 py-4 text-2xl font-mono text-center tracking-[0.25em] text-slate-900 focus:outline-none focus:ring-4 focus:ring-purple-600/10 transition-all uppercase placeholder-slate-300 shadow-inner">
				</div>

				<button type="submit" :disabled="loading" class="w-full py-4 px-6 rounded-2xl font-bold text-base bg-purple-600 hover:bg-purple-700 active:scale-[0.99] text-white shadow-xl shadow-purple-600/25 transition-all flex items-center justify-center gap-2">
					<template x-if="loading">
						<svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
					</template>
					<span x-text="loading ? 'Verifying Credentials...' : 'Authenticate Access Code'"></span>
				</button>
			</form>

			<div class="mt-8 pt-6 border-t border-slate-100 text-center">
				<p class="text-[11px] text-slate-400">Access codes are issued by your AWS Student Builder Group community leader.</p>
			</div>
		</div>

		<!-- 1.5 SLOT WAITING STAGE: TIME SLOT COUNTDOWN -->
		<div x-show="currentStage === 'slot_waiting'" x-transition:enter="transition ease-out duration-300 transform opacity-0 scale-95" class="max-w-md mx-auto w-full bg-white border border-purple-200/90 rounded-[28px] p-8 sm:p-10 shadow-2xl shadow-purple-950/20 text-slate-800 text-center space-y-6" x-cloak>
			<div class="w-16 h-16 rounded-2xl bg-purple-50 border border-purple-100 text-purple-600 mx-auto flex items-center justify-center shadow-sm">
				<svg class="w-8 h-8 text-purple-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
			</div>

			<div>
				<span class="inline-block text-[10px] font-extrabold px-3 py-1 rounded-full bg-purple-100 text-purple-800 border border-purple-200 uppercase tracking-widest mb-3">Scheduled Exam Time Slot</span>
				<h2 class="font-display text-2xl font-extrabold text-slate-900" x-text="slotInfo ? slotInfo.slot_name : 'Upcoming Exam Slot'"></h2>
				<p class="text-xs text-slate-500 mt-2 font-medium" x-text="slotInfo ? (slotInfo.start_time_formatted + ' to ' + slotInfo.end_time_formatted) : ''"></p>
			</div>

			<!-- BIG LIVE COUNTDOWN BOX -->
			<div class="p-6 rounded-2xl bg-slate-900 text-white shadow-inner space-y-2">
				<div class="text-[10px] uppercase font-extrabold tracking-widest text-emerald-400">Exam Slot Opens In</div>
				<div class="font-mono text-3xl sm:text-4xl font-extrabold text-white tracking-wider" x-text="slotCountdown || '00m 00s'"></div>
				<div class="text-[10px] text-slate-400">System automatically unlocks when timer reaches zero</div>
			</div>


			<div class="flex items-center gap-3">
				<button @click="currentStage = 'entry'" class="w-1/3 py-3.5 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 rounded-2xl font-bold text-xs transition-all">
					Change Code
				</button>
				<button @click="verifyCode()" :disabled="loading" class="flex-1 py-3.5 bg-purple-600 hover:bg-purple-700 text-white rounded-2xl font-bold text-xs shadow-lg shadow-purple-600/20 transition-all flex items-center justify-center gap-2">
					<template x-if="loading">
						<svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
					</template>
					<span>Check Slot Status ➔</span>
				</button>
			</div>
		</div>

		<!-- 2. INSTRUCTIONS STAGE: BRIEFING & RULES -->
		<div x-show="currentStage === 'instructions'" x-transition:enter="transition ease-out duration-300" class="max-w-2xl mx-auto w-full bg-white border border-slate-200/90 rounded-[28px] p-8 sm:p-10 shadow-2xl shadow-purple-950/20 text-slate-800 space-y-7" x-cloak>
			
			<!-- PARTICIPANT HEADER CARD -->
			<div class="flex items-center gap-4 pb-6 border-b border-slate-100">
				<div class="w-14 h-14 rounded-2xl bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center shrink-0">
					<svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.307 4.491 4.491 0 01-1.307-3.497A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497a4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" /></svg>
				</div>
				<div>
					<div class="flex items-center gap-2 flex-wrap">
						<span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 border border-purple-200 uppercase">AWS Certification Prep</span>
						<template x-if="examDetails.exam_mode === 'test'">
							<span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300 uppercase">🧪 TEST MODE (10 Qs)</span>
						</template>
						<template x-if="examDetails.slot_name">
							<span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200" x-text="'⏱️ ' + examDetails.slot_name"></span>
						</template>
						<span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">VERIFIED PARTICIPANT</span>
					</div>
					<h2 class="font-display text-2xl font-extrabold text-slate-900 mt-1">Participant: <span class="text-purple-700" x-text="examDetails.member_name"></span></h2>
					<p class="text-xs text-slate-500 font-medium" x-text="examDetails.track_title"></p>
				</div>
			</div>

			<!-- SPECS GRID -->
			<div class="grid grid-cols-3 gap-4 text-center">
				<div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
					<div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Question Count</div>
					<div class="font-display text-2xl font-extrabold text-slate-900 mt-1" x-text="questions.length"></div>
				</div>
				<div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
					<div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Per Question Time</div>
					<div class="font-display text-2xl font-extrabold text-emerald-600 mt-1" x-text="(examDetails.per_question_seconds || 40) + 's'"></div>
				</div>
				<div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
					<div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Pass Mark</div>
					<div class="font-display text-2xl font-extrabold text-purple-600 mt-1">700 / 1000</div>
				</div>
			</div>

			<!-- SECURITY & INTEGRITY RULES BOX -->
			<div class="p-6 rounded-2xl bg-purple-50/50 border border-purple-100 space-y-3.5 text-xs text-slate-800 leading-relaxed">
				<h3 class="font-display text-xs font-extrabold text-purple-900 uppercase tracking-wider mb-2 flex items-center gap-2">
					<svg class="w-4 h-4 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
					Exam Integrity Protocol & Guidelines
				</h3>
				<ul class="space-y-2.5 list-none text-slate-800 font-medium p-0 m-0">
					<li class="flex items-start gap-2.5">
						<span class="text-base leading-none">⏱️</span>
						<div><strong class="text-slate-950 font-bold">Per-Question Countdown:</strong> You have <strong class="text-emerald-800 font-extrabold" x-text="(examDetails.per_question_seconds || 40) + ' seconds'">40 seconds</strong> per question.</div>
					</li>
					<li class="flex items-start gap-2.5">
						<span class="text-base leading-none">⏩</span>
						<div><strong class="text-slate-950 font-bold">Auto-Advance:</strong> When the countdown reaches 0s, the system automatically records your selection and advances to the next question.</div>
					</li>
					<li class="flex items-start gap-2.5">
						<span class="text-base leading-none">🔒</span>
						<div><strong class="text-slate-950 font-bold">Single-Pass Mode:</strong> Backtracking is locked for exam security. You cannot return to previous questions once advanced.</div>
					</li>
					<li class="flex items-start gap-2.5">
						<span class="text-base leading-none">🛡️</span>
						<div><strong class="text-slate-950 font-bold">Anti-Cheating Protection:</strong> Fullscreen is required. Exiting fullscreen gives a 5-second return window before auto-submission.</div>
					</li>
					<li class="flex items-start gap-2.5">
						<span class="text-base leading-none">👁️</span>
						<div><strong class="text-slate-950 font-bold">Active Presence:</strong> Screen automatically blurs after 3 seconds of idle time. Move your mouse or touch the screen to keep it clear.</div>
					</li>
				</ul>
			</div>

			<!-- INSPIRATIONAL PRE-EXAM MOTIVATION CARD -->
			<div class="p-5 rounded-2xl bg-gradient-to-r from-purple-50 via-indigo-50/60 to-emerald-50 border border-purple-200/80 shadow-sm text-slate-800 space-y-2">
				<div class="flex items-center gap-2 text-sm font-black text-purple-950">
					<span>🌟</span>
					<span>A Note Before You Begin — Believe in Yourself!</span>
				</div>
				<p class="text-xs sm:text-[13px] leading-relaxed text-slate-700 font-medium">
					Attempt this exam with confidence, focus, and honesty. Trust the knowledge you have gained through your own effort. A score is only one part of your learning journey—what truly matters is staying true to yourself and growing from every attempt.
				</p>
			</div>

			<!-- ACTION BUTTONS -->
			<div class="pt-2 flex items-center gap-4">
				<button @click="currentStage = 'entry'" class="px-6 py-4 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 rounded-2xl font-bold text-xs transition-all">Cancel</button>
				<button @click="startExamNow()" class="flex-1 py-4 px-6 rounded-2xl font-extrabold text-base bg-emerald-600 hover:bg-emerald-700 text-white shadow-xl shadow-emerald-600/25 hover:scale-[1.01] active:scale-[0.99] transition-all flex items-center justify-center">
					Start Exam
				</button>
			</div>
		</div>
			</div>
		</div>

		<!-- 3. ACTIVE EXAM SIMULATOR STAGE -->
		<div x-show="currentStage === 'active_exam'" x-transition:enter="transition ease-out duration-300" class="aws-no-select select-none space-y-6" x-cloak>
			
			<!-- TOP EXAM STATUS BAR -->
			<div class="bg-white border border-slate-200/90 rounded-2xl p-4 sm:p-5 flex flex-wrap items-center justify-between gap-4 shadow-xl text-slate-900">
				<div class="flex items-center gap-3">
					<div class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></div>
					<div>
						<div class="flex items-center gap-2 flex-wrap">
							<span class="text-[10px] font-bold px-2 py-0.5 rounded bg-purple-100 text-purple-800 uppercase border border-purple-200">AWS Certification Prep</span>
							<template x-if="examDetails.exam_mode === 'test'">
								<span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-amber-100 text-amber-900 uppercase border border-amber-300">🧪 TEST MODE</span>
							</template>
							<template x-if="examDetails.slot_name">
								<span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800 border border-blue-200" x-text="'⏱️ ' + examDetails.slot_name"></span>
							</template>
							<span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200 flex items-center gap-1">
								<span>👁️</span> <span>Blurs on 3s idle (move mouse to view)</span>
							</span>
						</div>
						<h2 class="text-base font-bold text-slate-900 mt-1">Participant: <span class="text-purple-700" x-text="examDetails.member_name"></span></h2>
					</div>
				</div>

				<div class="flex items-center gap-4">
					<!-- Per-Question Timer Widget -->
					<div class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded-2xl border border-slate-200 shadow-inner" :class="qTimerSeconds <= 10 ? 'border-red-300 bg-red-50 text-red-600 animate-pulse' : 'text-slate-800'">
						<svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
						<span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Question Time</span>
						<span class="font-mono font-extrabold text-xl text-purple-700" x-text="qTimerSeconds + 's'"></span>
					</div>

					<!-- Grid Drawer Toggle Button -->
					<button @click="showNavDrawer = !showNavDrawer" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 rounded-2xl border border-slate-200 text-xs font-bold text-slate-700 flex items-center gap-2 transition-all">
						<svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
						<span>Question Grid (<span x-text="questions.length > 0 ? (currentIndex + 1) : 0"></span>/<span x-text="questions.length"></span>)</span>
					</button>

					<!-- Exit & Submit Button -->
					<button @click="confirmSubmitModal = true" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-bold text-xs shadow-lg shadow-red-600/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-1.5 shrink-0">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
						Exit & Submit
					</button>
				</div>
			</div>

			<!-- PER-QUESTION TIMED PROGRESS BAR -->
			<div class="w-full h-2.5 bg-slate-200 rounded-full overflow-hidden border border-slate-300 shadow-inner">
				<div class="h-full bg-gradient-to-r from-purple-600 via-indigo-600 to-emerald-500 transition-all duration-1000 ease-linear shadow-sm" :style="'width: ' + ((qTimerSeconds / examDetails.per_question_seconds) * 100) + '%'"></div>
			</div>

			<!-- MAIN QUESTION DISPLAY GRID -->
			<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
				
				<!-- QUESTION CARD CONTAINER (3 COLS) -->
				<div class="lg:col-span-3 space-y-6">
					
					<div id="aws-question-card" class="bg-white border border-slate-200/90 rounded-[28px] p-6 sm:p-10 shadow-xl relative min-h-[380px] overflow-hidden text-slate-800 transition-all duration-300" @mousemove="resetActivityTimer()" @touchstart="resetActivityTimer()">
						
						<!-- FORENSIC ANTI-PHOTO DYNAMIC WATERMARK -->
						<div class="absolute inset-0 pointer-events-none z-0 flex flex-col justify-around p-6 opacity-[0.06] select-none font-mono text-[11px] text-slate-950 uppercase transform -rotate-12 space-y-8">
							<div class="flex justify-between">
								<span x-text="'CANDIDATE: ' + examDetails.member_name + ' | CODE: ' + inputCode"></span>
								<span>AWS AIR UNIVERSITY | DO NOT PHOTOGRAPH</span>
							</div>
							<div class="flex justify-between">
								<span x-text="'CONFIDENTIAL — ' + examDetails.member_email"></span>
								<span x-text="'PROCTORED LIVE SESSION | ' + examDetails.exam_track"></span>
							</div>
							<div class="flex justify-between">
								<span>AWS STUDENT BUILDER GROUP | STRICTLY MONITORED</span>
								<span x-text="'CANDIDATE: ' + examDetails.member_name"></span>
							</div>
						</div>

						<!-- 3-SECOND INACTIVITY ANTI-CAMERA BLUR OVERLAY -->
						<div x-show="isQuestionBlurred" x-transition.opacity.duration.150ms class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-slate-900/10 backdrop-blur-md rounded-[28px] p-6 text-center cursor-pointer select-none" @click="resetActivityTimer()">
							<div class="bg-white/95 border border-purple-200 shadow-2xl px-5 py-3 rounded-2xl flex items-center gap-3 text-slate-900 animate-pulse">
								<span class="text-xl">👁️</span>
								<div class="text-left">
									<div class="text-xs font-black uppercase tracking-wider text-purple-900">Active Presence Required</div>
									<div class="text-xs text-slate-600 font-semibold">Move mouse or touch screen to reveal question</div>
								</div>
							</div>
						</div>

						<template x-if="questions.length > 0 && currentQuestion">
							<div class="relative z-10 space-y-6 transition-all duration-200" :class="isQuestionBlurred ? 'filter blur-[10px] select-none opacity-15 pointer-events-none' : ''">
								<!-- Question Header -->
								<div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
									<div class="flex flex-wrap items-center gap-2.5">
										<span class="text-xs font-extrabold px-3 py-1 bg-purple-50 border border-purple-200 text-purple-700 rounded-xl" x-text="'Question ' + (currentIndex + 1) + ' of ' + questions.length"></span>
										<span class="text-xs font-bold px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl" x-text="currentQuestion.domain"></span>
									</div>

									<span class="text-[11px] font-mono font-bold text-amber-700 px-3 py-1 rounded-xl bg-amber-50 border border-amber-200 flex items-center gap-1.5 shrink-0">
										<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15l-3-3m0 0l3-3m-3 3h12"/></svg>
										Single-Pass Question
									</span>
								</div>

								<!-- Question Content -->
								<div class="text-lg sm:text-xl font-display font-bold text-slate-900 leading-snug tracking-tight" x-html="currentQuestion.question"></div>

								<!-- Instruction Badge -->
								<div>
									<span class="inline-block text-xs font-bold px-3.5 py-1.5 rounded-xl bg-purple-50 text-purple-800 border border-purple-200" x-text="currentQuestion.is_multi ? 'Select ' + currentQuestion.multi_count + ' correct options:' : 'Select ONE correct option:'"></span>
								</div>

								<!-- Options List (Text-Only Tiles — Anti-AI Shortcut) -->
								<div class="space-y-3.5 pt-2">
									<template x-for="opt in currentQuestion.options" :key="opt.key">
										<label @click="selectOption(currentQuestion.id, opt.key, currentQuestion.is_multi, currentQuestion.multi_count)" class="flex items-center justify-between gap-4 p-4 sm:p-5 rounded-2xl border transition-all duration-200 cursor-pointer group" :class="isOptionSelected(currentQuestion.id, opt.key) ? 'bg-purple-50/90 border-2 border-purple-600 text-purple-950 font-bold shadow-md shadow-purple-500/10' : 'bg-slate-50 border-slate-200/90 hover:border-purple-300 hover:bg-purple-50/40 text-slate-800 hover:translate-x-1'">
											<div class="flex items-center gap-3.5">
												<div class="shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all" :class="isOptionSelected(currentQuestion.id, opt.key) ? 'border-purple-600 bg-purple-600 shadow-sm' : 'border-slate-300 bg-white group-hover:border-purple-400'">
													<div x-show="isOptionSelected(currentQuestion.id, opt.key)" class="w-2.5 h-2.5 rounded-full bg-white"></div>
												</div>
												<div class="text-sm sm:text-base leading-relaxed font-medium" x-text="opt.text"></div>
											</div>
											
											<!-- Selection Pill -->
											<div x-show="isOptionSelected(currentQuestion.id, opt.key)" class="shrink-0 text-[11px] px-2.5 py-1 rounded-lg bg-purple-600 text-white font-extrabold tracking-wider uppercase shadow-sm">
												Selected
											</div>
										</label>
									</template>
								</div>
							</div>
						</template>

						<template x-if="questions.length === 0">
							<div class="text-center py-16">
								<div class="w-12 h-12 border-4 border-purple-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
								<p class="text-slate-500 text-sm">Loading questions from bank...</p>
							</div>
						</template>

					</div>

					<!-- ACTION BUTTONS -->
					<div class="flex items-center justify-between gap-4">
						<button disabled class="px-5 py-3.5 bg-slate-100 opacity-60 cursor-not-allowed border border-slate-200 rounded-2xl font-bold text-xs text-slate-400 flex items-center gap-2">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15l-3-3m0 0l3-3m-3 3h12"/></svg>
							Backtracking Disabled
						</button>

						<button @click="advanceToNextQuestion()" class="px-8 py-4 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-sm rounded-2xl flex items-center gap-2 shadow-xl shadow-purple-600/25 hover:scale-[1.02] active:scale-[0.98] transition-all">
							<span x-text="currentIndex === questions.length - 1 ? 'Submit Final Exam' : 'Save & Next Question'"></span>
							<svg class="w-4 h-4 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
						</button>
					</div>

				</div>

				<!-- QUESTION PALETTE NAVIGATOR (1 COL) -->
				<div class="lg:col-span-1" x-show="showNavDrawer" x-transition>
					<div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xl sticky top-24 text-slate-800">
						<div class="flex items-center justify-between mb-4">
							<h3 class="font-display text-xs font-extrabold text-slate-900 uppercase tracking-widest">Question Grid</h3>
							<span class="text-[10px] font-mono text-purple-700 font-bold" x-text="countAnswered() + ' / ' + questions.length + ' Done'"></span>
						</div>

						<!-- Legend -->
						<div class="grid grid-cols-2 gap-2 text-center text-[10px] font-bold mb-5">
							<div class="p-2 rounded-xl bg-slate-100 text-slate-600 border border-slate-200">Remaining</div>
							<div class="p-2 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-200">Completed</div>
						</div>

						<!-- Grid -->
						<div class="grid grid-cols-5 gap-2.5 max-h-80 overflow-y-auto pr-1">
							<template x-for="(q, idx) in questions" :key="q.id">
								<div class="py-3 rounded-xl text-xs font-bold transition-all flex flex-col items-center justify-center relative select-none shadow-sm" :class="getGridButtonClass(q.id, idx)">
									<span x-text="idx + 1"></span>
								</div>
							</template>
						</div>
					</div>
				</div>

			</div>
		</div>

		<!-- 4. RESULTS & SCORECARD STAGE -->
		<div x-show="currentStage === 'results'" x-transition:enter="transition ease-out duration-300" class="space-y-8 max-w-5xl mx-auto w-full" x-cloak>
			
			<!-- SCORE HERO BANNER -->
			<div class="bg-white border border-slate-200/90 rounded-[28px] p-8 sm:p-12 text-center relative overflow-hidden shadow-2xl text-slate-900" :class="results.passed ? 'border-emerald-300 shadow-emerald-600/10' : 'border-amber-300 shadow-amber-500/10'">
				
				<!-- Top Status Pill -->
				<div class="inline-flex items-center justify-center px-5 py-2 rounded-full text-xs font-extrabold uppercase tracking-widest mb-6 shadow-sm" :class="results.passed ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-900 border border-amber-300'" x-text="results.passed ? 'Passed' : 'Below 700'"></div>

				<!-- Candidate Name & Event Organization Header -->
				<div class="max-w-2xl mx-auto mb-6 space-y-2">
					<div class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Official Exam Scorecard</div>
					<h2 class="font-display text-2xl sm:text-3xl font-black text-slate-900 tracking-tight" x-text="results.member_name"></h2>
					<div class="inline-flex items-center flex-wrap justify-center gap-1.5 px-4 py-2 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 text-xs font-medium">
						<span class="font-bold text-slate-800">AWS Certification Preparation Exam</span>
						<span class="text-slate-300 hidden sm:inline">•</span>
						<span>Organized by <strong class="text-purple-700 font-bold">AWS Student Builder Group</strong> at <strong class="text-slate-800">Air University Islamabad</strong></span>
					</div>
				</div>

				<h1 class="font-display text-4xl sm:text-6xl font-black text-slate-900 mb-3 tracking-tight">Scaled Score: <span :class="results.passed ? 'text-emerald-600' : 'text-amber-600'" x-text="results.score"></span> <span class="text-2xl font-normal text-slate-400">/ 1000</span></h1>
				<p class="text-slate-500 text-sm mb-6">Passing Benchmark: <strong class="text-slate-800">700 / 1000 (70%)</strong></p>

				<template x-if="results.passed">
					<div class="mb-8">
						<button @click="downloadCertificate()" class="py-4 px-8 rounded-2xl font-extrabold text-sm bg-purple-600 hover:bg-purple-700 text-white shadow-xl shadow-purple-600/25 hover:scale-[1.02] active:scale-[0.98] transition-all inline-flex items-center gap-2.5">
							<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
							<span>📜 Download Verified Certificate</span>
						</button>
					</div>
				</template>

				<template x-if="!results.passed">
					<div class="mb-8 max-w-xl mx-auto p-4 sm:p-5 rounded-2xl bg-amber-50/90 border border-amber-200 text-amber-900 text-center space-y-1.5 shadow-sm">
						<div class="text-base sm:text-lg font-black flex items-center justify-center gap-1.5 text-amber-950">
							<span>🌟</span>
							<span>Best of Luck for Next Time!</span>
						</div>
						<p class="text-xs sm:text-sm font-medium leading-relaxed text-amber-800">
							Awesome effort on the exam! More community learning sessions, challenges, and voucher opportunities are coming soon. Review the explanations below to keep leveling up!
						</p>
					</div>
				</template>

				<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-3xl mx-auto pt-8 border-t border-slate-100 text-center">
					<div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
						<div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Correct Answers</div>
						<div class="font-display text-2xl font-extrabold text-slate-900 mt-1" x-text="results.correct_count + ' / ' + results.total_questions"></div>
					</div>
					<div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
						<div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Accuracy Rate</div>
						<div class="font-display text-2xl font-extrabold text-emerald-600 mt-1" x-text="results.percentage + '%'"></div>
					</div>
					<div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
						<div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Time</div>
						<div class="font-display text-2xl font-extrabold text-slate-900 mt-1" x-text="formatTimer(results.time_spent_seconds)"></div>
					</div>
					<div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
						<div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Security Audit</div>
						<div class="font-display text-xl font-extrabold mt-1" :class="results.tab_switch_count > 0 ? 'text-red-600' : 'text-emerald-600'" x-text="results.tab_switch_count > 0 ? results.tab_switch_count + ' Warnings' : 'Clean'"></div>
					</div>
				</div>
			</div>

			<!-- DOMAIN BREAKDOWN -->
			<div class="bg-white border border-slate-200/90 rounded-[28px] p-8 shadow-xl text-slate-900 space-y-6">
				<h3 class="font-display text-xl font-bold text-slate-900">Domain Performance Breakdown</h3>
				<div class="space-y-5">
					<template x-for="(dData, dName) in results.domain_breakdown" :key="dName">
						<div>
							<div class="flex justify-between text-xs font-semibold mb-2">
								<span class="text-slate-700 font-bold" x-text="dName"></span>
								<span class="text-purple-700 font-mono font-bold" x-text="dData.correct + '/' + dData.total + ' (' + dData.percentage + '%)'"></span>
							</div>
							<div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
								<div class="h-full bg-gradient-to-r from-purple-600 to-emerald-500 transition-all duration-700" :style="'width: ' + dData.percentage + '%'"></div>
							</div>
						</div>
					</template>
				</div>
			</div>

			<!-- DETAILED QUESTION REVIEW -->
			<div class="bg-white border border-slate-200/90 rounded-[28px] p-8 shadow-xl text-slate-900 space-y-6">
				<h3 class="font-display text-xl font-bold text-slate-900">Answer Key & Explanations</h3>
				
				<div class="space-y-6">
					<template x-for="(res, idx) in results.item_results" :key="res.question_id">
						<div class="p-6 rounded-2xl border bg-slate-50/50" :class="res.is_correct ? 'border-emerald-200' : (res.user_selected && res.user_selected.length > 0 ? 'border-red-200' : 'border-slate-200')">
							<div class="flex items-center justify-between gap-4 mb-4">
								<span class="text-xs font-extrabold text-slate-500" x-text="'Question ' + (idx + 1) + ' — ' + res.domain"></span>
								<span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase" 
									:class="res.is_correct ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : (res.user_selected && res.user_selected.length > 0 ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-slate-100 text-slate-700 border border-slate-300')" 
									x-text="res.is_correct ? 'CORRECT (+1)' : (res.user_selected && res.user_selected.length > 0 ? 'INCORRECT (0)' : 'NOT ATTEMPTED (0)')">
								</span>
							</div>

							<div class="text-slate-900 font-semibold text-base mb-4" x-html="res.question"></div>

							<div class="text-xs sm:text-sm space-y-2 mb-4 p-4 rounded-xl bg-white border border-slate-200 font-sans">
								<div class="flex items-start gap-2">
									<span class="font-bold text-slate-500 min-w-[105px] shrink-0 text-xs uppercase tracking-wider">Your Selection:</span>
									<div :class="res.is_correct ? 'text-emerald-700 font-bold' : (res.user_selected && res.user_selected.length > 0 ? 'text-red-600 font-bold' : 'text-slate-500 italic')">
										<template x-if="res.user_selected_texts && res.user_selected_texts.length > 0">
											<span x-text="(res.is_correct ? '✅ ' : '❌ ') + res.user_selected_texts.join(', ')"></span>
										</template>
										<template x-if="!res.user_selected_texts || res.user_selected_texts.length === 0">
											<span x-text="(res.user_selected && res.user_selected.length > 0) ? ((res.is_correct ? '✅ ' : '❌ ') + res.user_selected.join(', ')) : '⚠️ Not Attempted (Timed Out)'"></span>
										</template>
									</div>
								</div>
								<div class="flex items-start gap-2 pt-2 border-t border-slate-100">
									<span class="font-bold text-slate-500 min-w-[105px] shrink-0 text-xs uppercase tracking-wider">Correct Answer:</span>
									<div class="text-emerald-700 font-bold" x-text="'✅ ' + ((res.correct_answers_texts && res.correct_answers_texts.length > 0) ? res.correct_answers_texts.join(', ') : res.correct_answers.join(', '))"></div>
								</div>
							</div>

							<div class="p-4 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 leading-relaxed">
								<strong class="text-purple-700 block mb-1 font-bold">Explanation & Rationale:</strong>
								<p x-text="res.explanation"></p>
								<template x-if="res.ref_url">
									<a :href="res.ref_url" target="_blank" class="inline-flex items-center gap-1.5 text-purple-700 hover:underline font-bold mt-3">
										Read Official AWS Documentation ↗
									</a>
								</template>
							</div>
						</div>
					</template>
				</div>
			</div>

		</div>

		<!-- VOUCHER SUCCESS CELEBRATION MODAL -->
		<div x-show="showSuccessVoucherModal" x-transition:enter="transition ease-out duration-300 transform opacity-0 scale-90" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md" x-cloak>
			<div class="bg-white border-2 border-emerald-400 rounded-[28px] p-8 sm:p-10 max-w-lg w-full text-center shadow-2xl relative overflow-hidden space-y-6 text-slate-900">
				
				<!-- Trophy Icon Badge -->
				<div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-emerald-100 text-emerald-600 border border-emerald-200 mx-auto shadow-sm text-3xl animate-bounce">
					🏆
				</div>

				<div>
					<h2 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900">Congratulations, <span class="text-emerald-600" x-text="results.member_name"></span>!</h2>
					<p class="text-xs sm:text-sm text-slate-600 font-medium mt-1.5 leading-relaxed">
						You passed the <strong class="text-slate-900">AWS Certification Preparation Exam</strong> with a score of <strong class="text-emerald-600 font-extrabold" x-text="results.score + ' / 1000'"></strong>!
					</p>
				</div>

				<!-- ENHANCED OFFICIAL AWS VOUCHER TICKET PASS -->
				<div class="p-6 rounded-3xl bg-gradient-to-br from-emerald-50/90 via-white to-purple-50/90 border-2 border-dashed border-emerald-400 relative shadow-lg text-left overflow-hidden space-y-4">
					
					<!-- Left & Right Decorative Ticket Stub Notches -->
					<div class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-slate-900/80 rounded-full border border-emerald-400"></div>
					<div class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-slate-900/80 rounded-full border border-emerald-400"></div>

					<!-- Ticket Body -->
					<div class="px-1 text-center sm:text-left">
						<h3 class="font-display text-lg sm:text-xl font-extrabold text-slate-900 leading-snug">
							You are eligible for AWS Certification Exam Voucher
						</h3>
					</div>

					<!-- Ticket Value Stub Box -->
					<div class="p-3.5 rounded-2xl bg-purple-600 text-white flex flex-wrap items-center justify-between gap-2 shadow-md">
						<div class="flex items-center gap-2">
							<svg class="w-5 h-5 text-emerald-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
							<span class="text-xs font-extrabold uppercase tracking-wider">Voucher Value:</span>
						</div>
						<span class="px-3 py-1 rounded-xl bg-white/20 text-white text-xs font-extrabold font-mono tracking-wider">$100 – $150 USD</span>
					</div>
				</div>

				<div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 leading-relaxed font-medium">
					You will be contacted soon with voucher claim instructions
				</div>

				<button @click="downloadCertificate()" :disabled="isGeneratingCert" class="w-full py-4 px-6 rounded-2xl font-bold text-sm bg-purple-600 hover:bg-purple-700 text-white shadow-xl shadow-purple-600/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed">
					<svg x-show="!isGeneratingCert" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
					<svg x-show="isGeneratingCert" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
					<span x-text="isGeneratingCert ? 'Generating...' : '📜 Download Verified Certificate'"></span>
				</button>

				<button @click="showSuccessVoucherModal = false" class="w-full py-3.5 px-6 rounded-2xl font-bold text-xs bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 transition-all">
					Awesome! View Detailed Scorecard & Explanations ➔
				</button>
			</div>
		</div>

		<!-- FULLSCREEN REQUIRED MODAL -->
		<div x-show="showFullscreenModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md" x-cloak>
			<div class="bg-white border border-amber-300 rounded-[28px] p-8 max-w-md w-full text-center shadow-2xl space-y-4 text-slate-900">
				<div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 mx-auto flex items-center justify-center border border-amber-200">
					<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-2V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
				</div>
				<h3 class="font-display text-xl font-bold text-slate-900">Fullscreen Mode Required</h3>
				<p class="text-xs text-slate-600 leading-relaxed">
					⚠️ <strong>Security Notice:</strong> Fullscreen mode is required. Please return to fullscreen within <strong class="text-amber-700 font-bold" x-text="fullscreenCountdown + 's'"></strong> or your exam will be automatically submitted!
				</p>
				<button @click="requestFullscreen()" class="w-full py-4 bg-purple-600 hover:bg-purple-700 text-white rounded-2xl font-bold text-sm shadow-xl shadow-purple-600/20 flex items-center justify-center gap-2">
					<span>Return to Fullscreen (<span x-text="fullscreenCountdown + 's'"></span>) 📺</span>
				</button>
			</div>
		</div>

		<!-- SECURITY VIOLATION WARNING MODAL -->
		<div x-show="showSecurityModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md" x-cloak>
			<div class="bg-white border border-red-300 rounded-[28px] p-8 max-w-md w-full text-center shadow-2xl text-slate-900">
				<div class="w-16 h-16 rounded-2xl bg-red-50 text-red-600 mx-auto flex items-center justify-center mb-4 border border-red-200">
					<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
				</div>
				<h3 class="font-display text-xl font-bold text-slate-900 mb-2">Tab Switch Detected</h3>
				<p class="text-xs text-slate-600 mb-6 leading-relaxed">
					Leaving the exam portal window is strictly prohibited. Warning <strong class="text-red-600 font-bold" x-text="tabSwitchCount"></strong> of 2. Exceeding 2 warnings will result in immediate exam termination!
				</p>
				<button @click="showSecurityModal = false" class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-bold text-sm shadow-lg shadow-red-600/20">Return to Exam</button>
			</div>
		</div>

		<!-- SUBMIT CONFIRMATION MODAL -->
		<div x-show="confirmSubmitModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md" x-cloak>
			<div class="bg-white border border-slate-200 rounded-[28px] p-8 max-w-md w-full text-center shadow-2xl text-slate-900">
				<h3 class="font-display text-xl font-bold text-slate-900 mb-2">Submit Exam Attempt?</h3>
				<p class="text-xs text-slate-600 mb-6 leading-relaxed">
					Are you sure you want to terminate your exam session? Unanswered items will be marked incorrect.
				</p>
				<div class="grid grid-cols-2 gap-3">
					<button @click="confirmSubmitModal = false" class="py-3.5 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 rounded-2xl font-bold text-xs">Continue Testing</button>
					<button @click="submitExam(false)" class="py-3.5 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-bold text-xs shadow-lg shadow-red-600/20">Yes, Submit Now</button>
				</div>
			</div>
		</div>

		</div> <!-- END DESKTOP ONLY CONTAINER -->

	</main>

	<!-- PORTAL FOOTER -->
	<footer class="py-4 text-center border-t border-gray-800/60 bg-[#050309]">
		<p class="text-[11px] text-gray-500">© <?php echo date('Y'); ?> AWS Student Builder Group Air University. All Rights Reserved.</p>
	</footer>

<script>
function awsExamApp() {
	return {
		currentStage: 'entry', // entry, slot_waiting, instructions, active_exam, results
		inputCode: '',
		sessionToken: '',
		loading: false,
		isGeneratingCert: false,
		errorMessage: '',

		// Slot State
		slotInfo: null,
		slotCountdown: '',
		slotCountdownInterval: null,
		slotRemainingSeconds: 0,

		examDetails: {},
		questions: [],
		currentIndex: 0,
		answers: {},

		// Timers
		totalTimeSpentSeconds: 0,
		totalTimeSpentMs: 0,
		examStartTimeMs: null,
		qTimerSeconds: 60,
		qTimerInterval: null,
		totalTimerInterval: null,

		tabSwitchCount: 0,
		showSecurityModal: false,
		showFullscreenModal: false,
		showSuccessVoucherModal: false,
		confirmSubmitModal: false,
		isMobileDevice: false,
		isSubmitting: false,
		fullscreenCountdown: 5,
		fullscreenTimerInterval: null,

		// 3-Second Active Presence Anti-Capture Blur
		isQuestionBlurred: false,
		idleBlurTimer: null,

		resetActivityTimer() {
			this.isQuestionBlurred = false;
			if (this.idleBlurTimer) {
				clearTimeout(this.idleBlurTimer);
				this.idleBlurTimer = null;
			}
			if (this.currentStage === 'active_exam') {
				this.idleBlurTimer = setTimeout(() => {
					if (this.currentStage === 'active_exam') {
						this.isQuestionBlurred = true;
					}
				}, 3000); // 3 seconds idle threshold
			}
		},

		get currentQuestion() {
			return (this.questions && this.questions.length > 0) ? this.questions[this.currentIndex] : null;
		},

		checkDeviceType() {
			const isMobileUA = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
			const isSmallScreen = window.innerWidth < 768;
			this.isMobileDevice = isMobileUA || isSmallScreen;
		},

		countAnswered() {
			let count = 0;
			if (!this.answers) return 0;
			for (let qId in this.answers) {
				if (this.answers[qId] && this.answers[qId].length > 0) count++;
			}
			return count;
		},

		init() {
			// Active Presence / Anti-Capture Motion Listeners
			const onUserPresence = () => {
				if (this.currentStage === 'active_exam') {
					this.resetActivityTimer();
				}
			};
			window.addEventListener('mousemove', onUserPresence, { passive: true });
			window.addEventListener('touchstart', onUserPresence, { passive: true });
			window.addEventListener('touchmove', onUserPresence, { passive: true });
			window.addEventListener('scroll', onUserPresence, { passive: true });
			window.addEventListener('keydown', onUserPresence, { passive: true });

			// Anti-copy protection
			document.addEventListener('contextmenu', e => {
				if (this.currentStage === 'active_exam') e.preventDefault();
			});
			document.addEventListener('copy', e => {
				if (this.currentStage === 'active_exam') e.preventDefault();
			});

			// Windows PrintScreen & Snipping Tool Shortcut Interception
			document.addEventListener('keyup', e => {
				if (this.currentStage === 'active_exam') {
					if (e.key === 'PrintScreen' || e.keyCode === 44) {
						this.wipeClipboardAndWarn();
					}
				}
			});

			document.addEventListener('keydown', e => {
				if (this.currentStage === 'active_exam') {
					// Block Ctrl+C, Cmd+C, Ctrl+U, Ctrl+A, F12
					if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'u' || e.key === 'a' || e.key === 's' || e.key === 'p')) {
						e.preventDefault();
						this.wipeClipboardAndWarn();
					}
					// Block Windows Snipping Tool shortcut (Win+Shift+S)
					if (e.key === 'PrintScreen' || (e.metaKey && e.shiftKey && (e.key === 'S' || e.key === 's'))) {
						this.wipeClipboardAndWarn();
					}
					if (e.key === 'F12') e.preventDefault();
				}
			});

			// Blur question container immediately when window loses focus (e.g. Snipping Tool launch)
			window.addEventListener('blur', () => {
				if (this.currentStage === 'active_exam' && !this.isSubmitting) {
					const card = document.getElementById('aws-question-card');
					if (card) card.style.filter = 'blur(45px)';
					this.handleTabSwitch();
				}
			});

			window.addEventListener('focus', () => {
				if (this.currentStage === 'active_exam') {
					setTimeout(() => {
						const card = document.getElementById('aws-question-card');
						if (card) card.style.filter = 'none';
					}, 400);
				}
			});

			// Page visibility change detection
			document.addEventListener('visibilitychange', () => {
				if (this.currentStage === 'active_exam' && !this.isSubmitting && document.hidden) {
					const card = document.getElementById('aws-question-card');
					if (card) card.style.filter = 'blur(45px)';
				}
			});

			// Fullscreen change detection
			const handleFullscreenChange = () => {
				if (this.currentStage === 'active_exam' && !this.isSubmitting) {
					const isFull = !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);
					if (!isFull) {
						this.showFullscreenModal = true;
						if (!this.fullscreenTimerInterval) {
							this.fullscreenCountdown = 5;
							this.fullscreenTimerInterval = setInterval(() => {
								this.fullscreenCountdown--;
								if (this.fullscreenCountdown <= 0) {
									clearInterval(this.fullscreenTimerInterval);
									this.fullscreenTimerInterval = null;
									this.showFullscreenModal = false;
									this.submitExam(true);
								}
							}, 1000);
						}
					} else {
						if (this.fullscreenTimerInterval) {
							clearInterval(this.fullscreenTimerInterval);
							this.fullscreenTimerInterval = null;
						}
						this.showFullscreenModal = false;
					}
				} else {
					if (this.fullscreenTimerInterval) {
						clearInterval(this.fullscreenTimerInterval);
						this.fullscreenTimerInterval = null;
					}
					this.showFullscreenModal = false;
				}
			};

			document.addEventListener('fullscreenchange', handleFullscreenChange);
			document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
			document.addEventListener('mozfullscreenchange', handleFullscreenChange);
			document.addEventListener('MSFullscreenChange', handleFullscreenChange);
		},

		requestFullscreen() {
			const elem = document.documentElement;
			const clearFsTimer = () => {
				if (this.fullscreenTimerInterval) {
					clearInterval(this.fullscreenTimerInterval);
					this.fullscreenTimerInterval = null;
				}
				this.showFullscreenModal = false;
			};

			if (elem.requestFullscreen) {
				elem.requestFullscreen().then(clearFsTimer).catch(clearFsTimer);
			} else if (elem.webkitRequestFullscreen) {
				elem.webkitRequestFullscreen();
				clearFsTimer();
			} else if (elem.msRequestFullscreen) {
				elem.msRequestFullscreen();
				clearFsTimer();
			} else {
				clearFsTimer();
			}
		},

		exitFullscreen() {
			try {
				if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
					if (document.exitFullscreen) {
						document.exitFullscreen().catch(() => {});
					} else if (document.webkitExitFullscreen) {
						document.webkitExitFullscreen();
					} else if (document.mozCancelFullScreen) {
						document.mozCancelFullScreen();
					} else if (document.msExitFullscreen) {
						document.msExitFullscreen();
					}
				}
			} catch(err) {}
		},

		wipeClipboardAndWarn() {
			try {
				navigator.clipboard.writeText('⚠️ Screenshots are prohibited during the AWS Mock Exam session! Candidate: ' + (this.examDetails.member_name || 'Candidate'));
			} catch(err) {}
			this.showSecurityModal = true;
		},

		startSlotCountdown(seconds) {
			if (this.slotCountdownInterval) clearInterval(this.slotCountdownInterval);
			this.slotRemainingSeconds = Math.max(0, parseInt(seconds) || 0);
			this.updateSlotCountdownDisplay();

			this.slotCountdownInterval = setInterval(() => {
				if (this.slotRemainingSeconds > 0) {
					this.slotRemainingSeconds--;
					this.updateSlotCountdownDisplay();
				} else {
					clearInterval(this.slotCountdownInterval);
					this.slotCountdown = '00m 00s';
					// Auto re-verify when countdown reaches 0
					setTimeout(() => {
						this.verifyCode();
					}, 1000);
				}
			}, 1000);
		},

		updateSlotCountdownDisplay() {
			const sec = this.slotRemainingSeconds;
			const h = Math.floor(sec / 3600);
			const m = Math.floor((sec % 3600) / 60);
			const s = sec % 60;
			this.slotCountdown = (h > 0 ? (String(h).padStart(2, '0') + 'h ') : '') + String(m).padStart(2, '0') + 'm ' + String(s).padStart(2, '0') + 's';
		},

		async verifyCode() {
			this.loading = true;
			this.errorMessage = '';

			let deviceFp = 'Unknown';
			try {
				const isMobile = ('maxTouchPoints' in navigator && navigator.maxTouchPoints > 0) || (window.screen.height > window.screen.width);
				deviceFp = `Agent: ${navigator.userAgent.substring(0, 80)} | Touch: ${navigator.maxTouchPoints || 0} | Screen: ${window.screen.width}x${window.screen.height} | Mobile: ${isMobile ? 'Yes' : 'No'}`;
			} catch(e) {}

			const cleanCode = (this.inputCode || '').trim().toUpperCase();
			const cachedToken = localStorage.getItem('aws_exam_st_' + cleanCode) || '';

			try {
				const response = await fetch('/wp-json/aws-sbg/v1/verify-code', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ 
						code: cleanCode,
						session_token: cachedToken,
						device_fingerprint: deviceFp
					})
				});

				const data = await response.json();

				// Check if slot has not opened yet
				if (data && data.status === 'slot_not_started') {
					this.slotInfo = data;
					this.currentStage = 'slot_waiting';
					this.startSlotCountdown(data.remaining_seconds);
					this.loading = false;
					return;
				}

				if (!response.ok || !data.success) {
					this.errorMessage = data.message || 'Invalid access code.';
					this.loading = false;
					return;
				}

				if (this.slotCountdownInterval) {
					clearInterval(this.slotCountdownInterval);
				}

				this.sessionToken = data.session_token;
				if (data.session_token && cleanCode) {
					try {
						localStorage.setItem('aws_exam_st_' + cleanCode, data.session_token);
					} catch(e) {}
				}

				this.examDetails = data;
				this.questions = data.questions || [];
				if (!this.examDetails.per_question_seconds || parseInt(this.examDetails.per_question_seconds) === 60 || parseInt(this.examDetails.per_question_seconds) === 45 || parseInt(this.examDetails.per_question_seconds) === 35 || parseInt(this.examDetails.per_question_seconds) === 30) {
					this.examDetails.per_question_seconds = 40;
				}
				this.qTimerSeconds = parseInt(this.examDetails.per_question_seconds) || 40;
				
				this.currentStage = 'instructions';

			} catch (err) {
				this.errorMessage = 'Network error verifying code. Please try again.';
			} finally {
				this.loading = false;
			}
		},

		startExamNow() {
			this.requestFullscreen();
			this.currentStage = 'active_exam';
			this.currentIndex = 0;
			this.examStartTimeMs = Date.now();
			this.totalTimeSpentSeconds = 0;
			this.totalTimeSpentMs = 0;
			this.startTotalTimer();
			this.resetQuestionTimer();
			this.resetActivityTimer();
		},

		startTotalTimer() {
			if (this.totalTimerInterval) clearInterval(this.totalTimerInterval);
			this.totalTimerInterval = setInterval(() => {
				if (this.examStartTimeMs) {
					const elapsedMs = Date.now() - this.examStartTimeMs;
					this.totalTimeSpentMs = elapsedMs;
					this.totalTimeSpentSeconds = parseFloat((elapsedMs / 1000).toFixed(3));
				} else {
					this.totalTimeSpentSeconds++;
				}
			}, 1000);
		},

		resetQuestionTimer() {
			if (this.qTimerInterval) clearInterval(this.qTimerInterval);
			const allowedSec = (this.examDetails && this.examDetails.per_question_seconds && parseInt(this.examDetails.per_question_seconds) !== 60 && parseInt(this.examDetails.per_question_seconds) !== 45 && parseInt(this.examDetails.per_question_seconds) !== 35 && parseInt(this.examDetails.per_question_seconds) !== 30) ? parseInt(this.examDetails.per_question_seconds) : 40;
			this.qTimerSeconds = allowedSec;

			this.qTimerInterval = setInterval(() => {
				if (this.qTimerSeconds > 0) {
					this.qTimerSeconds--;
				} else {
					clearInterval(this.qTimerInterval);
					this.autoTimeoutQuestion();
				}
			}, 1000);
		},

		autoTimeoutQuestion() {
			this.resetActivityTimer();
			if (this.currentIndex < this.questions.length - 1) {
				this.currentIndex++;
				this.resetQuestionTimer();
			} else {
				this.submitExam(false);
			}
		},

		advanceToNextQuestion() {
			if (this.qTimerInterval) clearInterval(this.qTimerInterval);
			this.resetActivityTimer();

			if (this.currentIndex < this.questions.length - 1) {
				this.currentIndex++;
				this.resetQuestionTimer();
			} else {
				this.submitExam(false);
			}
		},

		formatTimer(seconds) {
			const total = parseFloat(seconds) || 0;
			const m = Math.floor(total / 60);
			const s = Math.floor(total % 60);
			const ms = Math.floor(Math.round((total % 1) * 1000));
			if (ms > 0) {
				return `${m.toString().padStart(2, '0')}m ${s.toString().padStart(2, '0')}.${ms.toString().padStart(3, '0')}s`;
			}
			return `${m.toString().padStart(2, '0')}m ${s.toString().padStart(2, '0')}s`;
		},

		selectOption(qId, key, isMulti, multiCount) {
			if (!this.answers[qId]) {
				this.answers[qId] = [];
			}

			if (isMulti) {
				const idx = this.answers[qId].indexOf(key);
				if (idx > -1) {
					this.answers[qId].splice(idx, 1);
				} else {
					if (this.answers[qId].length < multiCount) {
						this.answers[qId].push(key);
					} else {
						this.answers[qId] = [key];
					}
				}
			} else {
				this.answers[qId] = [key];
			}
		},

		isOptionSelected(qId, key) {
			return this.answers[qId] && this.answers[qId].includes(key);
		},

		getGridButtonClass(qId, idx) {
			let isCurrent = (this.currentIndex === idx);
			let isCompleted = (idx < this.currentIndex);
			let isAnswered = (this.answers[qId] && this.answers[qId].length > 0);

			if (isCurrent) {
				return 'bg-[#ad5cff] text-white ring-2 ring-[#00ef88] scale-105 shadow-lg shadow-[#ad5cff]/40 font-black';
			}
			if (isCompleted) {
				return isAnswered 
					? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 font-bold'
					: 'bg-red-500/20 text-red-400 border border-red-500/30';
			}
			return 'bg-[#07040d] text-gray-600 border border-gray-800/80 opacity-50';
		},

		handleTabSwitch() {
			if (this.isSubmitting) return;
			this.tabSwitchCount++;
			if (this.tabSwitchCount >= 2) {
				this.submitExam(true);
			} else {
				this.showSecurityModal = true;
			}
		},

		async submitExam(isViolation) {
			this.isSubmitting = true;
			if (this.idleBlurTimer) clearTimeout(this.idleBlurTimer);
			this.isQuestionBlurred = false;
			if (this.qTimerInterval) clearInterval(this.qTimerInterval);
			if (this.totalTimerInterval) clearInterval(this.totalTimerInterval);

			this.confirmSubmitModal = false;
			this.showSecurityModal = false;
			this.showFullscreenModal = false;
			if (this.fullscreenTimerInterval) {
				clearInterval(this.fullscreenTimerInterval);
				this.fullscreenTimerInterval = null;
			}
			this.exitFullscreen();

			let totalMs = 0;
			if (this.examStartTimeMs) {
				totalMs = Math.max(0, Date.now() - this.examStartTimeMs);
			} else {
				totalMs = Math.round((this.totalTimeSpentSeconds || 0) * 1000);
			}
			this.totalTimeSpentMs = totalMs;
			const totalSec = parseFloat((totalMs / 1000).toFixed(3));
			this.totalTimeSpentSeconds = totalSec;

			let deviceFp = 'Unknown';
			try {
				const isMobile = ('maxTouchPoints' in navigator && navigator.maxTouchPoints > 0) || (window.screen.height > window.screen.width);
				deviceFp = `Touch: ${navigator.maxTouchPoints || 0} | Screen: ${window.screen.width}x${window.screen.height} | PixelRatio: ${window.devicePixelRatio || 1} | HardwareMobile: ${isMobile ? 'Yes' : 'No'}`;
			} catch(e) {}

			try {
				const response = await fetch('/wp-json/aws-sbg/v1/exam/submit', {
					method: 'POST',
					keepalive: true,
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({
						session_token: this.sessionToken,
						answers: this.answers,
						time_spent_seconds: totalSec,
						time_spent_ms: totalMs,
						tab_switch_count: this.tabSwitchCount,
						violation_submitted: isViolation,
						device_fingerprint: deviceFp
					})
				});

				const data = await response.json();

				if (data.success) {
					this.results = data;
					this.currentStage = 'results';
					this.exitFullscreen();
					if (this.inputCode) {
						try {
							localStorage.removeItem('aws_exam_st_' + (this.inputCode || '').trim().toUpperCase());
						} catch(e) {}
					}
					if (data.passed && !data.violation_submitted) {
						this.showSuccessVoucherModal = true;
						this.fireConfettiCelebration();
					}
					this.fetchLeaderboard(this.examDetails.exam_track);
				}
			} catch (err) {
				this.exitFullscreen();
				alert('Error submitting exam. Please check connection.');
			}
		},

		fireConfettiCelebration() {
			if (typeof confetti === 'function') {
				// Center burst
				confetti({
					particleCount: 120,
					spread: 80,
					origin: { y: 0.6 }
				});
				// Fireworks left and right side bursts
				setTimeout(() => {
					confetti({ particleCount: 70, angle: 60, spread: 55, origin: { x: 0.1, y: 0.5 } });
					confetti({ particleCount: 70, angle: 120, spread: 55, origin: { x: 0.9, y: 0.5 } });
				}, 250);
			}
		},

		async fetchLeaderboard(track) {
			try {
				const res = await fetch(`/wp-json/aws-sbg/v1/leaderboard?track=${track}`);
				const data = await res.json();
				if (data.success) {
					this.leaderboard = data.leaderboard;
				}
			} catch (e) {}
		},

		async downloadCertificate() {
			if (this.isGeneratingCert) return;
			this.isGeneratingCert = true;

			const templateImg = await new Promise((resolve) => {
				const img = new Image();
				img.crossOrigin = 'anonymous';
				img.onload = () => resolve(img);
				const templatePath = '<?php echo esc_url( get_template_directory_uri() . '/Certificate Template/AWS Certification Preparation Exam Pass Certificate Template.png' ); ?>';
				img.src = templatePath + '?v=<?php echo time(); ?>';
			});

			const canvas = document.createElement('canvas');
			const ctx = canvas.getContext('2d');
			
			if (templateImg) {
				canvas.width = templateImg.naturalWidth || 2560;
				canvas.height = templateImg.naturalHeight || 1810;
				ctx.drawImage(templateImg, 0, 0, canvas.width, canvas.height);
			} else {
				// Fallback blank background if template fails to load
				canvas.width = 1920;
				canvas.height = 1357;
				ctx.fillStyle = '#0b0e17';
				ctx.fillRect(0, 0, canvas.width, canvas.height);
			}
			
			const w = canvas.width;
			const h = canvas.height;
			
			const scaleX = w / 1920;
			const scaleY = h / 1357;
			
			// 1. High-Contrast Top-Left Green Box Typography (Prestigious Certificate Design)
			const greenBoxX = 105 * scaleX;
			const greenBoxY = 105 * scaleY;
			
			// Eyebrow Badge
			ctx.fillStyle = 'rgba(4, 32, 23, 0.9)';
			if ('letterSpacing' in ctx) ctx.letterSpacing = (2 * scaleX) + 'px';
			ctx.font = `700 ${Math.round(15 * scaleX)}px "Segoe UI", -apple-system, sans-serif`;
			ctx.fillText('★ AWS CERTIFICATION CHALLENGE 2026 ★', greenBoxX + (70 * scaleX), greenBoxY + (115 * scaleY));
			if ('letterSpacing' in ctx) ctx.letterSpacing = '0px';
			
			// Main Grand Headline: Certificate
			ctx.fillStyle = '#042017';
			ctx.font = `bold ${Math.round(72 * scaleX)}px "Georgia", "Times New Roman", serif`;
			ctx.fillText('Certificate', greenBoxX + (70 * scaleX), greenBoxY + (215 * scaleY));
			
			// Sub-headline: PASSED - AWS Certification Preparation Exam
			ctx.fillStyle = '#042017';
			ctx.font = `800 ${Math.round(24 * scaleX)}px "Segoe UI", sans-serif`;
			ctx.fillText('PASSED - AWS Certification Preparation Exam', greenBoxX + (70 * scaleX), greenBoxY + (305 * scaleY));
			
			// Elegant Horizontal Accent Line
			ctx.strokeStyle = 'rgba(4, 32, 23, 0.35)';
			ctx.lineWidth = Math.max(1, Math.round(2 * scaleX));
			ctx.beginPath();
			ctx.moveTo(greenBoxX + (70 * scaleX), greenBoxY + (345 * scaleY));
			ctx.lineTo(greenBoxX + (620 * scaleX), greenBoxY + (345 * scaleY));
			ctx.stroke();
			
			// Organization Footer Label
			ctx.fillStyle = '#064e3b';
			ctx.font = `600 ${Math.round(20 * scaleX)}px "Segoe UI", sans-serif`;
			ctx.fillText('Organized by AWS Student Builder Group at Air University Islamabad', greenBoxX + (70 * scaleX), greenBoxY + (400 * scaleY));
			
			// 2. Bottom-Right Participant Info Block (Centered in Dark Panel above Signature)
			const infoX = 1125 * scaleX;
			const infoY = 685 * scaleY;
			const infoW = (1920 - 1125 - 105) * scaleX;
			
			ctx.fillStyle = '#cbd5e1';
			ctx.font = `500 ${Math.round(22 * scaleX)}px "Courier New", Courier, monospace`;
			ctx.textAlign = 'center';
			ctx.fillText('Proudly presented to', infoX + infoW / 2, infoY + (55 * scaleY));
			
			// Dynamic Participant Name
			const nameStr = (this.results && this.results.member_name) ? this.results.member_name : (this.examDetails && this.examDetails.member_name ? this.examDetails.member_name : 'Participant');
			ctx.fillStyle = '#ffffff';
			ctx.font = `bold ${Math.round(54 * scaleX)}px "Georgia", "Times New Roman", serif`;
			ctx.fillText(nameStr, infoX + infoW / 2, infoY + (145 * scaleY));
			
			// Dynamic Score, Preparation Exam Statement & Date
			const scoreVal = (this.results && this.results.score) ? this.results.score : 850;
			const userCode = (this.results && this.results.access_code) ? this.results.access_code : (this.examDetails && this.examDetails.access_code ? this.examDetails.access_code : (this.inputCode ? this.inputCode.toUpperCase() : 'AWS-8K9P'));
			const dateStr = new Date().toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' });

			ctx.fillStyle = '#94a3b8';
			ctx.font = `400 ${Math.round(18 * scaleX)}px "Segoe UI", sans-serif`;
			ctx.fillText('For successfully passing the AWS Cloud Certification Preparation Exam', infoX + infoW / 2, infoY + (215 * scaleY));
			ctx.fillText(`with a scaled score of ${scoreVal} / 1000. (Code: ${userCode} • Issued: ${dateStr})`, infoX + infoW / 2, infoY + (250 * scaleY));

			// 3. Small Disclaimer at Bottom of Trophy Box (Bottom-Left Container)
			const trophyBoxX = 105 * scaleX;
			const trophyBoxY = (105 + 520 + 40) * scaleY;
			const trophyBoxW = 960 * scaleX;
			const trophyBoxH = (1357 - 665 - 75 - 30) * scaleY;

			ctx.fillStyle = 'rgba(255, 255, 255, 0.45)';
			ctx.font = `400 ${Math.round(13 * scaleX)}px "Segoe UI", sans-serif`;
			ctx.textAlign = 'center';
			ctx.fillText('This is a community-issued preparation exam certificate and does not represent an official AWS certification.', trophyBoxX + trophyBoxW / 2, trophyBoxY + trophyBoxH - (25 * scaleY));
			
			ctx.textAlign = 'left'; // Reset alignment
			
			// Download PNG (100% Client-Side - 0 Server Load - Using Blob for reliable large file download)
			canvas.toBlob((blob) => {
				const url = URL.createObjectURL(blob);
				const link = document.createElement('a');
				const safeName = nameStr.replace(/[^a-zA-Z0-9]/g, '_');
				link.download = `AWS-Air-University-Certificate-${safeName}.png`;
				link.href = url;
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
				URL.revokeObjectURL(url);
				this.isGeneratingCert = false;
			}, 'image/png');
		}
	}
}
</script>

<?php wp_footer(); ?>
</body>
</html>
