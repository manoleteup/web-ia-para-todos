import { defineConfig } from "astro/config";

export default defineConfig({
  site: "https://iaparatodoslatam.com",
  trailingSlash: "always",
  output: "static",
  build: {
    // "directory" genera dist/kids/index.html. Con "file" solo hay kids.html y en
    // cPanel/Apache la URL /kids/ busca una carpeta kids/ → 403/404.
    format: "directory",
  },
});
