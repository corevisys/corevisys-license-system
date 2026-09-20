// WCAG 2.1 contrast audit for the public-site theme tokens.
// Reads the same values declared in resources/css/app.css and reports
// contrast ratios for the combinations the public pages actually render.
//
// Usage: node scripts/wcag-contrast.mjs

const themes = {
    terminal: {
        amber: '#E8A33D',
        bg: '#100E0C',
        secondary: '#93897A',
        muted: '#5E574B',
        panel2: '#1B1712',
    },
    'dark-modern': {
        amber: '#3B82F6',
        bg: '#0F172A',
        secondary: '#CBD5E1',
        muted: '#94A3B8',
        panel2: '#171E2F',
    },
    'light-modern': {
        amber: '#2563EB',
        bg: '#F8FAFC',
        secondary: '#334155',
        muted: '#64748b',
        panel2: '#F8FAFC',
    },
    'solarized-dark': {
        amber: '#B58900',
        bg: '#002B36',
        secondary: '#93A1A1',
        muted: '#657B83',
        panel2: '#0B3F4B',
    },
    'tokyo-night': {
        amber: '#7AA2F7',
        bg: '#1A1B26',
        secondary: '#A9B1D6',
        muted: '#565F89',
        panel2: '#1F2335',
    },
};

// The ink used on every amber button across the public pages.
const ON_AMBER_INK = '#1A1305';

const hexToRgb = (hex) => {
    const value = hex.replace('#', '');
    return [
        parseInt(value.slice(0, 2), 16),
        parseInt(value.slice(2, 4), 16),
        parseInt(value.slice(4, 6), 16),
    ];
};

const linear = (channel) => {
    const c = channel / 255;
    return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
};

const luminance = (hex) => {
    const [r, g, b] = hexToRgb(hex).map(linear);
    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
};

const ratio = (fg, bg) => {
    const l1 = luminance(fg);
    const l2 = luminance(bg);
    const lighter = Math.max(l1, l2);
    const darker = Math.min(l1, l2);
    return (lighter + 0.05) / (darker + 0.05);
};

const verdict = (value, threshold) => (value >= threshold ? 'PASS' : 'FAIL');

const rows = [];

for (const [name, t] of Object.entries(themes)) {
    const buttonText = ratio(ON_AMBER_INK, t.amber);
    rows.push({
        theme: name,
        pair: `button ink ${ON_AMBER_INK} on amber ${t.amber}`,
        ratio: buttonText.toFixed(2),
        // Button text is >=14px and often not bold -> 4.5 normal-text threshold.
        requirement: '4.5 (normal text)',
        result: verdict(buttonText, 4.5),
    });

    const secondaryOnBg = ratio(t.secondary, t.bg);
    rows.push({
        theme: name,
        pair: `text-secondary ${t.secondary} on bg ${t.bg}`,
        ratio: secondaryOnBg.toFixed(2),
        requirement: '4.5 (normal text)',
        result: verdict(secondaryOnBg, 4.5),
    });

    const mutedOnBg = ratio(t.muted, t.bg);
    rows.push({
        theme: name,
        pair: `text-muted ${t.muted} on bg ${t.bg}`,
        ratio: mutedOnBg.toFixed(2),
        requirement: '4.5 (normal text)',
        result: verdict(mutedOnBg, 4.5),
    });

    const amberOnBg = ratio(t.amber, t.bg);
    rows.push({
        theme: name,
        pair: `amber ${t.amber} on bg ${t.bg}`,
        ratio: amberOnBg.toFixed(2),
        requirement: '4.5 (normal text)',
        result: verdict(amberOnBg, 4.5),
    });

    const secondaryOnPanel2 = ratio(t.secondary, t.panel2);
    rows.push({
        theme: name,
        pair: `text-secondary ${t.secondary} on panel-2 ${t.panel2}`,
        ratio: secondaryOnPanel2.toFixed(2),
        requirement: '4.5 (normal text)',
        result: verdict(secondaryOnPanel2, 4.5),
    });
}

const pad = (value, width) => String(value).padEnd(width);
console.log(pad('THEME', 16) + pad('PAIR', 46) + pad('RATIO', 8) + pad('REQUIRED', 20) + 'RESULT');
console.log('-'.repeat(105));
for (const row of rows) {
    console.log(
        pad(row.theme, 16) + pad(row.pair, 46) + pad(row.ratio, 8) + pad(row.requirement, 20) + row.result,
    );
}

const failures = rows.filter((row) => row.result === 'FAIL');
console.log('\n' + `${rows.length - failures.length}/${rows.length} combinations meet WCAG AA.`);
if (failures.length) {
    console.log('Failures:');
    for (const row of failures) {
        console.log(`  - ${row.theme}: ${row.pair} = ${row.ratio}`);
    }
}