/** @type {import('tailwindcss').Config} */
export default {
  content: ["./src/**/*.{astro,html,js}"],
  theme: {
    extend: {
      colors: {
        night: "#050816",
        slateDeep: "#0B1024",
        cardSurface: "#111936",
        brandBlue: "#3B82F6",
        brandViolet: "#6366F1",
        brandCyan: "#A5F3FC",
        brandSky: "#60A5FA",
        paper: "#F8FAFC",
        muted: "#CBD5E1",
      },
      fontFamily: {
        sans: ["Inter", "ui-sans-serif", "system-ui", "sans-serif"],
      },
      boxShadow: {
        glow: "0 0 40px rgba(96, 165, 250, 0.12)",
        violet: "0 0 32px rgba(99, 102, 241, 0.1)",
      },
      backgroundImage: {
        "radial-grid":
          "radial-gradient(circle at top left, rgba(96,165,250,0.14), transparent 32rem), radial-gradient(circle at 80% 20%, rgba(99,102,241,0.12), transparent 28rem)",
      },
    },
  },
  plugins: [],
};
