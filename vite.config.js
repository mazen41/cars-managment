import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import purge from '@erbelion/vite-plugin-laravel-purgecss'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'public/assets/css/web-styles.css',
                'public/assets/css/third-party.css',
                'public/assets/css/bootstrap-rtl.min.css',
            ],
            refresh: true,
        }),
        // purge({
        //     paths: [
        //         'resources/views/auth/login_register_js.blade.php',
        //         'resources/views/auth/boxed/*.blade.php',
        //         'resources/views/frontend/**/*.blade.php',
        //         'resources/views/frontend/**/*/*.blade.php',
        //         'resources/views/frontend/**/*/*/*.blade.php',
        //         'resources/views/errors/*.blade.php',
        //         'resources/views/modals/*.blade.php',
        //         'resources/views/otp_systems/frontend/auth/**/*.blade.php',
        //         'resources/views/partials/**/*.blade.php',
        //         'resources/views/refund_request/frontend/**/*.blade.php',
        //         'public/assets/js/*.js',
        //     ],
        //     safelist: [/^iti__/, /^alert-/]
        // })
    ],
});
