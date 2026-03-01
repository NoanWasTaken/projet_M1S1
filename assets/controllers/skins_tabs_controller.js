import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = [
    "hair", "skin", "body", "outfit",
    "btnHair", "btnSkin", "btnBody", "btnOutfit",
  ];

  static values = { current: String };

  connect() {
    if (!this.currentValue) this.currentValue = "hair";

    // conteneur commun des panels
    this.panelsContainer = this.hairTarget.parentElement;
    this.panelsContainer.style.position = "relative";

    // superposition
    [this.hairTarget, this.skinTarget, this.bodyTarget, this.outfitTarget].forEach((p) => {
      p.style.position = "absolute";
      p.style.inset = "0";
      p.style.width = "100%";
    });

    this.sync(false);
  }

  showHair() { this.currentValue = "hair"; this.sync(true); }
  showSkin() { this.currentValue = "skin"; this.sync(true); }
  showBody() { this.currentValue = "body"; this.sync(true); }
  showOutfit() { this.currentValue = "outfit"; this.sync(true); }

  sync(animate = false) {
    const key = this.currentValue;

    const map = {
      hair: this.hairTarget,
      skin: this.skinTarget,
      body: this.bodyTarget,
      outfit: this.outfitTarget,
    };

    Object.entries(map).forEach(([k, panel]) => {
      panel.classList.toggle("is-visible", k === key);
    });

    // boutons actifs
    this.btnHairTarget.classList.toggle("is-active", key === "hair");
    this.btnSkinTarget.classList.toggle("is-active", key === "skin");
    this.btnBodyTarget.classList.toggle("is-active", key === "body");
    this.btnOutfitTarget.classList.toggle("is-active", key === "outfit");

    if (animate) {
      const panel = map[key];
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