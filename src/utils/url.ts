/**
 * Prefija una ruta interna con el `base` de Astro (`import.meta.env.BASE_URL`).
 * Acepta paths con o sin barra inicial. Devuelve siempre una URL correcta:
 *   withBase("/kids")            → "/new-2/kids"
 *   withBase("kids/iko.webp")     → "/new-2/kids/iko.webp"
 *   withBase("/")                → "/new-2/"
 *   withBase("#contacto")        → "/new-2/#contacto"
 */
export function withBase(path: string = ""): string {
  const base = import.meta.env.BASE_URL ?? "/";
  const normalizedBase = base.endsWith("/") ? base : `${base}/`;

  if (!path || path === "/") return normalizedBase;

  if (path.startsWith("#")) return `${normalizedBase}${path}`;

  const clean = path.replace(/^\/+/, "");
  return `${normalizedBase}${clean}`;
}

/**
 * Construye una URL absoluta combinando el host + base.
 *   absoluteUrl("https://iaparatodoslatam.com", "/kids") → "https://iaparatodoslatam.com/new-2/kids"
 */
export function absoluteUrl(siteUrl: string, path: string = ""): string {
  const host = siteUrl.replace(/\/+$/, "");
  return `${host}${withBase(path)}`;
}
