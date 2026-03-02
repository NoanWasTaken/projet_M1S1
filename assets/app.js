import "./app.css";
import { Application } from "@hotwired/stimulus";
import { createIcons, icons } from "lucide";

window.Stimulus = Application.start();

const modules = import.meta.glob("./controllers/**/*_controller.js", { eager: true });

for (const path in modules) {
  const module = modules[path];
  const controllerName = path
    .split("/")
    .pop()
    .replace("_controller.js", "")
    .replace(/_/g, "-");

  window.Stimulus.register(controllerName, module.default);
}

function mountLucide() {
  createIcons({ icons });
}

mountLucide();

console.log("Vite + Tailwind + Stimulus loaded!");