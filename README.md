# IA Para Todos LATAM

Landing page y ecosistema web para un proyecto de alfabetización en inteligencia artificial en Latinoamérica.

## ¿Qué es este proyecto?

Este sitio está construido con Astro y presenta un ecosistema orientado a:

- Educación en IA para adultos, instituciones y comunidades.
- Una línea infantil con personajes, cuentos y recursos lúdicos.
- Un enfoque latinoamericano, humano y cercano.
- Mensajes de accesibilidad, ética y uso responsable.

## Secciones principales

- Hero principal con propuesta de valor y CTA.
- Ecosistema: adultos y Kids.
- IA y sociedad: por qué la alfabetización digital importa.
- Programas para adultos: talleres, programa social, libro y cursos.
- Kids: personajes, cuentos y herramientas educativas.
- Comunidad: públicos objetivo y uso real.
- Programa: fases de aprendizaje.
- Contacto: formulario y WhatsApp.

## Tecnologías

- Astro 6.2
- Tailwind CSS
- PostCSS
- Lucide Icons (lucide-astro)
- Prettier para formato de código

## Estructura clave

- `src/pages/index.astro`: página principal.
- `src/layouts/BaseLayout.astro`: layout base del sitio.
- `src/components/`: componentes reutilizables.
- `src/data/`: contenido editable y configuración de la landing.
- `public/`: archivos públicos y recursos accesibles.
- `docs/`: documentos de apoyo y transcripciones de audio.

## Configuración editable

Los textos, llamadas a la acción y datos del sitio se configuran desde:

- `src/data/site.js`
- `src/data/landing.js`
- `src/data/automation.js`

> Nota: actualmente el número de WhatsApp en `src/data/site.js` es un placeholder `569XXXXXXXX` y debe reemplazarse antes de producción.

## Scripts disponibles

```bash
npm install
npm run dev
npm run build
npm run preview
```

## Cómo iniciar el desarrollo

1. Clona o copia el proyecto.
2. Ejecuta `npm install`.
3. Ejecuta `npm run dev`.
4. Abre `http://localhost:3000` en el navegador.

## Despliegue

Astro genera un sitio estático en `dist/` tras ejecutar `npm run build`.

Puedes desplegarlo en servicios como:

- Vercel
- Netlify
- Cloudflare Pages
- GitHub Pages

## Personalización rápida

- Cambia título, descripción y CTA en `src/data/landing.js`.
- Ajusta datos de contacto y redes en `src/data/site.js`.
- Activa formularios, analytics o chatbot en `src/data/automation.js`.

## Buenas prácticas

- No comites `node_modules/`, `.astro/`, `dist/`, `.tmp/` ni archivos de entorno.
- Mantén actualizado el número de WhatsApp y las URLs de redes sociales.
- Versiona tus cambios con un `.gitignore` adecuado.

---

### Contacto del proyecto

`contacto@iaparatodoslatam.com`

`https://iaparatodoslatam.com`
