import postcss from 'postcss';
import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';
import tailwindConfig from './tailwind.config.js';

const twProcessor = postcss([tailwindcss(tailwindConfig)]);

export default {
    plugins: [
        {
            postcssPlugin: 'selective-tailwind',
            async Once(root, helpers) {
                const cssContent = root.source?.input?.css || '';
                const filePath = helpers.result.opts.from || '';

                if (
                    cssContent.includes('@tailwind') ||
                    filePath.includes('tailwind.css')
                ) {
                    const result = await twProcessor.process(root.clone(), { from: filePath });
                    root.removeAll();
                    root.append(result.root);
                }
            },
        },
        autoprefixer(),
    ],
};
