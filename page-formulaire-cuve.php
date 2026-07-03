<?php
/**
 * Template Name: ISPAG - Quote Configurator
 * Description: Multilingual technical quote form for custom tanks.
 * Text Domain: ispag-crm
 */

get_header(); ?>

<script src="https://cdn.tailwindcss.com"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap');
    .ispag-crm-wrap { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    .text-ispag { color: #E11D48; }
    .bg-ispag { background-color: #E11D48; }
    .admin-bar .sticky-nav { top: 32px; }
    @media screen and (max-width: 782px) { .admin-bar .sticky-nav { top: 46px; } }
</style>

<div class="ispag-crm-wrap min-h-screen pb-20">
    
    <!-- Header -->
    <div class="bg-white border-b border-slate-200 py-4 px-6 sticky top-0 z-40 sticky-nav shadow-sm">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="text-2xl font-black italic tracking-tighter text-slate-900">ISPAG</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-2 hidden sm:block">
                    <?php _e('Technical Configurator', 'ispag-crm'); ?>
                </span>
            </div>
            <button onclick="window.print()" class="text-slate-600 px-4 py-2 rounded-lg text-xs font-bold hover:bg-slate-100 transition-all flex items-center gap-2 border border-slate-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                <?php _e('Save as PDF', 'ispag-crm'); ?>
            </button>
        </div>
    </div>

    <main class="max-w-4xl mx-auto mt-12 px-6">
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success') : ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-6 rounded-2xl mb-8 flex items-center gap-4">
                <div class="bg-emerald-500 text-white rounded-full p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                </div>
                <div>
                    <p class="font-bold"><?php _e('Success!', 'ispag-crm'); ?></p>
                    <p class="text-sm opacity-90"><?php _e('Your request has been sent to our technical department.', 'ispag-crm'); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <header class="mb-10">
            <h1 class="text-3xl font-black text-slate-900 mb-2"><?php _e('Custom Tank Quote Request', 'ispag-crm'); ?></h1>
            <p class="text-slate-500 italic"><?php _e('Tailored solutions for engineers and installers.', 'ispag-crm'); ?></p>
        </header>

        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="space-y-8">
            <input type="hidden" name="action" value="submit_ispag_quote">
            <?php wp_nonce_field('ispag_quote_verify', 'ispag_nonce'); ?>

            <!-- SECTION 1: CUSTOMER -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-8 py-4 border-b border-slate-200 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-bold uppercase">01</div>
                    <h2 class="font-bold text-slate-800"><?php _e('Company Details', 'ispag-crm'); ?></h2>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider"><?php _e('Company *', 'ispag-crm'); ?></label>
                        <input type="text" name="company" required class="w-full border-b-2 border-slate-100 py-2 focus:border-rose-500 outline-none transition-colors">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider"><?php _e('Email *', 'ispag-crm'); ?></label>
                        <input type="email" name="customer_email" required class="w-full border-b-2 border-slate-100 py-2 focus:border-rose-500 outline-none transition-colors">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider"><?php _e('Project Name', 'ispag-crm'); ?></label>
                        <input type="text" name="project" class="w-full border-b-2 border-slate-100 py-2 focus:border-rose-500 outline-none transition-colors">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider"><?php _e('Phone', 'ispag-crm'); ?></label>
                        <input type="tel" name="phone" class="w-full border-b-2 border-slate-100 py-2 focus:border-rose-500 outline-none transition-colors">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: SPECS -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-8 py-4 border-b border-slate-200 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-bold uppercase">02</div>
                    <h2 class="font-bold text-slate-800"><?php _e('Technical Specifications', 'ispag-crm'); ?></h2>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase block mb-1"><?php _e('Diameter (mm)', 'ispag-crm'); ?></label>
                            <input type="number" name="dia" class="w-full bg-transparent text-xl font-bold outline-none" placeholder="1000">
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase block mb-1"><?php _e('Height (mm)', 'ispag-crm'); ?></label>
                            <input type="number" name="height" class="w-full bg-transparent text-xl font-bold outline-none" placeholder="2200">
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase block mb-1"><?php _e('Volume (L)', 'ispag-crm'); ?></label>
                            <input type="number" name="volume" class="w-full bg-transparent text-xl font-bold outline-none" placeholder="1500">
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase block mb-1"><?php _e('Pressure (bar)', 'ispag-crm'); ?></label>
                            <input type="number" name="pressure" class="w-full bg-transparent text-xl font-bold outline-none" placeholder="6">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <span class="text-[10px] font-black text-slate-400 uppercase block"><?php _e('Material Selection', 'ispag-crm'); ?></span>
                            <div class="grid gap-2">
                                <label class="flex items-center p-4 border border-slate-100 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors">
                                    <input type="radio" name="material" value="V4A" class="accent-rose-600" checked>
                                    <span class="ml-3 font-bold text-slate-800">Inox V4A (316L)</span>
                                </label>
                                <label class="flex items-center p-4 border border-slate-100 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors">
                                    <input type="radio" name="material" value="V2A" class="accent-rose-600">
                                    <span class="ml-3 font-bold text-slate-800">Inox V2A (304)</span>
                                </label>
                                <label class="flex items-center p-4 border border-slate-100 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors">
                                    <input type="radio" name="material" value="S235" class="accent-rose-600">
                                    <span class="ml-3 font-bold text-slate-800">Steel S235 (Heating)</span>
                                </label>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <span class="text-[10px] font-black text-slate-400 uppercase block"><?php _e('Insulation', 'ispag-crm'); ?></span>
                            <select name="insulation" class="w-full p-4 bg-slate-900 text-white rounded-xl font-bold appearance-none cursor-pointer outline-none">
                                <option value="none"><?php _e('None', 'ispag-crm'); ?></option>
                                <option value="rockwool"><?php _e('Rockwool', 'ispag-crm'); ?></option>
                                <option value="armaflex"><?php _e('Armaflex', 'ispag-crm'); ?></option>
                                <option value="pur"><?php _e('PUR Foam', 'ispag-crm'); ?></option>
                            </select>
                            <label class="flex items-center p-4 bg-rose-50 border border-rose-100 rounded-xl cursor-pointer mt-4">
                                <input type="checkbox" name="site_welding" class="accent-rose-600 w-5 h-5">
                                <span class="ml-3 text-sm font-bold text-rose-900"><?php _e('On-site welding required?', 'ispag-crm'); ?></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="bg-slate-900 rounded-3xl p-10 text-white flex flex-col md:flex-row items-center justify-between gap-8">
                <div>
                    <h3 class="text-2xl font-black italic mb-2"><?php _e('Finalize Request', 'ispag-crm'); ?></h3>
                    <p class="text-slate-400 text-sm max-w-sm"><?php _e('Our team will analyze your data and send you a formal offer via email.', 'ispag-crm'); ?></p>
                </div>
                <button type="submit" class="w-full md:w-auto bg-rose-600 hover:bg-rose-500 text-white px-12 py-5 rounded-2xl font-black uppercase tracking-widest transition-all shadow-lg shadow-rose-900/20">
                    <?php _e('Send Quote Request', 'ispag-crm'); ?>
                </button>
            </div>

        </form>
    </main>
</div>

<?php get_footer(); ?>