describe("Authentication Tests", () => {
    const email = `cypress.${Date.now()}@example.com`;
    const password = "Cypress123!";
    it("should register a new user", () => {
        cy.visit("/register");
        cy.get('input[name="registration_form[email]"]').type(email);
        cy.get('input[name="registration_form[plainPassword][first]"]').type(
            password
        );
        cy.get('input[name="registration_form[plainPassword][second]"]').type(
            password
        );
        cy.get(
            'input[type="checkbox"][name="registration_form[agreeTerms]"]'
        ).check();
        cy.get('button[type="submit"]').click();
        cy.contains("Se déconnecter").should("be.visible");
    });
    it("should login an existing user", () => {
        cy.visit("/login");
        cy.get('input[name="_username"]').type(email);
        cy.get('input[name="_password"]').type(password);
        cy.get('button[type="submit"]').click();
        cy.url().should("eq", `${Cypress.config().baseUrl}/`);
        cy.contains(`Connecté en tant que : ${email}`).should("be.visible");
    });
});
