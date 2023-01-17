// Refer to the online docs for more details:
// https://nightwatchjs.org/gettingstarted/configuration/
//

//  _   _  _         _      _                     _          _
// | \ | |(_)       | |    | |                   | |        | |
// |  \| | _   __ _ | |__  | |_ __      __  __ _ | |_   ___ | |__
// | . ` || | / _` || '_ \ | __|\ \ /\ / / / _` || __| / __|| '_ \
// | |\  || || (_| || | | || |_  \ V  V / | (_| || |_ | (__ | | | |
// \_| \_/|_| \__, ||_| |_| \__|  \_/\_/   \__,_| \__| \___||_| |_|
//             __/ |
//            |___/

module.exports = {
    // An array of folders (excluding subfolders) where your tests are located;
    // if this is not specified, the test source must be passed as the second argument to the test runner.
    src_folders: ['tests/e2e'],

    // See https://nightwatchjs.org/guide/concepts/page-object-model.html
    // page_objects_path: ['test/e2e/page-objects'],

    // See https://nightwatchjs.org/guide/extending-nightwatch/adding-custom-commands.html
    // custom_commands_path: ['test/e2e/custom-commands'],

    // See https://nightwatchjs.org/guide/extending-nightwatch/adding-custom-assertions.html
    // custom_assertions_path: ['test/e2e/custom-assertions'],

    // See https://nightwatchjs.org/guide/extending-nightwatch/adding-plugins.html
    // plugins: [],

    // See https://nightwatchjs.org/guide/concepts/test-globals.html
    globals_path: '',

    webdriver: {},

    test_settings: {
        default: {
            disable_error_log: false,
            launch_url: '${LAUNCH_URL}',

            screenshots: {
                enabled: false,
                on_failure: false,
                path: "./screens"
            },

            desiredCapabilities: {
                browserName: 'firefox'
            },

            webdriver: {
                start_process: true,
                server_path: ''
            },

        },

        firefox: {
            desiredCapabilities: {
                browserName: 'firefox',
                alwaysMatch: {
                    acceptInsecureCerts: true,
                    'moz:firefoxOptions': {
                        args: [
                            // '-headless',
                            // '-verbose'
                        ]
                    }
                }
            },
            webdriver: {
                start_process: true,
                server_path: '',
                cli_args: [
                    // very verbose geckodriver logs
                    // '-vv'
                ]
            }
        },

    }
};
