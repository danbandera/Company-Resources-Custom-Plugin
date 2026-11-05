(function (wp) {
  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var RichText = wp.blockEditor.RichText;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var PanelBody = wp.components.PanelBody;
  var TextControl = wp.components.TextControl;
  var BaseControl = wp.components.BaseControl;
  var ColorPicker = wp.components.ColorPicker;

  // Only allow link protocols that are safe to render in saved HTML.
  function isSafeUrl(url) {
    if (!url) return '';
    return /^(https?:|mailto:|tel:|#|\/)/i.test(url) ? url : '';
  }

  registerBlockType('simple-block/cta-box', {
    edit: function (props) {
      var attrs = props.attributes;
      var set = props.setAttributes;

      return el(
        wp.element.Fragment,
        null,

        // Sidebar settings.
        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            { title: 'Button settings' },
            el(TextControl, {
              label: 'Button URL',
              type: 'url',
              value: attrs.buttonUrl,
              onChange: function (value) {
                set({ buttonUrl: value });
              },
            }),
            el(
              BaseControl,
              { label: 'Button Color' },
              el(ColorPicker, {
                color: attrs.buttonColor,
                defaultValue: '#1e1e1e',
                onChange: function (value) {
                  set({ buttonColor: value });
                },
              })
            )
          )
        ),

        // Block in the editor.
        el(
          'div',
          useBlockProps({ className: 'sb-cta' }),
          el(RichText, {
            tagName: 'h3',
            placeholder: 'Title…',
            value: attrs.title,
            onChange: function (value) {
              set({ title: value });
            },
          }),
          el(RichText, {
            tagName: 'p',
            placeholder: 'Write something…',
            value: attrs.content,
            onChange: function (value) {
              set({ content: value });
            },
          }),
          el(RichText, {
            tagName: 'span',
            className: 'sb-cta__button',
            style: { backgroundColor: attrs.buttonColor || '#1e1e1e' },
            placeholder: 'Button text',
            value: attrs.buttonText,
            allowedFormats: [],
            onChange: function (value) {
              set({ buttonText: value });
            },
          })
        )
      );
    },

    save: function (props) {
      var attrs = props.attributes;
      var safeUrl = isSafeUrl(attrs.buttonUrl);

      return el(
        'div',
        useBlockProps.save({ className: 'sb-cta' }),
        el(RichText.Content, { tagName: 'h3', value: attrs.title }),
        el(RichText.Content, { tagName: 'p', value: attrs.content }),
        safeUrl &&
        el('a', { className: 'sb-cta__button', href: safeUrl, style: { backgroundColor: attrs.buttonColor } }, attrs.buttonText)
      );
    },
  });
})(window.wp);