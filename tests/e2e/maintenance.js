const got = require('got');

describe('maintenance addon testing', () => {
    /**
     * login
     */
    function login(browser) {
        /**
         * navigate to the login screen
         */
        browser.navigateTo('/redaxo/index.php');
        browser.pause(250);

        /**
         * check if the login input is present
         * add username
         */
        browser.assert.elementPresent('input[id=rex-id-login-user]');
        browser.sendKeys('input[id=rex-id-login-user]', 'nightwatch_username');
        browser.pause(250);

        /**
         * check if the password input is present
         * add password
         */
        browser.assert.elementPresent('input[id=rex-id-login-password]');
        browser.sendKeys('input[id=rex-id-login-password]', ['nightwatch_password', browser.Keys.ENTER]);
        browser.pause(250);
    }

    /**
     * logout
     */
    function logout(browser) {
        browser.pause(200);
        browser.click('.navbar-nav a.rex-logout');
    }

    beforeEach(browser => {
        login(browser);

        /**
         * check if we are logged in to the backend
         */
        browser.assert.urlContains('/redaxo/index.php?page=structure');

        /**
         * Add a start article if empty...
         */
        browser.getText('css selector', 'section.rex-page-section:last-of-type table tbody tr:last-of-type td:nth-of-type(3)', function (result) {
            if (result.value === '') {
                browser.navigateTo('/redaxo/index.php?page=structure&category_id=0&article_id=0&clang=1&function=add_art&artstart=0');
                browser.sendKeys('input[name=article-name]', ['nightwatch_test_start_article', browser.Keys.ENTER]);
                browser.waitForElementPresent('#rex-message-container .alert.alert-success');
            }
        });
    });

    function activateMaintenance(browser) {
        /**
         * navigate to the settings page
         * activate maintenance
         */
        browser.navigateTo('/redaxo/index.php?page=maintenance/frontend');
        browser.click('.rex-page-main button[data-id="deakt-front"]');
        browser.click('.rex-page-main #bs-select-1-1');
    }

    it('maintenance url secret - 503', function (browser) {
        activateMaintenance(browser);

        /**
         * set secret
         */
        browser.updateValue('input#rex-maintenance-secret-secret-we-got-a-secret', 'nightwatch_secret');

        /**
         * set 503 response code
         */
        browser.click('.rex-page-main button[data-id="responsecode"]');
        browser.click('.rex-page-main #bs-select-3-0');

        browser.click('#rex-maintenance-save');

        /**
         * logout
         */
        logout(browser);

        /**
         * navigate to the frontend
         */
        browser.navigateTo(this.settings.launchUrl);

        /**
         * test if maintenance is active
         */
        browser.assert.elementPresent('.maintenance-container', 'maintenance-container should exist');
        browser.assert.textContains('.maintenance-error-title', 'Maintenance', 'maintenance-error-title should contain "Maintenance"');
        browser.assert.textContains('.maintenance-error-message', 'This website is temporarily unavailable', 'maintenance-error-message should contain "This website is temporarily unavailable"');
        browser.perform(async () => {
            try {
                const response = await got(browser.launchUrl, { followRedirect: false });
                browser.assert.equal(response.statusCode, 503, 'Status code should be 503');
            } catch (error) {
                browser.assert.equal(error.response.statusCode, 503, 'Status code should be 503');
            }
            return true;
        });

        /**
         * test maintenance secret
         */
        browser.navigateTo('/?secret=nightwatch_secret');
        browser.perform(async () => {
            try {
                const response = await got(browser.launchUrl + '?secret=nightwatch_secret', { followRedirect: false });
                browser.assert.equal(response.statusCode, 200, 'Status code should be 200');
            } catch (error) {
                browser.assert.equal(error.response.statusCode, 200, 'Status code should be 200');
            }
            return true;
        });
        browser.assert.not.elementPresent('.maintenance-container', 'maintenance-container should not exist');
    })

    it('maintenance url secret - 403', function (browser) {
        activateMaintenance(browser);

        /**
         * set secret
         */
        browser.updateValue('input#rex-maintenance-secret-secret-we-got-a-secret', 'nightwatch_secret');

        /**
         * set 403 response code
         */
        browser.click('.rex-page-main button[data-id="responsecode"]');
        browser.click('.rex-page-main #bs-select-3-1');

        browser.click('#rex-maintenance-save');

        /**
         * logout
         */
        logout(browser);

        /**
         * navigate to the frontend
         */
        browser.navigateTo(this.settings.launchUrl);

        /**
         * test if maintenance is active
         */
        browser.assert.elementPresent('.maintenance-container', 'maintenance-container should exist');
        browser.assert.textContains('.maintenance-error-title', 'Maintenance', 'maintenance-error-title should contain "Maintenance"');
        browser.assert.textContains('.maintenance-error-message', 'This website is temporarily unavailable', 'maintenance-error-message should contain "This website is temporarily unavailable"');
        browser.perform(async () => {
            try {
                const response = await got(browser.launchUrl, { followRedirect: false });
                browser.assert.equal(response.statusCode, 403, 'Status code should be 403');
            } catch (error) {
                browser.assert.equal(error.response.statusCode, 403, 'Status code should be 403');
            }
            return true;
        });

        /**
         * test maintenance secret
         */
        browser.navigateTo('/?secret=nightwatch_secret');
        browser.perform(async () => {
            try {
                const response = await got(browser.launchUrl + '?secret=nightwatch_secret', { followRedirect: false });
                browser.assert.equal(response.statusCode, 200, 'Status code should be 200');
            } catch (error) {
                browser.assert.equal(error.response.statusCode, 200, 'Status code should be 200');
            }
            return true;
        });
        browser.assert.not.elementPresent('.maintenance-container', 'maintenance-container should not exist');
    })

    it('maintenance password - 503', function (browser) {
        /**
         * navigate to the settings page
         * activate maintenance
         */
        activateMaintenance(browser);

        /**
         * set secret
         */
        browser.updateValue('input#rex-maintenance-secret-secret-we-got-a-secret', 'nightwatch_secret');

        /**
         * set 503 response code
         */
        browser.click('.rex-page-main button[data-id="responsecode"]');
        browser.click('.rex-page-main #bs-select-3-0');

        /**
         * activate password
         */
        browser.click('.rex-page-main button[data-id="type"]');
        browser.click('.rex-page-main #bs-select-2-1');
        browser.click('#rex-maintenance-save');

        /**
         * logout
         */
        logout(browser);

        /**
         * navigate to the frontend
         */
        browser.navigateTo(this.settings.launchUrl);

        // /**
        //  * test if maintenance is active
        //  */
        browser.assert.elementPresent('.maintenance-pw-input', 'maintenance-pw-input should exist');
        browser.perform(async () => {
            try {
                const response = await got(browser.launchUrl, { followRedirect: false });
                browser.assert.equal(response.statusCode, 503, 'Status code should be 503');
            } catch (error) {
                browser.assert.equal(error.response.statusCode, 503, 'Status code should be 503');
            }
            return true;
        });
        browser.updateValue('.maintenance-pw-input', 'nightwatch_secret');
        browser.sendKeys('.maintenance-pw-input', [browser.Keys.ENTER]);
        browser.assert.not.elementPresent('.maintenance-pw-input', 'maintenance-pw-input should not exist');
    })

    it('maintenance password - 403', function (browser) {
        /**
         * navigate to the settings page
         * activate maintenance
         */
        activateMaintenance(browser);

        /**
         * set secret
         */
        browser.updateValue('input#rex-maintenance-secret-secret-we-got-a-secret', 'nightwatch_secret');

        /**
         * set 403 response code
         */
        browser.click('.rex-page-main button[data-id="responsecode"]');
        browser.click('.rex-page-main #bs-select-3-1');

        /**
         * activate password
         */
        browser.click('.rex-page-main button[data-id="type"]');
        browser.click('.rex-page-main #bs-select-2-1');
        browser.click('#rex-maintenance-save');

        /**
         * logout
         */
        logout(browser);

        /**
         * navigate to the frontend
         */
        browser.navigateTo(this.settings.launchUrl);

        // /**
        //  * test if maintenance is active
        //  */
        browser.assert.elementPresent('.maintenance-pw-input', 'maintenance-pw-input should exist');
        browser.perform(async () => {
            try {
                const response = await got(browser.launchUrl, { followRedirect: false });
                browser.assert.equal(response.statusCode, 403, 'Status code should be 403');
            } catch (error) {
                browser.assert.equal(error.response.statusCode, 403, 'Status code should be 403');
            }
            return true;
        });
        browser.updateValue('.maintenance-pw-input', 'nightwatch_secret');
        browser.sendKeys('.maintenance-pw-input', [browser.Keys.ENTER]);
        browser.assert.not.elementPresent('.maintenance-pw-input', 'maintenance-pw-input should not exist');
    })

    /**
     * reset maintenance settings
     */
    afterEach(browser => {
        /**
         * login
         */
        login(browser);

        browser.pause(200);

        /**
         * reset settings
         */
        browser.navigateTo('/redaxo/index.php?page=maintenance/frontend');
        browser.pause(200);
        browser.updateValue('input#rex-maintenance-secret-secret-we-got-a-secret', '');
        browser.sendKeys('input#rex-maintenance-secret-secret-we-got-a-secret', [browser.Keys.ENTER]);
        browser.pause(250);
        browser.click('.rex-page-main button.dropdown-toggle');
        browser.pause(250);
        browser.click('.rex-page-main #bs-select-1-0');
        browser.pause(250);
        browser.click('#rex-maintenance-save');
        browser.pause(250);

        logout(browser);
        browser.end();
    });

    /**
     * close the browser
     */
    after(browser => {
    });
});
