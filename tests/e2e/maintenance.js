describe('maintenance', () => {
    /**
     * login
     */
    function login(browser) {
        /**
         * navigate to the login screen
         */
        browser.navigateTo('/redaxo/index.php');

        /**
         * check if the login input is present
         * add username
         */
        browser.assert.elementPresent('input[id=rex-id-login-user]');
        browser.sendKeys('input[id=rex-id-login-user]', 'nightwatch_username');

        /**
         * check if the password input is present
         * add password
         */
        browser.assert.elementPresent('input[id=rex-id-login-password]');
        browser.sendKeys('input[id=rex-id-login-password]', ['nightwatch_password', browser.Keys.ENTER]);
    }

    /**
     * logout
     */
    function logout(browser) {
        browser.click('.navbar-nav a.rex-logout');
    }

    before(browser => {
        login(browser);

        browser.pause(500);

        /**
         * check if we are logged in to the backend
         */
        browser.assert.urlContains('/redaxo/index.php?page=structure');
    });

    it('Test Maintenance', function (browser) {
        /**
         * navigate to the settings page
         * activate maintenance
         */
        browser.navigateTo('/redaxo/index.php?page=maintenance/frontend');
        browser.click('.rex-page-main button.dropdown-toggle');
        browser.pause(250);
        browser.click('.rex-page-main #bs-select-1-1');
        browser.pause(250);
        browser.updateValue('input#rex-maintenance-secret-secret-we-got-a-secret', 'nightwatch_secret');
        browser.sendKeys('input#rex-maintenance-secret-secret-we-got-a-secret', [browser.Keys.ENTER]);
        browser.pause(250);

        /**
         * logout
         */
        logout(browser);

        /**
         * navigate to the frontend
         */
        browser.navigateTo('/');

        /**
         * test if maintenance is active
         */
        browser.assert.elementPresent(".maintenance-container");
        browser.assert.textContains('.maintenance-error-title', 'Maintenance');
        browser.assert.textContains('.maintenance-error-message', 'This website is temporarily unavailable');

        browser.pause(500);

        /**
         * test maintenance secret
         */
        browser.navigateTo('/?secret=nightwatch_secret');
        browser.assert.not.elementPresent(".maintenance-container");
        browser.pause(250);
    })

    /**
     * reset maintenance settings
     */
    afterEach(browser => {
        /**
         * login
         */
        login(browser);

        browser.pause(500);

        /**
         * reset settings
         */
        browser.navigateTo('/redaxo/index.php?page=maintenance/frontend');
        browser.updateValue('input#rex-maintenance-secret-secret-we-got-a-secret', '');
        browser.sendKeys('input#rex-maintenance-secret-secret-we-got-a-secret', [browser.Keys.ENTER]);
        browser.pause(250);
        browser.click('.rex-page-main button.dropdown-toggle');
        browser.pause(250);
        browser.click('.rex-page-main #bs-select-1-0');
        browser.pause(250);
        browser.click('#rex-maintenance-save');
        browser.pause(250);
    });

    /**
     * close the browser
     */
    after(browser => {
        browser.end();
    });
});
