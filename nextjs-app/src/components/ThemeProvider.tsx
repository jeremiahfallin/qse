// nextjs-app/src/components/ThemeProvider.tsx
'use client';

import React, { createContext, useContext, useState, ReactNode } from 'react';
import { Theme } from '@radix-ui/themes';
import { defaultThemeOptions } from '@/themes'; // Assuming alias '@/' is configured for 'src'

// Define the shape of the context data
interface ThemeContextType {
  appearance: 'light' | 'dark';
  toggleAppearance: () => void;
  // Add other theme properties or setters here if needed in the future
}

// Create the context with a default value
const ThemeContext = createContext<ThemeContextType | undefined>(undefined);

// Define the props for the ThemeProvider
interface ThemeProviderProps {
  children: ReactNode;
}

export const AppThemeProvider: React.FC<ThemeProviderProps> = ({ children }) => {
  const [appearance, setAppearance] = useState<'light' | 'dark'>('light');

  const toggleAppearance = () => {
    setAppearance((prevAppearance) => (prevAppearance === 'light' ? 'dark' : 'light'));
  };

  // Combine default options with dynamic appearance
  const themeProps = {
    ...defaultThemeOptions,
    appearance: appearance,
  };

  return (
    <ThemeContext.Provider value={{ appearance, toggleAppearance }}>
      <Theme {...themeProps}>
        {children}
      </Theme>
    </ThemeContext.Provider>
  );
};

// Custom hook to use the ThemeContext
export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (context === undefined) {
    throw new Error('useTheme must be used within an AppThemeProvider');
  }
  return context;
};
