/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        container: {
            center: true,
        },
        extend: {
            fontFamily: {
                poppins: ["Poppins", "sans-serif"],
            },
            colors: {
                orange: {
                    1: "#FCC997",
                    2: "#FF5927",
                },
                navy: "#0F0742",
                gray: {
                    1: "#E0E0E0",
                    2: "#989898",
                    3: "#BDBDBD",
                },
            },
            keyframes: {
                spinLeft: {
                    from: { transform: "rotate(360deg)" },
                    to: { transform: "rotate(0deg)" },
                },
            },
            animation: {
                spinLeft: "spinLeft 4.5s linear infinite",
            },
        },
    },
    plugins: [],
};
