describe("Application Tests", () => {
    it("should load the homepage", () => {
        cy.visit("/");
        cy.contains("Bienvenue sur GearForge");
        cy.get("h1").should("exist");
    });

    it("should navigate to login page", () => {
        cy.visit("/login");
        cy.url().should("include", "/login");
        cy.contains("Se connecter");
    });

    it("should navigate to register page", () => {
        cy.visit("/register");
        cy.url().should("include", "/register");
        cy.contains("Register");
    });

    it("should display login and register buttons when not authenticated", () => {
        cy.visit("/");
        cy.contains("Se connecter").should("be.visible");
        cy.contains("S'inscrire").should("be.visible");
    });
    
});
