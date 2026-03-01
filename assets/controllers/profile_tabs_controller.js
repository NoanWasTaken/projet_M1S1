import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["info", "skins", "btnInfo", "btnSkins"];
  static values = { current: String };

  connect() {
    if (!this.currentValue) this.currentValue = "info";

    this.panelsContainer = this.infoTarget.parentElement;
    this.panelsContainer.style.position = "relative";
    [this.infoTarget, this.skinsTarget].forEach((p) => {
      p.style.position = "absolute";
      p.style.inset = "0";
      p.style.width = "100%";
    });

    this.sync(false);
  }

  showInfo() {
    this.currentValue = "info";
    this.sync(true);
  }

  showSkins() {
    this.currentValue = "skins";
    this.sync(true);
  }

  sync(animate = false) {
    const showInfo = this.currentValue === "info";

    this.infoTarget.classList.toggle("is-visible", showInfo);
    this.skinsTarget.classList.toggle("is-visible", !showInfo);

    this.btnInfoTarget.classList.toggle("is-active", showInfo);
    this.btnSkinsTarget.classList.toggle("is-active", !showInfo);

    this.btnInfoTarget.classList.toggle("is-info", showInfo);
    this.btnInfoTarget.classList.toggle("is-skins", !showInfo); 
    this.btnSkinsTarget.classList.toggle("is-skins", !showInfo);
    this.btnSkinsTarget.classList.toggle("is-info", showInfo);  

    if (animate) {
      const panel = showInfo ? this.infoTarget : this.skinsTarget;
      panel.animate(
        [
          { transform: "translateX(12px)", opacity: 0 },
          { transform: "translateX(0px)", opacity: 1 },
        ],
        { duration: 180, easing: "ease-out" }
      );
    }
  }
}