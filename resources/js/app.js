

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
// ==========================================
// ESUBIZ MOTION SYSTEM
// ==========================================

document.addEventListener('DOMContentLoaded', () => {

    const observer = new IntersectionObserver((entries) => {

        entries.forEach((entry) => {

            if (!entry.isIntersecting) return;

            entry.target.classList.add(
                'motion-show',
                'motion-show-left',
                'motion-show-right',
                'motion-show-up',
                'motion-show-scale'
            );

        });

    }, {

        threshold: 0.15

    });

    document.querySelectorAll(

        '.motion-base, .motion-left, .motion-right, .motion-up, .motion-scale'

    ).forEach((el) => observer.observe(el));

});
