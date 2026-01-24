import React, { useEffect, useImperativeHandle, useRef, forwardRef } from 'react';

const DEFAULT_TOOLBAR = [
    ['style', ['style']],
    ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
    ['para', ['ul', 'ol', 'paragraph']],
    ['insert', ['link', 'picture', 'video']],
    ['view', ['fullscreen', 'codeview', 'help']]
];

const SummernoteEditor = forwardRef(({
    value = '',
    onChange,
    placeholder = '',
    height = 180,
    minHeight = null,
    maxHeight = null,
    toolbar = DEFAULT_TOOLBAR,
    className = '',
}, ref) => {
    const editorRef = useRef(null);
    const lastValue = useRef(value || '');

    useImperativeHandle(ref, () => ({
        focus: () => {
            const $ = window.$ || window.jQuery;
            if (!$ || !editorRef.current) {
                return;
            }
            const $el = $(editorRef.current);
            if ($el.data('summernote')) {
                $el.summernote('focus');
            }
        }
    }), []);

    useEffect(() => {
        const $ = window.$ || window.jQuery;
        if (!$ || !$.fn || !$.fn.summernote) {
            return undefined;
        }

        const $el = $(editorRef.current);

        $el.summernote({
            placeholder,
            height,
            minHeight,
            maxHeight,
            toolbar,
            callbacks: {
                onChange: (contents) => {
                    lastValue.current = contents;
                    if (onChange) {
                        onChange(contents);
                    }
                }
            }
        });

        $el.summernote('code', value || '');
        lastValue.current = value || '';

        return () => {
            if ($el.data('summernote')) {
                $el.summernote('destroy');
            }
        };
    }, []);

    useEffect(() => {
        const nextValue = value || '';
        if (nextValue === lastValue.current) {
            return;
        }

        const $ = window.$ || window.jQuery;
        if (!$ || !$.fn || !$.fn.summernote || !editorRef.current) {
            return;
        }

        const $el = $(editorRef.current);
        if ($el.data('summernote')) {
            $el.summernote('code', nextValue);
            lastValue.current = nextValue;
        }
    }, [value]);

    return (
        <div className={className} ref={editorRef} />
    );
});

SummernoteEditor.displayName = 'SummernoteEditor';

export default SummernoteEditor;
