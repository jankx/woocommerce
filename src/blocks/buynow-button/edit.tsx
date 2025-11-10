import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import {
  InspectorControls,
  useBlockProps,
} from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  ToggleControl,
  Notice,
} from '@wordpress/components';
import metadata from './block.json';
import type { BuyNowButtonAttributes } from './types';

interface EditProps {
  attributes: BuyNowButtonAttributes;
  setAttributes: (attributes: Partial<BuyNowButtonAttributes>) => void;
}

const blockName = metadata.name;

export default function Edit({
  attributes,
  setAttributes,
}: EditProps): JSX.Element {
  const {
    text = __('Buy Now', 'jankx'),
    openInNewTab = false,
    relNoFollow = false,
    relSponsored = false,
  } = attributes;

  const blockProps = useBlockProps({
    className: 'jankx-buynow-button',
  });

  return (
    <>
      <InspectorControls>
        <PanelBody
          title={__('Button Settings', 'jankx')}
          initialOpen
        >
          <TextControl
            label={__('Button Label', 'jankx')}
            value={text}
            onChange={(value: string) => setAttributes({ text: value })}
            help={__('Text displayed inside the button.', 'jankx')}
          />
          <ToggleControl
            label={__('Open in new tab', 'jankx')}
            checked={openInNewTab}
            onChange={(value: boolean) => setAttributes({ openInNewTab: value })}
          />
          <ToggleControl
            label={__('Add rel="nofollow"', 'jankx')}
            checked={relNoFollow}
            onChange={(value: boolean) => setAttributes({ relNoFollow: value })}
          />
          <ToggleControl
            label={__('Add rel="sponsored"', 'jankx')}
            checked={relSponsored}
            onChange={(value: boolean) => setAttributes({ relSponsored: value })}
          />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <Notice status="info" isDismissible={false}>
          {__(
            'The preview reflects the current product context. A placeholder button is shown when no product can be resolved.',
            'jankx',
          )}
        </Notice>
        <ServerSideRender block={blockName} attributes={attributes} />
      </div>
    </>
  );
}

