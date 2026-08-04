<header x-data="{
    open:false,
    company:false,
    products:false
}"
class="sticky top-0 z-50 bg-white/90 backdrop-blur-xl border-b border-slate-200">


<div class="max-w-7xl mx-auto px-6 lg:px-8">


<div class="flex items-center justify-between h-20">

<!-- Logo -->

<a href="{{ route('home') }}" class="flex items-center">

    <img
        src="{{ asset('images/esubiz-logo.png') }}"
        alt="Esubiz"
        class="h-14 lg:h-16 w-auto shrink-0">

</a>



<!-- Desktop Navigation -->

<nav class="hidden lg:flex items-center gap-7">


<!-- Home -->

<a href="{{ route('home') }}"
class="font-medium text-slate-700 hover:text-slate-900">

Home

</a>





<!-- Company -->

<div class="relative"
@mouseenter="company=true"
@mouseleave="company=false">


<button class="flex items-center gap-1 font-medium text-slate-700">

Company

<span>⌄</span>

</button>


<div x-show="company"
x-transition
class="absolute top-8 left-0 w-52 bg-white rounded-xl shadow-xl border p-3">


<a href="#about"
class="block px-4 py-3 rounded-lg hover:bg-slate-50">

About Us

</a>


<a href="#faq"
class="block px-4 py-3 rounded-lg hover:bg-slate-50">

FAQs

</a>


<a href="#blog"
class="block px-4 py-3 rounded-lg hover:bg-slate-50">

Blog

</a>


</div>


</div>





<!-- Products -->

<div class="relative"
@mouseenter="products=true"
@mouseleave="products=false">


<button class="flex items-center gap-1 font-medium text-slate-700">

Products

<span>⌄</span>

</button>


<div x-show="products"
x-transition
class="absolute top-8 left-0 w-60 bg-white rounded-xl shadow-xl border p-3">


<a href="#website-builder"
class="block px-4 py-3 rounded-lg hover:bg-slate-50">

Website Builder

</a>


<a href="#crm"
class="block px-4 py-3 rounded-lg hover:bg-slate-50">

CRM

</a>


<a href="#commerce"
class="block px-4 py-3 rounded-lg hover:bg-slate-50">

Commerce

</a>


<a href="#ai"
class="block px-4 py-3 rounded-lg hover:bg-slate-50">

AI Assistant

</a>


<a href="#workspace"
class="block px-4 py-3 rounded-lg hover:bg-slate-50">

Business Workspace

</a>


<a href="#developer"
class="block px-4 py-3 rounded-lg hover:bg-slate-50">

Developer Tools

</a>


</div>


</div>





<a href="#showcase"
class="font-medium text-slate-700">

Showcase

</a>




<a href="#pricing"
class="font-medium text-slate-700">

Pricing

</a>




<a href="#contact"
class="font-medium text-slate-700">

Contact Us

</a>



</nav>






<!-- Desktop Actions -->

<div class="hidden lg:flex items-center gap-5">


<a href="{{ route('login') }}"
class="font-semibold text-slate-700">

Login

</a>


<a href="{{ route('register') }}"
class="px-6 py-3 rounded-xl bg-slate-900 text-white font-bold">

Start Free

</a>


</div>





<!-- Mobile Button -->

<button @click="open=!open"
class="lg:hidden p-3 rounded-xl border">


<svg class="w-6 h-6"
fill="none"
viewBox="0 0 24 24"
stroke="currentColor">

<path stroke-linecap="round"
stroke-linejoin="round"
stroke-width="2"
d="M4 6h16M4 12h16M4 18h16"/>

</svg>


</button>



</div>





<!-- Mobile Menu -->

<div x-show="open"
x-transition
class="lg:hidden pb-6">


<nav class="flex flex-col gap-4 pt-6 border-t">





<a href="{{ route('home') }}">
Home
</a>






<!-- Mobile Company -->

<div>

    <button
        @click="company = !company"
        class="flex items-center justify-between w-full py-2 text-left font-medium text-slate-700">

        <span>Company</span>

        <span x-text="company ? '−' : '+'"></span>

    </button>

    <div
        x-show="company"
        x-transition
        class="mt-3 ml-5 flex flex-col space-y-3">

        <a href="#about"
           class="block text-slate-600 hover:text-slate-900">
            About Us
        </a>

        <a href="#faq"
           class="block text-slate-600 hover:text-slate-900">
            FAQs
        </a>

        <a href="#blog"
           class="block text-slate-600 hover:text-slate-900">
            Blog
        </a>

    </div>

</div>






<!-- Mobile Products -->

<div>

    <button
        @click="products = !products"
        class="flex items-center justify-between w-full py-2 text-left font-medium text-slate-700">

        <span>Products</span>

        <span x-text="products ? '−' : '+'"></span>

    </button>

    <div
        x-show="products"
        x-transition
        class="mt-3 ml-5 flex flex-col space-y-3">

        <a href="#website-builder"
           class="block text-slate-600 hover:text-slate-900">
            Website Builder
        </a>

        <a href="#crm"
           class="block text-slate-600 hover:text-slate-900">
            CRM
        </a>

        <a href="#commerce"
           class="block text-slate-600 hover:text-slate-900">
            Commerce
        </a>

        <a href="#ai"
           class="block text-slate-600 hover:text-slate-900">
            AI Assistant
        </a>

        <a href="#workspace"
           class="block text-slate-600 hover:text-slate-900">
            Business Workspace
        </a>

        <a href="#developer"
           class="block text-slate-600 hover:text-slate-900">
            Developer Tools
        </a>

    </div>

</div>




<a href="#showcase">
Showcase
</a>


<a href="#pricing">
Pricing
</a>


<a href="#contact">
Contact Us
</a>




<hr>



<a href="{{ route('login') }}">
Login
</a>



<a href="{{ route('register') }}"
class="px-5 py-3 rounded-xl bg-slate-900 text-white text-center">

Start Free

</a>




</nav>


</div>


</div>


</header>
