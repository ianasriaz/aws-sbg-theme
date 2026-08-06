<?php
/**
 * Template Name: AirCert Page
 */
get_header();
?>

<!-- Load Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
	tailwind.config = {
		theme: {
			extend: {
				colors: {
					brand: '#ad5cff',
					'supporting-green': '#00ef88',
					dark: '#080808',
					surface: '#121212',
				},
				fontFamily: {
					sans: ['Inter', 'system-ui', 'sans-serif'],
				},
				animation: {
					'float': 'float 8s ease-in-out infinite',
					'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
				},
				keyframes: {
					float: {
						'0%, 100%': { transform: 'translateY(0)' },
						'50%': { transform: 'translateY(-12px)' },
					},
					fadeInUp: {
						'0%': { opacity: '0', transform: 'translateY(20px)' },
						'100%': { opacity: '1', transform: 'translateY(0)' },
					}
				}
			}
		}
	}
</script>
<style>
	@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
	body { font-family: 'Inter', sans-serif; background-color: #080808; color: #f5f5f5; }
	
	/* The "Sweet Spot" Glass Card */
	.glass-card {
		background: rgba(255, 255, 255, 0.02);
		backdrop-filter: blur(16px);
		-webkit-backdrop-filter: blur(16px);
		border: 1px solid rgba(255, 255, 255, 0.08);
		box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
	}
	
	.glass-card:hover {
		background: rgba(255, 255, 255, 0.04);
		border-color: rgba(173, 92, 255, 0.3); /* Subtle brand border on hover */
		transform: translateY(-4px);
		box-shadow: 0 10px 40px rgba(173, 92, 255, 0.1);
	}
	
	/* Engaging Buttons */
	.btn-primary {
		background: linear-gradient(135deg, #ad5cff 0%, #7a22d9 100%);
		color: #ffffff;
		border: 1px solid rgba(255,255,255,0.1);
		box-shadow: 0 0 20px rgba(173,92,255,0.3);
	}
	.btn-primary:hover {
		box-shadow: 0 0 30px rgba(173,92,255,0.6);
		transform: scale(1.02);
	}
	
	.btn-secondary {
		background: rgba(255,255,255,0.05);
		color: #ffffff;
		border: 1px solid rgba(255,255,255,0.15);
	}
	.btn-secondary:hover {
		background: rgba(255,255,255,0.1);
		border-color: rgba(255,255,255,0.3);
	}
	
	.text-gradient {
		background: linear-gradient(to right, #ad5cff, #d194ff);
		-webkit-background-clip: text;
		-webkit-text-fill-color: transparent;
	}
	
	/* Dot grid background */
	.bg-grid {
		background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
		background-size: 30px 30px;
	}
</style>

<main class="min-h-screen relative overflow-hidden selection:bg-brand/30 selection:text-white">
	
	<!-- Engaging Background Elements -->
	<div class="absolute inset-0 bg-grid opacity-20 pointer-events-none z-0"></div>
	<div class="absolute top-[-10%] left-[-10%] w-[600px] h-[600px] bg-brand/10 rounded-full blur-[120px] pointer-events-none z-0"></div>
	<!-- 1. Hero Section -->
	<section class="pt-8 md:pt-24 pb-16 md:pb-20 px-4 md:px-6 lg:px-8 max-w-6xl mx-auto relative z-10 animate-fade-in-up">
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
			
			<!-- Left Column: Content -->
			<div class="text-left order-2 lg:order-1">
				
				<h1 class="text-3xl sm:text-4xl md:text-6xl font-black tracking-tight mb-2 leading-tight">
					AWS <span class="text-gradient font-extrabold">AIRCERT 1.0</span>
				</h1>
				<h2 class="text-xl sm:text-2xl font-bold text-white mb-4 md:mb-6">From Preparation to Certification</h2>
				
				<p class="text-base sm:text-lg md:text-xl text-gray-400 mb-8 md:mb-10 font-normal leading-relaxed max-w-xl pr-4 text-justify">
					AIRCERT is an AWS Certification Preparation Bootcamp organized by the AWS Student Builder Group at Air University Islamabad. Led by experienced AWS professionals, the program combines expert-led sessions, practical learning, and a supervised mock exam to help participants prepare for AWS Certification. Participants who score 70% or above in the mock exam may become eligible to receive an AWS Certification Exam Voucher, subject to AWS program guidelines and voucher availability.
				</p>
				
				<div class="flex flex-col sm:flex-row gap-4 justify-start items-center sm:items-start">
					<a href="#track1" class="btn-primary px-8 py-4 rounded-xl font-semibold transition-all duration-300 w-full sm:w-auto text-center">
						Explore Preparation Round
					</a>
					<span class="btn-secondary px-8 py-4 rounded-xl font-semibold opacity-60 cursor-not-allowed w-full sm:w-auto text-center">
						Registrations Temporarily Closed
					</span>
				</div>
			</div>

			<!-- Right Column: Visual Element -->
			<div class="relative w-full h-[300px] sm:h-[400px] md:h-[450px] order-1 lg:order-2 mb-8 lg:mb-0">
				<!-- Abstract Glowing Cloud -->
				<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[250px] md:w-[350px] h-[250px] md:h-[350px] bg-brand/20 rounded-full blur-[80px] z-0"></div>
				
				<!-- Floating Images -->
				<div class="absolute top-0 md:top-8 left-0 w-[70%] z-20 rounded-2xl overflow-hidden shadow-2xl border border-white/10 bg-[#111] animate-float">
					<img src="https://awscloudclubau.com/wp-content/uploads/2026/07/Event-Participant-Card.png" alt="ID Card" class="w-full h-auto object-cover">
				</div>
				<div class="absolute bottom-0 md:bottom-8 right-0 w-[70%] z-10 rounded-2xl overflow-hidden shadow-2xl border border-white/10 bg-[#111] animate-float" style="animation-delay: 2s;">
					<img src="https://awscloudclubau.com/wp-content/uploads/2026/07/xlarge_100591191-e858d8acf63ec3f822e959d6b1c38268.png" alt="Swags" class="w-full h-auto object-cover opacity-90">
				</div>
			</div>

		</div>
	</section>

	<!-- 1.5 Why Get Certified -->
	<section class="py-16 md:py-24 relative z-10">
		<div class="max-w-6xl mx-auto px-4 md:px-6 lg:px-8">
			<div class="text-center mb-10 md:mb-16">
				<h2 class="text-2xl sm:text-3xl md:text-4xl font-bold tracking-tight mb-4 uppercase">Why Get Certified?</h2>
				<p class="text-gray-400 max-w-xl mx-auto text-sm md:text-base px-2">An AWS Certification is a globally recognized credential that instantly makes your resume stand out to top tech employers.</p>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
				<!-- Card 1 -->
				<div class="glass-card p-6 md:p-8 rounded-2xl transition-all duration-300 group">
					<div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-brand/10 flex items-center justify-center text-brand mb-4 md:mb-6 group-hover:scale-110 transition-transform">
						<svg class="w-5 h-5 md:w-6 md:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
					</div>
					<h3 class="text-2xl md:text-3xl font-bold text-white mb-2">80% <span class="text-[10px] md:text-sm font-semibold text-gray-500 tracking-wider">HIGHER DEMAND</span></h3>
					<p class="text-gray-400 text-sm leading-relaxed">Top-tier employers actively seek certified professionals to manage critical cloud infrastructure.</p>
				</div>
				<!-- Card 2 -->
				<div class="glass-card p-6 md:p-8 rounded-2xl transition-all duration-300 group">
					<div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-supporting-green/10 flex items-center justify-center text-supporting-green mb-4 md:mb-6 group-hover:scale-110 transition-transform">
						<svg class="w-5 h-5 md:w-6 md:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
					</div>
					<h3 class="text-2xl md:text-3xl font-bold text-white mb-2">Global <span class="text-[10px] md:text-sm font-semibold text-gray-500 tracking-wider">VALIDATION</span></h3>
					<p class="text-gray-400 text-sm leading-relaxed">Prove your architectural skills with the most respected and recognized credential in the industry.</p>
				</div>
				<!-- Card 3 -->
				<div class="glass-card p-6 md:p-8 rounded-2xl transition-all duration-300 group">
					<div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500 mb-4 md:mb-6 group-hover:scale-110 transition-transform">
						<svg class="w-5 h-5 md:w-6 md:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
					</div>
					<h3 class="text-2xl md:text-3xl font-bold text-white mb-2">Top 3 <span class="text-[10px] md:text-sm font-semibold text-gray-500 tracking-wider">PAYING CERTS</span></h3>
					<p class="text-gray-400 text-sm leading-relaxed">AWS certifications consistently rank among the highest-paying technical qualifications globally.</p>
				</div>
			</div>
		</div>
	</section>

	<!-- 2. Track 1: Online Masterclasses -->
	<section id="track1" class="py-16 md:py-32 relative z-10 bg-surface/50 border-y border-white/5">
		<div class="max-w-4xl mx-auto px-4 md:px-6 lg:px-8">
			<div class="text-center mb-16">
				<div class="inline-block bg-[#ad5cff]/20 text-[#ad5cff] text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-4">Preparation Round</div>
				<h2 class="text-3xl sm:text-4xl md:text-5xl font-bold tracking-tight mb-4 uppercase">Preparation Sessions</h2>
				<p class="text-gray-400 text-sm md:text-base px-2 max-w-2xl mx-auto">Join our online masterclasses led by global cloud experts.</p>
			</div>

			<div class="space-y-6">
				
				<!-- Session 1 -->
				<div class="glass-card rounded-2xl overflow-hidden flex flex-col md:flex-row group transition-all duration-300 hover:shadow-[0_10px_40px_rgba(173,92,255,0.15)] hover:-translate-y-1 hover:border-brand/50 cursor-default">
					
					<!-- Column 1: Speaker -->
					<div class="w-full md:w-[25%] relative bg-gray-900 shrink-0 h-[250px] md:h-auto">
						<img src="https://awscloudclubau.com/wp-content/uploads/2026/07/Dr.-Frank.webp" alt="Dr. Frank Spiess" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-90 group-hover:opacity-100 object-top md:object-center">
						<div class="absolute top-4 left-4 bg-black/40 backdrop-blur-md border border-white/10 text-white text-[10px] md:text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow-lg flex items-center gap-1.5 z-10">
							<span class="w-1.5 h-1.5 rounded-full bg-[#00ef88] animate-pulse"></span>
							Virtual
						</div>
						<!-- Gradient overlay for logo -->
						<div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent z-0"></div>
						<div class="absolute bottom-5 left-6 z-10 flex flex-col gap-1">
							<span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Works At</span>
							<img src="https://awscloudclubau.com/wp-content/uploads/2026/07/pngwing.com_.png" alt="Amazon Web Services" class="h-6 w-auto object-contain brightness-0 invert opacity-90">
						</div>
					</div>

					<!-- Column 2: Date & Time -->
					<div class="w-full md:w-[20%] bg-brand/10 p-5 md:p-8 flex flex-row md:flex-col justify-between items-center md:items-start relative border-b md:border-b-0 md:border-r border-white/10 group-hover:bg-brand/20 transition-colors duration-300 shrink-0">
						<div class="z-10">
							<h3 class="text-brand font-black text-lg md:text-xl uppercase tracking-wider">Session 1</h3>
							<p class="text-gray-400 font-medium text-xs mt-1">5:00 - 6:45 PM</p>
						</div>
						<div class="z-10 mt-0 md:mt-12 text-right md:text-left">
							<h4 class="text-white font-black text-2xl md:text-4xl leading-tight flex gap-1.5 md:block">
								<span>AUG</span>
								<span>02</span>
							</h4>
						</div>
						<!-- Glowing orb accent -->
						<div class="absolute top-0 right-0 w-32 h-32 bg-brand/20 blur-[50px] rounded-full pointer-events-none"></div>
					</div>
					
					<!-- Column 3: Details & CTA -->
					<div class="w-full md:w-[55%] p-6 md:p-8 flex flex-col justify-center relative bg-white/[0.01]">
						<div class="flex flex-col gap-1 mb-6">
							<h3 class="text-2xl md:text-3xl font-bold text-white uppercase tracking-wide">DR. FRANK SPIESS</h3>
							<p class="text-brand font-medium text-sm flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-0">
								<span>Senior Technical Trainer</span>
								<span class="text-gray-600 hidden sm:inline px-2">|</span>
								<span>13x AWS Certified</span>
							</p>
						</div>
						<ul class="space-y-3 mb-6">
							<li class="flex items-center gap-3 text-gray-300 font-medium text-sm">
								<div class="w-1.5 h-1.5 rounded-full bg-brand shrink-0"></div>
								AWS Fundamentals
							</li>
							<li class="flex items-center gap-3 text-gray-300 font-medium text-sm">
								<div class="w-1.5 h-1.5 rounded-full bg-brand shrink-0"></div>
								AWS Certification Masterclass
							</li>
							<li class="flex items-start gap-3 text-[#ff9900] font-medium text-[13px] mt-4 pt-4 border-t border-white/5 leading-relaxed">
								<svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
								<span>Each participant will receive an <strong>AWS E-Certificate of Participation</strong>. One random participant will receive an <strong>AWS Swag Kit</strong> (AWS Bottle OR T-Shirt + Notepad).</span>
							</li>
						</ul>
						<div class="mt-auto">
							<a href="https://www.meetup.com/aws-sbg-at-air-univ-islamabad/events/315832073/" target="_blank" class="inline-flex items-center justify-center gap-2 bg-[#ad5cff] hover:bg-[#9b42f5] text-white px-6 py-3.5 rounded-xl font-bold transition-all text-sm shadow-[0_0_15px_rgba(173,92,255,0.3)] hover:shadow-[0_0_25px_rgba(173,92,255,0.5)] w-full sm:w-auto">
								<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M3.25 4A2.25 2.25 0 001 6.25v7.5A2.25 2.25 0 003.25 16h7.5A2.25 2.25 0 0013 13.75v-7.5A2.25 2.25 0 0010.75 4h-7.5zM19 4.75a.75.75 0 00-1.28-.53l-3 3a.75.75 0 00-.22.53v4.5c0 .199.079.39.22.53l3 3a.75.75 0 001.28-.53V4.75z"></path></svg>
								Reserve Your Virtual Seat
							</a>
						</div>
					</div>
				</div>

				<!-- Session 2 -->
				<div class="glass-card rounded-2xl overflow-hidden flex flex-col md:flex-row group transition-all duration-300 hover:shadow-[0_10px_40px_rgba(173,92,255,0.15)] hover:-translate-y-1 hover:border-brand/50 cursor-default">
					
					<!-- Column 1: Speaker -->
					<div class="w-full md:w-[25%] relative bg-gray-900 shrink-0 h-[250px] md:h-auto">
						<img src="https://awscloudclubau.com/wp-content/uploads/2026/07/Awais-Kamran.webp" alt="Awais Kamran" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-90 group-hover:opacity-100 object-top md:object-center">
						<div class="absolute top-4 left-4 bg-black/40 backdrop-blur-md border border-white/10 text-white text-[10px] md:text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow-lg flex items-center gap-1.5 z-10">
							<span class="w-1.5 h-1.5 rounded-full bg-[#00ef88] animate-pulse"></span>
							Virtual
						</div>
						<div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent z-0"></div>
						<div class="absolute bottom-5 left-6 z-10 flex flex-col gap-1">
							<span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Works At</span>
							<img src="https://awscloudclubau.com/wp-content/uploads/2026/07/KodeKloud-Logo.png" alt="KodeKloud" class="h-8 w-auto object-contain brightness-0 invert opacity-90">
						</div>
					</div>

					<!-- Column 2: Date & Time -->
					<div class="w-full md:w-[20%] bg-brand/10 p-5 md:p-8 flex flex-row md:flex-col justify-between items-center md:items-start relative border-b md:border-b-0 md:border-r border-white/10 group-hover:bg-brand/20 transition-colors duration-300 shrink-0">
						<div class="z-10">
							<h3 class="text-brand font-black text-lg md:text-xl uppercase tracking-wider">Session 2</h3>
							<p class="text-gray-400 font-medium text-xs mt-1">8:00 - 9:00 PM</p>
						</div>
						<div class="z-10 mt-0 md:mt-12 text-right md:text-left">
							<h4 class="text-white font-black text-2xl md:text-4xl leading-tight flex gap-1.5 md:block">
								<span>AUG</span>
								<span>09</span>
							</h4>
						</div>
						<div class="absolute top-0 right-0 w-32 h-32 bg-brand/20 blur-[50px] rounded-full pointer-events-none"></div>
					</div>
					
					<!-- Column 3: Details & CTA -->
					<div class="w-full md:w-[55%] p-6 md:p-8 flex flex-col justify-center relative bg-white/[0.01]">
						<div class="flex flex-col gap-1 mb-6">
							<h3 class="text-2xl md:text-3xl font-bold text-white uppercase tracking-wide">AWAIS KAMRAN</h3>
							<p class="text-brand font-medium text-sm flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-0">
								<span>AI Training Architect</span>
								<span class="text-gray-600 hidden sm:inline px-2">|</span>
								<span>AWS Certified Solutions Architect</span>
							</p>
						</div>
						<ul class="space-y-3 mb-6">
							<li class="flex items-center gap-3 text-gray-300 font-medium text-sm">
								<div class="w-1.5 h-1.5 rounded-full bg-brand shrink-0"></div>
								AWS Scaling Fundamentals
							</li>
							<li class="flex items-center gap-3 text-gray-300 font-medium text-sm">
								<div class="w-1.5 h-1.5 rounded-full bg-brand shrink-0"></div>
								Cloud Architecture Essentials
							</li>
							<li class="flex items-start gap-3 text-[#ff9900] font-medium text-[13px] mt-4 pt-4 border-t border-white/5 leading-relaxed">
								<svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
								<span>Each participant will receive an <strong>AWS E-Certificate of Participation</strong>. One random participant will receive an <strong>AWS Swag Kit</strong> (AWS Bottle OR T-Shirt + Notepad).</span>
							</li>
						</ul>
						<div class="mt-auto">
							<a href="https://www.meetup.com/aws-sbg-at-air-univ-islamabad/events/315842427/" target="_blank" class="inline-flex items-center justify-center gap-2 bg-[#ad5cff] hover:bg-[#9b42f5] text-white px-6 py-3.5 rounded-xl font-bold transition-all text-sm shadow-[0_0_15px_rgba(173,92,255,0.3)] hover:shadow-[0_0_25px_rgba(173,92,255,0.5)] w-full sm:w-auto">
								<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M3.25 4A2.25 2.25 0 001 6.25v7.5A2.25 2.25 0 003.25 16h7.5A2.25 2.25 0 0013 13.75v-7.5A2.25 2.25 0 0010.75 4h-7.5zM19 4.75a.75.75 0 00-1.28-.53l-3 3a.75.75 0 00-.22.53v4.5c0 .199.079.39.22.53l3 3a.75.75 0 001.28-.53V4.75z"></path></svg>
								Reserve Your Virtual Seat
							</a>
						</div>
					</div>
				</div>

				<!-- Session 3 -->
				<div class="glass-card rounded-2xl overflow-hidden flex flex-col md:flex-row group transition-all duration-300 hover:shadow-[0_10px_40px_rgba(173,92,255,0.15)] hover:-translate-y-1 hover:border-brand/50 cursor-default">
					
					<!-- Column 1: Speaker -->
					<div class="w-full md:w-[25%] relative bg-gray-900 shrink-0 h-[250px] md:h-auto">
						<img src="https://awscloudclubau.com/wp-content/uploads/2026/07/Anwaar-Hussain.webp" alt="Anwaar Hussain" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-90 group-hover:opacity-100 object-top md:object-center">
						<div class="absolute top-4 left-4 bg-black/40 backdrop-blur-md border border-white/10 text-white text-[10px] md:text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow-lg flex items-center gap-1.5 z-10">
							<span class="w-1.5 h-1.5 rounded-full bg-[#00ef88] animate-pulse"></span>
							Virtual
						</div>
						<div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent z-0"></div>
						<div class="absolute bottom-5 left-6 z-10 flex flex-col gap-1">
							<span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Works At</span>
							<img src="https://awscloudclubau.com/wp-content/uploads/2026/07/pngwing.com_.png" alt="Amazon Web Services" class="h-6 w-auto object-contain brightness-0 invert opacity-90">
						</div>
					</div>

					<!-- Column 2: Date & Time -->
					<div class="w-full md:w-[20%] bg-brand/10 p-5 md:p-8 flex flex-row md:flex-col justify-between items-center md:items-start relative border-b md:border-b-0 md:border-r border-white/10 group-hover:bg-brand/20 transition-colors duration-300 shrink-0">
						<div class="z-10">
							<h3 class="text-brand font-black text-lg md:text-xl uppercase tracking-wider">Session 3</h3>
							<p class="text-gray-400 font-medium text-xs mt-1">8:00 - 9:00 PM</p>
						</div>
						<div class="z-10 mt-0 md:mt-12 text-right md:text-left">
							<h4 class="text-white font-black text-2xl md:text-4xl leading-tight flex gap-1.5 md:block">
								<span>AUG</span>
								<span>12</span>
							</h4>
						</div>
						<div class="absolute top-0 right-0 w-32 h-32 bg-brand/20 blur-[50px] rounded-full pointer-events-none"></div>
					</div>
					
					<!-- Column 3: Details & CTA -->
					<div class="w-full md:w-[55%] p-6 md:p-8 flex flex-col justify-center relative bg-white/[0.01]">
						<div class="flex flex-col gap-1 mb-6">
							<h3 class="text-2xl md:text-3xl font-bold text-white uppercase tracking-wide">ANWAAR HUSSAIN</h3>
							<p class="text-brand font-medium text-sm flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-0">
								<span>Cloud Infrastructure Architect</span>
								<span class="text-gray-600 hidden sm:inline px-2">|</span>
								<span>AWS</span>
							</p>
						</div>
						<ul class="space-y-3 mb-6">
							<li class="flex items-center gap-3 text-gray-300 font-medium text-sm">
								<div class="w-1.5 h-1.5 rounded-full bg-brand shrink-0"></div>
								Real-World AWS Use Cases
							</li>
							<li class="flex items-center gap-3 text-gray-300 font-medium text-sm">
								<div class="w-1.5 h-1.5 rounded-full bg-brand shrink-0"></div>
								Building Production-Ready Applications
							</li>
							<li class="flex items-start gap-3 text-[#ff9900] font-medium text-[13px] mt-4 pt-4 border-t border-white/5 leading-relaxed">
								<svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
								<span>Each participant will receive an <strong>AWS E-Certificate of Participation</strong>. One random participant will receive an <strong>AWS Swag Kit</strong> (AWS Bottle OR T-Shirt + Notepad).</span>
							</li>
						</ul>
						<div class="mt-auto">
							<a href="https://www.meetup.com/aws-sbg-at-air-univ-islamabad/events/315842644/" target="_blank" class="inline-flex items-center justify-center gap-2 bg-[#ad5cff] hover:bg-[#9b42f5] text-white px-6 py-3.5 rounded-xl font-bold transition-all text-sm shadow-[0_0_15px_rgba(173,92,255,0.3)] hover:shadow-[0_0_25px_rgba(173,92,255,0.5)] w-full sm:w-auto">
								<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M3.25 4A2.25 2.25 0 001 6.25v7.5A2.25 2.25 0 003.25 16h7.5A2.25 2.25 0 0013 13.75v-7.5A2.25 2.25 0 0010.75 4h-7.5zM19 4.75a.75.75 0 00-1.28-.53l-3 3a.75.75 0 00-.22.53v4.5c0 .199.079.39.22.53l3 3a.75.75 0 001.28-.53V4.75z"></path></svg>
								Reserve Your Virtual Seat
							</a>
						</div>
					</div>
				</div>


		</div>
	</section>





	<!-- 3. Track 2: In-Person Event -->
	<section id="track2" class="py-20 md:py-32 relative z-10 bg-[#080808] border-t border-white/5">
		<div class="max-w-6xl mx-auto px-4 md:px-6 lg:px-8">
			<div class="text-center mb-12">
				<div class="inline-block bg-[#00ef88]/20 text-[#00ef88] text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-4">Certification Round</div>
				<h2 class="text-3xl sm:text-4xl md:text-5xl font-bold tracking-tight mb-4">AWS CERTIFICATION DAY</h2>
				<p class="text-gray-400 text-sm md:text-base px-2 max-w-2xl mx-auto">The ultimate in-person conclusion in Islamabad. Take a mock exam, win swags, and become eligible for an official AWS Certification Voucher.</p>
			</div>
			
			<div class="glass-card rounded-3xl p-6 md:p-10 mb-16 max-w-4xl mx-auto border-t border-[#00ef88]/30">
				<div class="grid grid-cols-2 md:grid-cols-5 gap-4 md:gap-6 text-center">
					<div class="p-4 transition-transform hover:scale-105">
						<div class="w-14 h-14 rounded-2xl bg-brand/10 flex items-center justify-center text-brand mx-auto mb-4 border border-brand/20 shadow-[0_0_20px_rgba(173,92,255,0.2)]">
							<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
						</div>
						<h4 class="font-bold text-white text-[13px] uppercase tracking-wide">Mock Exam</h4>
					</div>
					<div class="p-4 transition-transform hover:scale-105">
						<div class="w-14 h-14 rounded-2xl bg-brand/10 flex items-center justify-center text-brand mx-auto mb-4 border border-brand/20 shadow-[0_0_20px_rgba(173,92,255,0.2)]">
							<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
						</div>
						<h4 class="font-bold text-white text-[13px] uppercase tracking-wide">Exam Voucher</h4>
					</div>
					<div class="p-4 transition-transform hover:scale-105">
						<div class="w-14 h-14 rounded-2xl bg-[#00ef88]/10 flex items-center justify-center text-[#00ef88] mx-auto mb-4 border border-[#00ef88]/20 shadow-[0_0_20px_rgba(0,239,136,0.2)]">
							<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
						</div>
						<h4 class="font-bold text-white text-[13px] uppercase tracking-wide">AWS Swags</h4>
					</div>
					<div class="p-4 transition-transform hover:scale-105">
						<div class="w-14 h-14 rounded-2xl bg-[#ff9900]/10 flex items-center justify-center text-[#ff9900] mx-auto mb-4 border border-[#ff9900]/20 shadow-[0_0_20px_rgba(255,153,0,0.2)]">
							<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
						</div>
						<h4 class="font-bold text-white text-[13px] uppercase tracking-wide">Networking</h4>
					</div>
					<div class="p-4 transition-transform hover:scale-105 col-span-2 md:col-span-1">
						<div class="w-14 h-14 rounded-2xl bg-[#00ef88]/10 flex items-center justify-center text-[#00ef88] mx-auto mb-4 border border-[#00ef88]/20 shadow-[0_0_20px_rgba(0,239,136,0.2)]">
							<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m4-2v2m4-2v2M4 8h16v6a6 6 0 01-6 6H10a6 6 0 01-6-6V8zm16 2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2"></path></svg>
						</div>
						<h4 class="font-bold text-white text-[13px] uppercase tracking-wide">Refreshments</h4>
					</div>
				</div>
			</div>

			<!-- Registration CTA -->
			<div class="text-center mt-4">
				<span class="inline-flex items-center justify-center gap-3 bg-gray-700 text-amber-300 px-10 py-5 rounded-full font-black uppercase tracking-widest cursor-not-allowed opacity-80 shadow-none">
					<svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
					Registrations Temporarily Closed
				</span>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
