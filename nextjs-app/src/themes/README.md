# Theme Management for the Next.js Application

This directory (`nextjs-app/src/themes`) contains the configuration and assets for the visual themes used in the Next.js application. The theming is built upon Radix UI Themes.

## Structure

-   **`index.ts`**: Exports all theme configurations. This is the entry point for accessing themes.
-   **`default.ts`**: Defines the properties for the default theme used across the application. This includes settings like `accentColor`, `grayColor`, `panelBackground`, `scaling`, and `radius`.

## Modifying the Theme

To modify the application's theme (e.g., change the accent color):

1.  Open `nextjs-app/src/themes/default.ts`.
2.  Edit the `defaultThemeOptions` object with desired Radix UI theme properties. For available options, refer to the [Radix UI Themes documentation](https://www.radix-ui.com/themes/docs/overview/theming#all-theme-options).

Example (`default.ts`):
```typescript
export const defaultThemeOptions = {
  accentColor: 'crimson', // Changed from 'blue'
  grayColor: 'slate',
  panelBackground: 'solid',
  scaling: '100%',
  radius: 'large',
};
```

## Theme Provider

The application uses a custom `AppThemeProvider` located in `nextjs-app/src/components/ThemeProvider.tsx`. This provider:
- Wraps the standard Radix UI `<Theme>` component.
- Manages the current theme state (e.g., light/dark mode `appearance`).
- Provides a `useTheme` hook to access the current theme state (e.g., `appearance`) and functions to modify it (e.g., `toggleAppearance`).

## Adding New Theme Configurations (Future Enhancement)

While the current setup primarily uses a single configurable default theme, the structure is prepared for extension. If you wanted to introduce multiple, switchable named themes:

1.  **Create a new theme file**: e.g., `myNewTheme.ts` in this directory.
    ```typescript
    // nextjs-app/src/themes/myNewTheme.ts
    export const myNewThemeOptions = {
      accentColor: 'green',
      grayColor: 'olive',
      // ... other properties
    };
    ```
2.  **Export from `index.ts`**:
    ```typescript
    // nextjs-app/src/themes/index.ts
    export * from './default';
    export * from './myNewTheme';
    ```
3.  **Extend `AppThemeProvider`**: Modify `AppThemeProvider.tsx` to manage a selection of themes and allow switching between `defaultThemeOptions`, `myNewThemeOptions`, etc. (This part requires further implementation in the provider itself).
