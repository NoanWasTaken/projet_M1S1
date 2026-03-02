import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["info", "skins", "carts", "btnInfo", "btnSkins", "btnCarts"];
  static values = { current: String };

  connect() {
    if (!this.currentValue) this.currentValue = "info";

    this.panelsContainer = this.infoTarget.parentElement;
    this.panelsContainer.style.position = "relative";
    [this.infoTarget, this.skinsTarget, this.cartsTarget].forEach((p) => {
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

  showCarts() {
    this.currentValue = "carts";
    this.sync(true);
  }

  sync(animate = false) {
    const showInfo = this.currentValue === "info";
    const showSkins = this.currentValue === "skins";
    const showCarts = this.currentValue === "carts";

    this.infoTarget.classList.toggle("is-visible", showInfo);
    this.skinsTarget.classList.toggle("is-visible", showSkins);
    if (this.hasCartsTarget) {
      this.cartsTarget.classList.toggle("is-visible", showCarts);
    }

    this.btnInfoTarget.classList.toggle("is-active", showInfo);
    this.btnSkinsTarget.classList.toggle("is-active", showSkins);
    if (this.hasBtnCartsTarget) {
      this.btnCartsTarget.classList.toggle("is-active", showCarts);
    }

    this.btnInfoTarget.classList.toggle("is-info", showInfo);
    this.btnInfoTarget.classList.toggle("is-skins", !showInfo);
    this.btnSkinsTarget.classList.toggle("is-skins", showSkins);
    this.btnSkinsTarget.classList.toggle("is-info", !showSkins);
    if (this.hasBtnCartsTarget) {
      this.btnCartsTarget.classList.toggle("is-carts", showCarts);
    }

    if (animate) {
      let panel = null;
      if (showInfo) panel = this.infoTarget;
      else if (showSkins) panel = this.skinsTarget;
      else if (showCarts && this.hasCartsTarget) panel = this.cartsTarget;
      if (panel) {
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
}