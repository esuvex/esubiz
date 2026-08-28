{{-- ESUBIZ_GLOBAL_MEDIA_COMPONENT_MIGRATION_V1 --}}
<footer class="bg-slate-950 text-slate-300">

    <!-- Top -->

    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-20">

        <div class="grid lg:grid-cols-5 gap-14">

            <!-- Brand -->

            <div class="lg:col-span-2">

                <a href="{{ route('home') }}" class="inline-flex">

                    <x-media.image
    src="{{ asset('images/esubiz-logo.png') }}"
    alt="Esubiz"
    class="h-12 w-auto"
/>

                </a>

                <p class="mt-6 text-slate-400 leading-8 max-w-md">

                    Esubiz is the Business Operating System that helps entrepreneurs,
                    startups and growing companies build websites, manage customers,
                    automate operations and grow from one intelligent platform.

                </p>

                <div class="flex gap-4 mt-8">

                    <a href="#" class="w-11 h-11 rounded-xl bg-white/10 hover:bg-amber-400 hover:text-slate-900 transition flex items-center justify-center">

                        <i class="fab fa-facebook-f"></i>

                        F

                    </a>

                    <a href="#" class="w-11 h-11 rounded-xl bg-white/10 hover:bg-amber-400 hover:text-slate-900 transition flex items-center justify-center">

                        X

                    </a>

                    <a href="#" class="w-11 h-11 rounded-xl bg-white/10 hover:bg-amber-400 hover:text-slate-900 transition flex items-center justify-center">

                        in

                    </a>

                    <a href="#" class="w-11 h-11 rounded-xl bg-white/10 hover:bg-amber-400 hover:text-slate-900 transition flex items-center justify-center">

                        ▶

                    </a>

                </div>

            </div>



            <!-- Products -->

            <div>

                <h3 class="text-white font-bold text-lg">

                    Products

                </h3>

                <ul class="mt-6 space-y-4">

                    <li><a href="#" class="hover:text-amber-400 transition">Website Builder</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">CRM</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">Commerce</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">AI Assistant</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">Business Workspace</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">Developer Tools</a></li>

                </ul>

            </div>



            <!-- Company -->

            <div>

                <h3 class="text-white font-bold text-lg">

                    Company

                </h3>

                <ul class="mt-6 space-y-4">

                    <li><a href="#" class="hover:text-amber-400 transition">About Us</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">Blog</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">Partners</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">Contact</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">Careers</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">Press Kit</a></li>

                </ul>

            </div>



            <!-- Resources -->

            <div>

                <h3 class="text-white font-bold text-lg">

                    Resources

                </h3>

                <ul class="mt-6 space-y-4">

                    <li><a href="#" class="hover:text-amber-400 transition">Help Center</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">Documentation</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">API Reference</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">Privacy Policy</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">Terms of Service</a></li>

                    <li><a href="#" class="hover:text-amber-400 transition">System Status</a></li>

                    <li>
                        <a href="{{ route('gift-card.validate') }}"
                           class="hover:text-amber-400 transition">
                            Gift Card Validator
                        </a>
                    </li>

                </ul>

            </div>

        </div>

    </div>



    <!-- Bottom -->

    <div class="border-t border-white/10">

        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8">

            <div class="flex flex-col lg:flex-row items-center justify-between gap-6">

                <p class="text-slate-500 text-sm text-center lg:text-left">

                    © {{ date('Y') }} Esubiz. All rights reserved.

                </p>

                <div class="flex flex-wrap justify-center gap-6 text-sm">

                    <a href="#" class="hover:text-amber-400 transition">

                        Privacy

                    </a>

                    <a href="#" class="hover:text-amber-400 transition">

                        Terms

                    </a>

                    <a href="#" class="hover:text-amber-400 transition">

                        Cookies

                    </a>

                    <a href="#" class="hover:text-amber-400 transition">

                        Security

                    </a>

                    <a href="#" class="hover:text-amber-400 transition">

                        Accessibility

                    </a>

                </div>

            </div>

        </div>

    </div>

    <!-- Floating Back to Top -->
    <button
        id="back-to-top"
        type="button"
        aria-label="Back to top"
        style="position:fixed;right:24px;bottom:24px;z-index:99999;display:none;width:48px;height:48px;"
        class="rounded-full bg-[#c89b3c] text-slate-950 shadow-2xl items-center justify-center transition-all duration-300 hover:-translate-y-1 hover:opacity-90 focus:outline-none"
    >
        <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2.5"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M5 15l7-7 7 7"
            />
        </svg>
    </button>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('back-to-top');

            if (!button) return;

            function updateBackToTop() {
                button.style.display = window.scrollY > 300
                    ? 'flex'
                    : 'none';
            }

            window.addEventListener('scroll', updateBackToTop, {
                passive: true
            });

            button.addEventListener('click', function () {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            updateBackToTop();
        });
    </script>

</footer>


