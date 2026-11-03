

import Alpine from 'alpinejs';
import hljs from 'highlight.js/lib/core';
import php from 'highlight.js/lib/languages/php';
import javascript from 'highlight.js/lib/languages/javascript';
import typescript from 'highlight.js/lib/languages/typescript';
import xml from 'highlight.js/lib/languages/xml';
import css from 'highlight.js/lib/languages/css';
import sql from 'highlight.js/lib/languages/sql';
import bash from 'highlight.js/lib/languages/bash';
import json from 'highlight.js/lib/languages/json';
import yaml from 'highlight.js/lib/languages/yaml';
import python from 'highlight.js/lib/languages/python';
import markdown from 'highlight.js/lib/languages/markdown';
import dockerfile from 'highlight.js/lib/languages/dockerfile';

hljs.registerLanguage('php', php);
hljs.registerLanguage('javascript', javascript);
hljs.registerLanguage('js', javascript);
hljs.registerLanguage('typescript', typescript);
hljs.registerLanguage('ts', typescript);
hljs.registerLanguage('xml', xml);
hljs.registerLanguage('html', xml);
hljs.registerLanguage('css', css);
hljs.registerLanguage('sql', sql);
hljs.registerLanguage('bash', bash);
hljs.registerLanguage('sh', bash);
hljs.registerLanguage('shell', bash);
hljs.registerLanguage('json', json);
hljs.registerLanguage('yaml', yaml);
hljs.registerLanguage('yml', yaml);
hljs.registerLanguage('python', python);
hljs.registerLanguage('py', python);
hljs.registerLanguage('markdown', markdown);
hljs.registerLanguage('md', markdown);
hljs.registerLanguage('dockerfile', dockerfile);
hljs.registerLanguage('docker', dockerfile);

window.Alpine = Alpine;

function initCodeBlocks() {
    document.querySelectorAll('.markdown-content pre code').forEach((codeBlock) => {
        const pre = codeBlock.parentElement;
        if (!pre || pre.dataset.codeBlockInitialized) {
            return;
        }
        pre.dataset.codeBlockInitialized = 'true';

        // Apply syntax highlighting
        hljs.highlightElement(codeBlock);

        // Determine language label
        let lang = 'CODE';
        const langMatch = codeBlock.className.match(/language-([a-zA-Z0-9_-]+)/);
        if (langMatch) {
            lang = langMatch[1].toUpperCase();
        }

        // Wrapper container
        const wrapper = document.createElement('div');
        wrapper.className = 'code-block-wrapper my-6 rounded-xl overflow-hidden border border-gray-700 dark:border-gray-600 bg-gray-900 shadow-sm';

        // Header with language pill and copy button
        const header = document.createElement('div');
        header.className = 'flex items-center justify-between px-4 py-2 bg-gray-800 text-gray-300 text-xs font-mono border-b border-gray-700 select-none';

        const langBadge = document.createElement('span');
        langBadge.className = 'font-semibold tracking-wider text-gray-400';
        langBadge.textContent = lang;

        const copyBtn = document.createElement('button');
        copyBtn.type = 'button';
        copyBtn.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-gray-700/60 hover:bg-gray-700 text-gray-300 hover:text-white transition duration-150 cursor-pointer text-xs';
        copyBtn.innerHTML = `
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            <span>Copy</span>
        `;

        copyBtn.addEventListener('click', async () => {
            const codeToCopy = codeBlock.textContent || '';
            let copied = false;
            if (navigator.clipboard && window.isSecureContext) {
                try {
                    await navigator.clipboard.writeText(codeToCopy);
                    copied = true;
                } catch {
                    copied = false;
                }
            }
            if (!copied) {
                const textarea = document.createElement('textarea');
                textarea.value = codeToCopy;
                textarea.style.position = 'fixed';
                textarea.style.left = '-9999px';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                try {
                    copied = document.execCommand('copy');
                } catch {
                    copied = false;
                }
                document.body.removeChild(textarea);
            }

            if (copied) {
                copyBtn.innerHTML = `
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-emerald-400 font-medium">Copied!</span>
                `;
                setTimeout(() => {
                    copyBtn.innerHTML = `
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <span>Copy</span>
                    `;
                }, 2000);
            }
        });

        header.appendChild(langBadge);
        header.appendChild(copyBtn);

        pre.parentNode.insertBefore(wrapper, pre);
        wrapper.appendChild(header);
        wrapper.appendChild(pre);

        pre.className = (pre.className ? pre.className + ' ' : '') + 'p-4 overflow-x-auto text-sm leading-relaxed text-gray-100 font-mono bg-gray-900 m-0';
    });
}

