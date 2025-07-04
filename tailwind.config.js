import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import forms from "@tailwindcss/forms";

// tailwind.config.js
const colors = require("tailwindcss/colors");

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: { ...colors },
        },
    },

    plugins: [forms],
};
