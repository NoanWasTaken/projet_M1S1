import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["sidebar", "overlay"];

  connect() {
    this.isOpen = false;
    this._apply();
  }

  toggle() {
    this.isOpen = !this.isOpen;
    this._apply();
  }


  close() {
    this.isOpen = false;
    this._apply();
  }

  _apply() {
    this.sidebarTarget.classList.toggle("lg:w-72", this.isOpen);
    this.sidebarTarget.classList.toggle("lg:w-0", !this.isOpen);

    if (window.matchMedia("(min-width: 1024px)").matches) {
      this.sidebarTarget.classList.remove("-translate-x-full");
      this.sidebarTarget.classList.add("translate-x-0");
    } else {
      this.sidebarTarget.classList.toggle("-translate-x-full", !this.isOpen);
      this.sidebarTarget.classList.toggle("translate-x-0", this.isOpen);
    }

    // Overlay (mobile uniquement)
    if (this.hasOverlayTarget) {
      const show = this.isOpen && !window.matchMedia("(min-width: 1024px)").matches;

      this.overlayTarget.classList.toggle("opacity-100", show);
      this.overlayTarget.classList.toggle("opacity-0", !show);
      this.overlayTarget.classList.toggle("pointer-events-auto", show);
      this.overlayTarget.classList.toggle("pointer-events-none", !show);
    }

    const btn = this.element.querySelector("[data-sidebar-toggle]");
    if (btn) btn.setAttribute("aria-expanded", this.isOpen ? "true" : "false");
  }

}
