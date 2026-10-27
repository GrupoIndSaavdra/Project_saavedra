/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
    ],
    corePlugins: {
        preflight: false, // Protege el CSS Vanilla y resets existentes de la app
    },
    theme: {
        extend: {
            colors: {
                gis: {
                    blue: "#033966",
                    green: "#0a8504",
                    red: "#9c0303",
                    gray: "#404040",
                    dark: "#012344",
                },
            },
            fontFamily: {
                sans: ["Poppins", "sans-serif"],
            },
        },
    },
    plugins: [],
};
