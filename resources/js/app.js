

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
        wrapper.className = 'code-block-wrapper my-6 rounded-xl overflow-hidden border border-gray-700 bg-gray-900 shadow-sm';

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

document.addEventListener('DOMContentLoaded', () => {
    initCodeBlocks();
});

Alpine.start();
