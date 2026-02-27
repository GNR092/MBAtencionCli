import { tool } from "@opencode-ai/plugin";
import { execSync } from "child_process";

export default tool({
  description: "Búsqueda semántica en el código base. Úsala cuando necesites encontrar conceptos o lógica, no solo palabras exactas.",
  args: {
    query: tool.schema.string().describe("La consulta en lenguaje natural (ej: '¿donde se procesan los pagos?')"),
  },
  async execute({ query }) {
    try {
      // Ejecuta el comando mgrep instalado globalmente
      const output = execSync(`mgrep "${query}" --json`, { encoding: 'utf-8' });
      return output;
    } catch (error) {
      return `Error al ejecutar mgrep: ${error.message}`;
    }
  },
});