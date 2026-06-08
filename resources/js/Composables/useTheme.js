import { ref, onBeforeMount } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

export function useTheme() {
    const currentTheme = ref('dark-modern');
    const page = usePage();

    const themes = [
        { id: 'dark-modern', name: 'Dark Modern' },
        { id: 'light-modern', name: 'Light Modern' },
        { id: 'solarized-dark', name: 'Solarized Dark' },
        { id: 'tokyo-night', name: 'Tokyo Night' },
    ];

    const initTheme = () => {
        const userPref = page.props.auth.user?.theme_preference;
        // Default theme from backend settings or fallback
        const defaultTheme = page.props.settings?.default_theme || 'dark-modern';

        if (userPref) {
            currentTheme.value = userPref;
        } else {
            // Browser detection
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                currentTheme.value = 'dark-modern';
            } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
                currentTheme.value = 'light-modern';
            } else {
                currentTheme.value = defaultTheme;
            }
        }
        
        // Apply immediately
        updateDom(currentTheme.value);
    };

    const updateDom = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
    };

    const switchTheme = (themeId) => {
        currentTheme.value = themeId;
        updateDom(themeId);
        
        // Persist to backend if user is authenticated
        if (page.props.auth.user) {
            router.post(route('profile.update-theme'), { theme: themeId }, {
                preserveScroll: true,
                preserveState: true,
            });
        }
    };
    
    // Toggle function for simple UI (e.g. cycle through or just light/dark)
    const toggleTheme = () => {
        const currentIndex = themes.findIndex(t => t.id === currentTheme.value);
        const nextIndex = (currentIndex + 1) % themes.length;
        switchTheme(themes[nextIndex].id);
    };

    return {
        currentTheme,
        themes,
        initTheme,
        switchTheme,
        toggleTheme
    };
}
