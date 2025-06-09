# Project Documentation

This directory contains various documentation files related to the Next.js application, its structure, API design, and conversion notes from the original PHP codebase.

## Contents

-   **`api_documentation_strategy.md`**:
    Outlines the strategy for API documentation, discussing current manual methods and future recommendations like OpenAPI/Swagger.

-   **`api_routes.md`**:
    (Formerly `src/utils/apiRouteNotes.ts`) Detailed notes on all planned and implemented API routes, including their purpose, request/response structure, authentication requirements, and the PHP files they are intended to replace or model.

-   **`manual_api_testing_checklist.md`**:
    A checklist for manually testing implemented API routes, covering various success and error conditions.

-   **`project_structure.md`**:
    Describes the high-level directory structure of the Next.js application and the purpose of key folders.

-   **`refinement_notes.md`**:
    A collection of notes on areas for potential code refactoring, improvements, and outstanding TODOs identified during development.

-   **`security_notes.md`**:
    (Formerly `src/utils/securityNotes.ts`) Important considerations regarding application security, with a strong focus on the password migration strategy from the old system to the new bcrypt-based hashing, as well as other security best practices.

-   **`ui_component_conversion_notes.md`**:
    (Formerly `src/utils/conversionNotes.ts`) Notes mapping PHP functions (that were primarily responsible for UI generation or used template engines) to their corresponding React components to be implemented in the Next.js application.

Please refer to these documents for insights into the application's design, planned features, and development progress.
