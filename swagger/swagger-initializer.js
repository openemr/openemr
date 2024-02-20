window.onload = function() {
    // Begin Swagger UI call region
    const ui = SwaggerUIBundle({
    url: "openemr-api.yaml",
    dom_id: '#swagger-ui',
    oauth2RedirectUrl: `${window.location.protocol}//${window.location.host}${window.location.pathname.replace(/[^/]*$/, '')}oauth2-redirect.html`,
    deepLinking: true,
    presets: [
        SwaggerUIBundle.presets.apis
    ],
    defaultModelsExpandDepth: -1
    });
    ui.initOAuth({
    usePkceWithAuthorizationCodeGrant: true
    });
    // End Swagger UI call region

    window.ui = ui;
};