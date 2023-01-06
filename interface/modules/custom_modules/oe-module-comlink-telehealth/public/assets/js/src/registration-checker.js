
export function RegistrationChecker(scriptLocation)
{
    var checker = this;
    var timeoutId;
    var settings;
    var currentCheckCount = 0;
    var maxCheck = 10;

    this.checkRegistration = function()
    {
        if (currentCheckCount++ > maxCheck)
        {
            console.error("Failed to get a valid telehealth registration for user");
            return;
        }

        let location = scriptLocation + '?action=check_registration';

        window.top.restoreSession();
        window.fetch(location)
            .then(result => {
                if (!result.ok)
                {
                    throw new Error("Registration check failed");
                }
                return result.json();
            })
            .then(registrationSettings => {
                if (registrationSettings && registrationSettings.hasOwnProperty('errorCode')) {
                    if (registrationSettings.errorCode == 402) {
                        // user is not enrolled and so we will skip trying to register the user
                        checker.settings = {};
                    }
                }
                checker.settings = registrationSettings;
            })
            .catch(error => {
                console.error("Failed to execute check_registration action", error);
                timeoutId = setTimeout(checker.checkRegistration.bind(checker), 2000);
            });
    };
    return this;
}