/**
 * Global Theme System (Vanilla JavaScript)
 */
const THEME_STORAGE_KEY = 'theme';

function getStoredThemePreference() {
    try {
        return localStorage.getItem(THEME_STORAGE_KEY) || 'system';
    } catch {
        return 'system';
    }
}

function isSystemDark() {
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
}

export const themeManager = {
    getTheme() {
        return getStoredThemePreference();
    },

    setTheme(theme) {
        if (!['light', 'dark', 'system'].includes(theme)) {
            theme = 'system';
        }

        try {
            localStorage.setItem(THEME_STORAGE_KEY, theme);
        } catch {
            // Graceful fallback if storage restricted
        }

        this.applyTheme(theme);
        this.updateToggles(theme);

        window.dispatchEvent(
            new CustomEvent('theme-changed', {
                detail: {
                    theme,
                    resolvedTheme: this.getResolvedTheme(theme)
                }
            })
        );
    },

    getResolvedTheme(theme = this.getTheme()) {
        if (theme === 'system') {
            return isSystemDark() ? 'dark' : 'light';
        }
        return theme === 'dark' ? 'dark' : 'light';
    },

    applyTheme(theme = this.getTheme()) {
        const resolved = this.getResolvedTheme(theme);
        if (resolved === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    },

    toggleTheme() {
        const current = this.getTheme();
        if (current === 'light') {
            this.setTheme('dark');
        } else if (current === 'dark') {
            this.setTheme('system');
        } else {
            this.setTheme('light');
        }
    },

    updateToggles(theme = this.getTheme()) {
        const resolved = this.getResolvedTheme(theme);

        document.querySelectorAll('[data-theme-toggle]').forEach((toggleRoot) => {
            // Update active icon on toggle trigger
            const lightIcon = toggleRoot.querySelector('[data-theme-icon="light"]');
            const darkIcon = toggleRoot.querySelector('[data-theme-icon="dark"]');
            const systemIcon = toggleRoot.querySelector('[data-theme-icon="system"]');

            if (lightIcon) lightIcon.classList.toggle('hidden', theme !== 'light');
            if (darkIcon) darkIcon.classList.toggle('hidden', theme !== 'dark');
            if (systemIcon) systemIcon.classList.toggle('hidden', theme !== 'system');

            // Update active checkmarks and selected state on buttons
            toggleRoot.querySelectorAll('[data-set-theme]').forEach((optionBtn) => {
                const target = optionBtn.getAttribute('data-set-theme');
                const isSelected = target === theme;

                optionBtn.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                optionBtn.classList.toggle('bg-indigo-50', isSelected);
                optionBtn.classList.toggle('text-indigo-700', isSelected);
                optionBtn.classList.toggle('dark:bg-indigo-950/60', isSelected);
                optionBtn.classList.toggle('dark:text-indigo-300', isSelected);
                optionBtn.classList.toggle('font-semibold', isSelected);

                const checkmark = optionBtn.querySelector('[data-theme-check]');
                if (checkmark) {
                    checkmark.classList.toggle('opacity-100', isSelected);
                    checkmark.classList.toggle('opacity-0', !isSelected);
                }
            });
        });
    },

    init() {
        this.applyTheme();
        this.updateToggles();

        // Listen for OS scheme transitions when user theme is 'system'
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            const handleMediaChange = () => {
                if (this.getTheme() === 'system') {
                    this.applyTheme('system');
                    this.updateToggles('system');
                    window.dispatchEvent(
                        new CustomEvent('theme-changed', {
                            detail: {
                                theme: 'system',
                                resolvedTheme: isSystemDark() ? 'dark' : 'light'
                            }
                        })
                    );
                }
            };

            if (mediaQuery.addEventListener) {
                mediaQuery.addEventListener('change', handleMediaChange);
            } else if (mediaQuery.addListener) {
                mediaQuery.addListener(handleMediaChange);
            }
        }

        // Vanilla JS Theme Toggle Menu interactions
        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-theme-menu-trigger]');
            if (trigger) {
                event.stopPropagation();
                const container = trigger.closest('[data-theme-toggle]');
                const menu = container ? container.querySelector('[data-theme-menu]') : null;
                if (menu) {
                    const isExpanded = trigger.getAttribute('aria-expanded') === 'true';
                    // Close all menus first
                    document.querySelectorAll('[data-theme-menu]').forEach((m) => m.classList.add('hidden'));
                    document.querySelectorAll('[data-theme-menu-trigger]').forEach((t) => t.setAttribute('aria-expanded', 'false'));

                    if (!isExpanded) {
                        menu.classList.remove('hidden');
                        trigger.setAttribute('aria-expanded', 'true');
                    }
                }
                return;
            }

            const optionBtn = event.target.closest('[data-set-theme]');
            if (optionBtn) {
                const selected = optionBtn.getAttribute('data-set-theme');
                this.setTheme(selected);
                // Close parent dropdown menu
                const menu = optionBtn.closest('[data-theme-menu]');
                if (menu) {
                    menu.classList.add('hidden');
                    const container = menu.closest('[data-theme-toggle]');
                    const triggerBtn = container ? container.querySelector('[data-theme-menu-trigger]') : null;
                    if (triggerBtn) {
                        triggerBtn.setAttribute('aria-expanded', 'false');
                        triggerBtn.focus();
                    }
                }
                return;
            }

            // Close all open theme menus on click outside
            if (!event.target.closest('[data-theme-toggle]')) {
                document.querySelectorAll('[data-theme-menu]').forEach((menu) => menu.classList.add('hidden'));
                document.querySelectorAll('[data-theme-menu-trigger]').forEach((t) => t.setAttribute('aria-expanded', 'false'));
            }
        });

        // Keyboard navigation (Escape, ArrowDown, ArrowUp, Home, End)
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                const openMenus = document.querySelectorAll('[data-theme-menu]:not(.hidden)');
                openMenus.forEach((menu) => {
                    menu.classList.add('hidden');
                    const container = menu.closest('[data-theme-toggle]');
                    const triggerBtn = container ? container.querySelector('[data-theme-menu-trigger]') : null;
                    if (triggerBtn) {
                        triggerBtn.setAttribute('aria-expanded', 'false');
                        triggerBtn.focus();
                    }
                });
                return;
            }

            const toggleContainer = event.target.closest('[data-theme-toggle]');
            if (!toggleContainer) {
                return;
            }

            const menu = toggleContainer.querySelector('[data-theme-menu]');
            const triggerBtn = toggleContainer.querySelector('[data-theme-menu-trigger]');
            if (!menu || !triggerBtn) {
                return;
            }

            const items = Array.from(menu.querySelectorAll('[data-set-theme]'));
            if (items.length === 0) {
                return;
            }

            if (event.target === triggerBtn && (event.key === 'ArrowDown' || event.key === 'ArrowUp')) {
                event.preventDefault();
                menu.classList.remove('hidden');
                triggerBtn.setAttribute('aria-expanded', 'true');
                const targetItem = event.key === 'ArrowUp' ? items[items.length - 1] : items[0];
                targetItem.focus();
                return;
            }

            if (!menu.classList.contains('hidden')) {
                const currentIndex = items.indexOf(document.activeElement);
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    const nextIndex = currentIndex >= 0 ? (currentIndex + 1) % items.length : 0;
                    items[nextIndex].focus();
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    const prevIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1;
                    items[prevIndex].focus();
                } else if (event.key === 'Home') {
                    event.preventDefault();
                    items[0].focus();
                } else if (event.key === 'End') {
                    event.preventDefault();
                    items[items.length - 1].focus();
                }
            }
        });
    }
};

// Global API aliases
window.themeManager = themeManager;
window.getTheme = () => themeManager.getTheme();
window.setTheme = (theme) => themeManager.setTheme(theme);
window.applyTheme = (theme) => themeManager.applyTheme(theme);
window.toggleTheme = () => themeManager.toggleTheme();

// Initialize early
themeManager.init();

document.addEventListener('DOMContentLoaded', () => {
    initCodeBlocks();
    themeManager.updateToggles();
});

Alpine.start();
